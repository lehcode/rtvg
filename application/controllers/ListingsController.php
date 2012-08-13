<?php
/**
 * Frontend programs listings controller
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: ListingsController.php,v 1.11 2012-08-13 13:20:15 developer Exp $
 *
 */
class ListingsController extends Zend_Controller_Action
{

	protected $siteConfig;
	private   $_requestParams;
	
	public function __call ($method, $arguments) {
		header( 'HTTP/1.0 404 Not Found' );
		$this->_helper->layout->setLayout( 'error' );
		$this->view->render();
	}


	public function init () {
		$this->view->setScriptPath( APPLICATION_PATH . '/views/scripts/' );
		$this->siteConfig = Zend_Registry::get( 'site_config' )->site;
		$this->_requestParams = $this->_getAllParams();
	}

	
	public function searchAction(){
		
		if ( $this->_validateRequest( $this->_getParam('action') ) === true ){
			$model = new Xmltv_Model_Channels();
			$search = $this->_getParam('fs');
			$channel = $model->getByTitle($search);
			$this->_redirect('/телепрограмма/'. $channel->alias, array('exit'=>true));
		} else {
			$this->_redirect('/горячие-новости', array('exit'=>true));
		}
		
	}
	

	public function indexAction () {

		$this->_forward( 'day' );
	}


	public function dayDateAction(){
		$this->_forward('day-listing');
	}
	
