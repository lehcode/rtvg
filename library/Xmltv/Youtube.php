<?php
class Xmltv_Youtube {
	
	private $_safeSearch='moderate';
	private $_debug=false;
	private $_uniqueToken='';
	private $_order='relevance_lang_ru';
	private $_maxResults=5;
	private $_startIndex=1;
	private $_cacheSubfolder='';
	private $_operator='+';
	private $_language='ru';
	
	/**
	 * 
	 * Constructor
	 * @param array $config
	 */
	function __construct($config=array()){
		
		//var_dump($config);
		
		if (isset($config['safe_search']) && !empty($config['safe_search']))
			$this->_safeSearch = (string)$config['safe_search'];
		
		if (isset($config['debug']) && !empty($config['debug']))
			$this->_debug = (bool)$config['debug'];

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
			
		
			
		
				
	}
	
	public function fetchVideo($vid=null){
		
		if (!$vid)
		throw new Zend_Exception("Не указан один или более параметров для ".__FUNCTION__, 500);
		
		/*
		if ((bool)$decode===true) {
			$id = $this->_decodeId($id);
		}
		*/
		
		//var_dump(func_get_args());
		//var_dump($id);
		//die(__FILE__.': '.__LINE__);
		
		$yt = new Zend_Gdata_YouTube();
		$yt->setMajorProtocolVersion(2);
		
		try {
			if (Xmltv_Config::getCaching()){
				$cache = new Xmltv_Cache();
				$hash = $cache->getHash( __FUNCTION__.'_'.$vid);
				if (!$result = $cache->load($hash, 'Function')) {
					$result = $yt->getVideoEntry($vid);
					$cache->save($result, $hash, 'Function');
				}
			} else {
				$result = $yt->getVideoEntry($vid);
			}
		} catch (Exception $e) {
			echo $e->getMessage();
			exit();
		}
		
		return $result;
		//die(__FILE__.': '.__LINE__);
		
	}
	
	public function fetchRelated($vid=null){
		
		if (!$vid) {
			throw new Exception("Не задан параметр поиска видео");
			return false;
		}
		
		//var_dump($vid);
		//die(__FILE__.": ".__LINE__);
			
		$yt = new Zend_Gdata_YouTube();
		$yt->setMajorProtocolVersion(2);
		
		try {
			
			$cache = new Xmltv_Cache();
			$hash = $cache->getHash( __FUNCTION__.'_'.$vid);
			
			if (Xmltv_Config::getCaching()){
				if (!$result = $cache->load($hash, 'Function')) {
					$result = $yt->getRelatedVideoFeed($vid);
					$cache->save($result, $hash, 'Function');
				}
			} else {
				$result = $yt->getRelatedVideoFeed($vid);
			}
		} catch (Exception $e) {
			echo $e->getMessage();
		}
		
		//var_dump($result);
		//die(__FILE__.": ".__LINE__);
		
		return $result;
	
	}
	
