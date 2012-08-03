<?php
class Xmltv_Youtube {
	
	private $_safeSearch='moderate';
	private $_debug=false;
	private $_uniqueToken='';
	private $_order='relevance_lang_ru';
	private $_maxResults=5;
	private $_startIndex=1;
	
	/**
	 * 
	 * Constructor
	 * @param array $config
	 */
	function __construct($config=array()){
		
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
			
		if ($this->_debug===true) {
			//var_dump($config);
		}
				
	}
	
	/**
	 * 
	 * Fetch videos from YouTube
	 * @param array $data
	 * @param array $config
	 * @throws Exception
	 */
	public function fetchVideos($data=array(), $html_output=false){
		
		if (!count($data))
			throw new Exception("Не задан параметр поиска видео");
		
		$yt = new Zend_Gdata_YouTube();
		$yt->setMajorProtocolVersion(2);
		$query = $yt->newVideoQuery();
		$query->setMaxResults($this->_maxResults);
		$query->setStartIndex($this->_startIndex);
		$query->orderBy = $this->_order;
		$query->setSafeSearch($this->_safeSearch);
		$query->setParam('lang', 'ru');
		
		/*
		 * Cleanup query items
		 */
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
		
		$query->setVideoQuery($qs);
		
		$cacheSubfolder = 'Youtube';
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
		
		//die(__FILE__.': '.__LINE__);
		
		if ((bool)$html_output===true) {
			return $this->getHtml($videos);
		}
		
		//var_dump(count($videos));
		
		if (count($videos)) {
			foreach ($videos as $v) {
				$desc = @$v->getVideoDescription();
				if (!empty($desc)) {
					if (!self::descIsRussian($desc))
					unset($v);
				}
			}
			
			//var_dump($videos);
			//die(__FILE__.': '.__LINE__);
			return $videos;
		} else 
		return null;
			
	}
	
	public function getHtml($input=null){
		
		if (!$input)
			return '';
	
	}
	
	public static function descIsRussian($desc=''){
		
		if (!preg_match('/\p{Cyrillic}+/ui', $desc))
		return false;
		
		return true;
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