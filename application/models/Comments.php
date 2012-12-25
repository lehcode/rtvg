<?php
class Xmltv_Model_Comments
{
	private $_table;
	private $_appKey  = '2963750';
	private $_appSecret = 'cQWq66KgFHsKeomOI28q';
	private $_vkEmail = "repin@egeshi.com";
	private $_vkPass  = "majordata";
	
	const CURL_USERAGENT  = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.19 (KHTML, like Gecko) Chrome/0.2.153.1 Safari/525.19';
	const ERR_MISSING_PARAM = "Пропущен необходимый параметр!";
	
	public function __construct(){
		
		$siteConfig   = Zend_Registry::get('site_config')->site;
		$this->debug  = (bool)$siteConfig->get('debug', false);
		$this->_table = new Xmltv_Model_DbTable_Comments();
		
	}
	
	/*
	public function getVkComments( $query=null ){
		
		if ( is_array($query) )
		$query = implode( ' ', $query );
		
		if ( Xmltv_Config::getDebug()===true )
		var_dump( $query );
		
		//$frontendOptions = array( 'lifetime'=>7200, 'automatic_serialization'=>true );
	    //$backendOptions  = array('cache_dir'=>ROOT_PATH.'/cache/Comments' );
	    //$cache           = Zend_Cache::factory( 'Core', 'File', $frontendOptions, $backendOptions );
	    
	    //Zend_Feed_Reader::setCache( $cache );
	    //Zend_Feed_Reader::useHttpConditionalGet();
	    //$result = Zend_Feed_Reader::import('http://blogs.yandex.ru/search.rss?text='.urlencode($query).'&format=atom');
	    
		//$appKey="2369516";
		//$appSecret="ZCDx7PBLRzyEDKuGztoF";
		$accessToken = $this->_vkAuth();
		
		var_dump($accessToken);
		$videos = $this->_vkApiRequest('video.search', $accessToken, array("q"=>$query));
		var_dump($videos);
		die(__FILE__.': '.__LINE__);
		
		
	}
	*/
	public function vkApiRequest($api_method=null, $token=null, $method_params=array()){
		
		if (!$api_method || !$token)
		throw new Zend_Exception("Не указан один или более параметров для ".__METHOD__, 500);
		
		$vk = new Xmltv_Vk_Api($this->_appKey, $this->_appSecret);
		//$query = $vk->getAppBalance();
		//$vkResponse = $vk->getUserSettings( 1172531 );
		$vkResponse = $vk->videoSearch( array("коммандо") );
		//var_dump($vkResponse);
		
		/*
		$vkParams = array();
		foreach ($method_params as $k=>$param)
		$vkParams[] = "$k=$param";
		
		sort($vkParams);
		var_dump($vkParams);
		
		$json = new Zend_Json();
		
	    //$imp = implode( '', $vkParams );
	    $query = array();
	    foreach ($vkParams as $param){
	    	$query[]=$param;
	    }
	    $query[]='v=3.0';
	    sort( $query );
	    $method_params = implode( '&', $query );
	    $sig = md5( implode( '', $query ).$this->_appSecret );
	    
	    //var_dump( $query );
	    //var_dump( $sig );
	    //var_dump( md5( "6492api_id=4method=getFriendsv=3.0secret" ) );
	    //var_dump( md5( "6492api_id=4method=getFriendsv=3.0secret" ) );
	    //die( __FILE__.": ".__LINE__ );
	    //$url = "https://api.vk.com/api.php?v=3.0&api_id={$this->_appKey}&method={$api_method}&format=json&{$method_params}&access_token={$token}&sig={$sig}";
	    $url = "http://api.vk.com/api.php?v=3.0&api_id={$this->_appKey}&format=json&method={$api_method}&{$method_params}&sig={$sig}";
		var_dump( $url );
	    $result = $json->decode( $this->_curl($url ), Zend_Json::TYPE_OBJECT);
	    
	    var_dump( $result );
	    //var_dump( $result->error->request_params );
	    
		die( __FILE__.": ".__LINE__ );
	    
		$client = new Zend_Http_Client($url, $adapterConfig);
		$response = $client->request('POST');
		
		if ($response->isError()) {
			throw new Exception($response->asString());
			//echo "Error transmitting data.\n";
			//echo "Server reply was: " . $response->getStatus() . " " . $response->getMessage() . "\n";
		}
		
		//var_dump($response);
		
		$json = new Zend_Json();
		$result = json_decode($response->getBody(), Zend_Json::TYPE_OBJECT);
		
		//var_dump($result);
		
		return $result;
		*/
	}
	
