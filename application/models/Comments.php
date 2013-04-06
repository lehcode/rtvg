<?php
class Xmltv_Model_Comments extends Xmltv_Model_Abstract
{
	private $_table;
	private $_appKey  = '2963750';
	private $_appSecret = 'cQWq66KgFHsKeomOI28q';
	private $_vkEmail = "repin@egeshi.com";
	private $_vkPass  = "majordata";
	
	/**
	 * Rss feed container
	 * @var Zend_Feed_Reader_Feed_Rss;
	 */
	private $_feed;
	
	const CURL_USERAGENT  = 'PHP/5';
	
	public function __construct(){
		
		parent::__construct();
		
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
	 * Get RSS search results for particular query
	 * 
	 * @param  string|array $query
	 * @return Zend_Feed_Reader_Feed_Rss
	 */
	public function getYandexRss($query=''){
		
		if (is_array($query) && count($query)>1) {
			$query = trim( implode('|', $query) );
		} elseif (is_array($query) && count($query)==1){
			$query = trim( $query[0] );
		} elseif (is_string($query)) {
			$query = trim( $query );
		}
		
		$frontendOptions = array('lifetime'=>(86400*7), 'automatic_serialization'=>true );
		$backendOptions  = array(
			'cache_dir'=>ROOT_PATH.'/cache/Feeds/Yandex',
			'file_name_prefix'=>__CLASS__, 
		);
		$feedCache = Zend_Cache::factory( 'Core', 'File', $frontendOptions, $backendOptions );
		
		$query = preg_replace('/[^\p{Common}\p{Cyrillic}\p{Latin}\s\d]/ui', '', $query);
		
		if (APPLICATION_ENV=='development'){
			echo "<b>".__METHOD__."</b><br />";
			Zend_Debug::dump($query);
			//var_dump($feedCache);
			//die(__FILE__.': '.__LINE__);
		}
		
		Zend_Feed_Reader::setCache( $feedCache );
		Zend_Feed_Reader::useHttpConditionalGet();
		
		$q = urlencode($query);
		$url = "http://blogs.yandex.ru/search.rss?text=$q&ft=all";
		
		if (APPLICATION_ENV=='development'){
			//Zend_Debug::dump($url);
			//die(__FILE__.': '.__LINE__);
		}
		
		try {
			$result = Zend_Feed_Reader::import($url);
		} catch (Zend_Feed_Exception $e) {
			throw new Zend_Exception($e->getMessage(), $e->getCode(), $e);
		}
		
		if (APPLICATION_ENV=='development'){
			//Zend_Debug::dump($result);
			//die(__FILE__.': '.__LINE__);
		}
		
		$this->_feed = $result;
		
		return $result;
		
	}
	
	/**
	 * 
	 * Parse Yandex RSS feed
	 * @param Zend_Feed_Reader_Feed_Rss $feed_data
	 * @param Int $min_length
	 */
	public function parseYandexFeed($feed_data=null, $min_length=196){
		
		if (APPLICATION_ENV=='development'){
			//Zend_Debug::dump($min_length);
			//Zend_Debug::dump($this->_feed);
			//die(__FILE__.': '.__LINE__);
		}
		
		if (null !== $this->_feed){
		    $feed_data = $this->_feed;
		}
		
		$result = array();
		$c=0;
		foreach ($feed_data as $item) {
			
		    $content = $feed_data->current()->getContent();
		    
		    // Check if content is not empty
		    if (!empty($content)) {
		        
		        $feedItem = $feed_data->current();
		        
		        $content = strip_tags( $feedItem->getContent() );
		        $content = trim(strip_tags($content, 'em,b,u,strong'));

		        // Проверка на русский язык
		        if (preg_match('/[\p{Cyrillic}]+/ui', $content)){
		        
		        	if (APPLICATION_ENV=='development'){
		        		//Zend_Debug::dump(Xmltv_String::strlen($content));
		        		//die(__FILE__.': '.__LINE__);
		        	}
		        	 
		        	// Проверка на длину описания
		        	if ( Xmltv_String::strlen($content) >= $min_length ) {
		        
		        		if (APPLICATION_ENV=='development'){
		        			//var_dump($content);
		        			//die(__FILE__.': '.__LINE__);
		        		}
		        		
		        		/*
		        		 * Replace trash
		        		 */
		        		$search = array(
		        			'source_title',
		        			'source_desc',
		        			'attach1_title',
		        			'attach1_desc',
		        			'attach1_text',
		        		);
		        		foreach ($search as $string){
		        		    if (Xmltv_String::stristr($content, $string)){
		        		    	$content = Xmltv_String::str_ireplace($string, '', $content);
		        		    }
		        		}
		        		
		        		$regex = array(
		        			'/#[\d\w]+/ui'
		        		);
		        		foreach ($regex as $expr){
		        			if (preg_match($expr, $content)){
		        				$content = preg_replace($expr, '', $content);
		        			}
		        		}
		        		
		        		if (strstr($content, '. ')) {
		        			$ca = explode('.', $content);
		        			foreach ($ca as $k=>$a) {
		        				$ca[$k] = trim( $a );
		        			}
		        			$content = implode('. ', $ca);
		        		} else {
		        			$content = trim( $content );
		        		}
		        		
		        		$content = preg_replace('/\s+/', ' ', $content);
		        		
		        		// Проверка совпадения текста заголовка и сообщения	
		        		$t = trim( preg_replace('/\s+/mui', ' ', strip_tags( $feedItem->getTitle() )));
		        		$expl   = explode(' ', $t);
		        		$chunks = array_chunk($expl, 3);
		        		
		        		foreach ($chunks as $p) {
		        			$impl = implode(' ', $p);
		        			if (Xmltv_String::stristr($content, $impl ));
		        			$match = true;
		        		}
		        		
		        		if ($match===true) {
		        			$intro = $content;
		        		} else {
		        			$intro = ( $t.' '.$content );
		        		}
		        		
		        		$result[$c]['intro'] = strip_tags( html_entity_decode( $intro ));
		        		$result[$c]['src_url']  = $feedItem->getLink();
		        		
		        		try {
		        			$result[$c]['author'] = $this->_extractBlogAuthor( $feedItem->getLink() );
		        		} catch (Zend_Feed_Exception $e) {
		        			throw new Zend_Exception($e->getMessage(), $e->getCode(), $e);
		        		}

		        		if ($feedItem->getDateModified()) {
		        			$result[$c]['created'] = new Zend_Date( $feedItem->getDateCreated());
		        		}
		        		
		        		$c++;
		        	}
		        }
		    }
		}
		
		if (APPLICATION_ENV=='development'){
			//var_dump($result);
			//die(__FILE__.': '.__LINE__);
		}
		
		if (empty($result) || $result === false) {
			return false;
		}
		
		return $result;
		
	}
	
	private function _saveFakeUser($username=null){
	
		if (!$username)
		throw new Zend_Exception("Не указан один или более параметров для ".__FUNCTION__, 500);
		
	}
	
	/**
	 * 
	 * Save channel-related RSS entries to database
	 * 
	 * @param  array $list
	 * @param  int $parent_id //Channel ID
	 * @throws Zend_Exception
	 * @return array
	 */
	public function saveChannelComments(array $list=null, $parent_id=null ){
	
		if (empty($list) || !$parent_id) {
			throw new Zend_Exception( Rtvg_Message::ERR_MISSING_PARAM, 500);
		}

		foreach ( $list as $li ) {
			
		    if (APPLICATION_ENV=='development'){
		    	//var_dump($li);
		    	//die(__FILE__.': '.__LINE__);
		    }
		    
		    $new = $li;
		    $new['created'] = is_a($li['created'], 'Zend_Date') ? $li['created']->toString("YYYY-MM-dd HH:mm:ss") : $li['created'];
		    
		    if (!isset($new['added'])){
		        $new['added'] = is_a($li['added'], 'Zend_Date') ? $li['added']->toString("YYYY-MM-dd HH:mm:ss") : $li['added'];
		    }
		    
		    $new['src_url'] = $li['src_url'];
		    $new['parent_id'] = $parent_id;
		    $new['intro'] = $li['intro'];
		    $new['author'] = $this->_extractBlogAuthor( $new['src_url'] );
		    
		    if (APPLICATION_ENV=='development'){
		    	//var_dump($new);
		    	//die(__FILE__.': '.__LINE__);
		    }
		    
		    $row = $this->channelsCommentsTable->createRow($new);
		    
            if (APPLICATION_ENV=='development'){
            	//var_dump($row);
            	//die(__FILE__.': '.__LINE__);
            }
            
            try {
                $row->save();
            } catch (Exception $e) {
                return false;
            }
            
		    
	        return $row->toArray();
	        
		}
		
	}
	
	/**
	 * Extract blog author from post
	 * 
	 * @param  string $link
	 * @throws Zend_Exception
	 * @return string
	 */
	private function _extractBlogAuthor($link=null){
		
		if ($link && is_string($link)) {
		
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
			    // Generate fake username
				return $this->_genUsername();
			}
		} else {
		    // Generate fake username
		    return $this->_genUsername();
		}
		
	}
	
