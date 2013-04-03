<?php
/**
 * Core action controller for frontend
 * 
 * @author  Antony Repin
 * @version $Id: Action.php,v 1.14 2013-04-03 18:18:05 developer Exp $
 *
 */
class Rtvg_Controller_Action extends Zend_Controller_Action
{
	
    /**
     * @var string
     */
	protected static $bitlyLogin = 'rtvg';
	
	/**
	 * @var string
	 */
	protected static $bitlyKey = 'R_b37d5df77e496428b9403e236e672fdf';
	
	/**
	 * User agent properties container
	 * @var Zend_Http_UserAgent
	 */
	protected $userDevice;

	/**
	 * User agent properties container
	 * @var Zend_Http_UserAgent
	 */
	protected $userAgent;
	
	/**
	 * Youtube caching
	 * @var boolean
	 */
	protected static $videoCache=false;
	
	/**
	 * Url to redirect on error
	 * @var string
	 */
	protected $errorUrl;
	
	/**
	 * Helper
	 * @var Xmltv_Controller_Action_Helper_WeekDays
	 */
	protected $weekDays;
	
	/**
	 * @var array
	 */
	protected $kidsChannels=array();
	
	/**
	 * Javascript sources for inlineScript
	 * @var Rtvg_Ad_Collection
	 */
	//protected $adScripts;
	
	const FEATURED_CHANNELS_AMT=20;
	
	/**
	 * Channels model
	 * @var Xmltv_Model_Channels
	 */
	protected $channelsModel;
	
	/**
	 * Channels model
	 * @var Xmltv_Model_Programs
	 */
	protected $programsModel;

	/**
	 * Videos model
	 * @var Xmltv_Model_Videos
	 */
	protected $videosModel;

	/**
	 * Video cache model
	 * @var Xmltv_Model_vCache
	 */
	protected $vCacheModel;

	/**
	 * Video cache model
	 * @var Xmltv_Model_Comments
	 */
	protected $commentsModel;
	
	/**
	 *
	 * Validator
	 * @var Xmltv_Controller_Action_Helper_RequestValidator
	 */
	protected $_validator;
	
	/**
	 *
	 * Input filtering plugin
	 * @var Zend_Filter_Input
	 */
	protected $input;
	
	/**
	 * Caching object
	 * @var Rtvg_Cache
	 */
	protected $cache;
	
	/**
	 * 
	 * @var Zend_Controller_Action_Helper_ContextSwitch
	 */
	protected $contextSwitch;
	
	/**
	 * 
	 * @var Xmltv_Model_Users
	 */
	protected $usersModel;
	
	/**
     * FlashMessenger
     *
     * @var Zend_Controller_Action_Helper_FlashMessenger
     */
    protected $_flashMessenger;
	
    /**
     * Current data
     * @var Xmltv_User
     */
    protected $user;
    
