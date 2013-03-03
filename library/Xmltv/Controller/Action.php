<?php
/**
 * Core action controller for frontend
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: Action.php,v 1.9 2013-03-03 23:30:36 developer Exp $
 *
 */
class Xmltv_Controller_Action extends Zend_Controller_Action
{
	
	protected static $bitlyLogin = 'rtvg';
	protected static $bitlyKey = 'R_b37d5df77e496428b9403e236e672fdf';
	protected static $userAgent='';
	protected static $videoCache=false;
	protected $weekDays;
	protected $kidsChannels=array();
	
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
    protected $_flashMessenger = null;
	
    /**
     * Current data
     * @var Xmltv_User
     */
    protected static $user;
    
	const ERR_INVALID_INPUT        = 'Неверные данные для ';
	const ERR_MISSING_CHANNEL_INFO = "Не указаны данные канала для ";
	const ERR_MISSING_CONTROLLER   = "Не указан контроллер для ";
	const ERR_WRONG_PARAM          = "Неверный параметр для ";
	
	
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
		
		$this->validator = $this->_helper->getHelper('RequestValidator');
		
		self::$videoCache = (bool)Zend_Registry::get('site_config')->cache->youtube->get('enabled');
		if (self::$videoCache){
			$this->vCacheModel = new Xmltv_Model_Vcache();
		}
		
		self::$userAgent = Zend_Registry::get('user_agent');
		if (self::$userAgent===false){
			self::$userAgent = 'PHP/5.3';
			self::$videoCache = false;
		}
		
		$this->weekDays = $this->_helper->getHelper('WeekDays');
		$this->cache = new Xmltv_Cache();
		$this->cache->setLocation(ROOT_PATH.'/cache');
		$this->channelsModel = new Xmltv_Model_Channels();
		$this->programsModel = new Xmltv_Model_Programs();
		$this->videosModel = new Xmltv_Model_Videos();
		$this->commentsModel = new Xmltv_Model_Comments();
		$this->usersModel = new Xmltv_Model_Users();
		
		$this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
		
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
		
		$this->initView();
		
		
		$auth = Zend_Auth::getInstance();
		if ($auth->getIdentity()===null){
		    self::$user = $this->usersModel->defaultUser();
		} else {
		    self::$user = $auth->getIdentity();
		}
		$this->view->assign('user', self::$user);
		
	}
	
	/**
	 * Validate and filter request parameters
	 *
	 * @throws Zend_Exception
	 * @throws Zend_Controller_Action_Exception
	 * @return boolean
	 */
	protected function requestParamsValid($options=array()){
	
		if (!empty($options)){
			foreach ($options as $o=>$v){
				$vars[$o]=$v;
			}
		}
		
		foreach ($this->_getAllParams() as $k=>$p){
			$vars[$k]=$p;
		}
		
		// Validation routines
		$this->input = $this->validator->direct( array('isvalidrequest', 'vars'=>$vars));
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
			
			$hash = Xmltv_Cache::getHash('tinyurl_'.implode(';', $parts).implode(';', $uniq));
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
	 * @return Ambigous <stdClass, mixed>
	 */
	public function channelInfo(){
		
		$channelAlias = $this->input->getEscaped('channel');
		$model = new Xmltv_Model_Channels();
		if ($this->cache->enabled){
			$f = '/Channels';
			$this->cache->setLocation(ROOT_PATH.'/cache');
			$hash = $this->cache->getHash('channel_'.$channelAlias);
			if (($channel = $this->cache->load($hash, 'Core', $f))===false) {
				$channel = $model->getByAlias($channelAlias);
				$this->cache->save($channel, $hash, 'Core', $f);
			}
		} else {
			$channel = $model->getByAlias($channelAlias);
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
	public function listingDate(){
		
		if (preg_match('/^[\d]{2}-[\d]{2}-[\d]{4}$/', $this->input->getEscaped('date'))) {
			return new Zend_Date( new Zend_Date( $this->input->getEscaped('date'), 'dd-MM-YYYY' ), 'dd-MM-YYYY' );
		} elseif (preg_match('/^[\d]{4}-[\d]{2}-[\d]{2}$/', $this->input->getEscaped('date'))) {
			return new Zend_Date( new Zend_Date( $this->input->getEscaped('date'), 'YYYY-MM-dd' ), 'YYYY-MM-dd' );
		} else {
			return new Zend_Date();
		}
		
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
		    $this->cache->setLifetime(600);
		    $this->cache->setLocation(ROOT_PATH.'/cache');
			$f = '/Listings/Top';
			$hash = Xmltv_Cache::getHash('top'.$amt);
			if (!$result = $this->cache->load($hash, 'Core', $f)) {
				$result = $this->programsModel->topPrograms( $amt, $week_start, $week_end );
				$this->cache->save($result, $hash, 'Core', $f);
			}
		} else {
			$result = $this->programsModel->topPrograms( $amt, $week_start, $week_end );
		}
		return $result;
	
	}
	
	/**
	 * Programs categories
	 */
	protected function getProgramsCategories(){
	
		$table = new Xmltv_Model_DbTable_ProgramsCategories();
		if ($this->cache->enabled){
			$f = "/Channels";
			$this->cache->setLocation(ROOT_PATH.'/cache');
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
	 * Channels categories
	 */
	protected function getChannelsCategories(){
	
		$model = new Xmltv_Model_Channels();
		if ($this->cache->enabled){
			$f = "/Channels";
			$this->cache->setLocation(ROOT_PATH.'/cache');
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
	
	protected function _initCache(){
		
		$this->cache = new Xmltv_Cache();
		$this->cache->setLocation(ROOT_PATH.'/cache');
		$this->cache->lifetime = (int)Zend_Registry::get('site_config')->cache->system->get('lifetime');
		
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
			$hash = Xmltv_Cache::getHash('featuredchannels');
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
		    
		    $this->cache->setLocation(ROOT_PATH.'/cache');
			$hash = Xmltv_Cache::getHash('featuredchannels_'.(int)$amt);
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
}