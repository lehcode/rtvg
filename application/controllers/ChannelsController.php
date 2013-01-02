<?php

/**
 * Frontend Channels controller
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: ChannelsController.php,v 1.11 2013-01-02 16:58:27 developer Exp $
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
	    
	   //var_dump($this->requestParamsValid());
	    //die(__LINE__);
	    
	    if ($this->requestParamsValid()) {
	        
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
			if ($this->cache->enabled){
				$f = "/Channels";
				$hash  = $this->cache->getHash("channelscategories");
				if (!$cats = $this->cache->load($hash, 'Core', $f)) {
					$cats = $channelsModel->channelsCategories();
					$this->cache->save($cats, $hash, 'Core', $f);
				}
			} else 	{
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
			$amt = Zend_Registry::get('site_config')->topprograms->channellist->get('amount');
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
	    }
		
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
			$amt = Zend_Registry::get('site_config')->topprograms->channellist->get('amount');
			//var_dump($top);
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
			//var_dump($topPrograms);
			//die(__FILE__.': '.__LINE__);
			$this->view->assign('top_programs', $topPrograms);
			
			/*
			 * ######################################################
			 * Channels categories
			 * ######################################################
			*/
			if ($this->cache->enabled){
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
		    
		    $this->view->assign('hide_sidebar', 'left');
		    $this->view->assign('sidebar_videos', true);
		    $this->view->assign('pageclass', 'channel-week');
		    
		    // Channel properties
		    $channelsModel = new Xmltv_Model_Channels();
		    $channel = $channelsModel->getByAlias( $this->input->getEscaped('channel') );
		    $this->view->assign('channel', $channel);
		    //var_dump($channel);
		    //die(__FILE__.': '.__LINE__);
		    
		    //Week start and end dates
		    $s = $this->_helper->getHelper('weekDays')->getStart( Zend_Date::now() );
		    $this->view->assign('week_start', $s);
		    $e = $this->_helper->getHelper('weekDays')->getEnd( Zend_Date::now() );
		    $this->view->assign('week_end', $e);
		    
		    
		    $start = new Zend_Date($s->toString('U'), 'U');
		    $end   = new Zend_Date($e->toString('U'), 'U');
		    if ($this->cache->enabled){
		        $hash = Xmltv_Cache::getHash('channel_'.$channel->alias.'_week');
		        $f = '/Channels';
		        if (!$schedule = $this->cache->load($hash, 'Core', $f)) {
		            $schedule = $channelsModel->getWeekSchedule($channel, $start, $end);
		            $this->cache->save($schedule, $hash, 'Core', $f);
		        }
		    } else {
		    	$schedule = $channelsModel->getWeekSchedule($channel, $start, $end);
		    }
		    $this->view->assign('days', $schedule);
		    
		    
		    $channelsModel->addHit( $channel->ch_id );
		    
		}
		
	}
	
	/**
	 * Update comments for channel
	 */
	public function newCommentsAction(){
		 
		//var_dump($this->_getAllParams());
		//die(__FILE__.': '.__LINE__);
		
		/**
		 *
		 * Filter request vaiables
		 * @var Zend_Filter_Input
		 */
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
			
			// Channel properties
			$channelsModel = new Xmltv_Model_Channels();
			$channel = $channelsModel->getByAlias( $this->input->getEscaped('channel') );
			$this->view->assign('channel', $channel);
			
			//Attach model
			$model = new Xmltv_Model_Comments();
			
			//Fetch and parse feed
			try {
			    $feedData = $model->getYandexRss( array( ' телеканал "'.$channel->title.'"', $currentProgram->title ) );
			} catch (Zend_Feed_Exception $e) {
			    throw new Zend_Exception($e->getMessage(), $e->getCode(), $e);
			}
			//var_dump($feedData);
			if ($new = $model->parseYandexFeed( $feedData, 164 )){
				if (count($new)>0){
				    $model->saveComments($new, $channel->alias, 'channel');
				    $this->view->assign('items', $new);
				}
			}
			
			$this->_helper->layout->disableLayout();
			
			//var_dump($new);
			//die(__FILE__.': '.__LINE__);
			
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

