<?php

/**
 * Frontend Channels controller
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: ChannelsController.php,v 1.18 2013-03-02 09:43:55 developer Exp $
 *
 */
class ChannelsController extends Xmltv_Controller_Action
{
	
	/**
	 * Cache root for this controller
	 * @var string
	 */
	protected $cacheRoot = '/Channels';
	
	/**
	 * (non-PHPdoc)
	 * @see Zend_Controller_Action::init()
	 */
	public function init () {
		
		parent::init();
		
		/**
		 * Change layout for AJAX requests
		 */
		if ($this->getRequest()->isXmlHttpRequest()) {
		    
		    $ajaxContext = $this->_helper->getHelper( 'AjaxContext' );
		    $ajaxContext
		    	->addActionContext( 'typeahead', 'json' )
			    ->addActionContext( 'new-comments', 'html' )
			    ->initContext();
			
	   	}
	   	
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
		
		if ($this->requestParamsValid()) {
			
			$this->channelsModel = new Xmltv_Model_Channels();
			$this->view->assign('pageclass', 'allchannels');
			if ($this->cache->enabled){
				$hash = Xmltv_Cache::getHash('published_channels');
				if (!$rows = $this->cache->load($hash, 'Core', $this->cacheRoot)) {
					$rows = $this->channelsModel->getPublished();
					$this->cache->save($rows, $hash, 'Core', $this->cacheRoot);
				}
			} else {
				$rows = $this->channelsModel->getPublished();
			}
			
			if (APPLICATION_ENV=='development'){
				//var_dump($rows);
				//die(__FILE__.': '.__LINE__);
			}
			
			$this->view->assign('channels', $rows);
			
			/*
			 * ######################################################
			 * Channels categories
			 * ######################################################
			*/
			if ($this->cache->enabled){
			    $this->cache->setLocation(ROOT_PATH.'/cache');
				$f = "/Channels";
				$hash  = $this->cache->getHash("channelscategories");
				if (!$cats = $this->cache->load($hash, 'Core', $f)) {
					$cats = $this->channelsModel->channelsCategories();
					$this->cache->save($cats, $hash, 'Core', $f);
				}
			} else 	{
				$cats = $this->channelsModel->channelsCategories();
			}
			//var_dump($cats);
			//die(__FILE__.': '.__LINE__);
			$this->view->assign('channels_cats', $cats);
			
			/*
			 * #####################################################################
			 * Данные для модуля самых популярных программ
			 * #####################################################################
			 */
			$top = $this->getTopPrograms();
			$this->view->assign('top_programs', $top);
		}
		
	}
	
	/**
	 * Channels for typeahead script
	 */
	public function typeaheadAction () {
		
		if ($this->requestParamsValid()) {
			
			$channelsCategories = new Xmltv_Model_DbTable_ChannelsCategories();
			if ($this->_getParam('c')) {
				$category = $channelsCategories->fetchRow("`alias` LIKE '".$this->input->getEscaped('c')."'")->toArray();
			}
			
			$this->channelsModel = new Xmltv_Model_Channels();
			$hash = Xmltv_Cache::getHash( 'typeahead_all' );
			if ($this->cache->enabled) {
			    $this->cache->setLocation(ROOT_PATH.'/cache');
				if (($items = $this->cache->load( $hash, 'Core', $this->cacheRoot))===false){
					$items = $this->channelsModel->getTypeaheadItems( $category['id']);
					$this->cache->save($items, $hash, 'Core', $this->cacheRoot);
				}
			} else {
				$items = $this->channelsModel->getTypeaheadItems( $category['id']);
			}
			
			foreach ($items as $k=>$part){
				$result[]['title'] = $part['title'];
			}
			
			$this->view->assign('result', $result);
		}
		
	}
	
