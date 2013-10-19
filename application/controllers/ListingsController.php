<?php
/**
 * Programs listings display
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @version $Id: ListingsController.php,v 1.39 2013-04-06 22:35:03 developer Exp $
 *
 */
class ListingsController extends Rtvg_Controller_Action
{
	
	/**
	 * @var Xmltv_Model_Articles
	 */
	private $articlesModel;
	
	/**
	 * (non-PHPdoc)
	 * @see Zend_Controller_Action::init()
	 */
	public function init()
	{
		parent::init();
		
		$ajaxContext = $this->_helper->getHelper( 'AjaxContext' );
		$ajaxContext->addActionContext( 'update-comments', 'html' )
			->initContext();
		
		if ($this->getRequest()->getMethod()=='POST'){
			$this->_helper->layout()->setLayout('access-denied');
			return;
		}
		
		if (!$this->_request->isXmlHttpRequest()){
			$this->view->assign( 'pageclass', parent::pageclass(__CLASS__) );
		}
		
		$this->articlesModel = new Xmltv_Model_Articles();
	}

	/**
	 * Index page
	 */
	public function indexAction ()
	{
		$this->_forward( 'day-date' );
	}

	/**
	 * Forward request to dayListingAction()
	 */
	public function dayDateAction(){
		
		parent::validateRequest();
		
        if (($date = $this->_getParam('date', null))===null){
            $date = parent::listingDate();
        }
		return $this->_forward('day-listing', 'listings', 'default', array(
			'date'=>$date,
			'channel'=>$this->_getParam('channel')
		));
		
	}
	
