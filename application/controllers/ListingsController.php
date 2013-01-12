<?php
/**
 * Frontend programs listings controller
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: ListingsController.php,v 1.16 2013-01-12 09:06:22 developer Exp $
 *
 */
class ListingsController extends Xmltv_Controller_Action
{

	protected $kidsChannels=array();
	
	/**
	 * (non-PHPdoc)
	 * @see Zend_Controller_Action::init()
	 */
	public function init () {
		
		parent::init();
		
		$kc = Zend_Registry::get('site_config')->channels->kids;
		if (stristr($kc, ',')){
			$kc = explode(',', $kc);
			foreach ($kc as $k=>$c){
				if (!empty($c)) {
					$this->kidsChannels[$k] = intval($c);
				}
			}
		} else {
			if (!is_numeric($kc)){
				throw new Exception("Wrong data in Config site.ini");
			}
			$this->kidsChannels = intval($kc);
		}
		
		$ajaxContext = $this->_helper->getHelper( 'AjaxContext' );
		$ajaxContext->addActionContext( 'update-comments', 'html' )
			->initContext();
		
	}

	/**
	 * Index page
	 */
	public function indexAction () {
		
		if (parent::requestParamsValid()===true){
			$this->_forward( 'day' );
		} else {
			$this->__call($this->_getParam('method'));
		}
	}

	/**
	 * Forward request to dayListingAction()
	 */
	public function dayDateAction(){
		
		$this->_forward('day-listing');
		
	}
	