	private function _curl($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);
		curl_setopt($ch, CURLOPT_COOKIEFILE, ROOT_PATH."/vk.txt");
		curl_setopt($ch, CURLOPT_COOKIEJAR, ROOT_PATH."/vk.txt");
		$body = curl_exec($ch);
		curl_close($ch);
		//decode and return the response
		return $body;
	}
	
	/**
	 * Авторязация приложения vkontakte
	 * 
	 * @return string //VK.com token
	 */
	public function vkAuth(){
		
		//if (!$app_key || !$app_secret)
		//throw new Zend_Exception("Не указан один или более параметров для ".__METHOD__, 500);
		
		$adapterConfig = array(
	        'adapter'=>'Zend_Http_Client_Adapter_Curl',
	        'curloptions'=>array(
				CURLOPT_FOLLOWLOCATION=>false,
				CURLOPT_CONNECTTIMEOUT=>15,
				CURLOPT_COOKIEFILE=>ROOT_PATH.'/cookies/vk.jar',
				CURLOPT_COOKIEJAR=>ROOT_PATH.'/cookies/vk.jar',
				//CURLOPT_USERAGENT=>self::CURL_USERAGENT,
				//CURLOPT_HEADER=>true,
				//CURLINFO_HEADER_OUT=>true,
			)
	    );
		
	    $this->_vkEmail = urlencode($this->_vkEmail);
	    //$url = "http://api.vk.com/oauth/authorize?client_id={$this->_appKey}&redirect_uri=".urlencode("http://oauth.vk.com/blank.html")."&scope=16&display=wap&response_type=token";
	    //$url = "http://vkontakte.ru/login.php?app={$this->_appKey}&layout=popup&type=browser&settings=16";
	    
	    //$url = "https://api.vk.com/oauth/token?grant_type=client_credentials&client_id={$this->_appKey}&client_secret={$this->_appSecret}&username={$this->_vkEmail}&password={$this->_vkPass}&offline=1";
	    //$url = "https://oauth.vk.com/access_token?client_id={$this->_appKey}&client_secret={$this->_appSecret}&grant_type=client_credentials&offline=1'";
	    $url = "http://oauth.vk.com/authorize?client_id=2965316&scope=offline&redirect_uri=http://oauth.vk.com/blank.html&display=wap&response_type=token";
	    //var_dump($url);
	    $client = new Zend_Http_Client($url, $adapterConfig);
		$cache  = new Xmltv_Cache( array('location'=>'/cache/Comments') );
		$hash = $cache->getHash( __FUNCTION__ );
	    try {
			if ( Xmltv_Config::getCaching() ){
				if (!$cache->load($hash, 'Core', 'Comments')){
					$response = $client->request('GET');
					$cache->save( $response, $hash, 'Core', 'Comments' );
				} else
				$response = $client->request('GET');
			}
	    } catch (Exception $e) {
	    	echo $e->getMessage();
	    }
	    	
		if ($response->isError()) {
			throw new Exception("Ошибка передачи данных.\n"."Ответ сервера vk: " . $response->getStatus() . " " . $response->getMessage() . "\n");
		} else {
			
		}
		
		var_dump($response->getBody());
		die(__FILE__.': '.__LINE__);
		
		$response = json_decode( $response->getBody() );
		
		//var_dump($response);
		//die(__FILE__.': '.__LINE__);
		
		return $response->access_token;
		
		//$dom  = new Zend_Dom_Query( $response->getBody(), "UTF-8" );
		//$html = $dom->getDocument();
		
		//var_dump($html);
		/*
		die(__FILE__.': '.__LINE__);
		
		$adapterConfig = array(
	        'adapter'=>'Zend_Http_Client_Adapter_Curl',
	        'curloptions'=>array(
				CURLOPT_FOLLOWLOCATION=>true,
				CURLOPT_AUTOREFERER=>true,
				CURLOPT_CONNECTTIMEOUT=>15,
				CURLOPT_UNRESTRICTED_AUTH=>true,
				CURLOPT_COOKIEFILE=>ROOT_PATH.'/cookies/vk.jar',
				CURLOPT_COOKIEJAR=>ROOT_PATH.'/cookies/vk.jar',
				CURLOPT_POSTFIELDS=>urlencode( implode('&', $formFields) ),
				CURLOPT_USERAGENT=>self::CURL_USERAGENT,
			)
	    );
		
	    
		$client   = new Zend_Http_Client($formParams['action'], $adapterConfig);
		$response = $client->request("POST");
		if ($response->isError()) {
			throw new Exception( "Не могу отправить данные для авторизации на vk.com" );
		}
		$responseHtml =$response->getBody();
		Xmltv_Filesystem_File::write( ROOT_PATH.'/log/vk-resp.html', $responseHtml);
		var_dump($responseHtml);
		die(__FILE__.': '.__LINE__);
		*/
	}
	
	
	/**
	 * Get RSS search results for particular query
	 * @param string|array $query
	 */
	public function getYandexRss($query=''){
		
		if (is_array($query))
			$query = implode('|', $query);
		
		$regex = new Zend_Filter_PregReplace( array( 'match'=>'/[\.:\(\)]+/', 'replace'=>'' ) );
		$query = $regex->filter($query);
		
		$frontendOptions = array( 'lifetime'=>86400, 'automatic_serialization'=>true );
		$backendOptions  = array('cache_dir'=>ROOT_PATH.'/cache/Comments' );
	   	$feedCache       = Zend_Cache::factory( 'Core', 'File', $frontendOptions, $backendOptions );
	    
	   	Zend_Feed_Reader::setCache( $feedCache );
	    Zend_Feed_Reader::useHttpConditionalGet();
	    
	    $url = 'http://blogs.yandex.ru/search.rss?text='.urlencode($query).'&ft=all';
	    if (APPLICATION_ENV=='development'){
	    	var_dump($url);
	    }
	    $result = Zend_Feed_Reader::import($url);
	    return $result;
		
	}
	
	/**
	 * 
	 * Parse Yandex RSS feed
	 * @param Zend_Feed_Reader_Feed_Rss $feed_data
	 * @param Int $min_length
	 */
	public function parseYandexFeed(Zend_Feed_Reader_Feed_Rss $feed_data, $min_length=128){
		
		$comments = array();
		//$trim = new Zend_Filter_StringTrim(' -——');
		$c=0;
		foreach ($feed_data as $feed_item) {
			
			$content = strip_tags( $feed_item->getContent() );
			$content = trim(strip_tags($content, 'em,b,u,strong'));
			
			//var_dump($content);
			//die(__FILE__.': '.__LINE__);
			
			if (preg_match('/[\p{Cyrillic}]+/ui', $content)){
				
				//$content = trim( preg_replace('/\s+/', ' ', $content) );
				if ( Xmltv_String::strlen($content) >= $min_length ) {
					if (strstr($content, '. ')) {
						$ca = explode('.', $content);
						foreach ($ca as $k=>$a) {
							$ca[$k] = trim( $a );
						}
						$content = implode('. ', $ca);
					} else {
						$content = trim( $content );
					}
					//var_dump($content);
					/*
					 * Проверка совпадения текста заголовка и сообщения
					 */ 
					$t = trim( preg_replace('/\s+/mui', ' ', strip_tags( $feed_item->getTitle() )));
					$expl = explode(' ', $t);
					$chunks = array_chunk($expl, 3);
					foreach ($chunks as $p) {
						$impl = implode(' ', $p);
						if (Xmltv_String::stristr($content, $impl ));
							$match = true;
					}
					if ($match===true)
						$intro = $content;
					else
						$intro = $t.' '.$content;
					
					$comments[$c]['intro'] = html_entity_decode( $intro );
					$comments[$c]['link']  = $feed_item->getLink();
					
					if ($feed_item->getAuthor())
					$comments[$c]['author'] = $feed_item->getAuthor();
	
					if ($feed_item->getDateModified())
					$comments[$c]['date'] = new Zend_Date($feed_item->getDateModified());
					
				}
			}
			$c++;
		}
		
		//var_dump($comments);
		
		if (!empty($comments)) {
			sort($comments);
			return $comments;
		}
		
		return array();
		
	}
	
	private function _saveFakeUser($username=null){
	
		if (!$username)
		throw new Zend_Exception("Не указан один или более параметров для ".__FUNCTION__, 500);
		
	}
	
	/**
	 * 
	 * Save comments to database
	 * @param array $list
	 * @param int $parent_id
	 * @param string $parent_type
	 * @throws Zend_Exception
	 */
	public function saveComments($list=array(), $parent_id=null, $parent_type='channel'){
	
		if (empty($list) || !$parent_id)
		throw new Zend_Exception("Не указан один или более параметров для ".__FUNCTION__, 500);
				
		foreach ( $list as $list_item ) {
			
			$now = new Zend_Date();
			$row = $this->_table->createRow(array(), Zend_Db_Table::DEFAULT_CLASS);
			
			if (is_object( $list_item )) {
				$t = array();
				foreach ($list_item as $k=>$i) {
					if ($k=='date')
					$row->date_created = $i->toString("yyyy-MM-dd hh:mm:ss");
					else
					$t[$k]=$i;
				}
			} else 
			$t = $list_item;
			
			$row->date_created  = $t['date']->toString("yyyy-MM-dd hh:mm:ss");
			$row->date_added    = $now->toString("yyyy-MM-dd hh:mm:ss");
			$row->src_url       = $t['link'];
			$row->parent_type   = $parent_type=='channel' ? 'c' : 'p';
			$row->parent_id     = $parent_id;
			$row->author        = $this->_extractBlogAuthor($row->src_url);
			$row->intro         = $t['intro'];
			
			$dupes = $this->_table->fetchAll("`intro` LIKE '%$row->intro%' OR `date_created`='".$t['date']->toString("yyyy-MM-dd hh:mm:ss")."'");
			if (!count($dupes)) {
				try {
					$row->save();
				} catch (Exception $e) {
					//
				}
			}
						
		}
		//die( __FILE__.': '.__LINE__ );
		
	}
	
	private function _extractBlogAuthor($link=null){
		
		if (!$link)
		throw new Zend_Exception("Не указан один или более параметров для ".__FUNCTION__, 500);
		
		$trim = new Zend_Filter_StringTrim(' /\:.');
		
		if (stristr($link, 'blogs.mail.ru/mail/mail/')) {
			preg_match('/http:\/\/blogs\.mail\.ru\/mail\/(.+)\/[A-F0-9]+/u', $link, $m);
			if (!empty($m[1]))
				return $trim->filter($m[1]);
		} elseif (stristr($link, 'blogs.mail.ru/bk/')) {
			preg_match('/\/\/blogs\.mail\.ru\/bk\/(.+)\/[A-F0-9]+/u', $link, $m);
			if (!empty($m[1]))
				return $trim->filter($m[1]);
		} elseif (stristr($link, 'blogs.mail.ru/mail/')) {
			preg_match('/\/\/blogs\.mail\.ru\/mail\/(.+)\/[A-F0-9]+/u', $link, $m);
			if (!empty($m[1]))
				return $trim->filter($m[1]);
		} elseif (stristr($link, 'my.mail.ru/community/')) {
			preg_match('/\/\/my\.mail\.ru\/community\/(.+)\/[A-F0-9]+/u', $link, $m);
			if (!empty($m[1]))
				return $trim->filter($m[1]);
		} elseif (stristr($link, '.livejournal.com/')) {
			preg_match('/\/\/(.+)\.livejournal\.com/u', $link, $m);
			if (!empty($m[1]))
				return $trim->filter($m[1]);
		} elseif (stristr($link, 'my.mail.ru/community/')) {
			preg_match('/\/\/my\.mail\.ru\/community\/(.+)\/[A-Z]+\.html/u', $link, $m);
			if (!empty($m[1]))
				return $trim->filter($m[1]);
		} elseif (stristr($link, 'twitter.com/')) {
			preg_match('/\/\/twitter\.com\/(.+)\/statuses/u', $link, $m);
			if (!empty($m[1]))
				return $trim->filter($m[1]);
		} elseif (stristr($link, 'diary.ru/')) {
			preg_match('/\/\/(.+)\.diary\.ru\//u', $link, $m);
			if (!empty($m[1]))
				return $trim->filter($m[1]);
		}  elseif (stristr($link, 'comon.ru/user')) {
			preg_match('/comon\.ru\/user\/(.+)\/blog/u', $link, $m);
			if (!empty($m[1]))
				return $trim->filter($m[1]);
		}  elseif (stristr($link, '.blogspot.com/')) {
			preg_match('/\/\/(.+)\.blogspot\.com\//u', $link, $m);
			if (!empty($m[1]))
				return $trim->filter($m[1]);
		} else {
			$d = Xmltv_Filesystem_Folder::files(ROOT_PATH.'/dicts');
			$w1 = explode("\n", Xmltv_Filesystem_File::read(ROOT_PATH.'/dicts/'.$d[array_rand( $d )]));
			$w2 = explode("\n", Xmltv_Filesystem_File::read(ROOT_PATH.'/dicts/'.$d[array_rand( $d )]));
			$a = $w1[rand(0, count($w1)-1)].$w2[rand(0, count($w2)-1)];
			$a = preg_replace('/\s/', '', $a );
			$a = preg_replace('/[\d]+/', '', $a );
			return $a;
		}
		
	}
	
	/**
	 * 
	 * @param  string $alias    //Channel or program alias
	 * @param  string $type     //channel|program
	 * @param  bool   $paginate
	 * @throws Zend_Exception
	 * @return array
	 */
	public function channelComments($alias=null, $type='channel', $paginate=false){
	
		if (!$alias)
			throw new Zend_Exception( self::ERR_MISSING_PARAM, 500 );
		
		$amt = (int)Zend_Registry::get('site_config')->channels->comments->get('amount');
		$type = $type=='channel' ? 'c' : 'p';
		$select = new Zend_Db_Table_Select($this->_table);
		$select->where("`parent_id` LIKE '$alias'")
			->where("`parent_type`='$type'")
			->order("date_added DESC")
			->limit($amt);
		
		//var_dump($select->assemble());
		//die(__FILE__.': '.__LINE__);
		
		$result = $this->_table->fetchAll($select);
		foreach ($result as $r){
		    $r->published = (bool)$r->published;
		}
		$result = $result->toArray();
		
		return $result;
		
	}
	
	public function getToken(){
		
		$json = new Zend_Json();
		return $json->decode( $this->_curl("https://oauth.vk.com/access_token?client_id={$this->_appKey}&client_secret={$this->_appSecret}&grant_type=client_credentials") );
		
	}
	
	
	public function vkVideoSearch( $query = null, $token=null){
		
		if (is_array( $query ))
		implode( ' ', $query );//throw new Exception(__FUNCTION__.': Параметр $query должен быть строкой');
		
		if ( !$query )
		throw new Exception(__FUNCTION__.': Нужно указать access_token.');
		
		return $this->_curl("https://api.vk.com/method/video.search?q=".rawurlencode($query)."&access_token={$token}");
		
	}
	
}