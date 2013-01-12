<?php
/**
 * Frontend videos controller
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: VideosController.php,v 1.9 2013-01-12 09:06:22 developer Exp $
 *
 */
class VideosController extends Xmltv_Controller_Action
{
	
	
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
	 * 
	 * @throws Zend_Exception
	 * @throws Zend_Controller_Action_Exception
	 */
	public function showVideoAction(){
		
	    //var_dump($this->requestParamsValid());
	    //die(__FILE__.': '.__LINE__);
	    
	   if ($this->requestParamsValid()){
			
			$this->view->assign( 'pageclass', 'show-video' );
			$conf = Zend_Registry::get('site_config')->videos->video;
			$ytConfig=array(
				'max_results' => (int)$conf->get('max_results'),
				'safe_search' => $conf->get('safe_search'),
				'start_index' => (int)$conf->get('start_index'),
			);
			//var_dump($ytConfig);
			$youtube	  = new Xmltv_Youtube($ytConfig);
			$videosModel  = new Xmltv_Model_Videos();
			$rtvgId = $this->input->getEscaped('id');
			
			if ($rtvgId) {
				$ytId   = Xmltv_Youtube::decodeRtvgId( $rtvgId );
				if ($this->cache->enabled){
					$hash = Xmltv_Cache::getHash( $ytId);
					$f = '/Youtube/Main';
					if(($video = $this->cache->load($hash, 'Core', $f))===false){
						$video = $youtube->fetchVideo( $ytId );
						$this->cache->save($video, $hash, 'Core', $f);
					}
				} else {
					$video = $youtube->fetchVideo( $ytId);
				}
				
				if ( is_a( $video, 'Zend_Gdata_YouTube_VideoEntry')){
					if (Xmltv_Model_Videos::isPorn( $video->getVideoTitle())) {
						$this->_redirect('/', array('exit'=>true));
					}
					$this->view->assign( 'main_video', $video );
					
					if ($this->cache->enabled){
						$hash = Xmltv_Cache::getHash( $ytId.'_related');
						$f = '/Youtube/Main/Related';
						if(($related = $this->cache->load($hash, 'Core', $f))===false){
							$related = $youtube->fetchRelated( $ytId );
							$this->cache->save($related, $hash, 'Core', $f);
						}
					} else {
						$related = $youtube->fetchRelated( $ytId );
					}
					$this->view->assign( 'related_videos', $related );
					
				}
			} else {
				$this->view->assign( 'main_video', null );
				$this->view->assign( 'related_videos', null );
			}
			//var_dump($video);
			//var_dump($related->count());
			
			
			/*
			 * ######################################################
			* Top programs for left sidebar
			* ######################################################
			*/
			$amt = (int)Zend_Registry::get('site_config')->topprograms->channellist->get('amount');
			$top = $this->_helper->getHelper('Top');
			if ($this->cache->enabled && (parent::$nocache !== true)){
				$f = '/Listings/Programs';
				$hash = Xmltv_Cache::getHash('top'.$amt);
				if (!$topPrograms = $this->cache->load($hash, 'Core', $f)) {
					$topPrograms = $top->direct( 'TopPrograms', array( 'amt'=>$amt ));
					$this->cache->save($topPrograms, $hash, 'Core', $f);
				}
			} else {
				$topPrograms = $top->direct( 'TopPrograms', array( 'amt'=>$amt ));
			}
			//var_dump($top);
			//var_dump($topPrograms);
			//die(__FILE__.': '.__LINE__);
			$this->view->assign('top_programs', $topPrograms);
			
		}
		
		
		
	}
	
	/**
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