    /**
     * Access checking action helper
     * @var Zend_Controller_Action_Helper_IsAllowed
     */
    protected $isAllowed;
    
    
	
	
    /**
	 * (non-PHPdoc)
	 * @see Zend_Controller_Action::init()
	 */
	public function init(){
		
		/**
		 * Change layout for AJAX requests
		 */
		if ($this->getRequest()->isXmlHttpRequest()) {
			$this->contextSwitch = $this->_helper->getHelper('ContextSwitch');
		}
		
		$this->_validator = $this->_helper->getHelper('RequestValidator');
		$this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
		
		$this->isAllowed = $this->_helper->getHelper('IsAllowed')->direct( 'grantAccess', array(
			'privilege'=>$this->_getParam('action', 'index'),
			'module'=>'default',
			'controller'=>$this->_getParam('controller', 'index'),
			'action'=>$this->_getParam('action', 'index'),
			) );
		
		$this->cache = new Rtvg_Cache();
		$e = ((bool)Zend_Registry::get( 'site_config' )->cache->system->get( 'enabled' ) && APPLICATION_ENV=='production');
        $this->cache->enabled = ($e===true) ? true : false;
        $this->cache->setLifetime( (int)Zend_Registry::get( 'site_config' )->cache->system->get( 'lifetime' ) );
        $this->cache->setLocation( ROOT_PATH.'/cache' );
		
        $this->errorUrl = $this->view->url( array(), 'default_error_error' );
        
        /**
         * Load bootstrap
         * @var Bootstrap
         */
        $bootstrap = $this->getInvokeArg('bootstrap');
        
        if (!$this->_request->isXmlHttpRequest()){
	        try {
	        
	            /**
	        	 * @var Zend_Http_UserAgent
	        	 */
	        	$this->userAgent = $bootstrap->getResource('useragent');
	        
	        	/**
	        	 * @var Zend_Http_UserAgent_AbstractDevice
	        	 */
	        	$this->userDevice = $this->userAgent->getDevice();
	        	$this->view->assign( 'user_device', $this->userDevice );
	        	
	        } catch (Exception $e) {
	        	
	        }
        }
        
        self::$videoCache = (bool)Zend_Registry::get('site_config')->cache->youtube->get('enabled');
		if (self::$videoCache){
			$this->vCacheModel = new Xmltv_Model_Vcache();
		}
				
		$this->weekDays = $this->_helper->getHelper('WeekDays');
		$this->channelsModel = new Xmltv_Model_Channels();
		$this->programsModel = new Xmltv_Model_Programs();
		$this->videosModel = new Xmltv_Model_Videos();
		$this->commentsModel = new Xmltv_Model_Comments();
		$this->usersModel = new Xmltv_Model_Users();
		
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
		
		if (!$this->_request->isXmlHttpRequest()){
		    $this->view->assign( 'hide_sidebar', 'both' );
		    $this->view->assign( 'show_popunder', true );
		    $this->view->assign( 'is_frontpage', false );
		    $this->view->assign( 'vk_group_init', true );
		}
		
		
	}
	
	
	
	
	/**
	 * Validate and filter request parameters
	 *
	 * @throws Zend_Exception
	 * @throws Zend_Controller_Action_Exception
	 * @return boolean
	 */
	protected function validateRequest($options=array()){
	
		if (!empty($options)){
			foreach ($options as $o=>$v){
				$vars[$o]=$v;
			}
		}
		
		foreach ($this->_getAllParams() as $k=>$p){
			$vars[$k]=$p;
		}
		
		// Validation routines
		$this->input = $this->_validator->direct( array('isvalidrequest', 'vars'=>$vars));
		if ($this->input===false) {
			if (APPLICATION_ENV=='development'){
				echo "Wrong input!";
				Zend_Debug::dump($this->input->getMessages());
				//die(__FILE__.': '.__LINE__);
			} elseif(APPLICATION_ENV!='production'){
				throw new Zend_Exception(self::ERR_INVALID_INPUT, 404);
			}
			/*
			$this->_redirect( $this->view->url( array(
				'params'=>$this->_getAllParams(),
				'hide_sidebar'=>'right'), 'default_error_invalid-input'), array('exit'=>true));
			*/
		} else {
			
			$invalid=array();
			foreach ($this->_getAllParams() as $k=>$v){
				if (!$this->input->isValid($k)) {
					$invalid[$k] = $this->_getParam($k);
				}
			}
			
			if (APPLICATION_ENV=='development'){
				foreach ($this->_getAllParams() as $k=>$v){
					if (!$this->input->isValid($k)) {
						throw new Zend_Controller_Action_Exception("Invalid ".$k.'! Value: '.$invalid[$k]);
					}
				}
			}
			
			return true;
	
		}
	
	}
	
	/**
	 *
	 * @param DOMNodeList $links
	 * @param array $torrents
	 */
	protected function torrentsShortLinks(DOMNodeList $links){
		
		
		$maxTorrents = (int)Zend_Registry::get('site_config')->channels->torrents->get('amount');
		$i=0;
		$result = array();
		if($links->length>0) {
			foreach ($links as $link){
				if ($i<=$maxTorrents) {
					$result[$i] = new stdClass();
					try {
						$tinyurl = new Zend_Service_ShortUrl_BitLy( self::$bitlyLogin, self::$bitlyKey );
						$result[$i]->url   = trim($tinyurl->shorten( $links->item($i)->getAttribute('href') ));
					} catch (Zend_Service_ShortUrl_Exception $e) {
						die($e->getMessage());
					}
					
					$result[$i]->title = Xmltv_String::substr( $links->item($i)->nodeValue, 0, Xmltv_String::strrpos( $links->item($i)->nodeValue, ' ' ) );
					$i++;
				}
			};
			return $result;
		}
	}
	
	/**
	 * @return string $bitlyLogin
	 */
	protected function getBitlyLogin() {

		return $this->bitlyLogin;
	}