	/*
	 * Programs listing for 1 particular day
	 */
	public function dayListingAction () {

		//var_dump($this->_getAllParams());
		//var_dump($this->_validateRequest());
		//die(__FILE__.': '.__LINE__);
		
		if( !$this->_validateRequest() ) {
			throw new Zend_Exception("Неверные данные");
			$this->_redirect('/горячие-новости', array('exit'=>true));
		}
		
		$programs  = new Xmltv_Model_Programs();
		$channels  = new Xmltv_Model_Channels();
		$channel   = $channels->getByAlias($this->_requestParams['channel']);
		$comments  = new Xmltv_Model_Comments();
		$timeShift = $this->_getParam('tz', null);
		if ( $timeShift !== null ){
			if ( $timeShift == 0 ) {
				$this->_redirect('/телепрограмма/'.Xmltv_String::strtolower( $channel->alias ), array('exit'=>true));
			}
		}
		
		//var_dump($this->_getAllParams());
		
		//var_dump($channel);
		//die(__FILE__.': '.__LINE__);
		
		$d = $this->_getParam('date', null);
		
		//var_dump($d);
		//die(__FILE__.': '.__LINE__);
		
		$today = $d!==null ? new Zend_Date( $d, 'yyyy-MM-dd', 'ru' ) : new Zend_Date( null, null, 'ru' );
		$cache = new Xmltv_Cache(array('location'=>'/cache/Listings'));
		$hash = $cache->getHash(__FUNCTION__.'_'.$channel['ch_id'].'_'.$today->toString('yyyyMMdd'));
		try {
			if (Xmltv_Config::getCaching()){
				if (!$list = $cache->load($hash, 'Core', 'Listings')) {
					$list = $programs->getProgramsForDay( $today, $channel['ch_id'] );
					$cache->save($list, $hash, 'Core', 'Listings');
				}
			} else {
				$list = $programs->getProgramsForDay( $today, $channel['ch_id'] );
			}
		} catch (Exception $e) {
			echo $e->getMessage();
		}
		if ($timeShift!=0) {
			$today->addHour($timeShift);
		}
		$this->view->assign( 'today', $today );
		
		$currentProgram = null;
		if (!empty($list)) {
			foreach ($list as $list_item) {
				if ($list_item->now_showing === true)
				$currentProgram = $list_item;
			}
		}
		
		/**
		 * Add time adjustment
		 */
		//var_dump($list);
		
		//var_dump($timeShift);
		if ($timeShift!=0) {
			foreach ($list as $item) {
				//if ($timeShift<0) {
					//Zend_Date::;
				//var_dump( $item->start->toString('HH:mm') );
				$item->start = $item->start->addHour($timeShift);
				$item->end   = $item->end->addHour($timeShift);
				//var_dump( $item->start->toString('HH:mm') );
				//die(__FILE__.': '.__LINE__);
				//}
			}
		}
		$this->view->assign('timeshift', $timeShift);
		//die(__FILE__.': '.__LINE__);
		
		
		/**
		 * @todo Add vkontakte API calls
		 * Load and comments for channel and active program
		 */
		/*
		$hash = $cache->getHash(__FUNCTION__.'_vktoken');
		try {
			if (Xmltv_Config::getCaching()){
				if (!$token = $cache->load($hash, 'Core')) {
					$token = $comments->vkAuth();
					$cache->save($token, $hash, 'Core');
				} 
			} else {
				$token = $comments->vkAuth();
			}
		} catch (Exception $e) {
			echo $e->getMessage();
		}
		$this->view->assign('vk_token', $token);
		*/
		
		/*
		 * Process fake comments
		 * Caching is initialized in Xmltv_Model_Comments::getYandexRss
		 */
		if (isset($currentProgram->title)) {
			
			$query = array( '"'.$channel->title.'"', '"'.$currentProgram->title.'"');
			//var_dump($query);
			
			$feedData    = $comments->getYandexRss( $query );
			$commentsNew = $comments->parseYandexFeed( $feedData, 164 );
			
			if ( count($commentsNew) ) {
				foreach ( $commentsNew as $list_item ) {
					if (isset($list_item->link)) {
						if ( stristr( $list_item->link, 'liveinternet.ru') ) {
							$links = Zend_Feed_Reader::findFeedLinks($list_item->link);
							$list_item->rss_link = $links->rss;
						}
					}
				}
				
				//var_dump($commentsNew);
				//die(__FILE__.': '.__LINE__);
				
				$comments->saveComments( $commentsNew, $channel->alias, 'channel' );
				$hash = $cache->getHash(__FUNCTION__.md5('channel'.$channel->alias));
				try {
					if (Xmltv_Config::getCaching()){
						if (!$commentsLoaded = $cache->load($hash, 'Core', '/Feeds/Yandex')) {
							$commentsLoaded = $comments->dbGetComments( $channel->alias );
							$cache->save($commentsLoaded, $hash, 'Core', '/Feeds/Yandex');
						}
					} else {
						$commentsLoaded = $comments->dbGetComments( $channel->alias, 'channel', false );
					}
				} catch (Exception $e) {
					echo $e->getMessage();
				}
				
				$this->view->assign( 'comments', $commentsLoaded );
				//var_dump($commentsLoaded);
				//die(__FILE__.': '.__LINE__);
			}
			
			$this->view->assign( 'current_program', $currentProgram );
			$programs->addHit($currentProgram->alias);
			
		}
		/*
		$adult_channel = false;
		if ((bool)$channel->adult === true) {
			$adult_channel = true;
			$this->view->assign( 'sidebar_videos', false );
		} else {
			$this->view->assign( 'sidebar_videos', true );
		}
		//var_dump($adult_channel);
		*/
		
		$this->view->assign( 'channel', $channel );
		$this->view->assign( 'programs', $list );
		$this->view->assign( 'video_data', array() );
		$this->view->assign( 'pageclass', 'day-listing' );
		$this->view->assign( 'sidebar_videos', true );
		
		//var_dump($this->_getAllParams());
		//die(__FILE__.': '.__LINE__);
		
		$channels->addHit($channel->ch_id);
		
		//die(__FILE__.': '.__LINE__);
		
	}
	
	/*
	public function programTodayAction(){
		
		var_dump($this->_getAllParams());
		
		$url='/телепрограмма/'.$this->_getParam('channel').'/'.$this->_getParam('alias');
		//var_dump($url);
		//die(__FILE__.': '.__LINE__);
		$this->_redirect($url, array('exit'=>true));
		
		
	}
	*/