	/**
	 * 
	 * Fetch videos from YouTube
	 * @param array $data
	 * @param array $config
	 * @throws Exception
	 */
	public function fetchVideos($data=array(), $html_output=false){
		
		//var_dump(func_get_args());
		//die(__FILE__.': '.__LINE__);
		
		if (count($data)<1)
			throw new Exception("Не задан параметр поиска видео");
		
		$yt = new Zend_Gdata_YouTube();
		$yt->setMajorProtocolVersion(2);
		/*
		 * Zend_Gdata_YouTube_VideoQuery
		 */
		$query = $yt->newVideoQuery();
		$query->setMaxResults($this->_maxResults);
		$query->orderBy = $this->_order;
		//$query->setSafeSearch($this->_safeSearch);
		$query->setParam( 'lang', $this->_language );
		
		if ($this->_startIndex>1)
			$query->setStartIndex($this->_startIndex);
		
		/*
		 * Cleanup query items
		 */
		/*
		$colon  = new Zend_Filter_PregReplace(array( 'match'=>'/[:]/', 'replace'=>'|' ));
		$trim   = new Zend_Filter_StringTrim(' ,."\'`|');
		$quotes = new Zend_Filter_Word_SeparatorToSeparator( '"', '' );
		$qs = '';
		foreach ($data as $k=>$d) {
			$data[$k] = $trim->filter($quotes->filter( $colon->filter( $d )));
			$qs.=$trim->filter($data[$k]).'|';
		}
		$qs = $trim->filter($qs);
		
		if ($this->_debug===true) {
			//var_dump($qs);
		}
		*/
		foreach ($data as $k=>$word) {
			if ( Xmltv_String::strlen($word) <=2 ) {
				unset($data[$k]);
			} elseif ( is_numeric($word) ) {
				unset($data[$k]);
			} else {
				$data[$k] = trim( Xmltv_String::strtolower( $data[$k] ));
			}
		}
		$qs = implode($this->_operator, $data);
		
		$query->setVideoQuery($qs);
		
		if ($this->_debug) {
			var_dump($qs);
			var_dump($query->getQueryUrl());
		}
		
		$cacheSubfolder = 'Youtube'.$this->_cacheSubfolder;
		$cache = new Xmltv_Cache(array('location'=>"/cache/$cacheSubfolder"));
		$hash = $cache->getHash( __FUNCTION__.'_'.md5($qs.$this->_uniqueToken));
		if (Xmltv_Config::getYoutubeCaching()){
			if (!$videos = $cache->load($hash, 'Core', $cacheSubfolder)) {
				$videos = $yt->getVideoFeed($query->getQueryUrl(2));
				$cache->save($videos, $hash, 'Core', $cacheSubfolder);
			}
		} else {
			$videos = $yt->getVideoFeed($query->getQueryUrl(2));
		}
		
		//var_dump(count($videos));
		//die(__FILE__.': '.__LINE__);
		
		
		
		//var_dump(count($videos));
		//var_dump($videos->offsetExists(1));
		//var_dump($videos->offsetGet(1));
		//die(__FILE__.': '.__LINE__);
		$offset=0;
		if (count($videos)) {
			foreach ($videos as $k=>$v) {
				//$offset++;
				//if (@$videos->offsetExists($offset)) {
					//$current = $videos->current();
					//if ($current) {
						//
						$desc = $v->getVideoDescription();
						if (!empty($desc)) {
							
							if (self::isPorn($desc))
							$videos->offsetUnset($offset);
							//if (!self::isRussian($desc))
							//$videos->offsetUnset($offset);
							
						}
						
						$title = $v->getVideoTitle();
						if (!empty($title)) {
							
							if (self::isPorn($title))
							$videos->offsetUnset($offset);
							//if (!self::isRussian($title))
							//$videos->offsetUnset($offset);
							
						}
						
						if (!preg_match('/\p{Cyrillic}+/ui', $title))
						$videos->offsetUnset($offset);
						
						
					//}
					//$offset++;
				//}
				$offset++;
			}
		} else 
		return null;
		
		
		if ((bool)$html_output===true) {
			return $this->getHtml($videos);
		}
		
		return $videos;
			
	}
	
	public static function isPorn($title=''){
		
		if ( preg_match('/анал|порн|эрот|проститут|sex|секс/ui', $title))
		return true;
		
		return false;
		
	}
	
	public function getHtml($input=null){
		
		if (!$input)
			return '';
	
	}
	
	public static function isRussian($desc=''){
		
		if (preg_match('/[\p{Cyrillic}]+/ui', $desc))
			return true;
		
		return false;
	}
	
	public static function videoId($yt_id=''){
		
		if (!$yt_id)
			return'';
			
		return strrev( str_replace( "%3D", "", urlencode( base64_encode( (string)$yt_id))));
	
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
	
	public static function videoAlias($title=null){
		
		if (!$title)
		throw new Zend_Exception("Не указан один или более параметров для ".__METHOD__, 500);
		
		$trim       = new Zend_Filter_StringTrim(' -');
		$separator  = new Zend_Filter_Word_SeparatorToDash(' ');
		$regex      = new Zend_Filter_PregReplace(array("match"=>'/["\'.,:;-\?\{\}\[\]\!`\/\(\)]+/', 'replace'=>' '));
		$tolower    = new Zend_Filter_StringToLower();
		$doubledash = new Zend_Filter_PregReplace(array("match"=>'/[-]+/', 'replace'=>'-'));
		//$cyrlatin   = new Zend_Filter_PregReplace(array("match"=>'/[^\p{Latin}\p{Cyrillic}\p{N} -]+/ui', 'replace'=>''));
		
		$result = $tolower->filter( $trim->filter( $doubledash->filter( $separator->filter( $regex->filter($title)))));
		
		//if (preg_match('/[^\p{Latin}\p{Cyrillic}\p{N} -]+/ui', $result))
		//$result = $cyrlatin->filter($result);
		
		return $result;
		
	}
	
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
	
	
	
}