	/**
	 * Programs listing for 1 particular day
	 * @throws Zend_Exception
	 */
	public function dayListingAction () {
	    
		if (parent::requestParamsValid()){
			
		    $this->view->assign( 'pageclass', 'day-listing' );
			
			//Load models
			$programsModel = new Xmltv_Model_Programs( array('nocache'=>parent::$nocache));
			$channelsModel = new Xmltv_Model_Channels();
			$videosModel   = new Xmltv_Model_Videos();
			$commentsModel = new Xmltv_Model_Comments();
			
			//Current channel
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
			
			//var_dump($channelAlias);
			//var_dump($channel);
			//var_dump(!isset($channel->ch_id) || @empty($channel->ch_id));
			//die(__FILE__.': '.__LINE__);
			
			if (!isset($channel->ch_id) || @empty($channel->ch_id)){
				$this->render('channel-not-found');
				return true;
			}
			$this->view->assign('channel', $channel );
			//die(__FILE__.': '.__LINE__);
			
			/**
			 * Get current date from request variable
			 */
			if (preg_match('/^[\d]{2}-[\d]{2}-[\d]{4}$/', $this->input->getEscaped('date'))) {
				$listingDate = new Zend_Date( new Zend_Date( $this->input->getEscaped('date'), 'dd-MM-yyyy' ), 'dd-MM-yyyy' );		   	
			} elseif (preg_match('/^[\d]{4}-[\d]{2}-[\d]{2}$/', $this->input->getEscaped('date'))) {
				$listingDate = new Zend_Date( new Zend_Date( $this->input->getEscaped('date'), 'yyyy-MM-dd' ), 'yyyy-MM-dd' );
			} else {
				$listingDate = new Zend_Date();
			}
		   	//die(__FILE__.': '.__LINE__);
			$this->view->assign('listing_date', $listingDate);
			
			//Assign today's date to view 
			if ($listingDate->isToday()) {
				$this->view->assign('is_today', true);
			} else {
				$this->view->assign('is_today', false);
			}
			$this->view->assign('listing_date', $listingDate );
			
			//Detect timeshift and adjust listing time
			$timeShift = (int)$this->input->getEscaped('tz', 0);
			if ($timeShift!=0) {
				$listingDate->addHour($timeShift);
			}
			$this->view->assign('timeshift', $timeShift);
			
			//die(__FILE__.': '.__LINE__);
			
			/*
			 * ######################################################
			 * Fetch programs list for day and make decision on current program
			 * ######################################################
			 * (1)Load programs list for day
			 * 
			 */
			if ($this->cache->enabled){
				$f = "/Listings/Programs";
				$hash = Xmltv_Cache::getHash( $channel->ch_id.'_'.$listingDate->toString('U') );
				if (!$list = $this->cache->load($hash, 'Core', $f)) {
					$list = $programsModel->getProgramsForDay( $listingDate, $channel->ch_id );
					$this->cache->save($list, $hash, 'Core', $f);
				}
			} else {
				$list = $programsModel->getProgramsForDay( $listingDate, $channel->ch_id );
			}
			//var_dump($list);
			//die(__FILE__.': '.__LINE__);
			$this->view->assign( 'programs', $list );
			
			
			
			/**
			 * (2) Detect current program or start of the day
			 */
			$now = new Zend_Date();
			if (!$listingDate->isToday()){
				if ((int)$now->toString('H')>0){
					do {
						$now->subHour(1);
					} while ((int)$now->toString('H')>0);
				}
					
				if ((int)$now->toString('m')>0){
					do {
						$now->subMinute(1);
					} while ((int)$now->toString('m')>0);
				}
				if ((int)$now->toString('s')>0){
					do {
						$now->subSecond(1);
					} while ((int)$now->toString('m')>1);
				}
			}
			
			/*
			 * Get current program
			 */
			$currentProgram=null;
			if ($list){
				foreach ($list as $list_item) {
					$start = $list_item->start;
					$end   = $list_item->end;
					if ($list_item->now_showing===true){
						$currentProgram = $list_item;
					}
					$currentProgram = $list[0];
				}
				if ($currentProgram===null){
					$currentProgram = $list[0];
					$list[0]->now_showing=true;
				}
			}
			
			//(3) Update start and end times of each program in listing
			if ($this->_getParam('tz', null)!==null) {
				foreach ($list as $item) {
					$item->start = $item->start->addHour($timeShift);
					$item->end   = $item->end->addHour($timeShift);
					$this->view->headMeta()->setName('robots', 'noindex,follow');
				}
			}
			
			/*
			 * Данные для модуля самых популярных программ
			 */
			$this->view->assign('top_programs', 
				$this->getTopPrograms((int)Zend_Registry::get('site_config')
					->topprograms->channellist->get('amount')));
			
			/*
			 * Данные для модуля категорий каналов
			 */
			$this->view->assign('channels_cats', $this->getChannelsCategories());
			
			/*
			 * ######################################################
			 * Комменты
			 * ######################################################
			 */
			if ((bool)Zend_Registry::get('site_config')->channels->comments->get('enabled', true)){
				if ($this->cache->enabled){
					$f = '/Feeds/Yandex';
					$hash = Xmltv_Cache::getHash('channel_comments_'.$channel->title);
					if (!$channelComments = $this->cache->load($hash, 'Core', $f)) {
						$channelComments  = $commentsModel->channelComments( $channel->title );
						$this->cache->save($channelComments, $hash, 'Core', $f);
					}
				} else {
					$channelComments = $commentsModel->channelComments( $channel->title );
				}
			}
			$this->view->assign('comments', $channelComments);
			
			/*
			 * Данные для модуля видео в правой колонке
			 */
			$videos = $videosModel->getSidebarVideos( 'канал '.Xmltv_String::strtolower( $channel->title) );
			$this->view->assign('sidebar_videos', $videos);
			//die(__FILE__.': '.__LINE__);
			
			/*
			 * Видео для списка программ
			 */
			$videos = $videosModel->getRelatedVideos( $list, $channel->title, $listingDate->toString('dd-MM-yyyy'));
			$this->view->assign('listing_videos', $videos);
			//die(__FILE__.': '.__LINE__);
			
			
			/*
			 * ######################################################
			 * Torrents
			 * ######################################################
			*/
			if ((bool)Zend_Registry::get('site_config')->channels->torrents->get('enabled')===true) {
				
				$url  = 'http://torrent-poisk.com/search.php?q='.urlencode($channel->title).'&r=0&qsrv='.urlencode($channel->title);
				$curl = new Xmltv_Parser_Curl();
				$curl->setOption(CURLOPT_CONNECTTIMEOUT, 8);
				$curl->setOption(CURLOPT_TIMEOUT, 8);
				$curl->setUrl($url);
				$curl->setUserAgent(@$_SERVER['HTTP_USER_AGENT']);
				$f = '/Torrents/Programs';
				$hash = Xmltv_Cache::getHash($url);
				if ($this->cache->enabled){
					if (($html = $this->cache->load($hash, 'Core', $f))===false) {
						$html = $curl->fetch(Xmltv_Parser_Curl::PAGE_HTML);
						$this->cache->save($html, $hash, 'Core', $f);
					}
				} else {
					$html = $curl->fetch(Xmltv_Parser_Curl::PAGE_HTML);
				}
				
				if ($html){
					$dom = new DOMDocument('1.0', 'UTF-8');
					$dom->preserveWhiteSpace = false;
					$dom->recover = true;
					$dom->strictErrorChecking = false;
					@$dom->loadHTML($html);
					$xpath = new DOMXPath($dom);
					$links = $xpath->query("descendant-or-self::a[contains(concat(' ', normalize-space(@class), ' '), ' visit ')]");
					$torrentLinks = array();
					if ($this->cache->enabled){
						$hash = Xmltv_Cache::getHash('tinyurl_'.$url);
						$f	= '/Tinyurl/Torrents';
						if (($torrentLinks = $this->cache->load($hash, 'Core', $f))===false) {
							$torrentLinks = parent::torrentsShortLinks($links);
							$this->cache->save($torrentLinks, $hash, 'Core', $f);
						}
					} else {
						$torrentLinks = parent::torrentsShortLinks($links);
					}
					$this->view->assign('torrent_links', $torrentLinks);
					
					if (APPLICATION_ENV=='development'){
						//var_dump($torrentLinks);
						//die(__FILE__.': '.__LINE__);
					}
				}
			}
			
			$tinyUrl = $this->getTinyUrl(array('channel'=>$channel->alias), 
			'default_listings_day-listing',
			array(
				$this->_getParam('module'),
				$this->_getParam('controller'),
				$this->_getParam('action'),
				$channel->ch_id,
			));
			$this->view->assign('short_link', $tinyUrl);
			
			//Add hit for channel and model
			$channelsModel->addHit( $channel->ch_id );
			if ($currentProgram)
				$programsModel->addHit( $currentProgram );
			
			
		}
		
	}
	
