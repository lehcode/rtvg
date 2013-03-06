<?php
/**
 * Frontend videos controller
 * 
 * @author  Antony Repin
 * @uses    Xmltv_Controller_Action
 * @version $Id: VideosController.php,v 1.20 2013-03-06 03:52:37 developer Exp $
 *
 */
class VideosController extends Xmltv_Controller_Action
{
	
    /**
     * (non-PHPdoc)
     * @see Xmltv_Controller_Action::init()
     */
    public function init(){
        parent::init();
        $this->cache->enabled = (bool)Zend_Registry::get('site_config')->cache->youtube->get('enabled');
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
		
		
		if ($this->requestParamsValid()){
			
			$this->view->assign( 'pageclass', 'show-video' );
			$conf = Zend_Registry::get('site_config')->videos->get(related);
			$ytConfig=array(
				'max_results' => (int)$conf->get('amount'),
				'safe_search' => $conf->get('safe_search'),
				'start_index' => (int)$conf->get('start_index'),
			);
			
			$youtube	 = new Xmltv_Youtube($ytConfig);
			$rtvgId = $this->input->getEscaped('id');
			
			if (APPLICATION_ENV=='development'){
				//var_dump($rtvgId);
				//die(__FILE__.': '.__LINE__);
			}
			
			if ($rtvgId) {
			    
			    $ytId = Xmltv_Youtube::decodeRtvgId( $rtvgId );
				
				if (APPLICATION_ENV=='development'){
					//var_dump($this->cache->enabled);
					//var_dump(parent::$videoCache);
					//var_dump($rtvgId);
					//var_dump($ytId);
					//die(__FILE__.': '.__LINE__);
				}
				
				
				/*
				 * ################################################################################
				 * Try to load current video from cache 
				 * or fetch it from youtube.com if not found
				 * in either database or file cache
				 * ################################################################################
				 */
				if ($this->cache->enabled){
				
				    $t = (int)Zend_Registry::get( 'site_config' )->cache->youtube->main->get( 'lifetime' );
				    $t>0 ? $this->cache->setLifetime($t): $this->cache->setLifetime(86400) ;
					$this->cache->setLocation(ROOT_PATH.'/cache');
					$f = '/Youtube/ShowVideo/Main';
					$hash = Rtvg_Cache::getHash( $ytId );
					
					if (parent::$videoCache===true){
					    
					    // Search in database cache if was not found in file cache
					    // and if database cache is enabled
					    if (($mainVideo = $this->vCacheModel->getVideo($rtvgId))===false){
					        
							// Try to load main video data from file cache
							if (($mainVideo = $this->cache->load( $hash, 'Core', $f))==false){
					    		
							    // Search Youtube service for video
					            if (($ytEntry = $youtube->fetchVideo( $ytId ))===false) {
					                // Video was not found at youtube
					                return $this->render('video-not-found');
					                //return true;
					            } 
					            
					            $mainVideo = $this->videosModel->parseYtEntry( $ytEntry);
					            $this->cache->save( $mainVideo, $hash, 'Core', $f);
					        }
					    }
					    
					    if (parent::$videoCache===true){
							$this->vCacheModel->saveMainVideo($mainVideo);
						}
						
					} else {
					    
						if (($mainVideo = $this->cache->load( $hash, 'Core', $f))==false){
					    		
						    // Search Youtube service for video
				            if (($ytEntry = $youtube->fetchVideo( $ytId ))===false) {
				                // Video was not found at youtube
				                return $this->render('video-not-found');
				                //return true;
				            } 
				            
				            $mainVideo = $this->videosModel->parseYtEntry( $ytEntry);
				            $this->cache->save( $mainVideo, $hash, 'Core', $f);
				        }
					    
					}
					 
				} else {
				
					if (($ytEntry = $youtube->fetchVideo( $ytId ))!==false) {
						$mainVideo = $this->videosModel->parseYtEntry($ytEntry);
					} else {
						return $this->render('video-not-found');
						//return true;
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
				
				if ($this->cache->enabled){
				
					$this->cache->setLocation( ROOT_PATH.'/cache');
					$f = '/Youtube/ShowVideo/Related';
					$hash = Rtvg_Cache::getHash( 'related_'.$ytId);
				
					// Try to load videos from file cache
					if (($cached = $this->cache->load( $hash, 'Core', $f))!==false){
						$relatedVideos = $cached;
					} else {
						
					    // If was not found in file cache
					    $relatedVideos = false;
					    
					    // If DB cache is enabled
					    if (parent::$videoCache===true){
					        // Try to search DB cache
					        if (($cached = $this->videosModel->dbCacheVideoRelatedVideos($ytId, $relatedAmt))!==false){
					        	$relatedVideos = $cached;
					        }
					    }
					    
					    // If was not found in DB cache either
					    if ($relatedVideos===false){
					        
							// Try to load related videos list from youtube
							if (($ytRelated = $youtube->fetchRelated( $ytId ))!==false) {
								foreach ($ytRelated as $ytEntry){
									if (($parsed = $this->videosModel->parseYtEntry($ytEntry))!==false){
										if (!empty($parsed['desc']) && (Xmltv_String::strlen($parsed['desc'])>=256)) {
										    if (parent::$videoCache===true){
												$this->vCacheModel->saveRelatedVideo($ytEntry, $ytId);
										    }
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
			
			
			/*
			 * #############################################################
			 * Данные для модуля самых популярных программ
			 * #############################################################
			 */
			$this->view->assign('top_programs', $this->topPrograms());
			
			/*
			 * #####################################################################
			 * Данные для модуля категорий каналов
			 * #####################################################################
			 */
			$this->view->assign('channels_cats', $this->getChannelsCategories());

			/*
			 * #####################################################################
			 * Данные для модуля популярных каналов
			 * #####################################################################
			 */
			$this->view->assign('featured_channels', $this->getFeaturedChannels(36));
			
		}
		
		
		
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
	        
	        if (APPLICATION_ENV=='development'){
	        	//var_dump($v);
	        	//die(__FILE__.': '.__LINE__);
	        }
	        
	        if (!is_a($v, 'Zend_Gdata_YouTube_VideoEntry')) {
	            throw new Zend_Exception(parent::ERR_INVALID_INPUT.__METHOD__, 404);
	        } else {
	            
	            if (($parsed = $this->videosModel->parseYtEntry($v))!==false) {
	                if (APPLICATION_ENV=='development'){
	                	//var_dump($result[$c]);
	                	//die(__FILE__.': '.__LINE__);
	                }
	                $r[$c] = $parsed;
	                $r[$c]['yt_parent'] = $yt_id;
	                $result[$c] = $this->vCacheModel->saveRelatedVideo($r[$c]);
	                $c++;
	            }
	        }
	        
	    }
	    
	    if (APPLICATION_ENV=='development'){
	    	//var_dump($result);
	    	//die(__FILE__.': '.__LINE__);
	    }
	    
	    if (count($result)) {
	    	return $result;
	    }
	    
	    return false;
	    
	    
	    	    
	}
	
	
}