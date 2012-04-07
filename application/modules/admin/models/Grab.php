<?php

/**
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: Grab.php,v 1.2 2012-04-07 09:08:30 dev Exp $
 *
 */
class Admin_Model_Grab 
{
	protected $_session;
	protected $encoding='UTF-8';
	protected $parser;
	protected $siteKey;
	protected $startURI;
	protected $baseURL;
	protected $sites=array();
	protected $site;
	
	private $_debug=false;
	private $_caching=false;
	private $_proxy=false;
	private $_cache;
	private $_response;
	private $_cache_lifetime;
	private $_DOM;
	private $_HTML;
	private $_connect_timeout;
	private $_channels_info;
	
	const PAGE_JSON = 1;
	const PAGE_DOM  = 2;
	
	public $save_cookies = false;
	
	function __construct($config=array()){
		/*
		 * Подготовка CURL
		 */
		$this->_session = curl_init(); 
		curl_setopt($this->_session, CURLOPT_HEADER, false); 
		curl_setopt($this->_session, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($this->_session, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($this->_session, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($this->_session, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($this->_session, CURLOPT_FORBID_REUSE, false);
		curl_setopt($this->_session, CURLOPT_FRESH_CONNECT, false);
		curl_setopt($this->_session, CURLOPT_TIMEOUT, 30);
		curl_setopt($this->_session, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; ru-RU) AppleWebKit/525.19 (KHTML, like Gecko) Chrome/0.2.153.1 Safari/525.19');
		/*
		 * кэширование
		 */
		$t = isset($config['cache_lifetime']) && !empty($config['cache_lifetime']) ? (int)$config['cache_lifetime'] : Xmltv_Config::getCacheLifetime();
		$this->_cache  = Zend_Cache::factory('Output', 'File', 
		array(  'lifetime' => $t, 'automatic_serialization' => true), 
		array( 'cache_dir' => ROOT_PATH . Xmltv_Config::getCacheLocation() ));
		
		$this->parser = Xmltv_Parser_StringParser::getInstance();
	}
	
	public function __destruct() {
		curl_close($this->_session);
	}
	
	public function setSiteKey($key) {
		$this->siteKey = $key;
	}
	public function getSiteKey() {
		return $this->siteKey;
	}

	/**
	 * 
	 * Полученние списка каналов с сайта с расписанием программ
	 * 
	 * @param string $key
	 * @param array  $options
	 */
	public function setChannelsInfo($key=null, $options=array()){
		
		if (!$key) throw new Exception("Не указан класс сайта", 500);
		
		foreach ($this->sites as $s) {
    		if ($s->alias==$key) {
    			
    			$this->baseURL = $s->baseUrl;
    			$this->startURI = $s->startUri;
    			$url = $this->baseURL.$this->startURI;
    			
    			$this->fetchPage($url, $this->encoding);
    			$className = 'Xmltv_Parser_Listings_'.ucfirst($key);
    			$htmlParser = new $className;
    			$htmlParser->setWrap($this->_DOM->query('td.main'));
    			$htmlParser->parseChannelsList();
    			$channels_info = $htmlParser->getChannelsInfo();
    			var_dump($channels_info);
    			
    		}
    	}
	}
	
	
	
	
	
	/**
	 * Установка свойств сайта для получения и парсинга программы
	 * 
	 * @param string $siteKey	Alias
	 * @param array  $options	Options
	 */
	public function setSite($siteKey=null, $options=array()) {
		
		if (!$siteKey)
		throw new Exception("Пропущен псевдоним сайта", 500);
		
		if ((bool)$options['set_cookies'])
		$this->enableCookies();
		
		$this->setSiteKey($siteKey);
		$sites = Xmltv_Config::getSitesXmlConfig('listings');
    	$replace = new Zend_Filter_Word_SeparatorToSeparator('.', '');
    	$key = $this->getSiteKey();
    	foreach ($sites as $s){
    		
    			$alias = $replace->filter($s->title);
    			$this->sites[$alias] = new Zend_Config($s->toArray(), true);
    			$this->sites[$alias]->alias  = $alias;
    			$this->sites[$alias]->active = (int)$s->active;
    			
    	}
    		
	}
	
	public function getSite(){
		return $this;
	}
	
	public function setUrl($url) {
		curl_setopt($this->_session, CURLOPT_URL, $url);
	}
	
	public function setCookie($cookie) {
		curl_setopt($this->_session, CURLOPT_COOKIE, $cookie);
	}
	
	public function setProxy($config=array('host'=>null, 'port'=>null, 'type'=>null)){
		
		if (!empty($config['host']) && isset($config['host']))
		curl_setopt($this->_session, CURLOPT_PROXY, (string)$config['host']);
		else
		curl_setopt($this->_session, CURLOPT_PROXY, '127.0.0.1');
		
		if (!empty($config['port']) && isset($config['port'])) {
			curl_setopt($this->_session, CURLOPT_PROXYPORT, (int)$config['port']);
			$this->_proxy=true;
		}
		
		if (isset($config['type']) && !empty($config['type']) && $config['type']=='socks')
		curl_setopt($this->_session, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS4);
		
	}
	
	public function fetch($method = self::PAGE_JSON) {
	
		if ($data = curl_exec($this->_session)){
			switch($method) {
				
				case self::PAGE_JSON: 
					
					$data = json_decode($data);
					
					break;
					
				case self::PAGE_DOM:
					
					if (mb_detect_encoding($data)=='UTF-8') {
						if (!Xmltv_String::valid($data))
						$data = Xmltv_String::transcode($data, $this->encoding, 'UTF-8');
					}
					
					//if ($this->_caching)
					//$data = iconv('UTF-8', $this->encoding, $data);
					
					$this->_response = new Zend_Dom_Query($data, $this->encoding);
					
					break;
					
				default:
					
			}
		} else {
			throw new Exception("Пустой ответ");
		}

			
	}
	
	public function fetchPage($url=null){
		
		if (!$url)
		throw new Exception("Не указана страница для парсинга", 500);
		
		$this->setUrl($url);
		$hash = $this->_getCacheHash(__METHOD__);
		if ($this->_caching) {
			$hash = $this->_getCacheHash(__METHOD__);
			if (!$this->_response = $this->_cache->load($hash)){
				$this->fetch(self::PAGE_DOM);
				$this->_cache->save($this->_response, $hash);
			}
		} else {
			$this->fetch(self::PAGE_DOM);
		}
		
		$this->_response->setEncoding('UTF-8');
		$this->_DOM  = $this->_response;
		$this->_HTML = $this->_DOM->getDocument();
		
	}
	
	private function _getCacheHash($input=null){
		if (!$input)
		throw new Exception("Не указан кэш-идентификатор", 500);
		
		$filter = new Zend_Filter_Word_SeparatorToSeparator(':', '_');
		
		if (Xmltv_Config::getDebug()===true)
		return $filter->filter($input);
		else
		return md5($filter->filter($input));
	}
	
	
	/**
	 * @return string
	 */
	public function getEncoding () {

		return $this->encoding;
	}

	/**
	 * @param $encoding  Site encoding
	 */
	public function setEncoding ($encoding='UTF-8') {
		$this->encoding = $encoding;
		
	}

	public function enableProxy($options=array()){
		
		if (empty($options['host']) ||  !isset($options['host']))
		$options['host']='127.0.0.1';
		
		if (empty($options['host']))
		throw new Exception("Не указан порт прокси", 500);
		
		$this->setProxy($options);
		
	}
	
	public function enableCookies($cookie_file=null, $cookie_jar=null){
		
		if (!$cookie_file)
		throw new Exception("Не указан файл для сохранение куки", 500);
		
		$this->save_cookies = true;
		curl_setopt($this->_session, CURLOPT_COOKIEFILE, $cookie_file); 
		
		if ($cookie_jar)
		curl_setopt($this->_session, CURLOPT_COOKIEJAR, $cookie_jar); 
		
	}
	/**
	 * @return bool
	 */
	public function getCaching () {

		return $this->_caching;
	}

	/**
	 * @param bool $caching		true/false
	 * @param int $lifetime		seconds
	 */
	public function setCaching ($caching, $lifetime=600) {
		
		$this->_caching = $caching;
		$this->_cache_lifetime = $lifetime;
		//var_dump($this);
		//die();
	}

	
	/**
	 * @return Zend_Cache_Frontend
	 */
	private function _getCache(){
		return $this->_cache;
	}
	/**
	 * @return int
	 */
	public function getConnectTimeout () {

		return $this->_connect_timeout;
	}

	/**
	 * @param int $_connect_timeout		CURL Connection timeout in seconds
	 */
	public function setConnectTimeout ($_connect_timeout=15) {

		$this->_connect_timeout = $_connect_timeout;
		curl_setopt($this->_session, CURLOPT_CONNECTTIMEOUT, $this->_connect_timeout);
	}
	
	/**
	 * @param $_debug the $_debug to set
	 */
	public function setDebug ($debug=false) {

		$this->_debug = $debug;
	}


	
}