	/**
	 * Выпуски выбранной пользователем передачи сегодня и 
	 * список похожих по названию передач сегодня на других каналах
	 * 
	 * @throws Zend_Exception
	 */
	public function programDayAction () {
		
		// Validation routines
		if (parent::requestParamsValid()) {
		    
		    $programAlias = $this->input->getEscaped('alias');
		    
		    /**
			 * @todo
			 */
			if ( $this->input->getEscaped('date')=='неделя' ) {
				$this->_forward('program-week', 'listings', 'default', array( 'date'=>Zend_Date::now()->toString('dd-MM-yyyy') ));
				return true;
			}
			
			$programsModel = new Xmltv_Model_Programs(array(
				'week_days'=>$this->_helper->getHelper('WeekDays'),
			));
			$channelsModel = new Xmltv_Model_Channels();
			$videosModel   = new Xmltv_Model_Videos();
			$commentsModel = new Xmltv_Model_Comments();
			
			//Current channel
			$channelAlias = $this->input->getEscaped('channel');
			//var_dump($channelAlias);
			if ($this->cache->enabled){
				$f = '/Channels';
				$hash = $this->cache->getHash('channel_'.$channelAlias);
				if (($channel = $this->cache->load( $hash, 'Core', $f))===false) {
					$channel = $channelsModel->getByAlias($channelAlias);
					$this->cache->save($channel, $hash, 'Core', $f);
				}
			} else {
				$channel = $channelsModel->getByAlias($channelAlias);
			}
			$this->view->assign('channel', $channel );
			//var_dump($channel);
			//die(__FILE__.': '.__LINE__);
			
			$dg = $this->input->getEscaped('date');
			if ($dg!='сегодня' && $dg!='неделя') {
				if (preg_match('/^[\d]{2}-[\d]{2}-[\d]{4}$/', $dg)) {
					$listingDate = new Zend_Date($this->input->getEscaped('date'), 'dd-MM-yyyy');
				} elseif (preg_match('/^[\d]{4}-[\d]{2}-[\d]{2}$/', $dg)) {
					$listingDate = new Zend_Date($this->input->getEscaped('date'), 'yyyy-MM-dd');
				} else {
					$listingDate = Zend_Date::now();
				}
			} else {
				$listingDate = Zend_Date::now();
			}
			
			$l = (int)Zend_Registry::get('site_config')->listings->history->get('length');
			$this->view->assign( 'history_length', $l);
			$maxAgo = new Zend_Date( Zend_Date::now()->subDay($l)->toString('U'), 'U' ) ;
			if ($listingDate->compare($maxAgo)==-1){ //More than x days
			    $this->view->assign('hide_sidebar', 'right');
				$this->_forward('outdated');
				return true;
			}
			
			$this->view->assign('notfound', false);
			$this->view->assign('nosimilar', false);
			$currentProgram = $programsModel->getSingle( 
				$this->input->getEscaped('alias'), $channel->ch_id, $listingDate );
			
			
			$tinyUrl = $this->getTinyUrl(array('channel'=>$channel->alias), 
			'default_listings_day-listing',
			array(
				$this->_getParam('module'),
				$this->_getParam('controller'),
				$this->_getParam('action'),
				$channel->ch_id,
			));
			$this->view->assign('short_link', $tinyUrl);
			
			
			/*
			 * Данные для модуля видео в правой колонке
			*/
			$videos = $videosModel->getSidebarVideos( 'канал '.Xmltv_String::strtolower( $channel->title) );
			$this->view->assign('sidebar_videos', $videos);
			//die(__FILE__.': '.__LINE__);
			
			if (empty($currentProgram)){
			    
			    $this->view->assign('notfound', true);
			    if ($this->cache->enabled){
			    	$f = '/Listings/Similar';
			    	$hash = $this->cache->getHash('similarPrograms_'.$programAlias);
			    	if (($similarPrograms = $this->cache->load( $hash, 'Core', $f))===false) {
			    		$similarPrograms = $programsModel->getSimilarProgramsThisWeek(
			        		$this->input->getEscaped('alias'), $listingDate, $this->weekDays->getEnd($listingDate));
			    		$this->cache->save($similarPrograms, $hash, 'Core', $f);
			    	}
			    } else {
			        $similarPrograms = $programsModel->getSimilarProgramsThisWeek(
			        		$this->input->getEscaped('alias'), $listingDate, $this->weekDays->getEnd($listingDate));
			    }
			    
			    
			    
			    if(!empty($currentProgram)){
			        $this->render('similar-week');
			        return true;
			    } else {
			        $this->view->assign('hide_sidebar', 'right');
			        $this->render('program-not-found');
			        return true;
			    }
			    
			} else {
			    
			    //var_dump($currentProgram);
			    //var_dump($channel->alias);
			    //var_dump($listingDate);
			    //die(__FILE__.': '.__LINE__);
			    
			    $list = $programsModel->getProgramForDay( 
			    	$currentProgram['alias'], $channel->alias, $listingDate );
			    	
			    $this->view->assign( 'programs', $list );
			    $this->view->assign( 'current_program', $currentProgram );
			    $this->view->assign( 'channel', $channel  );
			    $this->view->assign( 'date', $listingDate );
			    $this->view->assign( 'pageclass', 'program-day' );
			    	
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
			    	
			    
			    /*
			     * Видео для списка программ
			    */
			    $videos = $videosModel->getRelatedVideos( $list, $channel->title, $listingDate->toString('dd-MM-yyyy'));
			    $this->view->assign('listing_videos', $videos);
			    //die(__FILE__.': '.__LINE__);
			    
			    /*
			     * ######################################################
			    * Comments for current channel
			    * ######################################################
			    */
			    if ((bool)Zend_Registry::get('site_config')->channels->comments->get('enabled', true)){
			    	if ($this->cache->enabled){
			    		$f = '/Feeds/Yandex';
			    		$hash = Xmltv_Cache::getHash('channel_comments_'.$channel->alias);
			    		if (!$channelComments = $this->cache->load($hash, 'Core', $f)) {
			    			$channelComments  = $commentsModel->channelComments( $channel->title );
			    			$this->cache->save($channelComments, $hash, 'Core', $f);
			    		}
			    	} else {
			    		$channelComments = $commentsModel->channelComments( $channel->title );
			    	}
			    }
			    //var_dump($channelComments);
			    //die(__FILE__.': '.__LINE__);
			    $this->view->assign('comments', $channelComments);
			    
			    
			    	
			    /*
				 * ######################################################
				 * Torrents
				 * ######################################################
				*/
				if ((bool)Zend_Registry::get('site_config')->channels->torrents->get('enabled')===true) {
					
					$url  = 'http://torrent-poisk.com/search.php?q='.urlencode($channel->title).'&r=0&qsrv='.urlencode($channel->title);
					$curl = new Xmltv_Parser_Curl();
					$curl->setOption(CURLOPT_CONNECTTIMEOUT, 8);
					$curl->setOption(CURLOPT_TIMEOUT, 8);
					$curl->setUrl($url);
					$curl->setUserAgent(@$_SERVER['HTTP_USER_AGENT']);
					$f = '/Torrents/Programs';
					$hash = Xmltv_Cache::getHash($url);
					if ($this->cache->enabled){
						if (($html = $this->cache->load($hash, 'Core', $f))===false) {
							$html = $curl->fetch(Xmltv_Parser_Curl::PAGE_HTML);
							$this->cache->save($html, $hash, 'Core', $f);
						}
					} else {
						$html = $curl->fetch(Xmltv_Parser_Curl::PAGE_HTML);
					}
					
					if ($html){
						$dom = new DOMDocument('1.0', 'UTF-8');
						$dom->preserveWhiteSpace = false;
						$dom->recover = true;
						$dom->strictErrorChecking = false;
						@$dom->loadHTML($html);
						$xpath = new DOMXPath($dom);
						$links = $xpath->query("descendant-or-self::a[contains(concat(' ', normalize-space(@class), ' '), ' visit ')]");
						$torrentLinks = array();
						if ($this->cache->enabled){
							$hash = Xmltv_Cache::getHash('tinyurl_'.$url);
							$f	= '/Tinyurl/Torrents';
							if (($torrentLinks = $this->cache->load($hash, 'Core', $f))===false) {
								$torrentLinks = parent::torrentsShortLinks($links);
								$this->cache->save($torrentLinks, $hash, 'Core', $f);
							}
						} else {
							$torrentLinks = parent::torrentsShortLinks($links);
						}
						$this->view->assign('torrent_links', $torrentLinks);
						
						if (APPLICATION_ENV=='development'){
							//var_dump($torrentLinks);
							//die(__FILE__.': '.__LINE__);
						}
					}
				}
				
				$tinyUrl = $this->getTinyUrl(array('channel'=>$channel->alias), 
				'default_listings_day-listing',
				array(
					$this->_getParam('module'),
					$this->_getParam('controller'),
					$this->_getParam('action'),
					$channel->ch_id,
				));
				$this->view->assign('short_link', $tinyUrl);
			    	
			    
			    	
			    /*
			     * Add hit for channel and model
			    */
			    $channelsModel->addHit( $channel->ch_id );
			    //if ($currentProgram)
			    //	$programsModel->addHit( $currentProgram );
			    	
			    //die(__FILE__.': '.__LINE__);
			    
			}
			
		}
		
	}
	
	
	/**
	 * 
	 * @throws Zend_Exception
	 */
	public function programWeekAction(){
		
		if (parent::requestParamsValid()) {
			
		    $this->view->assign( 'pageclass', 'program-week' );
		    $programAlias = $this->input->getEscaped('alias');
		    
			$channelsModel = new Xmltv_Model_Channels();
			$programsModel = new Xmltv_Model_Programs();
			
			$channel = $channelsModel->getByAlias( $this->input->getEscaped('channel') );
			if (!isset($channel->ch_id)){
				throw new Zend_Exception( self::ERR_MISSING_CHANNEL_INFO.$channel, 500);
			}
			$this->view->assign('channel', $channel );
			
			$dg = $this->input->getEscaped('date');
			if ($dg!='сегодня' && $dg!='неделя') {
				if (preg_match('/^[\d]{2}-[\d]{2}-[\d]{4}$/', $dg)) {
					$listingDate = new Zend_Date($this->input->getEscaped('date'), 'dd-MM-yyyy');
				} elseif (preg_match('/^[\d]{4}-[\d]{2}-[\d]{2}$/', $dg)) {
					$listingDate = new Zend_Date($this->input->getEscaped('date'), 'yyyy-MM-dd');
				} elseif (!$this->input->getEscaped('date')) {
					$listingDate = Zend_Date::now();
				}
			} else {
				$listingDate = Zend_Date::now();
			}
			
			$currentProgram = $programsModel->getSingle( $this->input->getEscaped('alias'), $channel->ch_id, $listingDate);
			//var_dump($currentProgram);
			//die(__FILE__.': '.__LINE__);
			$weekDays  = $this->_helper->getHelper('weekDays');
			$weekStart = $weekDays->getStart( $listingDate );
			$weekEnd   = $weekDays->getEnd( $listingDate );
			
			if (empty($currentProgram)){
				
			    $this->view->assign('notfound', true);
				if ($this->cache->enabled){
					$f = '/Listings/Similar';
					$hash = $this->cache->getHash('similarPrograms_'.$programAlias);
					if (($similarPrograms = $this->cache->load( $hash, 'Core', $f))===false) {
						$similarPrograms = $programsModel->getSimilarProgramsThisWeek( 
							$this->input->getEscaped('alias'), $weekStart, $weekEnd );
						$this->cache->save($similarPrograms, $hash, 'Core', $f);
					}
				} else {
					$similarPrograms = $programsModel->getSimilarProgramsThisWeek( 
							$this->input->getEscaped('alias'), $weekStart, $weekEnd );
				}

				if(!empty($currentProgram)){
					$this->render('similar-week');
					return true;
				} else {
					$this->view->assign('hide_sidebar', 'right');
					$this->render('program-not-found');
					return true;
				}
				 
			} else {
			
				$list = $programsModel->getProgramThisWeek( $this->input->getEscaped('alias'), $channel->ch_id, $weekStart, $weekEnd );
				$programsModel->addHit( $currentProgram );
				$channelsModel->addHit( $channel->ch_id );
				
				$this->view->assign( 'week_start', $weekStart );
				$this->view->assign( 'week_end', $weekEnd );
				$this->view->assign( 'list', $list );
				$this->view->assign( 'similar', $similarPrograms );
				$this->view->assign( 'program', $currentProgram );
				$this->view->assign( 'channel', $channel );
				
			
			}
			/*
			 * Данные для модуля категорий каналов
			*/
			$this->view->assign('channels_cats', $this->getChannelsCategories());
			
		}
		
	}
	
