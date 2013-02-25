<?php
class Xmltv_Youtube {
	
    /**
     * 
     * @var Xmltv_Youtube
     */
    private static $_instance;
    
	private $_safeSearch='moderate';
	//private $_debug=false;
	private $_uniqueToken='';
	private $_order='relevance_lang_ru';
	protected $maxResults;
	private $_startIndex=1;
	private $_cacheSubfolder='';
	private $_operator='+';
	private $_language='ru';
	
	/**
	 * HTTP client adatapter
	 * @var Zend_Http_Client_Adapter_Curl
	 */
	protected $adapter;
	
	protected $client;
	
	const YT_404_RESPONSE = "Expected response code 200, got 404";
	const ERR_MISSING_PARAMS = "Пропущен параметр!";
	
	/**
	 * 
	 * Закрываем доступ к функции вне класса. Паттерн Singleton не
	 * допускает вызов этой функции вне класса. При этом в функцию 
	 * можно вписать свой код инициализации. Также можно использовать
	 * деструктор класса. Эти функции работают по прежднему,
	 * только не доступны вне класса
	 * 
	 * @param array $config
	 */
	
	public function __construct($config=array()){
		
	    //
	    if (APPLICATION_ENV=='development') {
	    	//var_dump($config);
	    }
	    
		if (isset($config['safe_search']) && !empty($config['safe_search']))
			$this->_safeSearch = (string)$config['safe_search'];
		
		if (isset($config['unique_token']) && !empty($config['unique_token']))
			$this->_uniqueToken = $config['unique_token'];	
		
		if (isset($config['order']) && !empty($config['order']))
			$this->_order = (string)$config['order'];

		if (isset($config['max_results']) && (int)$config['max_results']!=0)
			$this->maxResults = intval( $config['max_results'] ); 

		if (isset($config['start_index']) && !empty($config['start_index']))
			$this->_startIndex = intval( $config['start_index'] );

		if (isset($config['cache_subfolder']) && !empty($config['cache_subfolder']))
			$this->_cacheSubfolder = '/'.$config['cache_subfolder'];

		if (isset($config['operator']) && !empty($config['operator']))
			$this->_operator = (string)$config['operator'];

		if (isset($config['language']) && !empty($config['language']))
			$this->_language = (string)$config['language'];	
		
		
	    $this->adapter = new Zend_Http_Client_Adapter_Curl();
	    $t = (int)Zend_Registry::get('site_config')->curl->get('timeout');
		if ($t>0){
			$this->adapter->setCurlOption(CURLOPT_TIMEOUT, $t);
		}
		
		$httpClient = new Zend_Http_Client();
		$httpClient->setConfig( array('adapter'=>$this->adapter));
		$this->client  = new Zend_Gdata_YouTube();
		$this->client->setHttpClient($httpClient);
		$this->client->setMajorProtocolVersion(2);
				
	}
	
	/**
	 * http://www.andrey-vasiliev.com/php/prakticheskaya-realizaciya-patterna-singleton-na-php/
     * Закрываем доступ к функции вне класса.
     * Паттерн Singleton не допускает вызов
     * этой функции вне класса
     */
	private function __clone(){
		
	}
	
	/**
	 * Singleton instance
	 * @return Xmltv_Youtube
	 * @deprecated
	 */
	public static function getInstance($config=array()) {
	    
	    if (!self::$_instance) {
	        self::$_instance = new Xmltv_Youtube($config);
	    }
	    return self::$_instance;
	    
	}
	
	/**
	 * Fetch single video by it's Youtube ID
	 * 
	 * @param  string $vid	//Youtube video ID
	 * @throws Zend_Exception
	 */
	public function fetchVideo($yt_id=null){
		
		if (!$yt_id) {
			throw new Zend_Exception("Не указан ID видео для ".__METHOD__, 500);
		}
		
		try {
			$result = $this->client->getVideoEntry($yt_id);	
		} catch (Zend_Gdata_App_Exception $e) {
		    throw new Zend_Exception($e->getMessage(), 404);
		}
		
		if (APPLICATION_ENV=='development'){
			//var_dump($result);
			//die(__FILE__.': '.__LINE__);
		}
		
		if (is_a($result, 'Zend_Gdata_YouTube_VideoEntry')) {
			return $result;
		}
		
		return false;
		
	}
	
