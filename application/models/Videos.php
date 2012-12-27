<?php
class Xmltv_Model_Videos
{
	public $debug = false;
	
	const ERR_MISSING_PARAMS="Пропущен енобходимый параметр!";
	
	public function __construct(){
		$siteConfig = Zend_Registry::get('site_config')->site;
		$this->debug = (bool)$siteConfig->get('debug', false);
	}
	
	/**
	 * 
	 * Convert Zend_Gdata_YouTube_VideoEntry data to array
	 * @param Zend_Gdata_YouTube_VideoEntry $entry
	 */
	public function parseYtEntry(Zend_Gdata_YouTube_VideoEntry $entry){
		
		$ok = self::okToOutput($entry);
		//var_dump($ok);
		if ($ok) {
			$v = new stdClass();
			$v->title   = $entry->getVideoTitle();
			$v->alias   = Xmltv_Youtube::videoAlias( $v->title );
			$v->desc    = $entry->getVideoDescription()!='' ? $entry->getVideoDescription() : null ;
			$v->yt_id   = $entry->getVideoId();
			$v->rtvg_id = Xmltv_Youtube::genRtvgId( $entry->getVideoId() );
			$v->views   = (int)$entry->getVideoViewCount();
			//$v->tags  = $entry->getVideoTags();
			
			$config = Zend_Registry::get('site_config')->videos->sidebar->right;
			$thumbs = $entry->getVideoThumbnails();
			$i=0;
			$v->thumbs = array();
			foreach($thumbs as $th) {
				//var_dump($th);
				if ( $th['width']==$config->get('thumb_width', 120)) {
					if (preg_match('/.+[\d]+\.jpg$/', $th['url'])) {
						$v->thumbs[$i]->time   = new Zend_Date($th['time'], 'HH:mm:ss.S');
						$v->thumbs[$i]->height = (int)$th['height'];
						$v->thumbs[$i]->width  = (int)$th['width'];
						$v->thumbs[$i]->url    = $th['url'];
						$i++;
					}
				}
			}
			
			$d = new Zend_Date($entry->getPublished(), Zend_Date::ISO_8601);
			$v->published = $d->addHour(3);
			$d = new Zend_Date($entry->getVideoDuration(), Zend_Date::TIMESTAMP);
			$v->duration = $d;
			
			return $v;
		}
		
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param Zend_Gdata_YouTube_VideoEntry $entry
	 * @return bool
	 */
	public static function okToOutput( Zend_Gdata_YouTube_VideoEntry $entry){
		
	    /*
		$string = $entry->getVideoDescription();
		//var_dump($string);
		if (!empty($string)) {
			if (self::isPorn($string)) {
				//var_dump($string);
				//die(__FILE__.': '.__LINE__);
				return false;
			}
		}
		*/
	    
		$string = $entry->getVideoTitle();
		//var_dump($string);
		if (!empty($string)) {
			if (self::isPorn($string)) {
				//var_dump($string);
				//die(__FILE__.': '.__LINE__);
				return false;
			}
			if (!self::isRussian($string)) {
				//var_dump($string);
				//die(__FILE__.': '.__LINE__);
				return false;
			}
		}
		
		//var_dump($string);
		if (!preg_match('/^[\p{L}\d\s\+\.,:;"\'\?\!-]+$/ui', $string)){
			return false;
		}
		
		return true;
		
	}
	
	/**
	 * 
	 * Check if text contains porn keywords
	 * @param  string $string
	 * @return bool
	 */
	public static function isPorn($string=null){
		
	    $regex = array(
	    	'\sанал.*',
	    	'\sвиагр.*',
	    	'\sпорн.*',
	    	'\sэрот.+',
	    	'\sпроститу.+',
	    	'\sсекс\s',
	    	'\sлесб.+',
	    	'\sporn.+',
	    	'\ssex\s',
	    	'\sprostitut',
	    	'\swhore',
	    	'\sblowj',
	    );
		if ( preg_match('/('.implode('|', $regex).')/mui', $string, $m)) {
			//var_dump($m);
			return true;
		}
		
		return false;
		
	}
	
	/**
	 * 
	 * Check if text contains cyrillic symbols
	 * @param  string $string
	 * @return bool
	 */
	public static function isRussian($string=null){
		
		if (preg_match('/[\p{Cyrillic}]+/mui', $string))
			return true;
		
		return false;
	}
	
	/**
	 * 
	 * Normalize tag
	 * @param string $input
	 * @throws Zend_Exception
	 * @deprecated 
	 */
	public function convertTag($input=null){
		
		if (!$input)
			throw new Zend_Exception("Не указан один или более параметров для ".__FUNCTION__, 500);
		
		return Xmltv_String::str_ireplace('-', ' ', $input);
		
	}
	
	public function parseYtFeed(){
		
		
		
	}
	
	/**
	 * 
	 * Create Youtube query part from channel title
	 * @param string $title
	 */
	public function channelToQuery($title=null){
		
		//$channelTitle = $this->escape(trim($this->channel['title']));
		//var_dump($title);
		if (strstr($title, ' ')) {
			$t = explode(' ', $title);
			$q = array();
			foreach ($t as $k=>$w) {
				$t[$k] = Xmltv_String::strtolower($this->escape(trim($w)));
				if ( Xmltv_String::strlen( $t[$k] ) > 2 )
					$q[] = Xmltv_String::strtolower( $t[$k] );
			}
			return implode(' ', $q);
		} else {
			return Xmltv_String::strtolower($title);
		}
		
	}
	
	/**
	 * 
	 * Create Youtube query part from program title
	 * @param string $title
	 */
	public function programToQuery($title=null){
		
		if (!$title)
			return false;
		
		$t = explode(' ', $title );
		$words = array();
		$q = array();
		foreach ($t as $k=>$w) {
			$t[$k] = Xmltv_String::strtolower($this->escape(trim($w)));
			if ( Xmltv_String::strlen( $t[$k] ) > 3 && !is_numeric($t[$k]) )
				$q[] = $t[$k];
		}
		return implode(' ', $q);
		
	}
	
	/**
	 * 
	 * Fetch Youtube videos depending on channel and program title
	 * @param string $channel
	 * @param string $program
	 * @throws Zend_Exception
	 * @return Zend_Gdata_YouTube_VideoFeed
	 */
	public function fetchYt($channel=null, $program=null, $config=array()){
		
	    if(APPLICATION_ENV=='development'){
	    	var_dump(func_get_args());
	    }
	    
		if (preg_match('/[\p{Latin}]+/', $channel) && !preg_match('/[\p{Cyrillic}]+/', $channel)) {
			$config['language']='en';
		} else {
		    $config['language']='ru';
		}
	
		$yt = new Xmltv_Youtube($config);
		$query = array($channel, $program);
		/**
		 * @var Zend_Gdata_YouTube_VideoFeed
		 */
		$result = $yt->fetchVideos($query);
		//var_dump($result);
		//die(__FILE__.': '.__LINE__);
		return $result; 
		
	}
	
}