	/**
	 * Programs listing for 1 particular day
	 * @throws Zend_Exception
	 */
	public function dayListingAction () {
		
		parent::validateRequest();
		
		$pageClass = parent::pageclass(__CLASS__);
		$this->view->assign( 'pageclass', $pageClass );
		$this->view->assign( 'hide_sidebar', 'right' );
		$this->view->assign( 'vk_group_init', false );
		$this->view->headLink()
			->prependStylesheet( 'http://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css', 'screen');
		
        $channel = parent::channelInfo($this->_getParam('channel'));
        if (!isset($channel['id']) || empty($channel['id'])){
			throw new Zend_Exception("Channel not found.");
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
		$listingDate = parent::listingDate($this->input);
		$this->view->assign('listing_date', $listingDate);
		
		//Assign today's date to view 
		if ($listingDate->isToday()) {
			$this->view->assign('is_today', true);
		} else {
			$this->view->assign('is_today', false);
			$this->view->assign('pageclass', $pageClass.' other-day');
		}
		
		/*
		 * ###################################################################
		 * Detect timeshift and adjust listing time
		 * ###################################################################
		 */
		$timeShift = (int)$this->input->getEscaped( 'tz', 'msk' );
		$listingDate = $timeShift!='msk' ? $listingDate->addHour( $timeShift ) : $listingDate ;
		
		$this->view->assign('timeshift', $timeShift);
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
		 */
		$amt = 4;
		if ($this->cache->enabled) {
			$this->cache->setLifetime(3600);
			$f = "/Listings/Programs";
			$hash = Rtvg_Cache::getHash( $channel['alias'].'_'.$listingDate->toString('DDD') );
			if (!$list = $this->cache->load( $hash, 'Core', $f)) {
				if ($listingDate->isToday()){
					$list = $this->bcModel->getProgramsForDay( $listingDate, $channel['id'], $amt );
				} else {
					$list = $this->bcModel->getProgramsForDay( $listingDate, $channel['id'] );
				}
				$this->cache->save( $list, $hash, 'Core', $f);
			}
			
		} else {
		 	if ($listingDate->isToday()){
				$list = $this->bcModel->getProgramsForDay( $listingDate, $channel['id'], $amt );
			} else {
				$list = $this->bcModel->getProgramsForDay( $listingDate, $channel['id'] );
			}
		}
		
		if ($list===false){
			$this->view->assign('hide_sidebar', 'right');
			$this->render('no-listings');
			return true;
		}
		
        if (empty($list)){
            throw new Zend_Controller_Action_Exception("Listing is empty", 500);
        }
        
		$list[0]['now_showing']=true;
		$this->view->assign( 'programs', $list );
		$currentProgram = $list[0];
		
		/*
		 * #####################################################################
		 * Articles
		 * #####################################################################
		 */
		$amt = 10;
		if ($this->cache->enabled) {
			$this->cache->setLifetime(86400);
			$f = "/Content/Articles";
			$hash = 'dayListingArticles_'.Rtvg_Cache::getHash( $channel['id'] );
			if (!$articles = $this->cache->load( $hash, 'Core', $f)) {
				$articles = $this->articlesModel->dayListingArticles( $currentProgram, $channel, $amt );
				$this->cache->save( $articles, $hash, 'Core', $f);
			}
		} else {
			$articles = $this->articlesModel->dayListingArticles( $currentProgram, $channel, $amt );
		}
		
		$this->view->assign( 'announces', $articles );
		$this->view->assign( 'show_announces', true );
		
		/*
		 * #####################################################################
		 * Update start and end times of each program in listing
		 * #####################################################################
		 */
		if ($this->_getParam('tz', null)!==null) {
			if ($timeShift!=0){
				foreach ($list as $item) {
					$item['start'] = $item['start']->addHour($timeShift);
					$item['end']   = $item['end']->addHour($timeShift);
					$this->view->headMeta()->setName('robots', 'noindex,follow');
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
		if (count($list) && ($list!==false) && $listingDate->isToday()) {
			
			$listingVideos = array();
			
			// Запрос в файловый кэш
			if ($this->cache->enabled){
				
				$t = (int)Zend_Registry::get( 'site_config' )->cache->youtube->listings->get( 'lifetime' );
				$t>0 ? $this->cache->setLifetime($t): $this->cache->setLifetime(86400) ;
				$f = '/Listings/Videos';
				$hash = Rtvg_Cache::getHash( 'listingVideo_'.$channel['title'].'-'.$listingDate->toString( 'YYYY-MM-dd' ));
				
				if (parent::$videoCache && $this->isAllowed) {
					
					// Ищем видео в кэше БД если он включен
					if (false === ($listingVideos = $this->vCacheModel->listingRelatedVideos( array_slice($list, 0, 3), $channel['title'], $listingDate ))){
						
						if (false === ($listingVideos=$this->cache->load( $hash, 'Core', $f) )){
						
							// Если не найдено ни в одном из кэшей, то делаем запрос к Yoututbe
							$listingVideos = $this->videosModel->ytListingRelatedVideos( array_slice($list, 0, 3), $channel['title'], $listingDate );
							
							if (!count($listingVideos) || ($listingVideos===false)) {
								return false;
							}
							
							// Сохраняем в кэш БД если он включен
							if (parent::$videoCache===true){
								foreach ($listingVideos as $k=>$vid) {
									try {
										$listingVideos[$k]['hash'] = $k;
										$this->vCacheModel->saveListingVideo( $listingVideos[$k] );
									} catch (Exception $e) {
										throw new Zend_Exception( $e->getMessage(), $e->getCode() );
									}
									
								}
							}
							// Сохранение в файловый кэш
							$this->cache->save( $listingVideos, $hash, 'Core', $f);
						} 	
					}
					
				} else {
					
                    if ((false === ($listingVideos=$this->cache->load( $hash, 'Core', $f)))){
						// Если не найдено ни в одном из кэшей, то делаем запрос к Yoututbe
						$listingVideos = $this->videosModel->ytListingRelatedVideos( array_slice($list, 0, 3), $channel['title'], $listingDate );
						
						if (!count($listingVideos) || ($listingVideos===false)) {
							return false;
						}
						
						if ($this->isAllowed){
							$this->cache->save($listingVideos, $hash, 'Core', $f);
						}
					}
					
				}
									
			} else {
				
                // Кэширование не используется 
				// запрос к Yoututbe
				$listingVideos = $this->videosModel->ytListingRelatedVideos( array_slice($list, 0, 3), $channel['title'], $listingDate );
				
			}
		}
        $this->view->assign('listing_videos', $listingVideos);
		
		/*
		 * ######################################################
		 * Комменты
		 * ######################################################
		 */
		if ((bool)Zend_Registry::get('site_config')->channels->comments->get('enabled')===true){
			$this->view->assign('comments', parent::yandexComments($channel) );
		}
		
		/* 
		 * ######################################################
		 * Данные для модуля видео в правой колонке
		 * ######################################################
		 */
		if ($this->view->hide_sidebar!='right'){
			$this->view->assign('sidebar_videos', parent::sidebarVideos($channel) );
		}
		
		/*
		 * ######################################################
		 * Torrents
		 * ######################################################
		 */
		/*
		if ((bool)Zend_Registry::get('site_config')->channels->torrents->get('enabled')===true) {
			
			$url  = 'http://torrent-poisk.com/search.php?q='.urlencode($channel['title']).'&r=0&qsrv='.urlencode($channel['title']);
			$curl = new Xmltv_Parser_Curl();
			$curl->setOption(CURLOPT_CONNECTTIMEOUT, 4);
			$curl->setOption(CURLOPT_TIMEOUT, 4);
			$curl->setUrl($url);
			$f = '/Listings/Torrents';
			$hash = Rtvg_Cache::getHash( $url );
			
			if ($this->cache->enabled){
				if (($html = $this->cache->load($hash, 'Core', $f))===false) {
					$html = $curl->fetch( Xmltv_Parser_Curl::PAGE_HTML );
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
		*/
		
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
	
	/**
	 * Выпуски выбранной пользователем передачи сегодня и 
	 * список похожих по названию передач сегодня на других каналах
	 * 
	 * @throws Zend_Exception
	 */
	public function programDayAction () {
		
		parent::validateRequest();
			
		if (isset($_GET['RTVG_PROFILE'])){
			//var_dump($listingDate->toString());
			//die(__FILE__.': '.__LINE__);
		}
		
		$this->view->assign( 'pageclass', 'program-day' );
		$programAlias = $this->input->getEscaped('alias');
		
		/**
		 * @todo
		 */
		if ( $this->input->getEscaped( 'date' )=='неделя' ) {
			return $this->_forward( 'program-week', 'listings', 'default', array( 'date'=>Zend_Date::now()->toString('dd-MM-yyyy')));
		}
		
		$programAlias = $this->input->getEscaped( 'alias' );
		
		/* ######################################################
		 * Channel properties
		 * ######################################################
		 */
		$channelAlias = $this->input->getEscaped( 'channel' );
		if (isset($_GET['RTVG_PROFILE'])){
			//var_dump($channelAlias);
			//die(__FILE__.': '.__LINE__);
		}
		$channel = parent::channelInfo( $channelAlias );
		$this->view->assign( 'channel', $channel );
		
		if (isset($_GET['RTVG_PROFILE'])){
			//var_dump($channel);
			//die(__FILE__.': '.__LINE__);
		}
		
		/*
		 * ######################################################
		 * Decision on listing timespan (date|сегодня|неделя) 
		 * ######################################################
		 */
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
		$this->view->assign( 'date', $listingDate );
		
		/*
		 * ##############################################################
		 * Check if requested date is older than allowed in history
		 * ##############################################################
		 */
		/*
		$l = (int)Zend_Registry::get( 'site_config' )->listings->history->get( 'length' );
		if (false === parent::checkDate( $listingDate, $l )){
			$this->view->assign( 'history_length', $l );
			$this->view->assign( 'hide_sidebar', 'right' );
			return $this->render( 'outdated' );
		}
		*/
		
		$this->view->assign('notfound', false);
		$this->view->assign('nosimilar', false);
		
		if ($this->cache->enabled){
			
			$this->cache->setLifetime( 86400 );
			$f = '/Listings/Program/Day';
			
			$hash = $this->cache->getHash( 'program-day-single_'.$programAlias.'_'.$channel['id'] );
			if (false === ($single = $this->cache->load( $hash, 'Core', $f ))) {
				$single = $this->bcModel->getProgramForDay( $programAlias, $channel, $listingDate, 1 );
				$this->cache->save( $single, $hash, 'Core', $f );
			}
		} else {
			$single = $this->bcModel->getProgramForDay( $programAlias, $channel, $listingDate, 1 );
		}
		$this->view->assign('current_program', $single);
		
		if (APPLICATION_ENV=='development'){
			//var_dump($single);
			//die(__FILE__.': '.__LINE__);
		}
		
		/*
		 * ######################################################
		 * Список программ
		 * ######################################################
		 */
		if ($single){
			
			if ($this->cache->enabled){
				
				$this->cache->setLifetime(86400);
				$f = '/Listings/Program/Day';
				
				$hash = $this->cache->getHash('program-day_'.$programAlias);
				if (false === ($list = $this->cache->load( $hash, 'Core', $f ))) {
					$list = $this->bcModel->getProgramForDay( $programAlias, $channel, $listingDate );
					$this->cache->save( $list, $hash, 'Core', $f );
				}
			} else {
				$list = $this->bcModel->getProgramForDay( $programAlias, $channel, $listingDate );
			}
			$this->view->assign( 'programs', $list );
				
			/*
			 * #####################################################################
			 * Данные для модуля категорий каналов
			 * #####################################################################
			 */
			$cats = parent::getChannelsCategories();
			$this->view->assign( 'channels_cats', $cats );
			
			
			/*
			 * ######################################################
			 * Комменты
			 * ######################################################
			 */
			if (true === (bool)Zend_Registry::get( 'site_config' )->channels->comments->get( 'enabled' )){
				$this->view->assign('comments', parent::yandexComments( $channel ) );
			}
			
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
			$this->bcModel->addHit( $list[0] );
			
			
		} else {
			
			if ($this->cache->enabled){
				 
				$this->cache->setLifetime(3600*6);
				$f = '/Listings/Similar/Day';
			
				$hash = $this->cache->getHash('similarPrograms_'.$programAlias);
				if (($similarPrograms = $this->cache->load( $hash, 'Core', $f))===false) {
					$similarPrograms = $this->bcModel->getSimilarProgramsForDay (
							$listingDate,
                            $this->input->getEscaped('alias'),
							$single['channel_id']);
					$this->cache->save($similarPrograms, $hash, 'Core', $f);
				}
				 
			} else {
				$similarPrograms = $this->bcModel->getSimilarProgramsForDay (
						$listingDate,
                        $single['alias'],
						$single['channel_id']);
			}
			 
			if ($similarPrograms===false){ 
				$this->view->assign('hide_sidebar', 'right');
				$this->render('program-not-found');
				return true;
			}
			
			$this->view->assign( 'similar', $similarPrograms);
			
			return $this->render('similar-day');
			
			
		}
		
	}
	
	
	/**
	 * 
	 * @throws Zend_Exception
	 */
	public function programWeekAction(){
		
		if (parent::validateRequest()) {
			
			$this->view->assign( 'pageclass', 'program-week' );
			$programAlias = $this->input->getEscaped('alias');
			
			$channel = parent::channelInfo( $this->input->getEscaped('channel') );
			if (!isset($channel['id'])){
				$this->view->assign('hide_sidebar', 'right');
				$this->render('channel-not-found');
				return true;
			}
			$this->view->assign( 'channel', $channel );
			
			if (APPLICATION_ENV=='development'){
				//var_dump($channel);
				//die(__FILE__.': '.__LINE__);
			}
			
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
			
			if (!$this->checkDate($listingDate)){
				$this->view->assign('hide_sidebar', 'right');
				$this->_forward('outdated');
				return true;
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
			
			/*
			 * #####################################################################
			 * Список передач
			 * #####################################################################
			 */
			if ($this->cache->enabled){
				$this->cache->setLifetime(21600);
				$f = '/Listings/Program/Week';
				$hash = md5('currentProgram_'.$programAlias.'_'.$channel['id']);
				if (!$list = $this->cache->load( $hash, 'Core', $f )) {
					$list = $this->bcModel->getProgramThisWeek( $programAlias, $channel['id'], $weekStart, $weekEnd );
					$this->cache->save($list, $hash, 'Core', $f );
				}
			} else {
				$list = $this->bcModel->getProgramThisWeek( $programAlias, $channel['id'], $weekStart, $weekEnd);
			}
			
			if (APPLICATION_ENV=='development'){
				//var_dump($list);
				//die(__FILE__.': '.__LINE__);
			}
			
			$this->view->assign( 'list', $list );
			
			/*
			if ($this->cache->enabled){
				$this->cache->setLifetime(86400);
				$f = '/Listings/Similar/Week';
				$hash = $this->cache->getHash( $programAlias.'_'.$channel['id'] );
				if (($similarPrograms = $this->cache->load( $hash, 'Core', $f))===false) {
					$similarPrograms = $this->bcModel->getSimilarProgramsThisWeek( $programAlias, $weekStart, $weekEnd, $channel['id'] );
					$this->cache->save($similarPrograms, $hash, 'Core', $f);
				}
			} else {
				$similarPrograms = $this->bcModel->getSimilarProgramsThisWeek( $programAlias, $weekStart, $weekEnd, $channel['id'] );
			}
			$this->view->assign( 'similar', $similarPrograms );
			*/
			
			if (APPLICATION_ENV=='development'){
				//var_dump(count($list));
				//die(__FILE__.': '.__LINE__);
			}
			
			if( $list[0] && !empty($list[0])){
				$this->bcModel->addHit( $list[0] );
				return $this->render('program-week');
			} elseif(empty($list[0]) && !empty($similarPrograms)){
				return $this->render('similar-week');
			} elseif (empty($list[0]) && empty($similarPrograms)) {
				$this->view->assign('hide_sidebar', 'right');
				return $this->render('program-not-found');
			}
			
		}
		
	}
	
	/**
	 * Search for channel
	 */
	public function searchAction(){
	
		if ( parent::validateRequest()){
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
		
		if ( parent::validateRequest( array('programsCategories'=>$cats))){
			
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
						$f = "/Listings/Category/Week";
						if (($list = $this->cache->load( $hash, 'Core', $f))===false){
							$list = $this->bcModel->categoryWeek( $categoryId, $weekStart, $weekEnd);
							$this->cache->save( $list, $hash, 'Core', $f);
						}
					} else {
						$list = $this->bcModel->categoryWeek( $categoryId, $weekStart, $weekEnd);
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
						$f = "/Listings/Category/Day";
						if (($list = $this->cache->load( $hash, 'Core', $f))===false){
							$list = $this->bcModel->categoryDay( $categoryId, $now);
							$this->cache->save( $list, $hash, 'Core', $f);
						}
					} else {
						$list = $this->bcModel->categoryDay( $categoryId, $now);
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
	
	/**
	 * All series in the week
	 */
	public function seriesWeekAction(){
	
		$model = new Xmltv_Model_Programs();
		$data['date'] = new Zend_Date(null, null, 'ru');
		$weekStart = $this->_helper->WeekDays( array( 'method'=>'getStart', 'data'=>$data));
		$data['date'] = new Zend_Date(null, null, 'ru');
		$weekEnd = $this->_helper->WeekDays( array( 'method'=>'getEnd', 'data'=>$data));
		$seriesList = $model->getCategoryForPeriod( $weekStart, $weekEnd, $this->categoriesMap['series'] );
		//var_dump($weekStart->toString('YYYY-MM-dd'));
		//var_dump($weekEnd->toString('YYYY-MM-dd'));
	
	
		//var_dump($seriesList);
		//die(__FILE__.': '.__LINE__);
	
		//$this->render('under-constriction');
	}
	
	
	
}

