<?php



class Xmltv_Site {

	protected $_session;
	protected $_encoding='UTF-8';
	
	const PAGE_JSON = 1;
	const PAGE_DOM  = 2;

	public function __construct() {	
		$this->_session = curl_init(); 
		curl_setopt($this->_session, CURLOPT_HEADER, false); 
		curl_setopt($this->_session, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($this->_session, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($this->_session, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($this->_session, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($this->_session, CURLOPT_FORBID_REUSE, false);
		curl_setopt($this->_session, CURLOPT_FRESH_CONNECT, false);
		curl_setopt($this->_session, CURLOPT_CONNECTTIMEOUT, 15);
		curl_setopt($this->_session, CURLOPT_TIMEOUT, 30);
		curl_setopt($this->_session, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.19 (KHTML, like Gecko) Chrome/0.2.153.1 Safari/525.19');
	}
	
	public function __destruct() {
		curl_close($this->_session);
	}
	
	public function setUrl($url) {
		curl_setopt($this->_session, CURLOPT_URL, $url);
	}
	
	public function setCookie($cookie) {
		curl_setopt($this->_session, CURLOPT_COOKIE, $cookie);
	}
	
	public function setProxy($config=array('host'=>null, 'port'=>null, 'type'=>null)){
		if (!empty($config['host']))
		curl_setopt($this->_session, CURLOPT_PROXY, $config['host']);
		if (!empty($config['port']))
		curl_setopt($this->_session, CURLOPT_PROXYPORT, $config['port']);
		if (isset($config['type']) && !empty($config['type']) && $config['type']=='socks')
		curl_setopt($this->_session, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS4);
	}
	
	public function fetch($method = self::PAGE_JSON, $encoding) {
	
		$data = curl_exec($this->_session);
		
		if($data) {
		
			switch($method) {
				case self::PAGE_JSON: 
					$data = json_decode($data);
					break;
				case self::PAGE_DOM:
					//$data = mb_convert_encoding( $data, 'UTF-8', $encoding);
					$data = new Zend_Dom_Query($data, $encoding);
					break;
				default:
					// do nothing - return regular string
			}
			
		}

		return $data;	
	}
	
	
	protected function fix_encoding($string){
		$src_encoding = mb_detect_encoding($string);
		//var_dump($src_encoding);
		if (strtolower($src_encoding)!='utf-8')
		return iconv($src_encoding, 'UTF-8//TRANSLIT', utf8_decode ($string));
		else
		return utf8_decode($string);
		
	}
	
	
	public function fetchPage($url=null, $encoding=null){
		
		if (!$url)
		throw new Exception("Не указана страница для парсинга", 500);
		if (!$encoding)
		throw new Exception("Не указана кодировка загружаемой страницы", 500);
		
		$this->setUrl($url);
		
		//var_dump($this);
		//die(__FILE__.': '.__LINE__);
		
		/*
		 * // Caching breaks encoding
		$cache = Zend_Cache::factory('Output', 'File',
			array( 'lifetime' => 7200, 'automatic_serialization' => true),
			array( 'cache_dir' => APPLICATION_PATH.'/../cache/' ),
			null,
			null,
			false
		);
		
		if (($this->_DOM = $cache->load(__FUNCTION__))===false){
			$this->_DOM = $this->fetch(Xmltv_Site::PAGE_DOM, $encoding);
			$cache->save($this->_DOM, __FUNCTION__);
		}
		*/
		$this->_DOM = $this->fetch(Xmltv_Site::PAGE_DOM, $encoding);
		$this->_HTML = $this->_DOM->getDocument();
		
	}
	
	
	/**
	 * @return string
	 */
	public function getEncoding () {

		return $this->_encoding;
	}

	/**
	 * @param $encoding  Site encoding
	 */
	public function setEncoding ($encoding) {

		$this->_encoding = $encoding;
	}

	
}