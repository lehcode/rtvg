<?php
/**
 * Core action controller for frontend
 * 
 * @author  Antony Repin
 * @version $Id: Action.php,v 1.16 2013-04-10 01:58:37 developer Exp $
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
	 * @deprecated
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
	 * @var Xmltv_Model_Broadcasts
	 */
	protected $bcModel;

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
     *
     * @var Zend_Log_Writer_Firebug
     */
    protected $fireLog;
	
	
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
        ));
		
		$this->cache = new Rtvg_Cache();
		$this->cache->enabled = (bool)Zend_Registry::get( 'site_config' )->cache->system->enabled;
        if ($this->cache->enabled){
            $this->cache->setLifetime( (int)Zend_Registry::get( 'site_config' )->cache->system->lifetime);
        }
        
        /**
         * Load bootstrap
         * @var Bootstrap
         */
        $bootstrap = $this->getInvokeArg('bootstrap');
        
        self::$videoCache = (bool)Zend_Registry::get('site_config')->cache->youtube->get('enabled');
		if (self::$videoCache){
			$this->vCacheModel = new Xmltv_Model_Vcache();
		}
				
		$this->weekDays = $this->_helper->getHelper('WeekDays');
		$this->channelsModel = new Xmltv_Model_Channels();
		$this->bcModel = new Xmltv_Model_Broadcasts();
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
		    //$this->view->assign( 'show_popunder', true );
		    $this->view->assign( 'is_frontpage', false );
		    $this->view->assign( 'vk_group_init', true );
		}
        
        $nav  = $this->navData('topnav');
        $this->view->assign('nav_data', $nav);
		
        $this->fireLog = new Zend_Log( new Zend_Log_Writer_Firebug() );
		
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
	public function channelInfo($alias=null){
		
	    if (!$alias) {
			$alias = $this->input->getEscaped('channel');
	    }
	    
	    return $this->channelsModel->getByAlias( $alias );
		
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
	protected function topBroadcasts(){
		
	    $now = Zend_Date::now();
	    $amt = (int)Zend_Registry::get('site_config')->top->broadcasts->get('amount');
        
        if ($amt===0){
            return array();
        }
        
        $week_start = $this->weekDays->getStart($now);
	    $week_end   = $this->weekDays->getEnd($now);
        $bcModel = new Xmltv_Model_Broadcasts();
		
        if ($this->cache->enabled){
		    $this->cache->setLifetime(43200);
			$f = '/Listings/Top';
			$hash = Rtvg_Cache::getHash('top'.$amt);
			if (!$result = $this->cache->load($hash, 'Core', $f)) {
				$result = $bcModel->topBroadcasts($amt, $week_start, $week_end);
				$this->cache->save($result, $hash, 'Core', $f);
			}
			
		} else {
			$result = $bcModel->topBroadcasts($amt, $week_start, $week_end);
		}
        
		return $result;
		
	}
	
	/**
	 * Programs categories
	 */
	protected function getProgramsCategories(){
	
		$table = new Xmltv_Model_DbTable_ProgramsCategories();
		if ($this->cache->enabled){
			
		    $f = "/Listings/Category";
		    $this->cache->setLifetime(86400*30);
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
	 * Display offline page
	 */
	public function offlineAction(){
		
    }
    
    public function getChannelsCategories(){
        return $this->channelsModel->channelsCategories();
    }
    
    public function channelSidebarVideos($channel=null){
        
        if (!$channel || !is_array($channel)){
            throw new Zend_Exception('$channel is not defined');
        }
        return $this->videosModel->sidebarVideos($channel);
        
    }
    
    public function getFeaturedChannels($amt=null){
        return $this->channelsModel->featuredChannels($amt);
    }
	
	/**
	 * (non-PHPdoc)
	 * @see Zend_Controller_Action::__call()
	 */
	public function __call($method=null, $args=array())
    {
        if ('Action' == substr($method, -6)) {
            $controller = $this->getRequest()->getControllerName();
            $url = '/' . $controller . '/index';
            return $this->_redirect($url, $args);
        }
        throw new Zend_Exception('Invalid method \''.$method.'\'');
    }
	
	/**
	 * Contruct pageclass name using actual PHP class name
	 * 
	 * @param  string $classname
	 * @return string
	 */
	public function pageclass($classname=null) {
	    return strtolower(str_ireplace('controller', '', $classname));
	}
    
    
    public function navData($type=null){
        
        if (!$type){
            throw new Zend_Exception("No navigation type provided");
        }
        
        $nav = array();
        $model = new Xmltv_Model_Abstract();
        
        switch ($type){
            case 'topnav':
                $nav[$type] = $model->getTopnavData();
                break;
        }
        
        return $nav;
        
    }
	
}