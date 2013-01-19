<?php
/**
 * Videos function
 *
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: Videos.php,v 1.9 2013-01-19 10:11:13 developer Exp $
 *
 */
class Xmltv_Model_Videos
{
	
	protected $cache;
	protected static $nocache = false;
	
	const ERR_MISSING_PARAMS="Пропущен необходимый параметр!";
	const ERR_WRONG_ENTRY="---Wrong entry: ";
	const ERR_PORN_ENTRY="---Porn entry: ";
	const ERR_NON_CYRILLIC="---Non-cyrillic entry: ";
	
	public function __construct($config=array()){
		
	    if (isset($config['nocache']) && is_bool($config['nocache'])){
	        self::$nocache = $config['nocache'];
	    }
	    
	    $this->cache = new Xmltv_Cache();
	}
	
	/**
	 * 
	 * Convert Zend_Gdata_YouTube_VideoEntry data to array
	 * @param Zend_Gdata_YouTube_VideoEntry $entry
	 */
	public function parseYtEntry(Zend_Gdata_YouTube_VideoEntry $entry){
		
		
		if (!self::okToOutput($entry)){
			return false;
		}
		
		$v = new stdClass();
		$v->title    = $entry->getVideoTitle();
		$v->alias    = Xmltv_Youtube::videoAlias( $v->title );
		$v->desc	 = $entry->getVideoDescription()!='' ? $entry->getVideoDescription() : null ;
		$v->yt_id    = $entry->getVideoId();
		$v->rtvg_id  = Xmltv_Youtube::genRtvgId( $entry->getVideoId() );
		$v->views    = (int)$entry->getVideoViewCount();
		$v->category = $entry->getVideoCategory();
		
		$config = Zend_Registry::get('site_config')->videos->sidebar->right;
		$thumbs = $entry->getVideoThumbnails();
		$i=0;
		$v->thumbs = array();
		foreach($thumbs as $th) {
			//var_dump($th);
			if ( $th['width']==$config->get('thumb_width', 120)) {
				if (preg_match('/.+[\d]+\.jpg$/', $th['url'])) {
					$v->thumbs[$i] = new stdClass();
					$v->thumbs[$i]->time   = new Zend_Date($th['time'], 'HH:mm:ss.S');
					$v->thumbs[$i]->height = (int)$th['height'];
					$v->thumbs[$i]->width  = (int)$th['width'];
					$v->thumbs[$i]->url	= $th['url'];
					$i++;
					
				}
			}
		}
		//die(__FILE__.': '.__LINE__);
		
		$d = new Zend_Date($entry->getPublished(), Zend_Date::ISO_8601);
		$v->published = $d->addHour(3);
		$d = new Zend_Date($entry->getVideoDuration(), Zend_Date::TIMESTAMP);
		$v->duration = $d;
		
		return $v;
		
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param Zend_Gdata_YouTube_VideoEntry $entry
	 * @return bool
	 */
	public static function okToOutput( Zend_Gdata_YouTube_VideoEntry $entry){
		
		if (self::isPorn( $entry->getVideoTitle())) {
			return false;
		}
		
		
		if (!self::isRussian( $entry->getVideoTitle())) {
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
			'/^.*\s*[^к]анал(\s+|ь)/ui',
			'/^[^к]анал(\s+|ь)/ui',
			'/^.*\s*виагр.+/ui',
			'/^.*\s*изнасил.+/ui',
			'/^.*\s*совра(тил|щен)/ui',
			'/^.*\s*зооф.*/ui',
			'/^.*\s*порн.*/ui',
			'/^.*\s*эрот.+/ui',
			'/^.*\s*секс\s/ui',
			'/^.*\s*лесб.+/ui',
			'/^.*\s*porn.+/ui',
			'/^.*\s*sex\s/ui',
			'/^.*\s*prostitut/ui',
			'/^.*\s*whore/ui',
			'/^.*\s*blowj/ui',
			'/^.*\s*порн(о|уха|графия)?/ui',
			'/^.*\s*seks/ui',
			'/^.*\s*проститу[тц]/ui',
			'/^.*\s*сиськ/ui',
			'/^.*\s*дойк/ui',
			'/^.*\s*бдсм/ui',
			'/^.*\s*xxx/ui',
			'/^.*\s*porn/ui',
			'/^.*\s*еб(а|л).+\s/ui',
			'/^.*\s*пизд.+\s/ui',
		);
		
		foreach ($regex as $r){
			if ( preg_match($r, $string, $m)) {
				if (APPLICATION_ENV=='development' || isset($_GET['RTVG_PROFILE'])){
					Zend_Debug::dump( self::ERR_PORN_ENTRY.$m);
				}
				return true;
			}
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
		
		if (APPLICATION_ENV=='development' || isset($_GET['RTVG_PROFILE'])) {
			//Zend_Debug::dump($string);
			//Zend_Debug::dump(preg_match('/\p{Cyrillic}+/mui', $string));
			//Zend_Debug::dump(preg_match('/[^\p{Cyrillic}]+/ui', $string));
		}
		
		
		if (preg_match('/\p{Cyrillic}+/mui', $string)) {
			return true;
		} else {
			if (APPLICATION_ENV=='development' || isset($_GET['RTVG_PROFILE'])) {
				Zend_Debug::dump( self::ERR_NON_CYRILLIC.$string);
			}
			return false;
		}
		
		
		
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
	public static function programToQuery($title=null){
		//var_dump($title);
		//die(__FILE__.': '.__LINE__);
		/*
		$reault=array();
		if (strstr($title, '|')){
			//die(__FILE__.': '.__LINE__);
			$parts = explode('|', $title);
			foreach ($parts as $pk=>$w){
				$t = explode(' ', trim($w) );
				foreach ($t as $k=>$ww) {
					$t[$k] = Xmltv_String::strtolower( trim($ww));
					if ( Xmltv_String::strlen( $t[$k] ) <= 3 || is_numeric($t[$k]) ) {
						unset($t[$k]);
					} else {
						$parts[$pk][$k] = preg_replace('/[^\w]+/uim', ' ', $t[$k]);
					}					
				}
			}
			$result = implode('|', $parts);
			//var_dump($result);
			//die(__FILE__.': '.__LINE__);
			return $result;
			
		} elseif (strstr($title, '.')) {
			die(__FILE__.': '.__LINE__);
			$parts = explode('. ', $title);
			foreach ($parts as $pk=>$w){
				$parts[$pk] = Xmltv_String::strtolower( trim($w));
				if ( Xmltv_String::strlen( $parts[$pk] ) > 3 && !is_numeric($parts[$pk]) )
					$q[] = trim(preg_replace('/[^\w]+/uim', ' ', $parts[$pk]));
				$parts[$pk] = implode(' ', $q);
			}
			return trim(implode(' ', $parts));
			
		} else {
			return trim(preg_replace('/[^\w]+/mui', ' ', $title));
		}
		*/
		
	}
	
	/**
	 * 
	 * Fetch Youtube videos depending on channel and program title
	 * @param string $channel
	 * @param string $program
	 * @throws Zend_Exception
	 * @return Zend_Gdata_YouTube_VideoFeed
	 */
	public function fetchYt($string=null, $config=array()){
		
		$yt = new Xmltv_Youtube($config);
		$s = self::programToQuery($string);
		return $yt->fetchVideos($s); 
		
	}
	
	/**
	 * Related videos
	 * 
	 * @param  array     $list
	 * @param  string    $channel
	 * @param  Zend_Date $date
	 * @param  bool      $video_cache
	 * @return array
	 */
	public function getRelatedVideos($list, $channel='', Zend_Date $date, $video_cache=false){
		
		$vc = Zend_Registry::get('site_config')->videos->listing;
		$ytConfig['max_results'] = (int)$vc->get('max_results');
		$ytConfig['order'] = $vc->get('order');
		$ytConfig['start_index'] = (int)$vc->get('start_index')>=1 ? (int)$vc->get('start_index') : 1 ;
		$ytConfig['safe_search'] = $vc->get('safe_search');
		$ytConfig['language']	= 'ru';
		
		if ( APPLICATION_ENV=='development' || isset($_GET['RTVG_PROFILE'])){
			//var_dump($ytConfig);
			//die(__FILE__.': '.__LINE__);
		}
		
		$e = (bool)Zend_Registry::get('site_config')->cache->youtube->get('enabled');
		$result = array();
		if ($list){
			foreach ($list as $li){
			    if ($li->fetch_video===true){
					if ($this->cache->enabled && $video_cache===false) {
						
						$t = (int)Zend_Registry::get('site_config')->cache->youtube->get('lifetime');
						$t>0 ? $this->cache->setLifetime((int)$t): $this->cache->setLifetime(86400) ;
						
						// Setup and create folder if needed
						$f = '/Youtube/Related/'.$li->ch_id;
						if (!is_dir(ROOT_PATH.'/cache'.$f)){
							mkdir(ROOT_PATH.'/cache'.$f, 0777, true);
						}
						
						$hash = Xmltv_Cache::getHash('related_'.$li->hash);
						if (($result[$li->hash] = $this->cache->load($hash, 'Core', $f))===false) {
							$result[$li->hash] = $this->_fetchYtRelated( Xmltv_String::strtolower($li->title), $ytConfig);
							$this->cache->save($result[$li->hash], $hash, 'Core', $f);
						}
					} else {
						$result[$li->hash] = $this->_fetchYtRelated( Xmltv_String::strtolower( $li->title ), $ytConfig);
					}
			    }
				
			} 
			
			if ($result){
				foreach ($result as $hash=>$arr){
					if (isset($arr[0])){
						$result[$hash]=$arr[0];
					}
				}
						
				return $result;
			}
			
		} else {
			return array();
		}
		
		
	}
	
	/**
	 *
	 * Related videos
	 *
	 * @param string $ch_title
	 * @param int $start
	 * @param string $safe
	 */
	public function getSidebarVideos($ch_title=null){
	
		$vc  = Zend_Registry::get('site_config')->videos->sidebar->right;
		$max = (int)Zend_Registry::get('site_config')->videos->sidebar->right->get('max_results');
		$ytConfig = array(
			'order'=>$vc->get('order'),
			'max_results'=>(int)$vc->get('max_results'),
			'start_index'=>(int)$vc->get('start_index'),
			'safe_search'=>$vc->get('safe_search'),
			'language'=>'ru',
		);
	
		if ( APPLICATION_ENV=='development' || isset($_GET['RTVG_PROFILE'])){
			//var_dump($ytConfig);
			//die(__FILE__.': '.__LINE__);
		}
	
		$result = array();
		
		$e = (bool)Zend_Registry::get('site_config')->cache->youtube->get('enabled');
	    if ($this->cache->enabled && ( self::$nocache !== true)){
	    	$t = (int)Zend_Registry::get('site_config')->cache->youtube->get('lifetime');
	    	$t>0 ? $this->cache->setLifetime($t): $this->cache->setLifetime(86400) ;
	    	$f = '/Youtube/SidebarRight';
	    	$hash = Xmltv_Cache::getHash('related_'.$ch_title);
	    	if ( ($result = $this->cache->load( $hash, 'Core', $f))===false) {
	    		$result = $this->_fetchYtRelated( $ch_title, $ytConfig);
	    		$this->cache->save( $result, $hash, 'Core', $f);
	    	}
	    } else {
	    	$result = $this->_fetchYtRelated( $ch_title, $ytConfig);
	    }
		
		
		return $result;
	
	}
	
	/**
	 * Fetch related Youtube videos
	 * 
	 * @param  string $search
	 * @param  array $config
	 * @throws Zend_Exception
	 * @return array
	 * @final
	 */
	private function _fetchYtRelated($search='', $config=array()){
			
		$result = array();
		$yt = new Xmltv_Youtube($config);
		//$yt->setUserAgent( $userAgent );
		
		if ( (bool)Zend_Registry::get('site_config')->proxy->get('active')) {
			$yt->setProxy(array(
					'host'=>Zend_Registry::get('site_config')->proxy->get('host'),
					'port'=>Zend_Registry::get('site_config')->proxy->get('port'),
			));
		}
		
		$vids = $yt->fetchVideos( preg_replace('/[^\p{Cyrillic}\p{Latin}\d\s]+/ui', ' ', $search) );
		
		if (is_a($vids, 'Zend_Gdata_YouTube_VideoFeed')) {
			foreach ($vids as $v){
				 
				$i=$this->parseYtEntry($v);
				if ( is_object($i))
					$result[]=$i;
		
			}
		}

		return $result;
		
	}
	
	/**
	 *
	 * @param  stdClass $object
	 * @return array
	 */
	public static function objectToArray($object=null){
		 
		if ($object){
			$props['title']     = $object->title;
			$props['alias']     = $object->alias;
			$props['desc']      = $object->desc;
			$props['yt_id']     = $object->yt_id;
			$props['rtvg_id']   = $object->rtvg_id;
			$props['views']     = (int)$object->views;
			$props['thumbs']    = Zend_Json::encode($object->thumbs);
			$props['published'] = $object->published->toString("MM-dd-YYYY");
			$props['duration']  = $object->duration->toString("HH:mm:ss");
			$props['delete_at'] = Zend_Date::now()->addSecond(86400)->toString("MM-dd-YYYY HH:mm:ss");
			$props['category']  = $object->category;
			return $props;
		}
	
	}
	
}