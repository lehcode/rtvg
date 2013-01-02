<?php
/**
 * Frontend videos controller
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: VideosController.php,v 1.8 2013-01-02 05:07:49 developer Exp $
 *
 */
class VideosController extends Zend_Controller_Action
{
	
	/**
	 *
	 * Validator
	 * @var Xmltv_Controller_Action_Helper_RequestValidator
	 */
	protected $validator;
	/**
	 *
	 * Input filtering plugin
	 * @var Zend_Filter_Input
	 */
	protected $input;
	
	/**
	 * Caching object
	 * @var Xmltv_Cache
	 */
	protected $cache;
	
	/**
	 * (non-PHPdoc)
	 * @see Zend_Controller_Action::__call()
	 */
	public function __call ($method, $arguments) {
		if (APPLICATION_ENV=='production') {
			header( 'HTTP/1.0 404 Not Found' );
			$this->_helper->layout->setLayout( 'error' );
			$this->view->render();
		}
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Zend_Controller_Action::init()
	 */
	public function init () {
		
		$this->view->setScriptPath( APPLICATION_PATH . '/views/scripts/' );
		$this->validator = $this->_helper->getHelper('requestValidator');
		$this->cache     = new Xmltv_Cache();
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
	 * 
	 * @throws Zend_Exception
	 * @throws Zend_Controller_Action_Exception
	 */
	public function showVideoAction(){
		
		if ($this->requestParamsValid()){
			
			$youtube = new Xmltv_Youtube();
			$videos  = new Xmltv_Model_Videos();
			if ($video = $youtube->fetchVideo( Xmltv_Youtube::decodeRtvgId( $this->input->getEscaped('id')))){
				$this->view->assign( 'main_video', $video );
				//var_dump($video);
					
				$related = $youtube->fetchRelated( $video->getVideoId() );
				$this->view->assign( 'related_videos', $related );
				//var_dump($related->count());
				
				$this->view->assign( 'pageclass', 'show-video' );
				//$this->view->assign( 'hide_sidebar', 'left' );
				
			}
			
			
			/*
			 * ######################################################
			* Top programs for left sidebar
			* ######################################################
			*/
			$amt = (int)Zend_Registry::get('site_config')->topprograms->channellist->get('amount');
			$top = $this->_helper->getHelper('Top');
			if ($this->cache->enabled){
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
			
			/*
			 * ######################################################
			* Related videos
			* (1)Right sidebar videos
			* ######################################################
			*/
			$vc = Zend_Registry::get('site_config')->videos->sidebar->right;
			$max = (int)Zend_Registry::get('site_config')->videos->sidebar->right->get('max_results');
			$ytConfig = array(
					'order'=>$vc->get('order', 'relevance'),
					'max_results'=>(int)$vc->get('max_results', $max),
					//'operator'=>$vc->get('operator', '|'),
					//'start_index'=>$vc->get('start_index', 1),
					//'safe_search'=>$vc->get('safe_search', 'none'),
					'language'=>'ru',
			);
			 
			if ($this->cache->enabled){
				$hash = Xmltv_Cache::getHash('sidebar_'.$channel->ch_id);
				$f = '/Youtube/SidebarRight';
				if (($videos = $this->cache->load($hash, 'Core', $f))===false) {
					$videos = $this->_fetchSidebarVideos( 'тв '.$channel->title, null, $ytConfig);
					$this->cache->save($videos, $hash, 'Core', $f);
				}
			} else {
				$videos = $this->_fetchSidebarVideos( 'тв '.$channel->title, null, $ytConfig);
			}
			//var_dump($videos);
			//die(__FILE__.': '.__LINE__);
			$this->view->assign('sidebar_videos', $videos);
			
		}
		
		
		
	}
	
	
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
	
	/**
	 * Validate nad filter request parameters
	 *
	 * @throws Zend_Exception
	 * @throws Zend_Controller_Action_Exception
	 * @return boolean
	 */
	protected function requestParamsValid(){
		 
		// Validation routines
		$this->input = $this->validator->direct(array('isvalidrequest', 'vars'=>$this->_getAllParams()));
		if ($this->input===false) {
			if (APPLICATION_ENV=='development'){
				var_dump($this->_getAllParams());
				die(__FILE__.': '.__LINE__);
			} elseif(APPLICATION_ENV!='production'){
				throw new Zend_Exception(self::ERR_INVALID_INPUT, 500);
			}
			$this->_redirect($this->view->url(array(), 'default_error_missing-page'), array('exit'=>true));
			 
		} else {
			 
			foreach ($this->_getAllParams() as $k=>$v){
				if (!$this->input->isValid($k)) {
					throw new Zend_Controller_Action_Exception("Invalid ".$k.'!');
				}
			}
		  
			return true;
		  
		}
		 
	}
	
	
}