	/**
	 * Channels from particular category
	 */
	public function categoryAction() {
		
		if (parent::requestParamsValid()) {
		   
			$this->view->assign('pageclass', 'category');
			$this->channelsModel = new Xmltv_Model_Channels();
			$catProps = $this->channelsModel->category( $this->input->getEscaped('category') )->toArray();
			$this->view->assign('category', $catProps);
			
			if ($this->cache->enabled){
			    $this->cache->setLocation(ROOT_PATH.'/cache');
				$hash = md5('channelcategories_'.$catProps['alias']);
				if (!$rows = $this->cache->load($hash, 'Core', $this->cacheRoot)){
					$rows = $this->channelsModel->categoryChannels($catProps['alias']);
					$this->cache->save($rows->toArray(), $hash, 'Core', $this->cacheRoot);
				}
			} else {
				$rows = $this->channelsModel->categoryChannels($catProps['alias']);
			}
			//var_dump($rows);
			//die(__FILE__.':'.__LINE__);
			$this->view->assign('channels', $rows);
			
			/*
			 * #####################################################################
			 * Данные для модуля самых популярных программ
			 * #####################################################################
			 */
			$top = $this->getTopPrograms();
			$this->view->assign('top_programs', $top);
			
			/*
			 * ######################################################
			 * Channels categories
			 * ######################################################
			*/
			if ($this->cache->enabled){
				$f = "/Channels";
				$this->cache->setLocation(ROOT_PATH.'/cache');
				$hash  = $this->cache->getHash("channelscategories");
				if (!$cats = $this->cache->load($hash, 'Core', $f)) {
					$cats = $this->channelsModel->channelsCategories();
					$this->cache->save($cats, $hash, 'Core', $f);
				}
			} else {
				$cats = $this->channelsModel->channelsCategories();
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
		
		// Validation routines
		if (parent::requestParamsValid()) {
			
			$this->view->assign('hide_sidebar', 'left');
			//$this->view->assign('sidebar_videos', true);
			$this->view->assign('pageclass', 'channel-week');
			
			// Channel properties
			$this->channelsModel = new Xmltv_Model_Channels();
			$channel = $this->channelsModel->getByAlias( $this->input->getEscaped('channel') );
			$this->view->assign('channel', $channel);
			
			//Week start and end dates
			$s = $this->_helper->getHelper('weekDays')->getStart( Zend_Date::now() );
			$this->view->assign('week_start', $s);
			$e = $this->_helper->getHelper('weekDays')->getEnd( Zend_Date::now() );
			$this->view->assign('week_end', $e);
			
			if ($this->cache->enabled){
			    $this->cache->setLocation(ROOT_PATH.'/cache');
				$hash = Xmltv_Cache::getHash('channel_'.$channel['alias'].'_week');
				$f = '/Channels';
				if (!$schedule = $this->cache->load($hash, 'Core', $f)) {
					$schedule = $this->channelsModel->getWeekSchedule($channel, $s, $e);
					$this->cache->save($schedule, $hash, 'Core', $f);
				}
			} else {
				$schedule = $this->channelsModel->getWeekSchedule($channel, $s, $e);
			}
			$this->view->assign('days', $schedule);
			
			$this->channelsModel->addHit( $channel['id'] );
			
		}
		
	}
	
	/**
	 * Update comments for channel
	 */
	public function newCommentsAction(){
		 
		$this->_helper->layout->disableLayout();
		
		if ($this->requestParamsValid()){
			
			foreach ($this->_getAllParams() as $k=>$v){
				if (!$this->input->isValid($k)) {
					throw new Zend_Controller_Action_Exception("Invalid ".$k.'!');
				}
			}
				
			// Channel properties
			$this->channelsModel = new Xmltv_Model_Channels();
			$channelAlias = $this->input->getEscaped('channel');
			if ($this->cache->enabled){
			    $this->cache->setLocation(ROOT_PATH.'/cache');
				$f    = '/Feeds/Yandex';
				$hash = $this->cache->getHash('channel_'.$channelAlias);
				if (($channel = $this->cache->load($hash, 'Core', $f))===false) {
					$channel = $this->channelsModel->getByAlias($channelAlias);
					$this->cache->save($channel, $hash, 'Core', $f);
				}
			} else {
				$channel = $this->channelsModel->getByAlias($channelAlias);
			}
			$this->view->assign('channel', $channel);
			
			//Attach comments model
			$commentsModel = new Xmltv_Model_Comments();
			$feedData = $commentsModel->getYandexRss( array( 'телеканал "'.Xmltv_String::strtolower($channel['title']).'"') );
			
			if (APPLICATION_ENV=='development'){
				//var_dump($feedData);
				//die(__FILE__.': '.__LINE__);
			}
			
			if ( ($new = $commentsModel->parseYandexFeed( $feedData ))!==false){
				
			    if (APPLICATION_ENV=='development'){
			        //var_dump($new);
			        //die(__FILE__.': '.__LINE__);
			    }
			    
			    if (count($new)){
				    $commentsModel->saveChannelComments($new, $channel['id']);
			    }
				
			    $this->view->assign('items', $new);
			    
			}

		}
		 
	}
	
}

