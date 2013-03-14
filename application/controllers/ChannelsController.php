<?php

/**
 * Frontend Channels controller
 * 
 * @author  Antony Repin
 * @version $Id: ChannelsController.php,v 1.25 2013-03-14 06:09:55 developer Exp $
 *
 */
class ChannelsController extends Rtvg_Controller_Action
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
		
		if (parent::validateRequest()) {
			
			$this->channelsModel = new Xmltv_Model_Channels();
			$this->view->assign('pageclass', 'allchannels');
			
			if ($this->cache->enabled){
			    
			    $this->cache->setLifetime(86400);
			    $this->cache->setLocation(ROOT_PATH.'/cache');
			    $f = '/Channels';
				
			    $hash = Rtvg_Cache::getHash('published_channels');
				if (!$rows = $this->cache->load($hash, 'Core', $f)) {
					$rows = $this->channelsModel->getPublished();
					$this->cache->save($rows, $hash, 'Core', $f);
				}
			} else {
				$rows = $this->channelsModel->getPublished( $this->view );
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
			$this->view->assign('channels_cats', $this->getChannelsCategories());
			
			/*
			 * #####################################################################
			 * Данные для модуля самых популярных программ
			 * #####################################################################
			 */
			$top = $this->topPrograms();
			$this->view->assign('top_programs', $top);
		}
		
	}
	
	/**
	 * Channels for typeahead script
	 */
	public function typeaheadAction () {
		
		if (parent::validateRequest()) {
			
			$channelsCategories = new Xmltv_Model_DbTable_ChannelsCategories();
			if ($this->_getParam('c')) {
				$category = $channelsCategories->fetchRow("`alias` LIKE '".$this->input->getEscaped('c')."'")->toArray();
			}
			
			$hash = Rtvg_Cache::getHash( 'typeahead_all' );
			if ($this->cache->enabled) {
			    $this->cache->setLocation(ROOT_PATH.'/cache');
			    $this->cache->setLifetime(86400*7);
			    $f = "/Channels";
				if (($items = $this->cache->load( $hash, 'Core', $f))===false){
					$items = $this->channelsModel->getTypeaheadItems( $category['id']);
					$this->cache->save($items, $hash, 'Core', $f);
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
		
		if (parent::validateRequest()) {
		   
			$this->view->assign('pageclass', 'category');
			$this->channelsModel = new Xmltv_Model_Channels();
			$catProps = $this->channelsModel->category( $this->input->getEscaped('category') )->toArray();
			$this->view->assign('category', $catProps);
			
			if (isset($_GET['RTVG_PROFILE'])){
				//Zend_Debug::dump($this->cache->enabled);
				//die(__FILE__.': '.__LINE__);
			}
			
			if ($this->cache->enabled){
			    
			    $this->cache->setLifetime(86400);
			    $this->cache->setLocation(ROOT_PATH.'/cache');
			    $f = "/Channels/Category";
			    
				$hash = md5('category_'.$catProps['alias']);
				if (!$rows = $this->cache->load($hash, 'Core', $f)){
					$rows = $this->channelsModel->categoryChannels($catProps['alias']);
					
					if (isset($_GET['RTVG_PROFILE'])){
						//Zend_Debug::dump($rows);
						//die(__FILE__.': '.__LINE__);
					}
					
					foreach ($rows as $k=>$row) {
				    	$rows[$k]['icon'] = $this->view->baseUrl('images/channel_logo/'.$row['icon']);
				    }
					$this->cache->save($rows, $hash, 'Core', $f);
				}
				
				if (isset($_GET['RTVG_PROFILE'])){
					//Zend_Debug::dump($rows);
					//die(__FILE__.': '.__LINE__);
				}
				
			} else {
				$rows = $this->channelsModel->categoryChannels($catProps['alias']);
				foreach ($rows as $k=>$row) {
					$rows[$k]['icon'] = $this->view->baseUrl('images/channel_logo/'.$row['icon']);
				}
			}
			
			if (isset($_GET['RTVG_PROFILE'])){
			    //Zend_Debug::dump($rows);
			    //die(__FILE__.': '.__LINE__);
			}
			
			$this->view->assign('channels', $rows);
			
			/*
			 * #####################################################################
			 * Данные для модуля самых популярных программ
			 * #####################################################################
			 */
			$this->view->assign('top_programs', $this->topPrograms());
			
			/*
			 * ######################################################
			 * Channels categories
			 * ######################################################
			*/
			if ($this->cache->enabled){
			    
				$this->cache->setLocation(ROOT_PATH.'/cache');
				$this->cache->setLifetime(86400);
				$f = "/Channels";
				
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
		if (parent::validateRequest()) {
			
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
				$hash = Rtvg_Cache::getHash('channel_'.$channel['alias'].'_week');
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
		
		if (parent::validateRequest()){
			
			// Channel properties
			$this->channelsModel = new Xmltv_Model_Channels();
			$channelAlias = $this->input->getEscaped('channel');
			
			if ($this->cache->enabled){
			    
			    $this->cache->setLocation(ROOT_PATH.'/cache');
			    $this->cache->setLifetime(86400*7);
				$f  = '/Channels';
				
				$hash = $this->cache->getHash('channel_'.$channelAlias);
				if (($channel = $this->cache->load($hash, 'Core', $f))===false) {
					$channel = $this->channelsModel->getByAlias( $channelAlias );
					$this->cache->save($channel, $hash, 'Core', $f);
				}
			} else {
				$channel = $this->channelsModel->getByAlias($channelAlias);
			}
			$this->view->assign('channel', $channel);
			
			//Attach comments model
			$commentsModel = new Xmltv_Model_Comments();
			if ($this->cache->enabled){
			    
			    $this->cache->setLocation(ROOT_PATH.'/cache');
			    $this->cache->setLifetime(86400);
			    $f  = '/Feeds/Yandex';
			    
			    $hash = $this->cache->getHash( 'comments_'.$channelAlias);
			    if (($new = $this->cache->load( $hash, 'Core', $f))===false) {
			    	$feedData = $commentsModel->getYandexRss( array( 'телеканал "'.Xmltv_String::strtolower($channel['title']).'"') );
			    	if (false !== ($new = $commentsModel->parseYandexFeed( $feedData ))){
			    		$commentsModel->saveChannelComments( $new, $channel['id']);
			    		$this->cache->save( $new, $hash, 'Core', $f);
			    	}
			    }
			} else {
			    $feedData = $commentsModel->getYandexRss( array( 'телеканал "'.Xmltv_String::strtolower($channel['title']).'"') );
			    $new = $commentsModel->parseYandexFeed( $feedData );
			}
			
			$this->view->assign('items', $new);
			
		}
		 
	}
	
}

