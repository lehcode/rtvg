<?php
/**
 * Frontend videos controller
 * 
 * @author  Antony Repin
 * @uses    Xmltv_Controller_Action
 * @version $Id: VideosController.php,v 1.31 2013-04-11 06:21:34 developer Exp $
 *
 */
class VideosController extends Rtvg_Controller_Action
{
	
    /**
     * (non-PHPdoc)
     * @see Xmltv_Controller_Action::init()
     */
    public function init(){
        
        parent::init();
        
        if (!$this->_request->isXmlHttpRequest()){
        	$this->view->assign( 'pageclass', parent::pageclass(__CLASS__) );
        }
        
        $this->view->assign('hideSiteunder', true);
        
    }
	
	/**
	 * Index page
	 * Redirect to video page
	 */
	public function indexAction () {
		
		$this->_forward( 'show-video' );
		
	}
	
	/**
	 * Generate data for video page
	 * 
	 * @throws Zend_Exception
	 * @throws Zend_Controller_Action_Exception
	 */
	public function showVideoAction(){
		
		parent::validateRequest();
		
		$this->view->assign( 'pageclass', 'showVideo' );
        
		$conf = Zend_Registry::get('site_config')->videos->related;
		$ytConfig=array(
			'max_results' => (int)$conf->get('amount'),
			'safe_search' => $conf->get('safe_search'),
			'start_index' => (int)$conf->get('start_index'),
		);
		
		$youtube = new Xmltv_Youtube($ytConfig);
		$rtvgId = $this->input->getEscaped('id');
		
        if ($rtvgId) {
		    
		    $ytId = Xmltv_Youtube::decodeRtvgId( $rtvgId );
            
            if($ytId===false){
                return $this->render('not-found');
            }
            
            if ($this->cache->enabled){
                $t = (int)Zend_Registry::get( 'site_config' )->cache->youtube->main->lifetime;
			    $t>0 ? $this->cache->setLifetime($t): $this->cache->setLifetime(86400) ;
                (APPLICATION_ENV=='development') ? $this->cache->setLifetime(100) : $this->cache->setLifetime($t);
				$f = '/Youtube/ShowVideo/Main';
				$hash = $this->cache->getHash( $ytId );
                
                if ((bool)($mainVideo = $this->cache->load( $hash, 'Core', $f)) === false){
                    
                    $ytEntry = $youtube->fetchVideo( $ytId );
                    
                    if ($ytEntry===403){
                        $this->view->assign('hide_sidebar', 'both');
                        return $this->render('private-video');
                    } elseif($ytEntry===404){
                        $this->view->assign('hide_sidebar', 'both');
                        return $this->render('not-found');
                    }
                    
                    $mainVideo = $this->videosModel->parseYtEntry( $ytEntry);
                    $this->cache->save( $mainVideo, $hash, 'Core', $f);

                }
                 
			} else {
                $ytEntry = $youtube->fetchVideo( $ytId );
                $mainVideo = $this->videosModel->parseYtEntry( $ytEntry);
                if ($ytEntry===403){
                    $this->view->assign('hide_sidebar', 'both');
                    return $this->render('private-video');
                } elseif($ytEntry===404){
                    $this->view->assign('hide_sidebar', 'both');
                    return $this->render('not-found');
                }
                
			}
            
            $this->view->assign( 'main_video', $mainVideo );
			
			
			/*
			 * ################################################################################
			 * Try to load related videos list from cache 
			 * or fetch it from youtube.com if not found
			 * in either database or file cache
			 * ################################################################################
			 */
			$relatedAmt = (int)Zend_Registry::get('site_config')->videos->related->get('amount');
			$relatedVideos = array();
			
			if ($this->cache->enabled && APPLICATION_ENV!='development'){
			
				$this->cache->setLifetime( 86400 );
				$f = '/Youtube/ShowVideo/Related';
				$hash = Rtvg_Cache::getHash( 'related_'.$ytId);
			
				// Try to load videos from file cache
				if (($cached = $this->cache->load( $hash, 'Core', $f))!==false){
					$relatedVideos = $cached;
				} else {
					
				    // If was not found in file cache
				    $relatedVideos = false;
				    
                    // If was not found in DB cache either
				    if ($relatedVideos===false){
				        
						// Try to load related videos list from youtube
						if (($ytRelated = $youtube->fetchRelated( $ytId ))!==false) {
							foreach ($ytRelated as $ytEntry){
								if (($parsed = $this->videosModel->parseYtEntry($ytEntry))!==false){
									if (!empty($parsed['desc']) && (Xmltv_String::strlen($parsed['desc'])>=256)) {
									    $relatedVideos[] = $parsed;
									}
								}
							}
							$this->cache->save( $relatedVideos, $hash, 'Core', $f);
							
						} else {
						    $relatedVideos = null;
						}
				    }
			
				}
			
			} else {
			
				$ytRelated = $youtube->fetchRelated( $ytId );
				foreach ($ytRelated as $ytEntry){
					if (($parsed = $this->videosModel->parseYtEntry($ytEntry))!==false){
						if (!empty($parsed['desc']) && (Xmltv_String::strlen($parsed['desc'])>128)) {
							$relatedVideos[] = $parsed;
						}
					}
				}
			
			}
			
			$this->view->assign( 'related_videos', $relatedVideos );
			
		} else {
			$this->view->assign( 'main_video', null );
			$this->view->assign( 'related_videos', null );
		}
		
		
		//Данные для модуля самых популярных программ
		$top = $this->bcModel->topBroadcasts();
		$this->view->assign('bcTop', $top);
		
		$ads = $this->_helper->getHelper('AdCodes');
        $adCodes = $ads->direct(2, 300, 240);
        $this->view->assign('ads', $adCodes);
        
        //Channels top
        $amt = 10;
        $chTop = $this->channelsModel->topChannels($amt);
        $this->view->assign('channelsTop', $chTop );
        $this->view->assign('channelsTopAmt', $amt );
        
    }
	
	
	/**
	 * 
	 * @param unknown_type $video_feed
	 * @param unknown_type $yt_id
	 * @throws Zend_Exception
	 * @return multitype:Ambigous <multitype:, boolean, Zend_Db_Table_Row_Abstract> |boolean
	 */
	private function _saveRelatedVideos($video_feed, $yt_id){
		
	    $c=0;
	    $result = array();
	    
	    foreach ($video_feed as $v){
	        
	        if (!is_a($v, 'Zend_Gdata_YouTube_VideoEntry')) {
	            throw new Zend_Exception(parent::ERR_INVALID_INPUT.__METHOD__, 404);
	        } else {
	            
	            if (($parsed = $this->videosModel->parseYtEntry($v))!==false) {
	                $r[$c] = $parsed;
	                $r[$c]['yt_parent'] = $yt_id;
	                $result[$c] = $this->vCacheModel->saveRelatedVideo($r[$c]);
	                $c++;
	            }
	        }
	        
	    }
	    
	    if (count($result)) {
	    	return $result;
	    }
	    
	    return false;
	    
	    
	    	    
	}
	
	
}