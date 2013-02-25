<?php
/**
 * Frontend videos controller
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: VideosController.php,v 1.12 2013-02-25 11:40:40 developer Exp $
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
					var_dump($rtvgId);
					var_dump($ytId);
					//die(__FILE__.': '.__LINE__);
				}
				
				if (parent::$videoCache===true){
				    /*
				     * 1. Query cache model for main video ( Xmltv_Model_Vcache::getVideo($rtvgId) )
				     */
				    if (($video = $vCacheModel->getVideo($rtvgId))===false){
				    	if (($video = $youtube->fetchVideo( $ytId ))===false) {
				    		$this->render('video-not-found');
				    		return true;
				    	}
				    	$video = $vCacheModel->saveMainVideo($video);
				    }
				    
				    if (APPLICATION_ENV=='development'){
				    	//var_dump($video);
				    	//die(__FILE__.': '.__LINE__);
				    }
				    
				} else {
				    $video = $youtube->fetchVideo( $ytId );
				}
				
				$this->view->assign( 'main_video', $video );
				
				/*
				 * 2. Related videos
				 */
				$youtube = new Xmltv_Youtube( array(
					'max_results' => (int)Zend_Registry::get('site_config')->videos->related->get('amt'),
				));
				
				$related = array();
				
				if (APPLICATION_ENV=='development'){
					//var_dump($this->cache->enabled);
					//var_dump(parent::$videoCache);
					//die(__FILE__.': '.__LINE__);
				}
				
				if ($this->cache->enabled){
				    
				    $this->cache->setLocation(ROOT_PATH.'/cache');
				    $f = '/Youtube/Main/Related';
				    $hash = Xmltv_Cache::getHash('relatedVideo_'.$ytId);
				    
				    if (parent::$videoCache===true){
				    
				    	if (APPLICATION_ENV=='development'){
				    		//var_dump($ytId);
				    		//var_dump((int)$conf->get('amt'));
				    		//var_dump($vCacheModel->getRelated($ytId, (int)$conf->get('amt'))===false);
				    		//var_dump($this->cache->load( $hash, 'Core', $f)===false);
				    		//die(__FILE__.': '.__LINE__);
				    	}
				    	
				    	if (($result = $this->cache->load( $hash, 'Core', $f))===false){
				    	    if (($result = $vCacheModel->getRelated($ytId, (int)$conf->get('amt')))===false){
					    	    $feed = $youtube->fetchRelated( $ytId );
					    		$result = $this->_saveRelatedVideos( $feed, $ytId);
					    		$this->cache->save( $result, $hash, 'Core', $f);
					    	} 
				    	} else {
				    	    $feed   = $youtube->fetchRelated( $ytId );
				    		$result = $this->_saveRelatedVideos( $feed, $ytId);
				    	}
				    		
				    	if (count($result)){
				    		$c=0;
				    		foreach ($result as $v){
				    		    var_dump($v);
				    		    die();
				    			$related[$c] = $v;
				    			$related[$c]['published'] = new Zend_Date($v['published'], 'yyyy-MM-dd');
				    			$related[$c]['duration']  = new Zend_Date($v['duration'], 'HH:mm:ss');
				    			$related[$c]['thumbs']    = Zend_Json::decode($v['thumbs']);
				    			$c++;
				    		}
				    	}
				    		
				    	if (APPLICATION_ENV=='development'){
				    		//var_dump($related);
				    		//die(__FILE__.': '.__LINE__);
				    	}
				    		
				    } else {
				        
				        if (!($result = $this->cache->load( $hash, 'Core', $f))){
				        	if (($result = $youtube->fetchRelated( $ytId ))===false){
				        		$v = $youtube->fetchRelated( $ytId );
				        		$result = $this->_saveRelatedVideo( $v, $ytId);
				        		$this->cache->save( $result, $hash, 'Core', $f);
				        	}
				        }   
				    }
				    
				} else {
				    $result = $youtube->fetchRelated( $ytId );
				    foreach ($result as $v){
				    	$related[] = $this->videosModel->parseYtEntry( $v);
				    }
				}
				
			    if (APPLICATION_ENV=='development'){
					//var_dump($this->cache);
					//var_dump($related);
					//die(__FILE__.': '.__LINE__);
				}
				
				$this->view->assign( 'related_videos', $related );
				
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
	
	private function _saveRelatedVideos($video_feed, $yt_id){
		
	    $c=0;
	    $result = array();
	    foreach ($video_feed as $v){
	        
	        if (!is_a($v, 'Zend_Gdata_YouTube_VideoEntry')) {
	            throw new Zend_Exception(parent::ERR_INVALID_INPUT.__METHOD__, 404);
	        } else {
	            $p = $this->videosModel->parseYtEntry($v);
	            $result['yt_parent'] = $yt_id;
	            $result[$c] = $this->vCacheModel->saveRelatedVideo($p);
	            $c++;
	        }
	        
	        return $result;
	        
	    }
	    	    
	}
	
	
}