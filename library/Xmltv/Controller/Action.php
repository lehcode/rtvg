<?php
/**
 * Core action controller for frontend
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: Action.php,v 1.3 2013-01-19 10:11:14 developer Exp $
 *
 */
class Xmltv_Controller_Action extends Zend_Controller_Action
{
    
    protected static $bitlyLogin = 'rtvg';
    protected static $bitlyKey = 'R_b37d5df77e496428b9403e236e672fdf';
    protected static $userAgent='';
    protected static $videoCache=false;
    protected $weekDays;
    
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
     * (non-PHPdoc)
     * @see Zend_Controller_Action::__call()
     */
    
    const ERR_INVALID_INPUT = 'Неверные данные для ';
    const ERR_MISSING_CHANNEL_INFO = "Не указаны данные канала для ";
    
    
    
    /**
     * (non-PHPdoc)
     * @see Zend_Controller_Action::init()
     */
    public function init(){
        
        /**
         * Change layout for AJAX requests
         */
        if ($this->getRequest()->isXmlHttpRequest()) {
        	$this->contextSwitch = $this->_helper->getHelper('contextSwitch');
        }
        
        $this->validator = $this->_helper->getHelper('requestValidator');
        
        
        if (Zend_Registry::get('user_agent')!==false){
            self::$userAgent = Zend_Registry::get('user_agent');
            self::$videoCache = true;
        } else {
            self::$userAgent = 'PHP/5.3';
            self::$videoCache = false;
        }
        
        //var_dump(self::$userAgent);
        //var_dump(self::$videoCache);
        //die(__FILE__.': '.__LINE__);
        
        if (isset($_GET['RTVG_PROFILE'])) {
        	var_dump(self::$nocache);
        	var_dump(self::$userAgent);
        }
        
        $this->weekDays = $this->_helper->getHelper('WeekDays');
        $this->cache = new Xmltv_Cache();
        
        //var_dump($this->_getAllParams());
        //die(__FILE__.': '.__LINE__);
        
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
    			var_dump($this->_getAllParams());
    			die(__FILE__.': '.__LINE__);
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
	
	/**
	 * 
	 * @param Zend_Date $date
	 * @return boolean
	 */
	protected function checkDate(Zend_Date $date){
		
	    $l = (int)Zend_Registry::get('site_config')->listings->history->get('length');
	    $this->view->assign( 'history_length', $l);
	    $maxAgo = new Zend_Date( Zend_Date::now()->subDay($l)->toString('U'), 'U' ) ;
	    if ($date->compare($maxAgo)==-1){ //More than x days
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
	    	$hash = $this->cache->getHash('channel_'.$channelAlias);
	    	if (($channel = $this->cache->load($hash, 'Core', $f))===false) {
	    		$channel = $model->getByAlias($channelAlias);
	    		$this->cache->save($channel, $hash, 'Core', $f);
	    	}
	    } else {
	    	$channel = $model->getByAlias($channelAlias);
	    }
	    
	    return $channel;
	    
	}
	
	/**
	 * Current date from request variable
	 */
	public function listingDate(){
		
	    if (preg_match('/^[\d]{2}-[\d]{2}-[\d]{4}$/', $this->input->getEscaped('date'))) {
	    	return new Zend_Date( new Zend_Date( $this->input->getEscaped('date'), 'dd-MM-yyyy' ), 'dd-MM-yyyy' );
	    } elseif (preg_match('/^[\d]{4}-[\d]{2}-[\d]{2}$/', $this->input->getEscaped('date'))) {
	    	return new Zend_Date( new Zend_Date( $this->input->getEscaped('date'), 'yyyy-MM-dd' ), 'yyyy-MM-dd' );
	    } else {
	    	return new Zend_Date();
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
	
	private function _initCache(){
		
	    $this->cache = new Xmltv_Cache();
	    $this->cache->lifetime = (int)Zend_Registry::get('site_config')->cache->system->get('lifetime');
	    
	}
}