	/**
	 * @return string $bitlyKey
	 */
	protected function getBitlyKey() {

		return $this->bitlyKey;
	}
	
	/**
	 *
	 * @param  array  $parts
	 * @param  string $route
	 * @param  array  $uniq
	 * @return string
	 */
	protected function getTinyUrl($parts=array(), $route=null, $uniq=array()){
	
		$tinyurl = new Zend_Service_ShortUrl_BitLy( self::$bitlyLogin, self::$bitlyKey);
		$url	 = 'http://rutvgid.ru'.$this->view->url( $parts, $route);
		$e = (bool)Zend_Registry::get('site_config')->cache->tinyurl->get('enabled');
		
		if ($e===true){
		    
			$t = (int)Zend_Registry::get('site_config')->cache->tinyurl->get('lifetime');
			$t>0 ? $this->cache->setLifetime((int)$t): $this->cache->setLifetime(86400) ;
			$this->cache->setLocation(ROOT_PATH.'/cache');
			$f = '/Tinyurl/Pages';
			
			$hash = Rtvg_Cache::getHash('tinyurl_'.implode(';', $parts).implode(';', $uniq));
			if (($link = $this->cache->load($hash, 'Core', $f))===false) {
				$link = trim($tinyurl->shorten( $url ));
				$this->cache->save($link, $hash, 'Core', $f);
			}
		} else {
			$link = trim($tinyurl->shorten( $url ));
		}
	
		return $link;
	
	}
	
	/**
	 * Check if listing date is earlier than allowed history 
	 * 
	 * @param  Zend_Date $date
	 * @param  int $history_length
	 * @return boolean
	 */
	protected static function checkDate(Zend_Date $date, $history_length=30){
		
	    $maxAgo = new Zend_Date( Zend_Date::now()->subDay($history_length)->toString('U'), 'U' ) ;
	    if ($date->compare($maxAgo)==-1){
	    	return false;
	    }
	    return true;
	    
	}

	/**
	 * Current channel
	 * 
	 * @param  string $alias //channel alias
	 * @return array
	 */
	protected function channelInfo($alias=null){
		
	    if (!$alias) {
			$alias = $this->input->getEscaped('channel');
	    }
	    
	    if (APPLICATION_ENV=='development'){
	        //var_dump($this->input->getEscaped('channel'));
	        //var_dump($alias);
	        //die(__FILE__.': '.__LINE__);
	    }
	    
		$model = new Xmltv_Model_Channels();
		
		if ($this->cache->enabled){
		    
		    $this->cache->setLifetime( 86400*7 );
			$f = '/Channels/Info';
			
			$hash = $this->cache->getHash( 'channel_'.$alias );
			if (($channel = $this->cache->load( $hash, 'Core', $f))===false) {
				$channel = $model->getByAlias( $alias );
				$this->cache->save($channel, $hash, 'Core', $f);
			}
		} else {
			$channel = $model->getByAlias( $alias );
		}
		
		if (APPLICATION_ENV=='development'){
			//var_dump($channel);
			//die(__FILE__.': '.__LINE__);
		}
		
		return $channel;
		
	}
	
	/**
	 * Current date from request variable
	 */
	protected function listingDate(){
		
	    $now = Zend_Date::now();
		if (preg_match('/^[\d]{2}-[\d]{2}-[\d]{4}$/', $this->input->getEscaped('date'))) {
			$d = new Zend_Date( new Zend_Date( $this->input->getEscaped('date'), 'dd-MM-YYYY' ), 'dd-MM-YYYY' );
		} elseif (preg_match('/^[\d]{4}-[\d]{2}-[\d]{2}$/', $this->input->getEscaped('date'))) {
			$d = new Zend_Date( new Zend_Date( $this->input->getEscaped('date'), 'YYYY-MM-dd' ), 'YYYY-MM-dd' );
			
		}
		
		//var_dump($d->compare($now, 'DD'));
		//die(__FILE__.': '.__LINE__);
		
		if (isset($d) && ($d->compare($now, 'DD')!=0)) {
		    $date = $d->toString('YYYY-MM-dd');
		    $time = $now->toString('HH:mm:ss');
		    return new Zend_Date( $date.' '.$time, 'YYYY-MM-dd HH:mm:ss' );
		} else {
			return $now;
		}
		
		if ( APPLICATION_ENV=='development' ){
		    //var_dump($d->toString());
		    //die(__FILE__.': '.__LINE__);
		}
		
		return $d;
		
	}
	
