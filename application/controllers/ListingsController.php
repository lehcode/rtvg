<?php
/**
 * Frontend programs listings controller
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: ListingsController.php,v 1.10 2012-08-04 20:59:05 developer Exp $
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

		if( !$this->_validateRequest() ) {
			throw new Zend_Exception("Неверные данные", 500);
			$this->_redirect('/error', array('exit'=>true));
		}
		
		$programs = new Xmltv_Model_Programs();
		$channels = new Xmltv_Model_Channels();
		$channel  = $channels->getByAlias($this->_requestParams['channel']);
		$comments = new Xmltv_Model_Comments();
		
		//var_dump($channel);
		//die(__FILE__.': '.__LINE__);
		
		$paramDate = $this->_getParam('date', null);
		$today = $paramDate!==null ? new Zend_Date( $paramDate, 'yyyy-MM-dd', 'ru' ) : new Zend_Date( null, null, 'ru' );
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
		
		$currentProgram = null;
		if (!empty($list)) {
			foreach ($list as $list_item) {
				if ($list_item->now_showing === true)
				$currentProgram = $list_item;
			}
		}
		
		
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
			var_dump($query);
			
			$feedData    = $comments->getYandexRss( $query );
			$commentsNew = $comments->parseYandexFeed( $feedData, 128 );
			
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
						$commentsLoaded = $comments->dbGetComments( $channel->alias );
					}
				} catch (Exception $e) {
					echo $e->getMessage();
				}
				
				$this->view->assign( 'comments', $commentsLoaded );
					
			}
			
			$this->view->assign( 'current_program', $currentProgram );
			$programs->addHit($currentProgram->alias);
			
		}
		
		$adult_channel = false;
		if ((bool)$channel->adult === true) {
			$adult_channel = true;
			$this->view->assign( 'sidebar_videos', false );
		} else {
			$this->view->assign( 'sidebar_videos', true );
		}
		//var_dump($adult_channel);

		$this->view->assign( 'channel', $channel );
		$this->view->assign( 'programs', $list );
		$this->view->assign( 'today', $today );
		$this->view->assign( 'video_data', array() );
		$this->view->assign( 'adult_channel', $adult_channel );
		
		$channels->addHit($channel->ch_id);
		
		
	}


	public function programDayAction () {
		
		//var_dump($this->_getAllParams());
		//var_dump($this->_validateRequest());
		//die(__FILE__.': '.__LINE__);
		
		if( $this->_validateRequest() ) {
			
			$programs = new Xmltv_Model_Programs();
			$channels = new Xmltv_Model_Channels();
			$channel  = $channels->getByAlias( $this->_getParam('channel') );
			
			$date     = new Zend_Date(null, null, 'ru');
			$pdate = $this->_getParam('date');
			if (!empty($pdate) && $pdate!='сегодня')
			$date = new Zend_Date($this->_getParam('date'), 'yyyy-MM-dd', 'ru');
			
			//var_dump($date->toString());
			
			$list = $programs->getProgramForDay( $this->_getParam('program'), $this->_getParam('channel'), $date );
			
			//var_dump($list);
			//die(__FILE__.': '.__LINE__);

			$this->view->assign( 'programs', $list );
			$this->view->assign( 'program_alias', $this->_getParam('program') );
			$this->view->assign( 'channel', $channel  );
			$this->view->assign( 'date', $date );
			
			$programs->addHit($this->_getParam('program'));
			$channels->addHit( Xmltv_String::strtolower( $channel->alias ));
		
		} else {
			//throw new Zend_Exception("Неверные данные", 500);
			$this->_redirect('/горячие-новости', array('exit'=>true));
		}
	}
	
	public function programWeekAction(){
		
		//var_dump($this->_getAllParams());
		//var_dump($this->_validateRequest());
		//die(__FILE__.': '.__LINE__);
		
		if( $this->_validateRequest() ) {
			
			$programs = new Xmltv_Model_Programs();
			$channels = new Xmltv_Model_Channels();
			$channel  = $channels->getByAlias( $this->_getParam('channel') );
			
			//var_dump($channel);
			//die(__FILE__.': '.__LINE__);
			
			$dates = $programs->getWeekDates();
			
			if (!$this->_getParam('date'))
			$list = $programs->getProgramThisWeek( $this->_getParam('program'), $this->_getParam('channel'), new Zend_Date() );
			else
			$list = $programs->getProgramThisWeek( $this->_getParam('program'), $this->_getParam('channel'), new Zend_Date( $this->_getParam('date') ) );

			$this->view->assign( 'dates', $dates );
			$this->view->assign( 'list', $list );
			$this->view->assign( 'program_alias', $this->_getParam('program') );
			$this->view->assign( 'channel', $channel );
			
			$programs->addHit($this->_getParam('program'));
			$channels->addHit($this->_getParam('program'));
			
		} else {
			throw new Zend_Exception("Неверные данные", 500);
			$this->_redirect('/error', array('exit'=>true));
		}
		
	}
	
	/**
	 * 
	 * Request parameters validation
	 */
	private function _validateRequest(){
		
		$filters = array('*'=>'StringTrim', '*'=>'StringToLower');
		$validators = array(
			'channel'=>array(new Zend_Validate_Regex( '/^[\p{L}0-9- ]+$/iu' )), 
			'program'=>array(new Zend_Validate_Regex( '/^[\p{L}0-9- ]+$/iu' )), 
			'module'=>array(new Zend_Validate_Regex( '/^[a-z]+$/u' )), 
			'controller'=>array(new Zend_Validate_Regex( '/^[a-z]+$/' )), 
			'action'=>array(new Zend_Validate_Regex( '/^[a-z-]+$/' )),
		);
		if( @isset( $this->_requestParams['date'] ) ) {
			$validators['date'] = array(
			new Zend_Validate_Regex( '/^([0-9]{4}-[0-9]{2}-[0-9]{2})|(сегодня)$/ui' ));
		}
		$input = new Zend_Filter_Input( $filters, $validators, $this->_requestParams );
		
		if( $input->isValid() ) {
			return true;
		}
		return false;
	}
	
	
}

