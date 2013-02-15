<?php
/**
 * Frontend videos controller
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: VideosController.php,v 1.11 2013-02-15 00:44:02 developer Exp $
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
		
		if ($this->requestParamsValid()){
			$this->_forward( 'show-video' );
		}
		
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
			$conf = Zend_Registry::get('site_config')->videos->video;
			$ytConfig=array(
				'max_results' => (int)$conf->get('max_results'),
				'safe_search' => $conf->get('safe_search'),
				'start_index' => (int)$conf->get('start_index'),
			);
			$youtube	 = new Xmltv_Youtube($ytConfig);
			$videosModel = new Xmltv_Model_Videos();
			$vCacheModel = new Xmltv_Model_Vcache();
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
					//die(__FILE__.': '.__LINE__);
				}
				
				if ($this->cache->enabled && parent::$videoCache===true){
					
				    // 1. Query cache model for main video ( Xmltv_Model_Vcache::getVideo($rtvgId) )
					if (!($video = $vCacheModel->getVideo($rtvgId))){
						$video = $youtube->fetchVideo( $ytId );
						if ($video===false) {
						    $this->render('private-video');
						    return true;
						}
						$videoId = $vCacheModel->saveMainVideo($video);
					}
					
					if ($video){
						var_dump($video);
						die(__FILE__.': '.__LINE__);
					}
					
					if ($video){
					    $video['thumbs'] = Zend_Json::decode($video['thumbs']);
					    $this->view->assign( 'main_video', $video );
					    
					    $conf = Zend_Registry::get('site_config')->videos->related;
					    $youtube = new Xmltv_Youtube( array(
					    		'max_results' => (int)$conf->get('amt'),
					    ));
					    	
					    if (!isset($related)){
					    	if (!($related = $vCacheModel->getRelated($ytId, (int)$conf->get('amt')))){
					    		$result = $youtube->fetchRelated( $ytId );
					    		foreach ($result as $v){
					    			$related[] = $vCacheModel->saveRelatedVideo( $v, $ytId);
					    		}
					    	}
					    }
					    //var_dump($related);
					    //die(__FILE__.': '.__LINE__);
					    $this->view->assign( 'related_videos', $related );
					    
					} else {
					    $this->view->assign( 'hide_sidebar', 'right');
					    $this->render('video-not-found');
					    return true;
					}
					
				} else {
				    
				    $video = $youtube->fetchVideo( $ytId );
				    
				    if (APPLICATION_ENV=='development'){
				        //var_dump($video);
				        //die(__FILE__.': '.__LINE__);
				    }
				    
				    if ($video){
				        
						$video = $videosModel->parseYtEntry($video);
						$video = $videosModel->objectToArray($video);
						$this->view->assign( 'main_video', $video );
						
						$conf = Zend_Registry::get('site_config')->videos->related;
						$youtube = new Xmltv_Youtube();
						
						$rel = $youtube->fetchRelated( $ytId );
						foreach ($rel as $v){
							$r = $videosModel->parseYtEntry($v);
							$r = $videosModel->objectToArray($r);
							$related[]=$r;
						}
						$this->view->assign( 'related_videos', $related );
						
						if (APPLICATION_ENV=='development'){
							//var_dump($rel);
							//var_dump($related);
							//die(__FILE__.': '.__LINE__);
						}
						
				    } else {
				        $this->view->assign( 'hide_sidebar', 'right');
				        $this->render('video-not-found');
				        return true;
				    }
				}
				
			} else {
				$this->view->assign( 'main_video', null );
				$this->view->assign( 'related_videos', null );
			}
			
			
			/*
			 * Данные для модуля самых популярных программ
			 */
			$this->view->assign('top_programs', 
				$this->getTopPrograms( (int)Zend_Registry::get('site_config')
					->top->channels->get('amount'), $this->_getParam('controller')));
			
		}
		
		
		
	}
	
	/**
	 * Generate data for video page compatible 
	 * with card-sharing.org Joomla component
	 * 
	 * @deprecated
	 */
	public function showVideoCompatAction(){
		
		if ($this->requestParamsValid()) {  
			
			$videos_model = new Xmltv_Model_Videos();
			
			$id = base64_decode($this->_getParam('id'));
			
			$video = $videos_model->getVideo( $id, false );
			$this->view->assign( 'main_video', $video );
			
			$related = $videos_model->getRelatedVideos( $video );
			$this->view->assign( 'related_videos', $related );
			
			$this->render('show-video');
			
		}
		
	}
	
	/**
	 * Generate data for related videos by tag page
	 * 
	 * @deprecated //Youtube disabled tags functionality in API V3
	 */
	public function showTagAction(){
		
		if ( $this->requestParamsValid()) { 
			$this->view->assign( 'pageclass', 'show-tag' );
			$this->view->assign( 'tag', base64_decode( $this->_getParam( 'p' ) ) );
			$this->view->assign( 'tag-alias', $this->_getParam( 'tag' ) );
		} else {
			exit("Неверные данные");
		}
				
	}
	
	
}