	/**
	 * Top programs for left sidebar which are publishded and
	 * available intul the end of the week
	 *
	 * @param  int $amt
	 * @params Zend_Date $week_start
	 * @params Zend_Date $week_end
	 * @return array
	 */
	protected function topPrograms($amt=20){
		/*
	    $now = Zend_Date::now();
	    $topAmt = (int)Zend_Registry::get('site_config')->top->listings->get('amount');
	    $week_start = $this->weekDays->getStart( $now);
	    $week_end   = $this->weekDays->getEnd( $now);
	    
	    if (APPLICATION_ENV=='development'){
	    	//var_dump($week_start->toString('dd-MM-YYYY'));
	    	//var_dump($week_end->toString('dd-MM-YYYY'));
	    	//die(__FILE__.': '.__LINE__);
	    }
	    
		$top   = $this->_helper->getHelper('Top');
		
		if ($this->cache->enabled){
		    
		    $this->cache->setLifetime(43200);
		    $this->cache->setLocation(ROOT_PATH.'/cache');
			$f = '/Listings/Top';
			
			$hash = Rtvg_Cache::getHash('top'.$amt);
			if (!$result = $this->cache->load($hash, 'Core', $f)) {
				$result = $this->programsModel->topPrograms( $amt, $week_start, $week_end );
				$this->cache->save($result, $hash, 'Core', $f);
			}
			
		} else {
			$result = $this->programsModel->topPrograms( $amt, $week_start, $week_end );
		}
		return $result;
		*/
	}
	