	/**
	 * Search for channel
	 */
	public function searchAction(){
	
		if ( $this->requestParamsValid()){
			$model   = new Xmltv_Model_Channels();
			$search  = $this->_getParam('fs');
			$channel = $model->getByTitle($search);
			$redirectUrl = $this->view->url(array(1=>$this->view->escape($channel->alias)), 'default_listings_day-listing');
			$this->_redirect( $redirectUrl, array('exit'=>true));
		} else {
			$this->_redirect( $this->view->url(array(), 'default_error_missing-page'), array('exit'=>true));
		}
	
	}
	
	
	
	/**
	 * Top programs for left sidebar
	 * 
	 * @param int $amt
	 * @return unknown
	 */
	protected function getTopPrograms($amt=20){
		
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
		return $topPrograms;
		
	}
	
	/**
	 * Channels categories
	 */
	protected function getChannelsCategories(){
		
		$model = new Xmltv_Model_Channels();
		if ($this->cache->enabled){
			$f = "/Channels";
			$hash  = Xmltv_Cache::getHash("ChannelsCategories");
			if (!$cats = $this->cache->load($hash, 'Core', $f)) {
				$cats = $model->channelsCategories();
				$this->cache->save($cats, $hash, 'Core', $f);
			}
		} else {
			$cats = $model->channelsCategories();
		}
		return $cats;
		
	}

