<?php
defined('APP_STARTED') or die();
class Admin_Model_Grab 
{
		
	protected $siteKey;
	//protected $siteConfig;
	protected $site;
	protected $startURI;
	protected $baseURL;
	protected $proxy;
	
	function __construct(){
		
	}
	
	public function setSiteKey($key) {
		$this->siteKey = $key;
	}
	public function getSiteKey() {
		return $this->siteKey;
	}

	public function setSite($siteKey=null) {
		
		if (!$siteKey)
		throw new Exception("Пропущен псевдоним сайта", 500);
		
		$this->setSiteKey($siteKey);
		$sites = Xmltv_Config::getSitesXmlConfig('listings');
	    	$replace = new Zend_Filter_Word_SeparatorToSeparator('.', '');
	    	$key = $this->getSiteKey();
	    	$this->site = new Xmltv_Grabber();
	    	foreach ($sites as $s){
	    		if ($replace->filter($s->title)==$key) {
	    			
	    			if (isset($s->startUri))
	    			$this->startURI = $s->startUri;
	    			if (isset($s->searchUrl))
	    			$this->startURI = $s->searchUrl;
	    			
	    			$this->baseURL = $s->baseUrl;
	    			
	    		}
	    	}
	    	
	}
	
	public function getSite(){
		return $this->site;
	}
	
	public function getChannelsPage(){
		return $this->site->fetchPage($this->baseURL.$this->startURI, $this->site->getEncoding());
	}

	public function setSiteEncoding($encoding=null){
		if (!$encoding) return;
		return $this->site->setEncoding($encoding);
	}
	
	public function enableProxy($options=array()){
		
		if (empty($options['host']) ||  !isset($options['host']))
		$options['host']='127.0.0.1';
		
		if (empty($options['host']))
		throw new Exception("Не указан порт прокси", 500);
		
		$this->site->setProxy($options);
	}
	/*
	private function _getClass($key=null) {
		if(!$key)
		return;
		$siteClass = "Xmltv_Site_" . str_replace(' ', '', ucwords(str_replace('-', ' ', $key)));
		$siteObj = new $siteClass;	
		if($siteObj)
		return $siteObj;
    }
	*/
}

