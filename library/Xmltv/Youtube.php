<?php
class Xmltv_Youtube {
	
	private $_safeSearch='moderate';
	//private $_debug=false;
	private $_uniqueToken='';
	private $_order='relevance_lang_ru';
	private $_maxResults=5;
	private $_startIndex=1;
	private $_cacheSubfolder='';
	private $_operator='+';
	private $_language='ru';
	protected $client;
	
	const YT_404_RESPONSE = "Expected response code 200, got 404";
	
	/**
	 * 
	 * Constructor
	 * @param array $config
	 */
	function __construct($config=array()){
		
		if (isset($config['safe_search']) && !empty($config['safe_search']))
			$this->_safeSearch = (string)$config['safe_search'];
		
		//if (isset($config['debug']) && !empty($config['debug']))
		//	$this->_debug = (bool)$config['debug'];

		if (isset($config['unique_token']) && !empty($config['unique_token']))
			$this->_uniqueToken = $config['unique_token'];	
		
		if (isset($config['order']) && !empty($config['order']))
			$this->_order = (string)$config['order'];

		if (isset($config['max_results']) && (int)$config['max_results']!=0)
			$this->_maxResults = intval( $config['max_results'] );

		if (isset($config['start_index']) && !empty($config['start_index']))
			$this->_startIndex = intval( $config['start_index'] );

		if (isset($config['cache_subfolder']) && !empty($config['cache_subfolder']))
			$this->_cacheSubfolder = '/'.$config['cache_subfolder'];

		if (isset($config['operator']) && !empty($config['operator']))
			$this->_operator = (string)$config['operator'];

		if (isset($config['language']) && !empty($config['language']))
			$this->_language = (string)$config['language'];	
		
		try {
			$curl['adapter']      = new Zend_Http_Client_Adapter_Curl();
			$curl['maxredirects'] = 0;
			$t = (int)Zend_Registry::get('site_config')->curl->get('timeout');
			if ($t>0){
				$curl['timeout']=$t;
				$curl['useragent']=@$_SERVER['HTTP_USER_AGENT']; //notice suppressed for phpunit
			}
			$c = new Zend_Http_Client(null, $curl);
			$this->client = new Zend_Gdata_YouTube();
			
		} catch (Exception $e) {
			throw new Zend_Exception( $e->getMessage(), $e->getCode(), $e );
		}
		
		$this->client->setMajorProtocolVersion(2);
				
	}
	
	/**
	 * Fetch single video by it's Yyoutube ID
	 * 
	 * @param  string $vid	//Youtube video ID
	 * @throws Zend_Exception
	 */
	public function fetchVideo($vid=null){
		
		if (!$vid)
			throw new Zend_Exception("Не указан один или более параметров для ".__FUNCTION__, 500);
		
		try {
			$result = $this->client->getVideoEntry($vid);	
		} catch (Exception $e) {
		    return false;
		}
		
		return $result;
		
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
			
		try {
			$result = $this->client->getRelatedVideoFeed($vid);
		} catch (Zend_Gdata_App_Exception $e) {
			throw new Zend_Exception($e->getMessage(), $e->getCode(), $e);
		}
		
		return $result;
	
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
		$query->setMaxResults($this->_maxResults);
		$query->orderBy = $this->_order;
		$query->setSafeSearch($this->_safeSearch);
		$query->setParam( 'lang', $this->_language );
		
		if ($this->_startIndex>1)
			$query->setStartIndex($this->_startIndex);
		
		if (APPLICATION_ENV=='development'){
			//var_dump($query_string);
		}
		
		$q = trim( $query_string );
		if ((bool)$q===false){
		    return false;
		} 
		
		try {
			$query->setVideoQuery($q);
			if (APPLICATION_ENV=='development'){
				//var_dump($query->getQueryUrl(2));
			}
			$videos = $this->client->getVideoFeed($query->getQueryUrl(2));
		} catch (Exception $e) {
			throw new Zend_Exception($e->getMessage(), $e->getCode(), $e);
		}
		
		//var_dump($videos);
		//die(__FILE__.': '.__LINE__);
		
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
	public static function decodeRtvgId($input=null){
	
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
	public static function videoAlias($title=null){
	
		if (!$title)
			throw new Zend_Exception('Не указан $title для '.__METHOD__, 500);
	
		$trim	    = new Zend_Filter_StringTrim(array(' ', '-'));
		$separator  = new Zend_Filter_Word_SeparatorToDash(' ');
		$cleanup    = new Zend_Filter_PregReplace( array("match"=>'/[«»"\'.,:;-\?\{\}\[\]\!`\/\(\)#]+/ui', 'replace'=>' '));
		$tolower	= new Zend_Filter_StringToLower();
		$doubledash = new Zend_Filter_PregReplace( array("match"=>'/[-]+/', 'replace'=>'-'));
		$whitespace = new Zend_Filter_PregReplace( array("match"=>'/\s+/', 'replace'=>' '));
		$result = $tolower->filter( $trim->filter( $whitespace->filter( $doubledash->filter( $separator->filter( $cleanup->filter( $title))))));
		return $result;
	
	}
	
	
	
	
}