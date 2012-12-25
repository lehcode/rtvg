<?php

/**
 * Frontend Channels controller
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: ChannelsController.php,v 1.8 2012-12-25 01:57:52 developer Exp $
 *
 */
class ChannelsController extends Zend_Controller_Action
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
	 * Root folder for cache
	 * @var string
	 */
	protected $cacheRoot;
	
	
	
	/**
	 * (non-PHPdoc)
	 * @see Zend_Controller_Action::__call()
	 */
	public function __call ($method, $arguments) {
		if (APPLICATION_ENV!='development'){
			header('HTTP/1.0 404 Not Found');
			$this->_helper->layout->setLayout( 'error' );
			$this->view->render();
		}
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Zend_Controller_Action::init()
	 */
	public function init () {
		
		$ajaxContext = $this->_helper->getHelper( 'AjaxContext' );
		$ajaxContext->addActionContext( 'typeahead', 'json' )
			->initContext();
			
		$this->cacheRoot = '/Channels';
		$this->cache = new Xmltv_Cache( array('location'=>$this->cacheRoot) );
		//var_dump($this->cache);
		//die();
		$this->view->setScriptPath(APPLICATION_PATH.'/views/scripts/');
		$this->validator = $this->_helper->getHelper('requestValidator');
		
	}
	

	/**
	 * Index page
	 * Redirect to frontpage
	 */
	public function indexAction () {
		$this->_forward( 'frontpage', 'index' );
	}

	/**
	 * All channels list
	 */
	public function listAction () {
		
		$channelsModel = new Xmltv_Model_Channels();
		$this->view->assign('pageclass', 'allchannels');
		if ($this->cache->enabled){
			$hash = Xmltv_Cache::getHash('published_channels');
			if (!$rows = $this->cache->load($hash, 'Core', $this->cacheRoot)) {
				$rows = $channelsModel->getPublished();
				$this->cache->save($rows, $hash, 'Core', $this->cacheRoot);
			}
		} else {
		    $rows = $channelsModel->getPublished();
		}
		//var_dump($rows);
		//die(__FILE__.': '.__LINE__);
		$this->view->assign('channels', $rows);
		
		/*
		 * ######################################################
		 * Channels categories
		 * ######################################################
		*/
		if ($cache->enabled){
			$f = "/Channels";
			$hash  = $this->cache->getHash("channelscategories");
			if (!$cats = $this->cache->load($hash, 'Core', $f)) {
				$cats = $channelsModel->channelsCategories();
				$this->cache->save($cats, $hash, 'Core', $f);
			}
		} else {
			$cats = $channelsModel->channelsCategories();
		}
		//var_dump($cats);
		//die(__FILE__.': '.__LINE__);
		$this->view->assign('channels_cats', $cats);
		
		/*
		 * ######################################################
		 * Top programs for left sidebar
		 * ######################################################
		 */
		$top = $this->_helper->getHelper('Top');
		if ($cache->enabled){
			$f = '/Listings/Programs';
			$hash = $this->cache->getHash('topPrograms');
			if (!$topPrograms = $this->cache->load($hash, 'Core', $f)) {
				$topPrograms = $top->direct( 'TopPrograms', array( 'amt'=>20 ));
				$this->cache->save($topPrograms, $hash, 'Core', $f);
			}
		} else {
			$topPrograms = $top->direct( 'TopPrograms', array( 'amt'=>20 ));
		}
		//var_dump($top);
		//var_dump($topPrograms);
		//die(__FILE__.': '.__LINE__);
		$this->view->assign('top_programs', $topPrograms);
		
	}
	
	/**
	 * Channels for typeahead script
	 */
	public function typeaheadAction () {
		
		$response=array();
		$channels = new Xmltv_Model_Channels();
		$response = $channels->getTypeaheadItems();
		$this->view->assign('response', $response);
		
	}
	
	/**
	 * Channels from particular category
	 */
	public function categoryAction() {
		/**
		 * 
		 * Filtered request variables
		 * @var Zend_Filter_Input
		 */
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
		    
		    $this->view->assign('pageclass', 'category');
			$channelsModel = new Xmltv_Model_Channels();
			$catProps = $channelsModel->category( $this->input->getEscaped('category') )->toArray();
			$this->view->assign('category', $catProps);
			
			if ($this->cache->enabled){
				$hash = md5('channelcategories_'.$catProps['alias']);
				if (!$rows = $this->cache->load($hash, 'Core', $this->cacheRoot)){
					$rows = $channelsModel->categoryChannels($catProps['alias']);
					$this->cache->save($rows->toArray(), $hash, 'Core', $this->cacheRoot);
				}
			} else {
				$rows = $channelsModel->categoryChannels($catProps['alias']);
			}
			//var_dump($rows);
			//die(__FILE__.':'.__LINE__);
			$this->view->assign('channels', $rows);
			
			/*
			 * ######################################################
			 * Top programs for left sidebar
			 * ######################################################
			 */
			$top = $this->_helper->getHelper('Top');
			$amt = 20;
			//var_dump($top);
			if ($cache->enabled){
				$f = '/Listings/Programs';
				$hash = Xmltv_Cache::getHash('top'.$amt);
				if (!$topPrograms = $this->cache->load($hash, 'Core', $f)) {
					$topPrograms = $top->direct( 'TopPrograms', array( 'amt'=>$amt ));
					$this->cache->save($topPrograms, $hash, 'Core', $f);
				}
			} else {
				$topPrograms = $top->direct( 'TopPrograms', array( 'amt'=>$amt ));
			}
			//var_dump($topPrograms);
			//die(__FILE__.': '.__LINE__);
			$this->view->assign('top_programs', $topPrograms);
			
			/*
			 * ######################################################
			 * Channels categories
			 * ######################################################
			*/
			if ($cache->enabled){
				$f = "/Channels";
				$hash  = $this->cache->getHash("channelscategories");
				if (!$cats = $this->cache->load($hash, 'Core', $f)) {
					$cats = $channelsModel->channelsCategories();
					$this->cache->save($cats, $hash, 'Core', $f);
				}
			} else {
				$cats = $channelsModel->channelsCategories();
			}
			//var_dump($cats);
			//die(__FILE__.': '.__LINE__);
			$this->view->assign('channels_cats', $cats);
			
			
			$this->render('list');
		    
		}
		
	}
	
	/**
	 * Week listing for channel
	 * @throws Exception
	 */
	public function channelWeekAction(){
		
		/**
		 * 
		 * Filter request vaiables
		 * @var Zend_Filter_Input
		 */
		$input = $this->_helper->requestValidator( array( 'method'=>'isValidRequest', 'action'=>$this->_getParam('action')));
		if ($input){ 
			
			$channels = new Xmltv_Model_Channels(array(
				'site_config'=>$this->_siteConfig,
				'app_config'=>$this->_appConfig,
				'cache'=>$this->_cache,
			));
			$channel  = $channels->getByAlias( $input->getEscaped('channel') );

			/*
			 * initialize week start and week end dates
			 */
			$d = $this->_getParam('start', null)!==null ? new Zend_Date($input->getEscaped('start'), 'YYYY-MM-dd') : new Zend_Date() ; 
			$start = $this->_helper->weekDays(array('method'=>'getStart', "data"=>array('date'=>$d) ));
			$d = $this->_getParam('end', null)!==null ? new Zend_Date($input->getEscaped('end'), 'YYYY-MM-dd') : new Zend_Date() ; 
			$end = $this->_helper->weekDays(array('method'=>'getEnd', "data"=>array('date'=>$d) ));

			try {
				$schedule = $channels->getWeekSchedule($channel, $start, $end);
			} catch (Zend_Exception $e) {
				echo $e->getMessage();
			}
			
			/*
			 * re-initialize $start date
			 */
			$d = $this->_getParam('start', null)!==null ? new Zend_Date($input->getEscaped('start'), 'YYYY-MM-dd') : new Zend_Date() ; 
			$start = $this->_helper->weekDays(array('method'=>'getStart', "data"=>array('date'=>$d) ));
			
			$this->view->assign('channel', $channel);
	    	$this->view->assign('days', $schedule);
	    	$this->view->assign('week_start', $start);
	    	$this->view->assign('week_end', $end);
	    	$this->view->assign('hide_sidebar', 'left');
	    	$this->view->assign( 'sidebar_videos', true );
	    	$this->view->assign('pageclass', 'channel-week');
	    	
	    	$channels->addHit( $channel->ch_id );
			
		} else {
    		throw new Exception("Неверные данные", 500);
    		exit();
    	}
		
	}
	
	/**
	 * 
	 * Validation routines
	 */
	/*
	private function _validateRequest(){
		
		//var_dump($this->_getAllParams());
		//die(__FILE__.': '.__LINE__);
		
		$filters = array('*'=>'StringTrim', '*'=>'StringToLower');
		$validators = array(
			//'channel'   => array(new Zend_Validate_Regex('/^[0-9\p{L} -]+$/iu')),
			//'alias'     => array(new Zend_Validate_Regex('/^[0-9\p{L}-]+$/iu')), 
			'module'    => array(new Zend_Validate_Regex('/^[a-z]+$/u')), 
			'controller'=> array(new Zend_Validate_Regex('/^[a-z]+$/')), 
			'action'    => array(new Zend_Validate_Regex('/^[a-z-]+$/')),
		);
		
		if ($this->_getParam('category')){
			$validators['category'] = array(new Zend_Validate_Regex('/^[\p{Cyrillic}-]+$/iu'));
		}
		
		
		$input = new Zend_Filter_Input( $filters, $validators, $this->_getAllParams() );
		
		if( $input->isValid() ) {
			return $input;
		}
		return false;
		
	}
	*/
	
}