	public function programDayAction () {
		
		//var_dump($this->_getAllParams());
		//var_dump($this->_validateRequest());
		//die(__FILE__.': '.__LINE__);
		
		if ( $this->_getParam('date')=='неделя' ) {
			$d = new Zend_Date();
			$this->_forward('program-week', 'listings', 'default', array( 'date'=>$d->toString('YYYY-MM-dd') ));
			return true;
		}
		
		if (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $this->_getParam('alias'))) {
			$this->_forward('day-date', 'listings', 'default', array('date'=>$this->_getParam('alias')) );
			$this->_setParam('alias', null);
			return true;
		}
		
		//var_dump($this->_getAllParams());
		//die(__FILE__.': '.__LINE__);
		
		if( $this->_validateRequest() ) {
			
			$programs = new Xmltv_Model_Programs();
			$channels = new Xmltv_Model_Channels();
			$channelAlias = $this->_getParam('channel');
			$channel  = $channels->getByAlias( $this->_getParam('channel') );
			
			$date = new Zend_Date(null, null, 'ru');
			$pdate = $this->_getParam('date');
			if (!empty($pdate) && $pdate!='сегодня')
			$date = new Zend_Date($this->_getParam('date'), 'yyyy-MM-dd', 'ru');
			
			$programAlias = $this->_getParam('alias');
			
			//var_dump($date->toString());
			//var_dump($date->toString());
			//var_dump($date->toString());
			$program = $programs->getByAlias($programAlias, $channel->ch_id, $date);
			//$program->start = new Zend_Date($program->start, 'YYYY-MM-dd HH:mm:ss');
			
			$list = $programs->getProgramForDay( $program->alias, $channel->alias, $date );
			
			//var_dump($list);
			//die(__FILE__.': '.__LINE__);

			$this->view->assign( 'programs', $list );
			$this->view->assign( 'current_program', $program );
			$this->view->assign( 'channel', $channel  );
			$this->view->assign( 'date', $date );
			$this->view->assign( 'pageclass', 'program-day' );
			$this->view->assign( 'sidebar_videos', true );
			
			$programs->addHit( $programAlias );
			$channels->addHit( $channelAlias );
		
		} else {
			throw new Zend_Exception(__METHOD__." - Неверные данные", 500);
			$this->_redirect('/горячие-новости', array('exit'=>true));
		}
	}
	
	public function programWeekAction(){
		
		//var_dump($this->_getAllParams());
		//var_dump($this->_validateRequest());
		//die(__FILE__.': '.__LINE__);
		
		if( $this->_validateRequest() ) {
			
			
			$channels = new Xmltv_Model_Channels();
			$channel  = $channels->getByAlias( $this->_getParam('channel') );
			
			//var_dump($channel);
			//die(__FILE__.': '.__LINE__);
			
			$programs = new Xmltv_Model_Programs();
			$program = $programs->getByAlias( $this->_getParam('alias'), $channel->ch_id, new Zend_Date($this->_getParam('date'), 'YYYY-MM-dd'));
			
			//var_dump($program);
			//die(__FILE__.': '.__LINE__);
			
			/*
			 * initialize week start and week end dates
			 */
			$weekStart = new Zend_Date();
			
			$d = $this->_getParam('end', null)!==null ? new Zend_Date($this->_getParam('end'), null, 'ru') : new Zend_Date( null, null, 'ru' ) ; 
			/**
			 * @var Zend_Date
			 */
			$weekEnd = $this->_helper->weekDays(array('method'=>'getEnd', "data"=>array('date'=>$d) ));
			
			//var_dump($start->toString('YYYY-MM-dd'));
			//var_dump($end->toString('YYYY-MM-dd'));
			//die(__FILE__.': '.__LINE__);
			
			try {
				if (!$this->_getParam('date'))
					$list = $programs->getProgramThisWeek( $this->_getParam('alias'), $channel->ch_id, new Zend_Date() );
				else
					$list = $programs->getProgramThisWeek( $this->_getParam('alias'), $channel->ch_id, new Zend_Date( $this->_getParam('date') ) );
				
			} catch (Exception $e) {
				echo $e->getMessage();
			}
			

			//var_dump(count($list));
			if (!count($list)) {
				$this->view->assign( 'notfound', true );
				$list = $programs->getSimilarProgramsThisWeek( $this->_getParam('alias'), new Zend_Date() );
			}
			
			foreach ($list as $item) {
				//var_dump($item->start);
				//var_dump($item->end);
				$item->start = new Zend_Date($item->start, 'YYYY-MM-dd HH:mm:ss');
				$item->end = new Zend_Date($item->end, 'YYYY-MM-dd HH:mm:ss');
				//die(__FILE__.': '.__LINE__);
			}
			
			$program->start = new Zend_Date($program->start, 'YYYY-MM-dd HH:mm:ss');
			$program->end = new Zend_Date($program->end, 'YYYY-MM-dd HH:mm:ss');
			
			//var_dump($list);
			//die(__FILE__.': '.__LINE__);	
			
			$programs->addHit($this->_getParam('alias'));
			$channels->addHit($this->_getParam('channel'));

			$this->view->assign( 'week_start', $weekStart );
			$this->view->assign( 'week_end', $weekEnd );
			$this->view->assign( 'list', $list );
			$this->view->assign( 'program', $program );
			$this->view->assign( 'channel', $channel );
			$this->view->assign( 'pageclass', 'program-week' );
			
			
			
		} else {
			throw new Zend_Exception(__METHOD__." - Неверные данные", 500);
			$this->_redirect('/горячие-новости', array('exit'=>true));
		}
		
	}
	
	/**
	 * 
	 * Request parameters validation
	 */
	private function _validateRequest(){
		
		//var_dump($this->_getAllParams());
		//die(__FILE__.': '.__LINE__);
		
		$filters = array('*'=>'StringTrim', '*'=>'StringToLower');
		$validators = array(
			'channel'=>array(new Zend_Validate_Regex( '/^[\p{L}0-9- ]+$/iu' )), 
			'alias'=>array(new Zend_Validate_Regex( '/^[\p{L}0-9- ]+$/iu' )), 
			'module'=>array(new Zend_Validate_Regex( '/^[a-z]+$/u' )), 
			'controller'=>array(new Zend_Validate_Regex( '/^[a-z]+$/' )), 
			'action'=>array(new Zend_Validate_Regex( '/^[a-z-]+$/' )),
		);
		if( @isset( $this->_requestParams['date'] ) ) {
			$validators['date'] = array( new Zend_Validate_Regex( '/^([0-9]{4}-[0-9]{2}-[0-9]{2})|(сегодня)$/ui' ));
		}
		if( @isset( $this->_requestParams['timezone'] ) ) {
			$validators['timezone'] = array( new Zend_Validate_Regex( '/^-[0-9]{1,2}$/' ));
		}
		
		switch ($this->_requestParams['action']){
			case 'search':
				//var_dump($this->_getAllParams());
				//die(__FILE__.': '.__LINE__);
				$validators['fs'] = array( new Zend_Validate_Regex( '/^[\+\(\)\p{L}0-9- ]+$/iu' ));
				break;
		}
		
		//var_dump($expression)
		//var_dump(isset( $this->_requestParams['fs'] ));
		//var_dump($this->_requestParams['fs']);
		//die();
		
		$input = new Zend_Filter_Input( $filters, $validators, $this->_requestParams );
		
		//var_dump($input->isValid());
		//die(__FILE__.': '.__LINE__);
		
		if( $input->isValid() ) {
			return true;
		}
		return false;
	}
	
	
}