	/**
	 * Programs categories
	 */
	protected function getProgramsCategories(){
	
		$table = new Xmltv_Model_DbTable_ProgramsCategories();
		if ($this->cache->enabled){
			
		    $f = "/Listings/Category";
		    $this->cache->setLifetime(86400*30);
			$this->cache->setLocation(ROOT_PATH.'/cache');
			$hash  = Rtvg_Cache::getHash("ProgramsCategories");
			
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
	 * Channels categories
	 */
	protected function getChannelsCategories(){
	
		$model = new Xmltv_Model_Channels();
		if ($this->cache->enabled){
		    
			$f = "/Channels";
			$this->cache->setLocation(ROOT_PATH.'/cache');
			$this->cache->setLifetime(86400*30);
			
			$hash  = Rtvg_Cache::getHash("channels-categories");
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
	 * Top channels programs listing
	 * 
	 * @param  int $amt
	 * @return array
	 */
	protected function getTopChannels($amt=10){
		
		if (!$amt || !is_numeric($amt)){
			$a = (int)Zend_Registry::get('site_config')->top->channels->get('amount');
			$amt = $a>0 ? $a : self::FEATURED_CHANNELS_AMT;
		}
		
		if ($this->cache->enabled){
		    $this->cache->setLocation(ROOT_PATH.'/cache');
			$hash = Rtvg_Cache::getHash('featuredchannels');
			$f = '/Channels/Top';
			if (($result = $this->cache->load($hash, 'Core', $f))===false) {
				$result = $this->channelsModel->topChannels($amt);
				$this->cache->save($result, $hash, 'Core', $f);
			}
		} else {
			$result = $this->channelsModel->topChannels($amt);
		}
		
		return $result;
		
	}
	
	/**
	 * Featured channels
	 * 
	 * @param  int $amt
	 * @return array
	 */
	protected function getFeaturedChannels($amt=null){
		
		if (!$amt || !is_numeric($amt)){
			$a = (int)Zend_Registry::get('site_config')->featured->channels->get('amount');
			$amt = $a>0 ? $a : self::FEATURED_CHANNELS_AMT;
		}
		
		if ($this->cache->enabled){
		    
		    $this->cache->setLocation( ROOT_PATH.'/cache' );
			$hash = Rtvg_Cache::getHash( 'featuredchannels_'.(string)$amt );
			$f = '/Channels/Featured';
			if (($result = $this->cache->load($hash, 'Core', $f))===false) {
				$result = $this->channelsModel->featuredChannels($amt);
				$this->cache->save($result, $hash, 'Core', $f);
			}
			
		} else {
			$result = $this->channelsModel->featuredChannels($amt);
		}
		
		return $result;
		
	}
	
	/**
	 * Display offline page
	 */
	public function offlineAction(){
		
	}
	
	/**
	 * Fetch blog entries from blogs.yandex.ru
	 *
	 * @param  array $channel
	 * @throws Zend_Exception
	 * @return array
	 * 
	 */
	protected function yandexComments($channel=array()){
	
		if (empty($channel) && !is_array($channel)){
			throw new Zend_Exception( parent::ERR_WRONG_PARAM.__METHOD__, 500);
		}
		
		$comments = $this->commentsModel->channelComments( $channel['id'] );
		
		if ($comments===false) {
		    return false;
		}
		
		
		return $comments;
		
	}
	
	/**
	 * Videos for right sidebar
	 *
	 * @param  array $channel
	 * @return array
	 */
	protected function sidebarVideos($channel){
	
		$vc  = Zend_Registry::get('site_config')->videos->sidebar->right;
		$max = (int)Zend_Registry::get('site_config')->videos->sidebar->right->get('max_results');
		$ytConfig = array(
				'order'=>$vc->get('order'),
				'max_results'=>(int)$vc->get('max_results'),
				'start_index'=>(int)$vc->get('start_index'),
				'safe_search'=>$vc->get('safe_search'),
				'language'=>'ru',
		);
		
		$videos = array();
		$ytSearch = 'канал '.Xmltv_String::strtolower($channel['title']);
		
		// If file cache is enabled
		if ($this->cache->enabled) {
			 
			$t = (int)Zend_Registry::get( 'site_config' )->cache->youtube->sidebar->get( 'lifetime' );
			$t>0 ? $this->cache->setLifetime($t): $this->cache->setLifetime(86400*7) ;
			$f = '/Youtube/SidebarRight';
			$this->cache->setLocation( ROOT_PATH.'/cache' );
			$hash = Rtvg_Cache::getHash( 'related_'.$channel['title'].'_u'.time());
			/*  
			if (self::$videoCache && $this->isAllowed){

			    // Query database cache for video
				if (($videos = $this->vCacheModel->sidebarVideos( $channel['id'] ))===false){
					
				    // Query file cache
				    // if video was not found inDB cache
				    if (($videos = $this->cache->load( $hash, 'Core', $f))===false) {
				    
				        // Query Youtube if video was not found 
				        // in neither DB cache nor file cache
				        $videos = $this->videosModel->ytSearch( $ytSearch, $ytConfig);
				        
				        if (!count($videos) || $videos===false){
				        	return false;
				        }
				    	
				    }
				    
				    // Save to file cache
				    $this->cache->save( $videos, $hash, 'Core', $f );
				    
			        // Save to database cache if it is enabled
			        if (self::$videoCache==true){
			        	foreach ($videos as $vid){
			        		$this->vCacheModel->saveSidebarVideo( $vid, $channel['id'] );
			        	}
			        }
			        
				}
				
			} else {
			 */	 
				// Database cache is disabled
				// Try to fetch from file cache
				if (($videos = $this->cache->load( $hash, 'Core', $f))===false) {
					$videos = $this->videosModel->ytSearch( $ytSearch, $ytConfig);
					$this->cache->save( $videos, $hash, 'Core', $f );
				}
			//}
			 
		} else {
		    
		    // No caching
			$videos = $this->videosModel->ytSearch( $ytSearch, $ytConfig);
		}
		 
		return $videos;
		 
	}
	
	protected function getChannel( $alias=null )
	{
	    if (!$alias){
	        throw new Zend_Exception( Rtvg_Message::ERR_MISSING_PARAM, 500 );
	    }
	    
	    if ($this->cache->enabled){
	    	 
	    	$f = '/Channels';
	    	$this->cache->setLifetime(86400);
	    	$hash = $this->cache->getHash('channel_'.$alias);
	    
	    	if (false === ($result = $this->cache->load( $hash, 'Core', $f))) {
	    		$result = $this->channelsModel->getByAlias( $alias );
	    		$this->cache->save( $result, $hash, 'Core', $f);
	    	}
	    } else {
	    	$result = $this->channelsModel->getByAlias($alias);
	    }

	    return $result;
	    
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Zend_Controller_Action::__call()
	 */
	public function __call ($method, $arguments)
	{
		throw new Zend_Exception( $method." does not exist!", 404);
	}
	
	/**
	 * Contruct pageclass name 
	 * using actual PHP class name
	 * 
	 * @param  string $classname
	 * @return string
	 */
	public function pageclass($classname=null)
	{
	    return strtolower(str_ireplace('controller', '', $classname));
	}
	
}