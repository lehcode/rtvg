<?php
/**
 * Frontend programs listings controller
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: ListingsController.php,v 1.15 2013-01-02 16:58:27 developer Exp $
 *
 */
class ListingsController extends Zend_Controller_Action
{

	const ERR_INVALID_INPUT = 'Неверные данные!';
	
	private $_kidsChannels=array();
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
	
	protected $bitlyLogin = 'rtvg';
	protected $bitlyKey = 'R_b37d5df77e496428b9403e236e672fdf';
	
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
		$this->siteConfig = Zend_Registry::get( 'site_config' )->site;
		
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
		
		$this->validator = $this->_helper->getHelper('requestValidator');
		$this->cache = new Xmltv_Cache();
		
		$ajaxContext = $this->_helper->getHelper( 'AjaxContext' );
		$ajaxContext->addActionContext( 'update-comments', 'html' )
			->initContext();
		
	}

	/**
	 * Index page
	 */
	public function indexAction () {
		
		if ($this->requestParamsValid()===true){
			$this->_forward( 'day' );
		} else {
			$this->__call($this->_getParam('method'));
		}
	}

	/**
	 * Forward request to dayListingAction()
	 */
	public function dayDateAction(){
		
		// Validation routines
		if ($this->requestParamsValid()===true){
			$this->_forward('day-listing');
		}
		
	}
	
	/**
	 * Programs listing for 1 particular day
	 * @throws Zend_Exception
	 */
	public function dayListingAction () {

		if ($this->requestParamsValid()===true){
				
			$this->view->assign( 'pageclass', 'day-listing' );
			
			//Load models
			$programsModel = new Xmltv_Model_Programs();
			$channelsModel = new Xmltv_Model_Channels();
			$videosModel   = new Xmltv_Model_Videos();
			$commentsModel = new Xmltv_Model_Comments();
			
			//Current channel
			$ch = $this->input->getEscaped('channel');
			if ($this->cache->enabled){
				$f = '/Channels';
				$hash = $this->cache->getHash('channel_'.$ch);
				if (($channel = $this->cache->load($hash, 'Core', $f))===false) {
					$channel = $channelsModel->getByAlias($ch);
					$this->cache->save($channel, $hash, 'Core', $f);
				}
			} else {
			    $channel = $channelsModel->getByAlias($ch);
			}
			//var_dump($channel);
			//die(__FILE__.': '.__LINE__);
			
			$this->view->assign('channel', $channel );
			
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
			$v = $videosModel->getSidebarVideos( 'тв '.$channel->title );
			//var_dump(count($v));
			$this->view->assign('sidebar_videos', $v);
			//die(__FILE__.': '.__LINE__);
			
			/*
			 * Видео для списка программ
			 */
			$v = $videosModel->getRelatedVideos( $list, $channel->title, $listingDate->toString('dd-MM-yyyy'));
			//var_dump(count($v));
			$this->view->assign('listing_videos', $v);
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
							$torrentLinks = $this->_fetchShortLinks($links);
							$this->cache->save($torrentLinks, $hash, 'Core', $f);
						}
					} else {
						$torrentLinks = $this->_fetchShortLinks($links);
					}
					//var_dump($torrentLinks);
					//die(__FILE__.': '.__LINE__);
					$this->view->assign('torrent_links', $torrentLinks);
					
				}
			}
			//die(__FILE__.': '.__LINE__);
			
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
			
			//die(__FILE__.': '.__LINE__);
			
		}
		
	}
	
	/**
	 * 
	 * @param DOMNodeList $links
	 * @param array $torrents
	 */
	private function _fetchShortLinks(DOMNodeList $links){
		$tinyurl = new Zend_Service_ShortUrl_BitLy( $this->bitlyLogin, $this->bitlyKey );
		$maxTorrents = (int)Zend_Registry::get('site_config')->channels->torrents->get('amount');
		//var_dump($maxTorrents);
		$i=0;
		$result = array();
		if($links->length>0) {
			foreach ($links as $link){
				if ($i<=$maxTorrents) {
					$result[$i]=new stdClass();
					$result[$i]->url   = trim($tinyurl->shorten( $links->item($i)->getAttribute('href') ));
					$result[$i]->title = Xmltv_String::substr( $links->item($i)->nodeValue, 0, Xmltv_String::strrpos( $links->item($i)->nodeValue, ' ' ) );
					$i++;
				}
			};
			return $result;
		}
	}
	
	
	
	
	
	public function programDayAction () {
		
		// Validation routines
		if ($this->requestParamsValid()) {
			
			/**
			 * @todo
			 */
			if ( $this->input->getEscaped('date')=='неделя' ) {
				$this->_forward('program-week', 'listings', 'default', array( 'date'=>Zend_Date::now()->toString('dd-MM-yyyy') ));
				return true;
			}
			
			$programsModel = new Xmltv_Model_Programs();
			$channelsModel = new Xmltv_Model_Channels();
			$videosModel   = new Xmltv_Model_Videos();
			$commentsModel = new Xmltv_Model_Comments();
			
			//Current channel properties
			$channel = $channelsModel->getByAlias( $this->input->getEscaped('channel') );
			
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
			//var_dump($listingDate->toString());
			//die(__FILE__.': '.__LINE__);
			
			
			$currentProgram = $programsModel->getByAlias( $this->input->getEscaped('alias'), $channel->ch_id, $listingDate );
			
			var_dump($currentProgram);
			//var_dump($channel->alias);
			//var_dump($listingDate);
			die(__FILE__.': '.__LINE__);
			
			$list = $programsModel->getProgramForDay( $currentProgram->alias, $channel->alias, $listingDate );
			
			//var_dump($list);
			//die(__FILE__.': '.__LINE__);
			
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
			
			/*
			 * ######################################################
			 * Related videos
			 * (2)Listing videos
			 * ######################################################
			 */
			$vc = Zend_Registry::get('site_config')->videos->listing;
			$ytConfig['max_results'] = (int)$vc->get('max_results', 48);
			$ytConfig['order'] = $vc->get('order', 'relevance');
			//$ytConfig['start_index'] = (int)$vc->get('start_index', 1);
			//$ytConfig['safe_search'] = $vc->get('safe_search', 'none');
			$ytConfig['language'] = 'ru';
			//var_dump(count($list));
			if (count($list)) {
				if ($this->cache->enabled){
					$hash = Xmltv_Cache::getHash('programs_'.$channel->ch_id.'_'.$listingDate->toString('ddMMyyyy'));
					$f = '/Youtube/Programs';
					if (($videos = $this->cache->load($hash, 'Core', $f))===false) {
						//die(__FILE__.': '.__LINE__);
						$videos = $this->_fetchListingVideos($list, $ytConfig);
						$this->cache->save($videos, $hash, 'Core', $f);
					}
					//die(__FILE__.': '.__LINE__);
				} else {
					$videos = $this->_fetchListingVideos($list, $ytConfig);
				}
			}
			//var_dump(count($videos));
			//die(__FILE__.': '.__LINE__);
			$this->view->assign('listing_videos', $videos);
			
			/*
			 * ######################################################
			 * Torrents
			 * ######################################################
			*/
			if ((bool)Zend_Registry::get('site_config')->channels->torrents->get('enabled')===true) {
				
				$url  = 'http://torrent-poisk.com/search.php?q='.urlencode($channel->title).'&r=0&qsrv='.urlencode($channel->title);
				$curl = new Xmltv_Parser_Curl();
				$curl->setOption(CURLOPT_CONNECTTIMEOUT, 5);
				$curl->setOption(CURLOPT_TIMEOUT, 5);
				$curl->setUrl($url);
				$curl->setUserAgent(@$_SERVER['HTTP_USER_AGENT']); //otput suppressed for phpunit
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
							$torrentLinks = $this->_fetchShortLinks($links);
							$this->cache->save($torrentLinks, $hash, 'Core', $f);
						}
					} else {
						$torrentLinks = $this->_fetchShortLinks($links);
					}
					//var_dump($torrentLinks);
					//die(__FILE__.': '.__LINE__);
					$this->view->assign('torrent_links', $torrentLinks);
					
				}
			}
			
			if (APPLICATION_ENV=='production' || APPLICATION_ENV=='staging'){
			    
				$tinyurl = new Zend_Service_ShortUrl_BitLy( $this->bitlyLogin, $this->bitlyKey);
				$d = (string)Zend_Registry::get('app_config')->domain;
				$url = 'http://'.$_SERVER['HTTP_HOST'].$this->view->url(array('channel'=>$channel->alias), 'default_listings_day-listing');
				if ($this->cache->enabled){
					$hash = Xmltv_Cache::getHash('tinyurl_'.$this->_getParam('module').'_'.$this->_getParam('controller').'_'.$this->_getParam('action'));
					$f = '/Tinyurl/Pages';
					if (($link = $this->cache->load($hash, 'Core', $f))===false) {
						$link = trim($tinyurl->shorten( $url ));
						$this->cache->save($link, $hash, 'Core', $f);
					}
				} else {
					$link = trim($tinyurl->shorten( $url ));
				}
				$this->view->assign('short_link', $link);
				
			}
			
			/*
			 * Add hit for channel and model
			 */
			$channelsModel->addHit( $channel->ch_id );
			if ($currentProgram)
				$programsModel->addHit( $currentProgram );
			
			//die(__FILE__.': '.__LINE__);
			
		}
		
	}
	
	
	/**
	 * 
	 * @throws Zend_Exception
	 */
	public function programWeekAction(){
		
		if ($this->requestParamsValid()) {
			
			$channelsModel = new Xmltv_Model_Channels();
			$programsModel = new Xmltv_Model_Programs();
			
			$channel = $channelsModel->getByAlias( $this->input->getEscaped('channel') );
			
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
			
			$currentProgram = $programsModel->getByAlias( $this->input->getEscaped('alias'), $channel->ch_id, $listingDate);
			//var_dump($currentProgram);
			//die(__FILE__.': '.__LINE__);
			
			$weekDays  = $this->_helper->getHelper('weekDays');
			$weekStart = $weekDays->getStart( $listingDate );
			$weekEnd   = $weekDays->getEnd( $listingDate );
			
			$list = $programsModel->getProgramThisWeek( $this->input->getEscaped('alias'), $channel->ch_id, $weekStart, $weekEnd );
			if (!count($list)) {
				$this->view->assign( 'notfound', true );
				$similar = $programsModel->getSimilarProgramsThisWeek( $this->input->getEscaped('alias'), $weekStart, $weekEnd );
			} 
			$similar = $programsModel->getSimilarProgramsThisWeek( $this->input->getEscaped('alias'), $weekStart, $weekEnd );
			
			if ($currentProgram) {
				$programsModel->addHit( $currentProgram );
			}			
			
			$channelsModel->addHit( $channel->ch_id );
			
			$this->view->assign( 'week_start', $weekStart );
			$this->view->assign( 'week_end', $weekEnd );
			$this->view->assign( 'list', $list );
			$this->view->assign( 'similar', $similar );
			$this->view->assign( 'program', $currentProgram );
			$this->view->assign( 'channel', $channel );
			$this->view->assign( 'pageclass', 'program-week' );
			
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
	
		if ( $this->_validateRequest( $this->_getParam('action') ) === true ){
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
			$hash  = Xmltv_Cache::getHash("channelscategories");
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
	 * 
	 * @param  array  $parts
	 * @param  string $route
	 * @param  array  $uniq
	 * @return string
	 */
	protected function getTinyUrl($parts=array(), $route=null, $uniq=array()){
		
		$tinyurl = new Zend_Service_ShortUrl_BitLy( $this->bitlyLogin, $this->bitlyKey);
		$url	 = 'http://rutvgid.ru'.$this->view->url( $parts, $route);
		$e = (bool)Zend_Registry::get('site_config')->cache->tinyurl->get('enabled');
		if ($e===true){
			$t = (int)Zend_Registry::get('site_config')->cache->tinyurl->get('lifetime');
			$t>0 ? $this->cache->setLifetime((int)$t): $this->cache->setLifetime(86400) ;
			$hash = Xmltv_Cache::getHash('tinyurl_'.implode(';', $parts).implode(';', $uniq));
			$f = '/Tinyurl/Pages';
			if (($link = $this->cache->load($hash, 'Core', $f))===false) {
				$link = trim($tinyurl->shorten( $url ));
				$this->cache->save($link, $hash, 'Core', $f);
			}
		} else {
			$link = trim($tinyurl->shorten( $url ));
		}
		
		return $link;
		
	}
	
	
}

