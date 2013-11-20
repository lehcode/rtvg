<?php
/**
 * Videos function
 *
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: Videos.php,v 1.28 2013-04-11 05:21:11 developer Exp $
 *
 */
class Xmltv_Model_Videos extends Xmltv_Model_Abstract
{
	
    public function __construct($config=array()){
		
		parent::__construct();
		
	}
	
	/**
	 * 
	 * Convert Zend_Gdata_YouTube_VideoEntry data to array
	 * @param Zend_Gdata_YouTube_VideoEntry|array $entry
	 */
	public function parseYtEntry($entry, $img_width=120){
		
        if (is_a($entry, 'Zend_Gdata_YouTube_VideoEntry')) {
		    
		    $v = array();
			$v['rtvg_id']  = Xmltv_Youtube::genRtvgId( $entry->getVideoId() );
			$v['yt_id']	   = $entry->getVideoId();
			$v['title']	   = $entry->getVideoTitle();
			
			try {
			    $v['alias'] = Xmltv_Youtube::videoAlias( $v['title'] );
			} catch (Zend_Exception $e) {
			    return false;
			}
			
			$v['desc']	   = $entry->getVideoDescription()!='' ? $entry->getVideoDescription() : null ;
			$v['views']	   = (int)$entry->getVideoViewCount();
			$v['category'] = $entry->getVideoCategory();
			$v['category_ru'] = $this->getCatRu($v['category']);
            
			date_default_timezone_set("Etc/UTC");
			$v['published'] = new Zend_Date($entry->getPublished(), Zend_Date::ISO_8601);
            $v['duration']  = new Zend_Date($entry->getVideoDuration(), Zend_Date::TIMESTAMP);
            date_default_timezone_set("Europe/Moscow");
            
            $thumbs = $entry->getVideoThumbnails();
			$i=0;
			$v['thumbs'] = array();
			foreach($thumbs as $th) {
				if ( $th['width'] == (int)$img_width ) {
					if ( stristr($th['url'], '.jpg') ) {
						$v['thumbs'][$i]['time']  = new Zend_Date($th['time'], 'HH:mm:ss.S');
						$v['thumbs'][$i]['height'] = (int)$th['height'];
						$v['thumbs'][$i]['width']  = (int)$th['width'];
						$v['thumbs'][$i]['url']	= $th['url'];
						$i++;
						
					}
				}
			}
		} elseif (is_array($entry)){
			
			$v['published'] = is_a($entry['published'], 'Zend_Date') ? $entry['published'] : new Zend_Date($entry['published'], 'yyyy-MM-dd');
			$v['duration']  = is_a($entry['published'], 'Zend_Date') ? $entry['duration'] : new Zend_Date($entry['duration'], 'HH:mm:ss');
			$v['thumbs']	= is_array($entry['thumbs']) ? $entry['thumbs'] : Zend_Json::decode($entry['thumbs']);
			
		} else {
			throw new Zend_Exception("Error parsing YT Entry. " . get_class($entry));
		}
		
		return $v;
		
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param Zend_Gdata_YouTube_VideoEntry $entry
	 * @return bool
	 */
	public static function okToOutput( Zend_Gdata_YouTube_VideoEntry $entry){
		
		//if (self::isPorn( $entry->getVideoTitle())) {
		//	return false;
		//}
		
		
		//if (!self::isRussian( $entry->getVideoTitle())) {
		//	return false;
		//}
		
		//if (self::isHack( $entry->getVideoTitle())) {
		//	return false;
		//}
		
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
			'/^.*\s*еб(а|л|у).+\s/ui',
			'/^.*\s*пизд.+\s/ui',
		);
		
		foreach ($regex as $r){
			if ( preg_match($r, $string, $m)) {
				$profile = (bool)Zend_Registry::get('site_config')->profile;
				if ($profile){
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
		
		if (preg_match('/\p{Cyrillic}+/mui', $string)) {
			return true;
		} else {
			$profile = (bool)Zend_Registry::get('site_config')->profile;
			   if ($profile){
				Zend_Debug::dump( self::ERR_NON_CYRILLIC.$string);
			}
			return false;
		}
		
	}
	
	/**
	 * Check if title conyains hacking terms
	 * 
	 * @param  string $title
	 * @return boolean
	 */
	public static function isHack($title=null){
		
	    if (Xmltv_String::stristr($title, ' взлом ') || 
	    	Xmltv_String::stristr($title, ' хак ') ||
	    	Xmltv_String::stristr($title, ' накрутка ') ||
	    	Xmltv_String::stristr($title, ' кряк ')
	    ){
	    	return true;
	    } else {
	    	if (APPLICATION_ENV=='development'){
	    		Zend_Debug::dump( Rtvg_Message::ERR_IS_HACK.$title);
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
	
	
	/**
	 * 
	 * Create Youtube query part from channel title
	 * @param string $title
	 */
	public function channelToQuery($title=null){
		
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
	 * @param  array	 $list
	 * @param  string	$channel
	 * @param  Zend_Date $date
	 * @param  bool	  $video_cache
	 * @return array
	 */
	public function getRelatedVideos($list, $channel=array(), Zend_Date $date){
		
		$vc = Zend_Registry::get('site_config')->videos->listing;
		$ytConfig['max_results'] = (int)$vc->get('max_results');
		$ytConfig['order'] = $vc->get('order');
		$ytConfig['start_index'] = (int)$vc->get('start_index')>=1 ? (int)$vc->get('start_index') : 1 ;
		$ytConfig['safe_search'] = $vc->get('safe_search');
		$ytConfig['language']	= 'ru';
		
		$result = array();
		if ($list){
			foreach ($list as $li){
				
				if ($li['fetch_video']===true){
				    
					$e = (bool)Zend_Registry::get('site_config')->cache->youtube->get('enabled');
					
					if ($e===true) {
						$t = (int)Zend_Registry::get('site_config')->cache->youtube->get('lifetime');
						$t>0 ? $this->cache->setLifetime((int)$t): $this->cache->setLifetime(86400) ;
						
                        // Setup and create folder if needed
						$f = '/Youtube/Related/'.$li['id'];
						if (!is_dir(APPLICATION_PATH.'/../cache'.$f)){
							mkdir(APPLICATION_PATH.'/../cache'.$f, 0777, true);
						}
						
						$hash = Rtvg_Cache::getHash('related_'.$li['hash']);
						if (($result[$li['hash']] = $this->cache->load($hash, 'Core', $f))===false) {
							$result[$li['hash']] = $this->ytSearch( Xmltv_String::strtolower($li['title']), $ytConfig);
							$this->cache->save($result[$li['hash']], $hash, 'Core', $f);
						}
					} else {
						$result[$li['hash']] = $this->ytSearch( Xmltv_String::strtolower( $li['title'] ), $ytConfig);
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
	 * Channel-related videos for sidebar from database cache
	 *
	 * @param string $channel
	 */
	public function dbCacheSidebarVideos($channel){
		
		$max = (int)Zend_Registry::get('site_config')->videos->sidebar->right->get('max_results');
		$select = $this->db->select()
			->from( array('VID'=>$this->vcacheSidebarTable->getName()), array(
				'rtvg_id',
				'yt_id',
				'channel',
				'title',
				'alias',
				'desc',
				'views',
				'published',
				'duration',
				'category',
				'thumbs',
			))
			->where( "`VID`.`channel`= ".(int)$channel['id'])
			->limit( $max);
		
		$result = $this->db->fetchAll( $select, null, Zend_Db::FETCH_ASSOC);
		
		if (count($result)){
			foreach($result as $k=>$v){
				$result[$k]['published'] = new Zend_Date( $v['published'], 'YYYY-MM-dd HH:mm:ss');
				$result[$k]['duration']  = new Zend_Date( $v['duration'], 'HH:mm:ss');
				$result[$k]['thumbs']	= Zend_Json::decode( $v['thumbs']);
			}
			return $result;
		}
		
		return false;
	
	}
	
	public function dbCacheVideoRelatedVideos($yt_id=null, $amt=5){
	    
	    if (!$yt_id){
	        throw new Zend_Exception( Rtvg_Message::ERR_WRONG_PARAM, 500);
        }
        
		$select = $this->db->select()
			->from( array('rel'=>$this->vcacheRelatedTable->getName()), array(
				'rtvg_id',
				'yt_id',
				'title',
				'alias',
				'desc',
				'views',
				'duration',
				'category',
				'thumbs',
				'published',
			))
			->where("`yt_parent`='$yt_id'")
			->limit($amt);
		
		$result = $this->db->fetchAll($select, null, Zend_Db::FETCH_ASSOC);
		
		if (count($result)) {
		    foreach ($result as $k=>$v){
		        
		        $result[$k]['published'] = new Zend_Date( $v['published'], 'YYYY-MM-dd HH:mm:ss');
			    $result[$k]['duration']  = new Zend_Date( $v['duration'], 'HH:mm:ss');
			    $result[$k]['thumbs']    = Zend_Json::decode($v['thumbs']);
		    }
		    
		    return $result;
		}
		
		return false;
		
	}
	
	/**
	 * Fetch videos related for today's listing from Youtube
	 * 
	 * @param  array     $list
	 * @param  string    $channel_title
	 * @param  Zend_Date $date
	 * @return array|boolean
	 */
	public function ytListingRelatedVideos( $list=array() ){
		
		$conf = Zend_Registry::get('site_config')->videos->listing;
		$ytConfig['max_results'] = 1;
		$ytConfig['order'] = $conf->get('order');
		$ytConfig['safe_search'] = $conf->get('safe_search');
		$ytConfig['language']	= 'ru';
		
        $result = array();
		if (count($list)){
		    foreach ($list as $li){
                $progTitle = Xmltv_String::strtolower($li['title']);
				if (($ytParsed = $this->ytSearch( $progTitle, $ytConfig))!==false){
				    if (!empty($ytParsed) && $ytParsed!==false){
						$result[$li['hash']] = $ytParsed[0];
				    }
				}
			}
        } else {
			return array();
		}
        
        return $result;
		
	}
    
    public function channelRelatedVideos($channel=null, $max=5){
        
        if (!$channel){
            throw new Zend_Exception("Channel is required");
        }
        
		$ytConfig['max_results'] = $max;
		$ytConfig['order'] = 'published';
		$ytConfig['safe_search'] = 'moderate';
		$ytConfig['language'] = 'ru';
        $channel = str_replace('-', ' ', $channel);
		
		$result = array();
		if (($ytParsed = $this->ytSearch( $channel, $ytConfig))!==false){
            if (!empty($ytParsed) && $ytParsed!==false){
                $result[] = $ytParsed[0];
            }
        }
        
        return $result;
        
    }
	
	/**
	 * Save listing-related video to database
	 * 
	 * @param  array $video
	 * @throws Zend_Exception
	 */
	private function _storeListingVideo($video=array()){
	    
	    if (empty($video) && !is_array($video)){
	        throw new Zend_Exception( Rtvg_Message::ERR_WRONG_PARAM, 500);
	    }
	    
		try {
			$this->vcacheListingsTable->store($video);
		} catch (Zend_Db_Table_Exception $e) {
			throw new Zend_Exception($e->getMessage(), $e->getCode(), $e);
		}
		
		return true;
	    
	}

	public function ytVideoRelatedVideos($yt_id=null, $file_cache=false){
		
		if (!$yt_id){
			throw new Zend_Exception( Rtvg_Message::ERR_MISSING_PARAM);
		}
        
	}
	
	
	/**
	 * Fetch sidebar videos from Youtube
	 * 
	 * @param  array $channel
	 * @param  bool  $file_cache
	 * @return Zend_Gdata_YouTube_VideoFeed|false
	 */
	public function ytSidebarVideos(array $channel=null, array $yt_config=null) {
		
		if (empty($channel)) {
			throw new Zend_Exception( Rtvg_Message::ERR_MISSING_PARAM);
		}
		
		$result = $this->ytSearch( $channel['title'], $yt_config);
		
		if (empty($result)){
			return false;
		} else {
		    $videos = array();
		    $c=0;
		    foreach($result as $k=>$v){
				
				if ($v!==false){
		            $videos[$c] = $v;
		        	$videos[$c]['published'] = new Zend_Date($v['duration'], 'YYYY_MM-dd HH:mm:ss');
		        	$videos[$c]['duration']  = new Zend_Date($v['duration'], 'HH:mm:ss');
		        	$videos[$c]['channel']   = (int)$channel['id'];
		        	$c++;
		        }
	    	}
	    	
	    	return $videos;
		}
		
		
		
	}
	
	/**
	 * Search youtube for videos
	 * 
	 * @param  string $search
	 * @param  array $config
	 * @throws Zend_Exception
	 * @return array
	 * @final
	 */
	public function ytSearch($search='', $config=array()){
			
		$result = array();
		$yt = new Xmltv_Youtube($config);
		
		$img_width = isset($config['img_width']) && !empty($config['img_width']) ? (int)$config['img_width'] : 120 ;
		$search = preg_replace('/[^\p{Cyrillic}\p{Latin}\d\s]+/ui', ' ', $search);
		$search = preg_replace('/\s+/ui', ' ', $search);
		
        $vids = $yt->fetchVideos( $search );
        
        var_dump($vids);
        die(__FILE__ . ': ' . __LINE__);
        
		if (is_a($vids, 'Zend_Gdata_YouTube_VideoFeed')) {
			$c=0;
			foreach ($vids as $v){
			    $r = $this->parseYtEntry($v, $img_width);
			    if ($r!==false){
				    $result[$c]=$r;
					$c++;
			    }
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
			$props['title']	 = $object->title;
			$props['alias']	 = $object->alias;
			$props['desc']	  = $object->desc;
			$props['yt_id']	 = $object->yt_id;
			$props['rtvg_id']   = $object->rtvg_id;
			$props['views']	 = (int)$object->views;
			$props['thumbs']	= Zend_Json::encode($object->thumbs);
			if (preg_match('/^\d{4}-\d{2}-\d{2}\s\d{2}-\d{2}-\d{2}$/', $object->published)) {
				$props['published'] = new Zend_Date($props->published, 'YYYY-MM-dd HH:mm:ss');
			} else {
				if (is_a($object->published, 'Zend_Date')){
					$props['published'] = $object->published->toString("MM-dd-YYYY");
				} else {
					throw new Zend_Exception(parent::ERR_WRONG_FORMAT.' published');
				}
			}
			
			$props['duration']  = $object->duration->toString("HH:mm:ss");
			$props['delete_at'] = Zend_Date::now()->addSecond(86400)->toString("MM-dd-YYYY HH:mm:ss");
			$props['category']  = $object->category;
			return $props;
		}
	
	}
	
	/**
	 * Get Russian title for Youtube video category
	 * 
	 * @param  string $cat_en
	 * @return string|Ambigous <string>|unknown
	 */
	public function getCatRu($cat_en=''){
	
		if (empty($cat_en)){
			throw new Zend_Exception('Category not set');
        }
	
		$select = $this->db->select()
            ->from(array('REF'=>$this->ytCategoriesTable->getName()), null)
            ->join(array('BCCAT'=>$this->bcCategoriesTable->getName()), "`REF`.`bc_cat_id` = `BCCAT`.`id`", array(
                'bc_cat_id'=>'id',
                'bc_cat_title'=>'title',
                'bc_cat_alias'=>'alias',
                'bc_cat_single'=>'title_single',
            ))
            ->join(array('CHCAT'=>$this->channelsCategoriesTable->getName()), "REF.ch_cat_id = CHCAT.id", array(
                'channel_cat_id'=>'id',
                'channel_cat_title'=>'title',
                'channel_cat_alias'=>'alias',
            ))
            ->join(array('CONTCAT'=>$this->contentCategoriesTable->getName()), "REF.content_cat_id = CONTCAT.id", array(
                'content_cat_id'=>'id',
                'content_cat_title'=>'title',
                'content_cat_alias'=>'alias',
            ))
            ->where("REF.title_en = ".$this->db->quote(strtolower($cat_en)));
        ;
        
        $result = $this->db->fetchRow($select);
        
        $result['bc_cat_id'] = (int)$result['bc_cat_id'];
        $result['channel_cat_id'] = (int)$result['channel_cat_id'];
        $result['content_cat_id'] = (int)$result['content_cat_id'];
        
        return $result;
	}
    
    /**
	 * Videos for right sidebar
	 *
	 * @param  array $channel
	 * @return array
	 */
	public function sidebarVideos($channel=null, $amt=5){
	
        if (!$channel){
            throw new Zend_Exception("Channel not defined");
        }
        
        $vConf = Zend_Registry::get('site_config')->videos->sidebar->right;
        if (!$amt){
            $amt = (int)$vConf->get('max_results');
        }
        
        $ytConfig = array(
            'order'=>$vConf->get('order'),
            'max_results'=>(int)$amt,
            'start_index'=>(int)$vConf->get('start_index'),
            'safe_search'=>$vConf->get('safe_search'),
            'language'=>'ru',
		);
		
		$videos = array();
		$ytSearch = 'канал '.Xmltv_String::strtolower($channel['title']);
		
		if ($this->cache->enabled) {			 
			$t = (int)Zend_Registry::get( 'site_config' )->cache->youtube->sidebar->lifetime;
            (APPLICATION_ENV!='production') ? $this->cache->setLifetime(100) : $this->cache->setLifetime($t);
			$f = '/Youtube/SidebarRight';
			$hash = Rtvg_Cache::getHash( 'related_'.$channel['title'].'_u'.time());
            if (($videos = $this->cache->load( $hash, 'Core', $f))===false) {
                $videos = $this->ytSearch( $ytSearch, $ytConfig);
                $this->cache->save( $videos, $hash, 'Core', $f );
            }
		} else {
			$videos = $this->ytSearch( $ytSearch, $ytConfig);
		}
        
        return $videos;
		 
	}
	
}