	/**
	 * Fetch related videos
	 * 
	 * @param  string $vid	//Youtube video ID
	 * @throws Exception
	 */
	public function fetchRelated($vid=null){
		
		if (!$vid)
			throw new Exception("Не задан параметр поиска видео");
		
		$vids = array();
		try {
			$vids = $this->client->getRelatedVideoFeed($vid);
		} catch (Zend_Gdata_App_Exception $e) {
			return null;
		}
		
		return $vids;
	
	}
	
	/**
	 * 
	 * Fetch videos from YouTube
	 * 
	 * @param  array $data
	 * @param  array $config
	 * @throws Exception
	 */
	public function fetchVideos($query_string=null){
		
		//var_dump(func_get_args());
		//die(__FILE__.': '.__LINE__);
		
		if (!$query_string)
			throw new Zend_Exception("Не задан параметр поиска видео");
		if (is_array($query_string))
		    $query_string = implode($this->_operator, $query_string);
		
		/*
		 * Zend_Gdata_YouTube_VideoQuery
		 */
		$query = $this->client->newVideoQuery();
		$query->setMaxResults($this->maxResults);
		$query->orderBy = $this->_order;
		$query->setSafeSearch($this->_safeSearch);
		$query->setParam( 'lang', $this->_language );
		
		if ($this->_startIndex>1) {
			$query->setStartIndex($this->_startIndex);
		}
		
		$q = trim( $query_string );
		if ((bool)$q===false){
			return false;
		}
		
		$query->setVideoQuery($q);
		
		if (APPLICATION_ENV=='development'){
			//var_dump( $query->getQueryUrl(2));
			//var_dump(urldecode( $query->getQueryUrl(2)));
		}
		
		try {
		    $videos = $this->client->getVideoFeed( $query->getQueryUrl(2) );
		} catch (Zend_Gdata_App_Exception $e) {
		    throw new Zend_Exception($e->getMessage(), $e->getCode(), $e);
		}
		
		return $videos;
			
	}
	
	
	public static function getCatRu($cat_en=''){
		
		//var_dump(func_get_args());
		
		if (empty($cat_en))
			return '';
		
		$cats = array(
			'film'=>'Кино',
			'games'=>'Мультфильмы',
			'education'=>'Образовательные',
			'comedy'=>'Юмористические',
			'tech'=>'Научные',
			'people'=>'Общественные',
			'music'=>'Музыкальные',
			'news'=>'Новости',
			'sports'=>'Спорт',
			'entertainment'=>'Развлекательные',
		);
		
		$tolower = strtolower($cat_en);
		
		if (array_key_exists($tolower, $cats))
			return $cats[$tolower];
		else 
			return $cat_en;
		
	}
	
	/*
	public static function videoAlias($title=null){
		
		if (!$title)
		throw new Zend_Exception("Не указан один или более параметров для ".__METHOD__, 500);
		
		$trim	   = new Zend_Filter_StringTrim(' -');
		$separator  = new Zend_Filter_Word_SeparatorToDash(' ');
		$regex	  = new Zend_Filter_PregReplace(array("match"=>'/["\'.,:;-\?\{\}\[\]\!`\/\(\)]+/', 'replace'=>' '));
		$tolower	= new Zend_Filter_StringToLower();
		$doubledash = new Zend_Filter_PregReplace(array("match"=>'/[-]+/', 'replace'=>'-'));
		//$cyrlatin   = new Zend_Filter_PregReplace(array("match"=>'/[^\p{Latin}\p{Cyrillic}\p{N} -]+/ui', 'replace'=>''));
		
		$result = $tolower->filter( $trim->filter( $doubledash->filter( $separator->filter( $regex->filter($title)))));
		
		//if (preg_match('/[^\p{Latin}\p{Cyrillic}\p{N} -]+/ui', $result))
		//$result = $cyrlatin->filter($result);
		
		return $result;
		
	}
	*/
	
