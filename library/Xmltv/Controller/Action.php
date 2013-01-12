<?php
class Xmltv_Controller_Action extends Zend_Controller_Action
{
    
    protected static $bitlyLogin = 'rtvg';
    protected static $bitlyKey = 'R_b37d5df77e496428b9403e236e672fdf';
    protected static $userAgent='';
    protected static $nocacheAgents=array(
    	'yandex',
    	'google',
    	'ahrefs',
    	'mail.ru',
    	'rambler',
    	'baidu',
    );
    protected static $nocache=false;
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
        $this->cache = new Xmltv_Cache();
        
        foreach ( self::$nocacheAgents as $a){
            if (stristr( self::$userAgent, $a)){
                self::$nocache = true;
            }
        }
        
        $this->weekDays = $this->_helper->getHelper('WeekDays');
        
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
	    
		$tinyurl     = new Zend_Service_ShortUrl_BitLy( self::$bitlyLogin, self::$bitlyKey );
		$maxTorrents = (int)Zend_Registry::get('site_config')->channels->torrents->get('amount');
		$i=0;
		$result = array();
		if($links->length>0) {
			foreach ($links as $link){
				if ($i<=$maxTorrents) {
					$result[$i] = new stdClass();
					$result[$i]->url   = trim($tinyurl->shorten( $links->item($i)->getAttribute('href') ));
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
	public function getBitlyLogin() {

		return $this->bitlyLogin;
	}

	/**
	 * @return string $bitlyKey
	 */
	public function getBitlyKey() {

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

	
}