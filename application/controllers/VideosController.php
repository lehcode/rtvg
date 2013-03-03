<?php
/**
 * Frontend videos controller
 * 
 * @author  Antony Repin
 * @uses    Xmltv_Controller_Action
 * @version $Id: VideosController.php,v 1.16 2013-03-03 23:34:13 developer Exp $
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
				
				if (parent::$videoCache===true){
				    
				    /* 
				     * ################################################################################
				     * Request cache model for main video data ( Xmltv_Model_Vcache::getVideo($rtvgId) )
				     * ################################################################################
				     */
				    // 1. Query database cache for main video
				    if (($cached = $this->vCacheModel->getVideo($rtvgId))!==false){
				        
				        $mainVideo = $cached;
				        
				    } else {
				        
				        if ($this->cache->enabled){
				            
				        	$this->cache->setLocation(ROOT_PATH.'/cache');
				        	$f = '/Youtube/ShowVideo/Main';
				        	$hash = md5( $ytId );
				        	
				        	// 2. Try to load main video data from file cache
				        	if (($cached = $this->cache->load( $hash, 'Core', $f))!==false){
				        	    $mainVideo = $cached;
				        	} else { // If not found in file cache
				        	    if (($ytEntry = $youtube->fetchVideo( $ytId ))!==false) {
				        	        $mainVideo = $this->videosModel->parseYtEntry( $ytEntry);
				        	        $this->cache->save( $mainVideo, $hash, 'Core', $f);
				        	    } else {
				        	        // Video not fount at youtube
				        	        $this->render('video-not-found');
				        	        return true;
				        	    }
				        	    
				        	}
				        } else {
				            
				            if (($ytEntry = $youtube->fetchVideo( $ytId ))!==false) {
				            	$mainVideo = $this->videosModel->parseYtEntry($ytEntry);
				            } else {
				            	$this->render('video-not-found');
				            	return true;
				            }
				            
				        }
				    }
				    
				} else {
					if (($ytEntry = $youtube->fetchVideo( $ytId ))!==false) {
		            	$mainVideo = $this->videosModel->parseYtEntry($ytEntry);
		            } else {
		            	$this->render('video-not-found');
		            	return true;
		            }
				}
				
				if (APPLICATION_ENV=='development'){
					//var_dump($mainVideo);
					//die(__FILE__.': '.__LINE__);
				}
				
				$this->view->assign( 'main_video', $mainVideo );
				
				
				/* 
				 * ##################################################
				 * 2. Related videos
				 * ##################################################
				 */
				$relatedAmt = (int)Zend_Registry::get('site_config')->videos->related->get('amount');
				$relatedVideos = array();
				
				if (parent::$videoCache===true){
					
				    // 1. Query database cache for main video
				    if (($cached = $this->videosModel->dbCacheVideoRelatedVideos($ytId, $relatedAmt))!==false){
				    	
				        $relatedVideos = $cached;
				         
				    } else { 
				        
				        // 2. Try to load main video data from file cache
				    	if ($this->cache->enabled){
				            
				            $this->cache->setLocation( ROOT_PATH.'/cache');
				            $f = '/Youtube/ShowVideo/Main';
				            $hash = Xmltv_Cache::getHash( 'relatedVideo_'.$ytId);
				            
				            // 3. Try to load main video data from file cache
				            if (($cached = $this->cache->load( $hash, 'Core', $f))!==false){
				                
				            	$relatedVideos = $cached;
				            	
				            } else { 
				                
				                // 4. If was not found in file cache
				            	if (($ytRelated = $youtube->fetchRelated( $ytId ))!==false) {
					            	foreach ($ytRelated as $ytEntry){
						    	    	if (($parsed = $this->videosModel->parseYtEntry($ytEntry))!==false){
						    	    		if (!empty($parsed['desc']) && (Xmltv_String::strlen($parsed['desc'])>128)) {
						    	    			$this->vCacheModel->saveRelatedVideo($ytEntry, $ytId);
						    	    			$relatedVideos[] = $parsed;
						    	    		}
						    	    	}
						    	    }
				            		$this->cache->save( $relatedVideos, $hash, 'Core', $f);
				            	} else {
				            		// 5. Video was not found at youtube
				            		$this->render('video-not-found');
				            		return true;
				            	}
				            	 
				            }
				            
				        } else {
				            
				            $result = $ytRelated = $youtube->fetchRelated( $ytId );
				        	foreach ($ytRelated as $ytEntry){
				    	    	if (($parsed = $this->videosModel->parseYtEntry($ytEntry))!==false){
				    	    		if (!empty($parsed['desc']) && (Xmltv_String::strlen($parsed['desc'])>128)) {
				    	    			$relatedVideos[] = $parsed;
				    	    		}
				    	    	}
				    	    }
				            
				        }
				    	 
				    }
				} else {
				    
				    $result = $youtube->fetchRelated( $ytId );
				    foreach ($result as $v){
				    	$relatedVideos[] = $this->videosModel->parseYtEntry( $v);
				    }
				    
				}
				
			    if (APPLICATION_ENV=='development'){
					//var_dump($relatedVideos);
					//die(__FILE__.': '.__LINE__);
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
			$this->view->assign('featured_channels', $this->getFeaturedChannels());
			
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
	    	var_dump($result);
	    	die(__FILE__.': '.__LINE__);
	    }
	    
	    if (count($result)) {
	    	return $result;
	    }
	    
	    return false;
	    
	    
	    	    
	}
	
	
}