	public static function processLinks($text=null, $title='', $do='convert'){
		
		if (!$text)
			return '';
		
		$entities = new Zend_Filter_HtmlEntities();
		//var_dump(func_get_args());
		preg_match_all('/[w]{3}?\.[\p{L}\p{N}-]{2,128}\.[a-z]{2,3}/', $text, $m);
		//var_dump($m);
		
		foreach ($m as $i) {
			foreach ($i as $link) {
				
				$linkTitle = sprintf('Смотреть видео %s на сайте %s', $entities->filter('"'.$title.'"'), $entities->filter($link));
				$link = $entities->filter( trim( str_replace('http://', '', $link), ' /') );
				//var_dump($do);
				switch ($do) {
					case 'convert':
						$text = str_replace($link, '<a href="http://'.$link.'/" rel="nofollow" title="'.$linkTitle.'" target="_blank">'.$link.'</a>', $text);
						break;
					
					default:
					case 'add':
						$text = str_replace($link, '', $text);
						$text .= '<p><a class="btn" href="http://'.$link.'/" rel="nofollow" title="'.$link.'" target="_blank">'.$linkTitle.'</a></p>';
						//var_dump($text);
						//die(__FILE__.': '.__LINE__);
						break;
				}
			}
		}
		//var_dump($text);
		//die(__FILE__.': '.__LINE__);
		return $text;
		
		
	}
	
	/**
	 * Generate Rtvg ID from Youtube ID
	 *
	 * @param  string $input //Youtube ID
	 * @throws Zend_Exception
	 * @return string
	 */
	public static function genRtvgId($input=null){
	
		if (!$input)
			throw new Zend_Exception(self::ERR_MISSING_PARAMS, 500);
	
		return strrev( str_replace( "%3D", "", urlencode( base64_encode( (string)$input))));
	
	}
	
	/**
	 * Decide RTVG video ID
	 * @param  string $input //Rtvg ID
	 * @throws Zend_Exception
	 * @return string
	 */
	public static function decodeRtvgId( $input=null){
	
		if (!$input)
			throw new Zend_Exception(self::ERR_MISSING_PARAMS, 500);
	
		return base64_decode( strrev($input).'=');
	
	}
	
	/**
	 *
	 * Generate RTVG video alias
	 * @param  string $title
	 * @throws Zend_Exception
	 * @return string
	 */
	public static function videoAlias( $title=null){
	
		if (!$title)
			throw new Zend_Exception( 'Не указан $title для '.__METHOD__, 500);
	
		$separator  = new Zend_Filter_Word_SeparatorToDash(' ');
		$cleanup    = new Zend_Filter_PregReplace( array("match"=>'/[«»"\'.,:;-\?\{\}\[\]\!`\/\(\)#]+/ui', 'replace'=>' '));
		$tolower	= new Zend_Filter_StringToLower();
		$whitespace = new Zend_Filter_PregReplace( array("match"=>'/[\s+|-]+/', 'replace'=>'-'));
		
		$result = $tolower->filter( $whitespace->filter( $separator->filter( $cleanup->filter( $title))));
		
		return $result;
	
	}
	
	/**
	 * @param string $userAgent
	 */
	/*
	public function setUserAgent($userAgent) {
		
	    if ($userAgent) {
			$this->userAgent = $userAgent;
			$this->adapter->setCurlOption(CURLOPT_USERAGENT, $userAgent);
	    }
	    
	}
	*/
	
	public function setProxy($config=array()){
		
	    if (array_key_exists('host', $config)){
            $this->adapter->setCurlOption(CURLOPT_PROXY, $config['host']);
            if (array_key_exists('port', $config)){
                $this->adapter->setCurlOption(CURLOPT_PROXYPORT, $config['port']);
            }
        }
	    
	    
	}
	
	
}