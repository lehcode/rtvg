<?php

/**
 * Frontend Channels controller
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: ChannelsController.php,v 1.15 2013-02-25 11:40:40 developer Exp $
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
			
			$this->contextSwitch
				->addActionContext('typeahead', 'json')
				->initContext();
	   	}
		
		$this->validator = $this->_helper->getHelper( 'requestValidator');
		$this->_initCache();
	}
	
	/**
	 * Initialize caching
	 */
	protected function _initCache(){
		
		$this->cacheRoot = '/Channels';
		$this->cache = new Xmltv_Cache( array('location'=>$this->cacheRoot) );
		$this->cache->lifetime = (int)Zend_Registry::get('site_config')->cache->system->get('lifetime');
		
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
			$amt = Zend_Registry::get('site_config')->top->listings->get('amount');
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
		
		if ($this->requestParamsValid()) {
			
			$channelsCategories = new Xmltv_Model_DbTable_ChannelsCategories();
			if ($this->_getParam('c')) {
				$category = $channelsCategories->fetchRow("`alias` LIKE '".$this->input->getEscaped('c')."'")->toArray();
			}
			
			$channelsModel = new Xmltv_Model_Channels();
			$hash = Xmltv_Cache::getHash( 'typeahead_all' );
			if ($this->cache->enabled) {
				if (($items = $this->cache->load( $hash, 'Core', $this->cacheRoot))===false){
					$items = $channelsModel->getTypeaheadItems( $category['id']);
					$this->cache->save($items, $hash, 'Core', $this->cacheRoot);
				}
			} else {
				$items = $channelsModel->getTypeaheadItems( $category['id']);
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
			$amt = Zend_Registry::get('site_config')->top->listings->get('amount');
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
		
		// Validation routines
		if (parent::requestParamsValid()) {
			
			$this->view->assign('hide_sidebar', 'left');
			//$this->view->assign('sidebar_videos', true);
			$this->view->assign('pageclass', 'channel-week');
			
			// Channel properties
			$channelsModel = new Xmltv_Model_Channels();
			$channel = $channelsModel->getByAlias( $this->input->getEscaped('channel') );
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
					$schedule = $channelsModel->getWeekSchedule($channel, $s, $e);
					$this->cache->save($schedule, $hash, 'Core', $f);
				}
			} else {
				$schedule = $channelsModel->getWeekSchedule($channel, $s, $e);
			}
			$this->view->assign('days', $schedule);
			
			$channelsModel->addHit( $channel['id'] );
			
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
			$channelsModel = new Xmltv_Model_Channels();
			$channelAlias = $this->input->getEscaped('channel');
			if ($this->cache->enabled){
				$f = '/Channels';
				$hash = $this->cache->getHash('channel_'.$channelAlias);
				if (($channel = $this->cache->load($hash, 'Core', $f))===false) {
					$channel = $channelsModel->getByAlias($channelAlias);
					$this->cache->save($channel, $hash, 'Core', $f);
				}
			} else {
				$channel = $channelsModel->getByAlias($channelAlias);
			}
			$this->view->assign('channel', $channel);
			
			//Attach comments model
			$commentsModel = new Xmltv_Model_Comments();
				
			//Fetch and parse feed
			if ($this->cache->enabled){
				$f = '/Feeds/Yandex';
				$hash = $this->cache->getHash('YandexRss_'.$channelAlias);
				if (($feedData = $this->cache->load($hash, 'Core', $f))===false) {
					$feedData = $commentsModel->getYandexRss( array( ' телеканал "'.$channel['title'].'"', $currentProgram->title ) );
					$this->cache->save($feedData, $hash, 'Core', $f);
				}
			} else {
				$feedData = $commentsModel->getYandexRss( array( ' телеканал "'.$channel['title'].'"', $currentProgram->title ) );
			}
			
			//var_dump($feedData);
			//die(__FILE__.': '.__LINE__);
			
			if ($new = $commentsModel->parseYandexFeed( $feedData, 164 )){
				if (count($new)>0){
					$commentsModel->saveComments($new, $channel['alias'], 'channel');
					$this->view->assign('items', $new);
				}
			}
			
			if (APPLICATION_ENV=='development'){
				var_dump($new);
				die(__FILE__.': '.__LINE__);
			}
		}
		 
	}
	
}

