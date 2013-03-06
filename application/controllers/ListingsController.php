<?php
/**
 * Programs listings display
 * 
 * @author  Antony Repin
 * @uses    Xmltv_Controller_Action
 * @version $Id: ListingsController.php,v 1.28 2013-03-06 03:52:37 developer Exp $
 *
 */
class ListingsController extends Xmltv_Controller_Action
{
	
	/**
	 * (non-PHPdoc)
	 * @see Zend_Controller_Action::init()
	 */
	public function init() {
		
		parent::init();
		
		$ajaxContext = $this->_helper->getHelper( 'AjaxContext' );
		$ajaxContext->addActionContext( 'update-comments', 'html' )
			->initContext();

		//var_dump($expression);
		
	}

	/**
	 * Index page
	 */
	public function indexAction () {
		
		if (parent::requestParamsValid()===true){
			$this->_forward( 'day' );
		} else {
			$this->_flashMessenger->addMessage( Rtvg_Message::ERR_WRONG_PARAM );
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
			
			$channel = parent::channelInfo();
			if (!isset($channel['id']) || empty($channel['id'])){
				$this->render('channel-not-found');
				return true;
			}
			$this->view->assign('channel', $channel );
			
			
			/*
			 * #####################################################################
			 * Данные для модуля категорий каналов
			 * #####################################################################
			 */
			$cats = $this->getChannelsCategories();
			$this->view->assign('channels_cats', $cats);
			
			/*
			 * #####################################################################
			 * Текущая дата
			 * #####################################################################
			 */
			$listingDate = parent::listingDate();
			$this->view->assign('listing_date', $listingDate);
			
			if (APPLICATION_ENV=='development'){
				//var_dump($listingDate->toString());
				//die(__FILE__.': '.__LINE__);
			}
			
			$l = (int)Zend_Registry::get('site_config')->listings->history->get('length');
			if (parent::checkDate($listingDate, $l)===false){
			    $this->view->assign( 'history_length', $l);
				$this->view->assign('hide_sidebar', 'right');
				$this->render('outdated');
				return true;
			}
			
			if (APPLICATION_ENV=='development'){
				//var_dump($listingDate->toString());
				//die(__FILE__.': '.__LINE__);
			}
			
			//Assign today's date to view 
			if ($listingDate->isToday()) {
				$this->view->assign('is_today', true);
			} else {
				$this->view->assign('is_today', false);
			}
			
			/*
			 * ###################################################################
			 * Detect timeshift and adjust listing time
			 * ###################################################################
			 */
			$timeShift = (int)$this->input->getEscaped('tz', 0);
			if ($timeShift!=0) {
				$listingDate->addHour($timeShift);
			}
			$this->view->assign('timeshift', $timeShift);
				
			if (APPLICATION_ENV=='development'){
				//var_dump($listingDate->toString());
				//var_dump($timeShift);
				//die(__FILE__.': '.__LINE__);
			}
			
			/*
			 * #####################################################################
			 * (2) Detect time
			 * #####################################################################
			 */
			/*
			if ($listingDate->isToday()===false){
			    
				if ((int)$listingDate->toString('H')>0){
					do {
						$listingDate->subHour(1);
					} while ((int)$listingDate->toString('H')>0);
				}
					
				if ((int)$listingDate->toString('m')>0){
					do {
						$listingDate->subMinute(1);
					} while ((int)$listingDate->toString('m')>0);
				}
				if ((int)$listingDate->toString('s')>0){
					do {
						$listingDate->subSecond(1);
					} while ((int)$listingDate->toString('m')>1);
				}
			} 
			
			$now = $listingDate;
			
			if (APPLICATION_ENV=='development' || isset($_GET['RTVG_PROFILE'])){
				//var_dump($now->toString('dd-MM-YYYY'));
				//die(__FILE__.': '.__LINE__);
			}
			*/
			
			$this->view->assign('listing_date', $listingDate);
			
			/*
			 * #####################################################################
			 * Данные для модуля самых популярных программ
			 * #####################################################################
			 */
			$top = $this->topPrograms();
			$this->view->assign('top_programs', $top);
			
			
			/*
			 * #####################################################################
			 * Fetch programs list for day and make decision on current program
			 * #####################################################################
			 * 
			 * 
			 * @todo (1)Load short programs list for day
			 * List include 4 items:
			 * Сейчас
			 * Затем
			 * Далее
			 * Потом
			 * 
			 */
			if ($this->cache->enabled) {
			    
			    $this->cache->setLocation(ROOT_PATH.'/cache');
			    $this->cache->setLifetime(600);
			    $f = "/Listings/Programs";
				
			    $hash = Rtvg_Cache::getHash( $channel['id'].'_'.$listingDate->toString('DDD') );
				if (!$list = $this->cache->load($hash, 'Core', $f)) {
					$list = $this->programsModel->getProgramsForDay( $listingDate, $channel['id'] );
					$this->cache->save($list, $hash, 'Core', $f);
				}
				
			} else {
				$list = $this->programsModel->getProgramsForDay( $listingDate, $channel['id'] );
			}
			
			if ($list===false){
			    $this->view->assign('hide_sidebar', 'right');
			    $this->render('no-listings');
			    return true;
			}
			
			$this->view->assign( 'programs', $list );
			
			if (APPLICATION_ENV=='development' || isset($_GET['RTVG_PROFILE'])){
				//var_dump(count($list));
				//var_dump($list);
				//var_dump($currentProgram);
				//die(__FILE__.': '.__LINE__);
			}
			
			foreach ($list as $li){
			    if ($li['now_showing']===true){
			        $currentProgram = $li;
			    }
			}
			
			
			/*
			 * #####################################################################
			 * Update start and end times of each program in listing
			 * #####################################################################
			 */
			if ($this->_getParam('tz', null)!==null) {
			    if ($timeShift!=0){
					foreach ($list as $item) {
					    if (APPLICATION_ENV=='development'){
					        //var_dump($item);
					        //die(__FILE__.': '.__LINE__);
					    }
						$item['start'] = $item['start']->addHour($timeShift);
						$item['end']   = $item['end']->addHour($timeShift);
						$this->view->headMeta()->setName('robots', 'noindex,follow');
					}
					
					if ($currentProgram){
						$currentProgram['start'] = $currentProgram['start']->addHour($timeShift);
						$currentProgram['end']   = $currentProgram['end']->addHour($timeShift);
					}
			    }
			}
			
			
			/*
			 * #####################################################################
			 * Видео для списка программ
			 * #####################################################################
			 * 
			 * @todo 
			 * Тестирование следующей последоватеьность работы с кэшем:
			 *   2. Запрос в файловый кэш
			 * 		Если найдено - сохранение в БД
			 */
			if (count($list) && ($list!==false)) {
			    
			    $listingVideos = array();
			    
			    // Запрос в файловый кэш
			    if ($this->cache->enabled){
			        
			    	$t = (int)Zend_Registry::get( 'site_config' )->cache->youtube->listings->get( 'lifetime' );
			    	$t>0 ? $this->cache->setLifetime($t): $this->cache->setLifetime(86400) ;
			    	$this->cache->setLocation( ROOT_PATH.'/cache' );
			    	$f = '/Listings/Videos';
			    	$hash = Rtvg_Cache::getHash( 'listingVideo_'.$channel['title'].'-'.$listingDate->toString( 'YYYY-MM-dd' ));
			    	
			    	// Если видео найдено в файловом кэше
			    	if (($listingVideos=$this->cache->load( $hash, 'Core', $f) )!==false){
			    	    
			    	    // Сохраняем в кэш БД если он включен
			    	    if (parent::$videoCache===true){
			    	        foreach ($listingVideos as $vid) {
			    	            if ($vid!==false){
				    	            $this->vCacheModel->saveListingVideo( $vid );
			    	            }
			    	        }
			    	    }
			    	    
			    	} else {
			    	    
			    	    // Если не видео не найдено в файловом кэше
			    	    // Ищем его в кэше БД если он включен
			    	    if (parent::$videoCache===true){
			    	        $listingVideos = $this->videosModel->dbCacheListingRelatedVideos( $list, $channel['title'], $listingDate );
			    	    }
			    	    
			    	    if (!$listingVideos){
			    	    	// Если не найдено ни в одном из кэшей, то делаем запрос к Yoututbe
			    	    	$listingVideos = $this->videosModel->ytListingRelatedVideos( $list, $channel['title'], $listingDate );
			    	    }
			    	    
			    	    if (count($listingVideos)) {
			    	        
			    	        // Сохранение в файловый кэш
			    	        $this->cache->save( $listingVideos, $hash, 'Core', $f);
			    	        
			    	        // Сохраняем в кэш БД если он включен
			    	        if (parent::$videoCache===true){
			    	        	foreach ($listingVideos as $vid) {
			    	        		if ($vid!==false){
			    	        		    $this->vCacheModel->saveListingVideo( $vid );
			    	        		}
			    	        	}
			    	        }
			    	        
			    	        $this->cache->save($listingVideos, $hash, 'Core', $f);
			    	        
			    	    }
			    	    
			    	}
			    } else {
			        
			        // Кэширование не используется 
			        // запрос к Yoututbe
			    	$listingVideos = $this->videosModel->ytListingRelatedVideos( $list, $channel['title'], $listingDate );
			    	
			    	if (parent::$videoCache===true){
				    	foreach ($listingVideos as $k=>$vid){
				    		if ($vid!==false){
				    			$this->vCacheModel->saveListingVideo( $vid, $k);
				    		}
				    	}
			    	}
			    }
			    
			} 
			$this->view->assign('listing_videos', $listingVideos);
			
			
			
			/*
			 * ######################################################
			 * Комменты
			 * ######################################################
			 */
			$this->view->assign('comments', parent::yandexComments($channel) );
			
			
			/* 
			 * ######################################################
			 * Данные для модуля видео в правой колонке
			 * ######################################################
			 */
			$this->view->assign('sidebar_videos', parent::sidebarVideos($channel) );
			
			
			
			/*
			 * ######################################################
			 * Torrents
			 * ######################################################
			*/
			if ((bool)Zend_Registry::get('site_config')->channels->torrents->get('enabled')===true) {
				
				$url  = 'http://torrent-poisk.com/search.php?q='.urlencode($channel['title']).'&r=0&qsrv='.urlencode($channel['title']);
				$curl = new Xmltv_Parser_Curl();
				$curl->setOption(CURLOPT_CONNECTTIMEOUT, 4);
				$curl->setOption(CURLOPT_TIMEOUT, 4);
				$curl->setUrl($url);
				$curl->setUserAgent(Zend_Registry::get('user_agent'));
				$f = '/Listings/Torrents';
				$hash = Rtvg_Cache::getHash($url);
				
				if ($this->cache->enabled){
				    $this->cache->setLocation(ROOT_PATH.'/cache');
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
					$this->cache->enabled = (bool)Zend_Registry::get('site_config')->cache->torrents->get('enabled');
					if ($this->cache->enabled){
					    $this->cache->setLocation(ROOT_PATH.'/cache');
						$hash = Rtvg_Cache::getHash('tinyurl_'.$url);
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
			
			$tinyUrl = $this->getTinyUrl(array('channel'=>$channel['alias']), 
			'default_listings_day-listing',
			array(
				$this->_getParam('module'),
				$this->_getParam('controller'),
				$this->_getParam('action'),
				$channel['id'],
			));
			$this->view->assign('short_link', $tinyUrl);
			
			//Add hit for channel and model
			$this->channelsModel->addHit( $channel['id'] );
			$this->view->assign('featured', $this->getFeaturedChannels());
			
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
			
			$this->programsModel = new Xmltv_Model_Programs(array(
				'week_days'=>$this->_helper->getHelper('WeekDays'),
			));
			$this->channelsModel = new Xmltv_Model_Channels();
			
			//Current channel
			$channelAlias = $this->input->getEscaped('channel');
			//var_dump($channelAlias);
			if ($this->cache->enabled){
				$f = '/Channels';
				$hash = $this->cache->getHash('channel_'.$channelAlias);
				if (($channel = $this->cache->load( $hash, 'Core', $f))===false) {
					$channel = $this->channelsModel->getByAlias($channelAlias);
					$this->cache->save($channel, $hash, 'Core', $f);
				}
			} else {
				$channel = $this->channelsModel->getByAlias($channelAlias);
			}
			$this->view->assign('channel', $channel );
			
			
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
			
			/*
			 * ##############################################################
			 * Check if requested date is older than allowed in history
			 * ##############################################################
			 */
			$l = (int)Zend_Registry::get('site_config')->listings->history->get('length');
			if (parent::checkDate($listingDate, $l)===false){
			    $this->view->assign( 'history_length', $l);
				$this->view->assign('hide_sidebar', 'right');
				$this->render('outdated');
				return true;
			}
			
			$this->view->assign('notfound', false);
			$this->view->assign('nosimilar', false);
			$this->view->assign('date', $listingDate);
			$currentProgram = $this->programsModel->getSingle( $this->input->getEscaped('alias'), $channel['id'], $listingDate );
			
			if (APPLICATION_ENV=='development'){
			    //var_dump($currentProgram);
			    //die(__FILE__.': '.__LINE__);
			}
			
			if ($currentProgram){
			    
			    /*
			     * ######################################################
			    * Спискок программ
			    * ######################################################
			    */
			    $list = $this->programsModel->getProgramForDay( $currentProgram['alias'], $channel['alias'], $listingDate );
			    	
			    $this->view->assign( 'programs', $list );
			    $this->view->assign( 'current_program', $currentProgram );
			    $this->view->assign( 'channel', $channel  );
			    $this->view->assign( 'date', $listingDate );
			    $this->view->assign( 'pageclass', 'program-day' );
			    	
			    /*
			     * #####################################################################
			    * Данные для модуля категорий каналов
			    * #####################################################################
			    */
			    $cats = $this->getChannelsCategories();
			    $this->view->assign('channels_cats', $cats);
			    
			    /*
			     * ######################################################
			    * Видео для списка программ
			    * ######################################################
			    */
			    $videos = $this->videosModel->getRelatedVideos( $list, $channel['title'], $listingDate);
			    $this->view->assign('listing_videos', $videos);
			    
			    /*
			     * ######################################################
			    * Комменты
			    * ######################################################
			    */
			    //$this->view->assign('comments', $this->($channel));
			    
			    /*
			     * ######################################################
			    * Короткая ссылка на страницу
			    * ######################################################
			    */
			    $tinyUrl = $this->getTinyUrl(array('channel'=>$channel['alias']), 'default_listings_day-listing', array(
			    	$this->_getParam('module'),
			    	$this->_getParam('controller'),
			    	$this->_getParam('action'),
			    	$channel['id'],
			    ));
			    $this->view->assign('short_link', $tinyUrl);
			    
			    
			    /*
			     * ######################################################
			     * Add hit for program
			     * ######################################################
			     */
			    if ($currentProgram) {
			    	$this->programsModel->addHit( $currentProgram );
			    }
			    
			} else {
				
			    if ($this->cache->enabled){
			    	 
			        $this->cache->setLifetime(3600*6);
			    	$this->cache->setLocation(ROOT_PATH.'/cache');
			    	$f = '/Listings/Similar/Day';
			    
			    	$hash = $this->cache->getHash('similarPrograms_'.$programAlias);
			    	if (($similarPrograms = $this->cache->load( $hash, 'Core', $f))===false) {
			    		$similarPrograms = $this->programsModel->getSimilarProgramsForDay (
			    				$this->input->getEscaped('alias'),
			    				$listingDate,
			    				$currentProgram['channel_id']);
			    		$this->cache->save($similarPrograms, $hash, 'Core', $f);
			    	}
			    	 
			    } else {
			    	$similarPrograms = $this->programsModel->getSimilarProgramsForDay (
			    			$currentProgram['alias'],
			    			$listingDate,
			    			$currentProgram['channel_id']);
			    }
			     
			    if ($similarPrograms===false){ 
				    $this->view->assign('hide_sidebar', 'right');
				    $this->render('program-not-found');
				    return true;
			    }
			    
			    $this->view->assign( 'similar', $similarPrograms);
			    
			    return $this->render('similar-day');
				
				
			}
			
			/*
			 * ######################################################
			 * Данные для модуля видео в правой колонке
			 * ######################################################
			 */
			$this->view->assign('sidebar_videos', parent::sidebarVideos($channel));
			
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
			
			$channel = parent::channelInfo();
			if (!isset($channel['id'])){
				$this->view->assign('hide_sidebar', 'right');
				$this->render('channel-not-found');
				return true;
			}
			$this->view->assign( 'channel', $channel );
			
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
			
			$weekDays = $this->_helper->getHelper('weekDays');
			$weekStart = $weekDays->getStart( $listingDate );
			$this->view->assign('week_start', $weekStart);
			$weekEnd = $weekDays->getEnd( $listingDate );
			$this->view->assign('week_end', $weekEnd);
			
			/*
			 * #####################################################################
			 * Данные для модуля самых популярных программ
			 * #####################################################################
			*/
			$top = $this->topPrograms();
			$this->view->assign('top_programs', $top);
			
			/*
			 * #####################################################################
			* Данные для модуля категорий каналов
			* #####################################################################
			*/
			$cats = $this->getChannelsCategories();
			$this->view->assign('channels_cats', $cats);
			
			if ($this->cache->enabled){
			    $this->cache->setLocation(ROOT_PATH.'/cache');
				$f = '/Listings/Programs';
				$hash = $this->cache->getHash('currentProgram_'.$programAlias.'_'.$channel['id']);
				if (($list = $this->cache->load( $hash, 'Core', $f))===false) {
					$list = $this->programsModel->getProgramThisWeek( $programAlias, $channel['id'], $weekStart, $weekEnd);
					$this->cache->save($list, $hash, 'Core', $f);
				}
			} else {
				$list = $this->programsModel->getProgramThisWeek( $programAlias, $channel['id'], $weekStart, $weekEnd);
			}
			
			
			if (APPLICATION_ENV=='development'){
				//var_dump($list);
				//die(__FILE__.': '.__LINE__);
			}
			
			$this->programsModel->addHit( $list[0] );
			$this->view->assign( 'list', $list );
			
			if (!$this->checkDate($listingDate)){
				$this->view->assign('hide_sidebar', 'right');
				$this->_forward('outdated');
				return true;
			}
			
			
			if ($this->cache->enabled){
			    $this->cache->setLocation(ROOT_PATH.'/cache');
				$f = '/Listings/Similar/Week';
				$hash = $this->cache->getHash('similarPrograms_'.$programAlias.'_'.$channel['id']);
				if (($similarPrograms = $this->cache->load( $hash, 'Core', $f))===false) {
					$similarPrograms = $this->programsModel->getSimilarProgramsThisWeek( $programAlias, $weekStart, $weekEnd, $channel['id'] );
					$this->cache->save($similarPrograms, $hash, 'Core', $f);
				}
			} else {
				$similarPrograms = $this->programsModel->getSimilarProgramsThisWeek( $programAlias, $weekStart, $weekEnd, $channel['id'] );
			}
			$this->view->assign( 'similar', $similarPrograms );
			
			if (APPLICATION_ENV=='development'){
				//var_dump($similarPrograms);
				//die(__FILE__.': '.__LINE__);
			}
			
			if( $list[0] && !empty($list[0])){
				$this->render('program-week');
				return true;
			} elseif(empty($list[0]) && !empty($similarPrograms)){
				$this->render('similar-week');
				return true;
			} elseif (empty($list[0]) && empty($similarPrograms)) {
				$this->view->assign('hide_sidebar', 'right');
				$this->render('program-not-found');
				return true;
			}
			
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
			$redirectUrl = $this->view->url(array(1=>$this->view->escape($channel['alias'])), 'default_listings_day-listing');
			$this->_redirect( $redirectUrl, array('exit'=>true));
		} else {
			$this->_redirect( $this->view->url(array(), 'default_error_missing-page'), array('exit'=>true));
		}
	
	}
	
	/**
	 * Категория программ за неделю
	 */
	public function categoryAction(){
	
		$cats = $this->getProgramsCategories();
		if ( $this->requestParamsValid( array('programsCategories'=>$cats))){
		    
		    $categoriesTable = new Xmltv_Model_DbTable_ProgramsCategories();
		    $category = $categoriesTable->fetchRow("`alias`='".$this->input->getEscaped('category')."'")->toArray();
		    $this->view->assign('category', $category);
		    $categoryId = $category['id'];
		    $now = Zend_Date::now();
		    
		    /*
		     * #####################################################################
		     * Данные для модуля самых популярных программ
		     * #####################################################################
		     */
		    $top = $this->topPrograms();
		    $this->view->assign('top_programs', $top);
		    
		    switch ($this->input->getEscaped('timespan')){
					
				case 'неделя':
				    
				    $weekStart = $this->weekDays->getStart( $now);
				    $weekEnd   = $this->weekDays->getEnd( $now);
				    
				    if ($this->cache->enabled){
				        $hash = "categoryWeek_$categoryId";
				        $this->cache->setLifetime( 86400);
				        $this->cache->setLocation( ROOT_PATH.'/cache');
				        $f = "/Listings/Category/Week";
						if (($list = $this->cache->load( $hash, 'Core', $f))===false){
						    $list = $this->programsModel->categoryWeek( $categoryId, $weekStart, $weekEnd);
						    $this->cache->save( $list, $hash, 'Core', $f);
						}
				    } else {
				        $list = $this->programsModel->categoryWeek( $categoryId, $weekStart, $weekEnd);
				    }
				    
					
					$this->view->assign( 'weekStart', $weekStart);
					$this->view->assign( 'weekEnd', $weekEnd);
					$this->view->assign( 'list', $list);
					$this->view->assign( 'pageclass', 'category-week');
					$this->render( 'category-week');
					
					break;
	
				case 'сегодня':
				    
				    if ($this->cache->enabled){
				    	$hash = "categoryDay_".$categoryId."_".$now->toString("ddd");
				    	$this->cache->setLifetime( 7200);
				    	$this->cache->setLocation( ROOT_PATH.'/cache');
				    	$f = "/Listings/Category/Day";
				    	if (($list = $this->cache->load( $hash, 'Core', $f))===false){
				    		$list = $this->programsModel->categoryDay( $categoryId, $now);
				    		$this->cache->save( $list, $hash, 'Core', $f);
				    	}
				    } else {
				    	$list = $this->programsModel->categoryDay( $categoryId, $now);
				    }
					
				    
					$this->view->assign('list', $list);
					$this->view->assign('pageclass', 'category-day');
					$this->view->assign('today', $now);
					
					$this->render('category-day');
					
					break;
			}
			
			/*
			 * #####################################################################
			 * Данные для модуля категорий каналов
			 * #####################################################################
			 */
			$cats = $this->getChannelsCategories();
			$this->view->assign('channels_cats', $cats);
			
				
			
	
		} else {
			$this->_redirect( $this->view->url(array(), 'default_error_missing-page'), array('exit'=>true));
		}
	
	}
	
	/**
	 * Outdated listing
	 */
	public function outdatedAction(){
		
	}
	
	
	
	
	/**
	 * 
	 */
	public function premieresWeekAction(){
		
	    $this->view->assign('hide_sidebar', 'right');
	    
	}
	
	
	
}