	/**
	 * Programs categories
	 */
	protected function getProgramsCategories(){
		
		$table = new Xmltv_Model_DbTable_ProgramsCategories();
		if ($this->cache->enabled){
			$f = "/Channels";
			$hash  = Xmltv_Cache::getHash("ProgramsCategories");
			if (!$cats = $this->cache->load($hash, 'Core', $f)) {
				$cats = $table->fetchAll();
				$this->cache->save($cats, $hash, 'Core', $f);
			}
		} else {
			$cats = $table->fetchAll();
		}
		return $cats;
		
	}
	
	
	/**
	 * Категория программ за неделю
	 */
	public function categoryAction(){
		
		$cats = $this->getProgramsCategories();
		if ( $this->requestParamsValid( array('programsCategories'=>$cats))){
			
		    $model    = new Xmltv_Model_Programs();
		    $weekDays = $this->_helper->getHelper('WeekDays');
		    $today    = new Zend_Date();
			switch ($this->input->getEscaped('timespan')){
			    
			    case 'неделя':
			        $list = $model->categoryWeek( $this->input->getEscaped('category'), $weekDays->getStart($today), $weekDays->getEnd($today));	
			    	die(__FILE__.': '.__LINE__);
			    		
			    	break;
			    
			    case 'сегодня':
			        $list = $model->categoryDay( $this->input->getEscaped('category'), $today);
					die(__FILE__.': '.__LINE__);
					
					break;
					
				
			}
			
		} else {
			$this->_redirect( $this->view->url(array(), 'default_error_missing-page'), array('exit'=>true));
		}
		
	}
	
	/**
	 * Outdated listing
	 */
	public function outdatedAction(){
		
	}
	
}

