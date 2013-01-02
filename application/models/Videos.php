<?php
/**
 * Videos function
 *
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: Videos.php,v 1.7 2013-01-02 16:58:27 developer Exp $
 *
 */
class Xmltv_Model_Videos
{
	
    protected $cache;
	
	const ERR_MISSING_PARAMS="Пропущен енобходимый параметр!";
	
	public function __construct(){
		
	    $this->cache = new Xmltv_Cache();
	}
	
	/**
	 * 
	 * Convert Zend_Gdata_YouTube_VideoEntry data to array
	 * @param Zend_Gdata_YouTube_VideoEntry $entry
	 */
	public function parseYtEntry(Zend_Gdata_YouTube_VideoEntry $entry){
		
		
		if (!self::okToOutput($entry)){
			if (APPLICATION_ENV=='development'){
		    	//var_dump('---Wrong entry: "'.$entry->getVideoTitle().'"');
			}
			return false;
		}
		
		$v = new stdClass();
		$v->title   = $entry->getVideoTitle();
		$v->alias   = Xmltv_Youtube::videoAlias( $v->title );
		$v->desc	= $entry->getVideoDescription()!='' ? $entry->getVideoDescription() : null ;
		$v->yt_id   = $entry->getVideoId();
		$v->rtvg_id = Xmltv_Youtube::genRtvgId( $entry->getVideoId() );
		$v->views   = (int)$entry->getVideoViewCount();
		
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
		
		$string = $entry->getVideoTitle();
		if (self::isPorn($string)) {
			if (APPLICATION_ENV=='development'){
				//var_dump($string);
			}
			return false;
		}
		
		if (!self::isRussian($string)) {
			if (APPLICATION_ENV=='development'){
				//var_dump($string);
			}
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
			'/\s*[^к]анал(\s+|ь)/ui',
			'/^[^к]анал(\s+|ь)/ui',
			'/\sвиагр.*/ui',
			'/\s*зооф.*/ui',
			'/\s*малолет.*/ui',
			'/\s*порн.*/ui',
			'/\s*эрот.+/ui',
			'/\s*секс\s/ui',
			'/\s*лесб.+/ui',
			'/\s*porn.+/ui',
			'/\s*sex\s/ui',
			'/\s*prostitut/ui',
			'/\s*whore/ui',
			'/\s*blowj/ui',
			'/\s*порн(о|уха|графия)?/ui',
			'/\s*seks/ui',
			'/\s*проститу[тц]/ui',
			'/\s*сиськ/ui',
			'/\s*дойк/ui',
			'/\s*бдсм/ui',
			'/\s*xxx/ui',
			'/\s*porn/ui',
		);
		
		foreach ($regex as $r){
			if ( preg_match($r, $string, $m)) {
				if (APPLICATION_ENV=='development'){
					//var_dump($m);
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
	 * @param array  $list
	 * @param string $ch_id
	 * @param string $date
	 * @param int	 $start
	 * @param string $safe
	 */
	public function getRelatedVideos($list, $channel='', $date){
		
		if (!$date){
			$date = Zend_Date::now()->toString("yyyy-MM-dd");
		}
		
		$vc = Zend_Registry::get('site_config')->videos->listing;
		$ytConfig['max_results'] = (int)$vc->get('max_results');
		$ytConfig['order'] = $vc->get('order');
		$ytConfig['start_index'] = (int)$vc->get('start_index')>=1 ? (int)$vc->get('start_index') : 1 ;
		$ytConfig['safe_search'] = $vc->get('safe_search');
		$ytConfig['language']    = 'ru';
		
		if (APPLICATION_ENV=='development'){
			//var_dump($ytConfig);
		}
		
		$e = (bool)Zend_Registry::get('site_config')->cache->youtube->get('enabled');
		foreach ($list as $li){
		    if ($e){
		    	
			    $t = (int)Zend_Registry::get('site_config')->cache->youtube->get('lifetime');
			    $t>0 ? $this->cache->setLifetime((int)$t): $this->cache->setLifetime(86400) ;
			    
			    // Setup and create folder if needed
			    $f = '/Youtube/Related/'.$li->ch_id;
			    if (!is_dir(ROOT_PATH.'/cache'.$f)){
			    	mkdir(ROOT_PATH.'/cache'.$f, 0777, true);
			    }
		    	
			    $hash = Xmltv_Cache::getHash('related_'.$li->hash);
			    if (($result[$li->hash] = $this->cache->load($hash, 'Core', $f))===false) {
		    	    $result[$li->hash] = $this->_fetchYtRelated(new Xmltv_Model_Videos(), $ytConfig, Xmltv_String::strtolower($li->title));
		        	$this->cache->save($result[$li->hash], $hash, 'Core', $f);
		        }
		    } else {
		        $result[$li->hash] = $this->_fetchYtRelated(new Xmltv_Model_Videos(), $ytConfig, Xmltv_String::strtolower( $li->title ));
	        }
	        
		} 
		
		foreach ($result as $hash=>$arr){
		    if (isset($arr[0])){
				$result[$hash]=$arr[0];
		    }
		}
				
		return $result;
		
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
				'start_index'=>$vc->get('start_index'),
				'safe_search'=>$vc->get('safe_search'),
				'language'=>'ru',
		);
	
		if (APPLICATION_ENV=='development'){
			//var_dump($ytConfig);
		}
	
		$result = array();
		
		$e = (bool)Zend_Registry::get('site_config')->cache->youtube->get('enabled');
		if ($e===true){
			$t = (int)Zend_Registry::get('site_config')->cache->youtube->get('lifetime');
			$t>0 ? $this->cache->setLifetime($t): $this->cache->setLifetime(86400) ;
			$f = '/Youtube/SidebarRight';
			$hash = Xmltv_Cache::getHash('related_'.$ch_title);
			if ( ($result = $this->cache->load( $hash, 'Core', $f))===false) {
			    $result = $this->_fetchYtRelated( new Xmltv_Model_Videos(), $ytConfig, $ch_title);
				$this->cache->save( $result, $hash, 'Core', $f);
			}
		} else {
			$result = $this->_fetchYtRelated(new Xmltv_Model_Videos(), $ytConfig, $ch_title);
		}
		return $result;
	
	}
	
	/**
	 * Fetch related Youtube videos
	 *
	 * @param Xmltv_Model_Videos $model
	 * @param unknown_type $li
	 * @param unknown_type $channel
	 * @throws Zend_Exception
	 * @return multitype:unknown
	 */
	private function _fetchYtRelated(Xmltv_Model_Videos $model, $config=array(), $search=''){
			
		if (APPLICATION_ENV=='development'){
			//var_dump($search);
		}
		$result = array();
		try {
			if (Xmltv_String::strlen($search)>3){
				$yt = new Xmltv_Youtube($config);
				$vids = $yt->fetchVideos( preg_replace('/[^\w]+/ui', ' ', $search) );
				if (is_a($vids, 'Zend_Gdata_YouTube_VideoFeed')) {
					foreach ($vids as $v){
						if ( is_object( $i=$model->parseYtEntry($v))){
							$result[]=$i;
						}
					}
				}
			}
		} catch (Exception $e) {
			throw new Zend_Exception($e->getMessage(), $e->getCode());
		}

		//var_dump($result);
		//die(__FILE__.': '.__LINE__);
		
		return $result;
	}
	
}