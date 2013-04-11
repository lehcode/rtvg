<?php
class Xmltv_Parser_Curl extends Xmltv_Parser_StringParser
{
	/**
	 * @var string
	 */
	protected $_session;
	/**
	 * @var string
	 */
	protected $_userAgent='Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.19 (KHTML, like Gecko) Chrome/0.2.153.1 Safari/525.19';
	/**
	 * @var int
	 */
	const PAGE_JSON = 1;
	/**
	 * @var int
	 */
	const PAGE_DOM  = 2;
	/**
	 * @var int
	 */
	const PAGE_HTML  = 3;
	
	public function __construct($url=null) {
			
		$this->_session = curl_init(); 
		curl_setopt($this->_session, CURLOPT_HEADER, false); 
	    curl_setopt($this->_session, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($this->_session, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($this->_session, CURLOPT_AUTOREFERER, false);
		
		if (isset($url) && !empty($url)) {
			curl_setopt($this->_session, CURLOPT_URL, $url);
		}
		$this->_userAgent = $_SERVER['HTTP_USER_AGENT'];
		curl_setopt($this->_session, CURLOPT_USERAGENT, $this->_userAgent);
		
	}
	
	public function __destruct() {
		curl_close($this->_session);
	}
	
	public function downloadFile( $url=null, $file=null ){
		
		if (!$url || !$file)
			return false;

		$this->setUrl( $url );
		
		$dir = str_replace(basename($file), '', $file);
		if (!is_dir($dir)) {
			mkdir($dir, 0775);
		}
		
		try {
			$data = curl_exec($this->_session);
			if (!(file_put_contents( $file, $data ))){
				throw new Zend_Exception("Cannot write file!");
			} else {
				return true;
			}
		} catch (Exception $e) {
			throw new Zend_Exception("Cannot download file!");
		}
		
	}
	
	/**
	 * 
	 * Set custom CURL option
	 * @param string $option // CURL option name
	 * @param mixed $value
	 */
	public function setOption($option=null, $value=null){
		
		if (!$option || !$value)
			throw new Zend_Exception( Rtvg_Message::ERR_MISSING_PARAM );
		
		$option = strtoupper($option);
		curl_setopt($this->_session, $option, $value);
		
	}
	
	/**
	 * 
	 * Set target URL
	 * @param string $url
	 */
	public function setUrl( $url ) {
		curl_setopt($this->_session, CURLOPT_URL, $url);
	}
	
	/**
	 * 
	 * Full path to file where to save cookies
	 * @param string $path
	 */
	public function setCookiePath( $file='' ){
		curl_setopt($this->_session, CURLOPT_COOKIEJAR, $file);
		curl_setopt($this->_session, CURLOPT_COOKIEFILE, $file);
	}
	
	/**
	 * 
	 * Set CURL referer header
	 * @param unknown_type $ref
	 */
	public function setReferrer($ref=''){
		curl_setopt($this->_session, CURLOPT_REFERER, $ref);
	}
	
	/**
	 * 
	 * Enable tracking
	 */
	public function trackCurl(){
		curl_setopt($this->_session, CURLINFO_HEADER_OUT, true);
	}
	
	/**
	 * 
	 * Set POST request variables
	 * @param array $post_vars
	 */
	public function setPostVars( $post_vars=array() ){
		$vars = array();
		foreach ($post_vars as $k=>$var) {
			$vars[]="$k:".urlencode($var);
		}
		$vars = implode('&', $vars);
 		curl_setopt($this->_session, CURLOPT_POSTFIELDS , $vars);
	}
	
	/**
	 * 
	 * Process POST request
	 * @return mixed
	 */
	public function post(){
		
		curl_setopt($this->_session, CURLOPT_POST , true);
		return $this->fetch();
		
	}
	
	/**
	 * 
	 * Proxy properties
	 * @param string $host
	 * @param int $port
	 * @param string $user
	 * @param string $pass
	 * @param string $socks
	 */
	public function setProxy($host='', $port=null, $user='', $pass='', $socks=false){
		
		curl_setopt($this->_session, CURLOPT_PROXY, $host);
		curl_setopt($this->_session, CURLOPT_PROXYPORT, $port);
		if ($user!='') {
			curl_setopt($this->_session, CURLOPT_PROXYUSERPWD, $user.':'.$pass);
		}
		if ($socks===true) {
			curl_setopt($this->_session, CURLOPT_PROXYTYPE, "CURLPROXY_SOCKS5");
		} else {
			curl_setopt($this->_session, CURLOPT_PROXYTYPE, "CURLPROXY_HTTP");
		}
			
		//curl_setopt($this->_session, CURLOPT_HTTPPROXYTUNNEL, true);
	}
	
	/**
	 * 
	 * Change user agent from default one
	 * @param string $agent
	 */
	public function setUserAgent($agent=null){
		if (!$agent){
		    $a = $this->_userAgent;
		} else {
			$a = $agent;
			$this->_userAgent = $a;
		}
		
		curl_setopt($this->_session, CURLOPT_USERAGENT, $a);
	}
	
	/**
	 * 
	 * Process request and return 
	 * @param int $method
	 */
	public function fetch($method=self::PAGE_DOM) {
		
		
		$data = curl_exec($this->_session);
		if($data) {
			switch($method) {
				case self::PAGE_JSON: 
					$data = json_decode($data);
					break;
				case self::PAGE_DOM:
					$data = new Zend_Dom_Query($data);
					break;
				case self::PAGE_HTML:
					return $data;
					break;
				default: break;
			}
			
		}
		return $data;
			
	}
	
	/**
	 * 
	 * Track headers
	 * @param bool $value
	 */
	public function displayHeaders($val=false){
		curl_setopt($this->_session, CURLOPT_HEADER, $val); 
	}
	
	/**
	 * @return string
	 */
	public function getUserAgent() {
		return $this->_userAgent;
	}

	
}