	private function _genUsername(){
	    
	    $d = Xmltv_Filesystem_Folder::files(ROOT_PATH.'/dicts');
	    $w1 = explode("\n", Xmltv_Filesystem_File::read(ROOT_PATH.'/dicts/'.$d[array_rand( $d )]));
	    $w2 = explode("\n", Xmltv_Filesystem_File::read(ROOT_PATH.'/dicts/'.$d[array_rand( $d )]));
	    $a = $w1[mt_rand(0, count($w1)-1)].$w2[mt_rand(0, count($w2)-1)];
	    $a = preg_replace('/\s/', '', $a );
	    $a = preg_replace('/[\d]+/', '', $a );
	    return $a;
	    
	}
	
	/**
	 * 
	 * @param  string $alias	//Channel or program alias
	 * @param  string $type	 //channel|program
	 * @param  bool   $paginate
	 * @throws Zend_Exception
	 * @return array
	 */
	public function channelComments($id=null, $paginate=false){
	
		if (!$id || !is_numeric($id)) {
			throw new Zend_Exception( Rtvg_Message::ERR_WRONG_PARAM );
		}
		
		$amt = (int)Zend_Registry::get('site_config')->channels->comments->get('amount');
		$select = $this->db->select()
			->from( array('comment'=>$this->channelsCommentsTable->getName()) )
			->where("`parent_id` = '$id'")
			->order("added DESC")
			->limit($amt);
		
		if (APPLICATION_ENV=='development'){
		    Zend_Registry::get('console_log')->log( $select->assemble(), Zend_Log::INFO );
		    //parent::debugSelect($select, __METHOD__);
			//die(__FILE__.': '.__LINE__);
		}
		
		$result = $this->db->fetchAll($select);
		
		if (!count($result)) {
		    return false;
		}
		
		if (APPLICATION_ENV=='development'){
			//var_dump($result);
			//die(__FILE__.': '.__LINE__);
		}
		
		foreach ($result as $k=>$r){
			$result[$k]['published'] = (bool)$r['published'];
			$result[$k]['date_created'] = new Zend_Date( $r['date_created'] );
			$result[$k]['date_added']   = new Zend_Date( $r['date_added'] );
		}
			
		if (APPLICATION_ENV=='development'){
			//var_dump($result);
			//die(__FILE__.': '.__LINE__);
		}
			
		return $result;
		
	}
	
	/*
	public function getVkToken(){
		
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
	*/
	
}