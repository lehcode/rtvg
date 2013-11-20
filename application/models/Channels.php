<?php
/**
 * Frontend channels model
 *
 * @author     Antony Repin <egeshisolutions@gmail.com>
 * @version    $Id: Channels.php,v 1.28 2013-04-11 05:21:11 developer Exp $
 */
class Xmltv_Model_Channels extends Xmltv_Model_Abstract
{
    
    /**
     *
     * @var Xmltv_Model_DbTable_Channels
     */
    protected $channelsTable;
    
    /**
     *
     * @var Xmltv_Model_DbTable_ChannelsRatings
     */
    protected $ratingsTable;

    /**
     *
     * @var Xmltv_Model_DbTable_ChannelsComments
     */
    protected $commentsTable;

    /**
     *
     * @var Xmltv_Model_DbTable_ChannelsCategories
     */
    protected $channelsCategoriesTable;

    /**
     *
     * @var Xmltv_Model_DbTable_ProgramsCategories
     */
    protected $programsCategoriesTable;
    
    
    public function __construct($config=array()){
        
        if (empty($config)){
        	$config['db'] = Zend_Registry::get('app_config')->resources->multidb->local;
        }
        parent::__construct($config);
        $this->channelsTable = new Xmltv_Model_DbTable_Channels();
        $this->ratingsTable = new Xmltv_Model_DbTable_ChannelsRatings();
        $this->programsCategoriesTable = new Xmltv_Model_DbTable_ProgramsCategories();
        $this->channelsCategoriesTable = new Xmltv_Model_DbTable_ChannelsCategories();
		/**
		 * @TODO Add test for production system to
		 * load coments without errors
		 */
        //$this->commentsTable = new Xmltv_Model_DbTable_ChannelsComments();
        
    }
    
    /**
     * Required. 
     * !!! ATTENTION !!! Beaks dependencies if deleted
     */
    public function getBroadcasts(){}
    
	/**
	 *
	 * Load all published channels
     * @param bool $not_empty //Fetch only channels which have events on this week
	 */
	public function getPublished($not_empty=false){
		
        $select = $this->db->select()
            ->from(array('CH'=>$this->channelsTable->getName()), array(
                'id',
                'title',
                'alias',
                'icon'=>"CONCAT('images/channel_logo/', CH.icon)"
            ))
            ->joinLeft(array('TORRENTTV'=>'rtvg_ref_streams_torrtv'), "`CH`.`id` = `TORRENTTV`.`channel`", 'stream')
            ->joinLeft(array('TVFORSITE'=>'rtvg_ref_streams_tvforsite'), "`CH`.`id` = `TVFORSITE`.`channel`", 'stream')
            ->where("`CH`.`published` = '1'")
            ->order("CH.title ASC")
        ;
        
        if ((bool)Zend_Registry::get('adult') !== true){
            $select->where("`CH`.`adult` != '1'");
        }
        
        $channels = $this->db->fetchAll($select);
        
        if (APPLICATION_ENV=='testing' || $not_empty===true){
        
            $ws = new Zend_Date();
            if ($ws->toString(Zend_Date::WEEKDAY_DIGIT, 'ru')!=1) {
                while ($ws->toString(Zend_Date::WEEKDAY_DIGIT, 'ru')!=1) {
                    $ws->subDay(1);		
                };
            }

            $we = new Zend_Date();
            if ($we->toString(Zend_Date::WEEKDAY_DIGIT, 'ru')!=0) {
                while ($we->toString(Zend_Date::WEEKDAY_DIGIT, 'ru')!=0) {
                    $we->addDay(1);
                }
            }
            
            foreach ($channels as $k=>$ch){
                $sql = "SELECT COUNT(*) FROM ". $this->eventsTable->getName() ." 
                WHERE `start` >= '".$ws->toString("YYYY-MM-dd 00:00:00")."' 
                    AND `start` < '".$we->toString("YYYY-MM-dd 23:59:59")."'
                    AND `channel` = ".(int)$ch['id'];
                
                try {
                    if((int)$this->db->fetchOne($sql)<1){
                        unset($channels[$k]);
                    }
                } catch (Exception $e) {
                    throw new Zend_Exception($e);
                }

                
            }
            
        }
        
        foreach ($channels as $k=>$c){
            $channels[$k]['id'] = (int)$c['id'];
            $channels[$k]['stream'] = ($c['stream']===null) ? null : (int)$c['stream'] ;
        }
        
        return $channels;
		
	}
	
	/**
	 *
	 * @param  string $alias
	 * @return stdClass
	 */
	public function getByAlias($alias=null){
		
	    $select = $this->db->select()
			->from( array('CH'=>$this->channelsTable->getName()), array(
				'id',
                'title',
                'alias',
                'free',
                'featured',
                'icon',
                'desc_intro',
                'desc_body',
                'format',
                'desc_body',
                'site_url'=>'url',
                'keywords',
                'video_aspect',
                'video_quality',
                'audio',
                'added',
                'address',
                'region',
                'location',
                'geo_lt',
                'geo_lg',
			))
            ->join( array('CHCAT'=>$this->channelsCategoriesTable->getName()), '`CH`.`category`=`CHCAT`.`id`', array(
				'category_id'=>'id',
				'category_title'=>'title',
				'category_alias'=>'alias',
				'category_image'=>'image'
            ))
            ->join(array('COUNTRY'=>'rtvg_countries'), "`CH`.`country` = `COUNTRY`.`iso`", array(
                'country_iso'=>'iso',
                'country'=>'name',
            ))
            ->join(array('LANG'=>'rtvg_languages'), "`CH`.`lang` = `LANG`.`iso`", array(
                'lang_iso'=>'iso',
                'lang'=>'name',
            ))
            ->joinLeft(array('NEWS'=>'rtvg_channels_news'), "`CH`.`id` = `NEWS`.`channel`", array(
                'rss_url'=>'url',
                'rss_enabled'=>'active',
            ))
            ->joinLeft(array('TORRENTTV'=>'rtvg_ref_streams_torrtv'), '`CH`.`id` = `TORRENTTV`.`channel`', array(
                'torrenttv_id'=>'stream'
            ))
            ->joinLeft(array('TVFORSITE'=>'rtvg_ref_streams_tvforsite'), '`CH`.`id` = `TVFORSITE`.`channel`', array(
                'tvforsite_id'=>'stream'
            ))
			->where( "`CH`.`alias` = '$alias'")
			->where( "`CH`.`published` IS TRUE")
        ;
        
        $result = $this->db->fetchRow($select);
        
        if (empty($result)){
            return array();
        }
        
        $result = $this->db->fetchRow($select);
        $result['torrenttv_id'] = ($result['torrenttv_id'] !== null) ? (int)$result['torrenttv_id'] : null ;
        $result['tvforsite_id'] = ($result['tvforsite_id'] !== null) ? $result['tvforsite_id'] : null ;
        $result['id'] = (int)$result['id'];
        $result['free'] = (bool)$result['free'];
        $result['featured'] = (bool)$result['featured'];
        $result['category_id'] = (int)$result['category_id'];
        $result['rss_enabled'] = (bool)$result['rss_enabled'];
        $result['geo_lt'] = (float)$result['geo_lt'];
        $result['geo_lg'] = (float)$result['geo_lg'];
        $result['keywords'] = explode(',', $result['keywords']);
        if ($result['added']){
            $result['added'] = new Zend_Date($result['added'], 'YYYY-MM-dd');
        }
        ksort($result);
        
        return $result;
		
	}

	/**
	 * Get channel properties by channel title
	 *
	 * @param  string $title
	 * @throws Zend_Exception
	 * @return Zend_Db_Table_Row
	 */
	public function getByTitle($title=null){
		
		if (!$title) {
			throw new Zend_Exception( Rtvg_Message::ERR_MISSING_PARAM);
		}
		
		$result = $this->channelsTable->fetchRow(" `title` LIKE '$title'");
		$result->alias = Xmltv_String::strtolower($result->alias);
		
		return $result;
		
	}

	public function getById($id=null){
		
		if (!$id || !is_numeric($id)) {
			throw new Zend_Exception("Не указан один или более параметров");
		}
		
		$select = $this->db->select()
            ->from(array("CH"=>$this->channelsTable->getName()), array(
                'id',
                'title',
                'alias',
                'free',
                'featured',
                'icon',
                'desc_intro',
                'desc_body',
                'format',
                'published',
                'parse',
                'url',
                'keywords',
                'metadesc',
                'video_aspect',
                'video_quality',
                'audio',
                'address',
                'geo_lt',
                'geo_lg',
            ))
            ->join(array("CHCAT"=>$this->channelsCategoriesTable->getName()), "`CH`.`category` = `CHCAT`.`id`", array(
                'category_id'=>'id',
                'category_title'=>'title',
                'category_alias'=>'alias',
            ))
            ->joinLeft(array('TORRENTTV'=>'rtvg_ref_streams_torrtv'), "`CH`.`id` = `TORRENTTV`.`channel`", array(
                'torrenttv_id'=>'stream'
            ))
            ->joinLeft(array('TVFORSITE'=>'rtvg_ref_streams_tvforsite'), "`CH`.`id` = `TVFORSITE`.`channel`", array(
                'tvforsite_id'=>'stream'
            ))
            ->where("CH.id = ".(int)$id)
            ->limit(1)
        ;
        
        $result = $this->db->fetchRow($select);
        
        $result['alias'] = Xmltv_String::strtolower($result['alias']);
		$result['start'] = new Zend_Date($result['start'], 'YYYY-MM-dd HH:mm:ss');
		$result['end']   = new Zend_Date($result['end'], 'YYYY-MM-dd HH:mm:ss');
		$result['id'] = (int)$result['id'];
		$result['category'] = (int)$result['category'];
		$result['torrenttv_id'] = (int)$result['torrenttv_id'];
		$result['free'] = (bool)$result['free'];
		$result['featured'] = (bool)$result['featured'];
		
		return $result;
		
	}
	
	/**
	 * Fetch channes info for typeahead
	 */
	public function getTypeaheadItems($category_id=null){
	    
	    $table = new Xmltv_Model_DbTable_Channels();
		try {
		    if ($category_id){
		        $result = $table->fetchAll("`category`='".$category_id."'", "title ASC")->toArray();
		    } else {
		        $result = $table->fetchAll(null, "title ASC")->toArray();
		    }
    	} catch (Zend_Db_Table_Exception $e) {
    		throw new Exception( $e->getMessage(), $e->getCode(), $e);
    	}
    	
    	return $result;
    	
	}
	
	/**
	 * Add hit to channel rating
	 *
	 * @param  int $id // channel ID
	 * @throws Zend_Exception
	 */
	public function addHit($id=null){
		
		if (!$id || !is_int($id)) {
			throw new Zend_Exception( 'Wrong channel ID', 500);
		}
		
		$this->ratingsTable->addHit($id);
			
	}
	
	
	/**
	 * Load channel description
	 *
	 * @param  int $id
	 * @throws Zend_Exception
	 */
	public function getDescription($id=null){
		
		if (!$id) {
			throw new Zend_Exception( Rtvg_Message::ERR_WRONG_PARAM, 500);
		}
		
		$props = $this->channelsTable->find($id)->current();
		$desc['intro'] = $props->desc_intro;
		$desc['body']  = $props->desc_body;
		return $desc;
		
	}
	
	
	/**
	 *
	 * Channels belonging to particular category
	 *
	 * @param  string $cat_alias
	 * @throws Zend_Exception
	 * @return array
	 */
	public function categoryChannels($cat_alias=null){
	
		if (!$cat_alias) {
			throw new Zend_Exception( Rtvg_Message::ERR_WRONG_PARAM);
        }
		
		$select = $this->db->select()
			->from(array('CH'=>$this->channelsTable->getName()), array(
                'id',
				'title',
				'alias',
				'desc_intro',
				'desc_body',
				'category',
				'featured',
				'icon'=>"CONCAT('/images/channel_logo/', `CH`.`icon`, '')",
				'format',
				'lang',
				'url',
				'country',
				'keywords',
				'metadesc',
				'video_aspect',
				'video_quality',
				'audio',
            ))
			->join(array('CHCAT'=>$this->channelsCategoriesTable->getName()), "`CH`.`category` = `CHCAT`.`id`", null)
			->joinLeft(array('TORRENTTV'=>'rtvg_ref_streams_torrtv'), '`CH`.`id` = `TORRENTTV`.`channel`', 'stream' )
            ->joinLeft(array('TVFORSITE'=>'rtvg_ref_streams_tvforsite'), '`CH`.`id` = `TVFORSITE`.`channel`', 'stream' )
			->where("`CHCAT`.`alias` = '$cat_alias'")
			->where("`CH`.`published` IS TRUE")
			->order("CH.title ASC");
        
        if ((bool)Zend_Registry::get('adult') !== true){
            $select->where("`CH`.`adult` != '1'");
        }
        
        $result = $this->db->fetchAll($select);
		
        foreach ($result as $k=>$v){
            $result[$k]['id'] = (int)$v['id'];
            $result[$k]['category'] = (int)$v['category'];
            $result[$k]['stream'] = (int)$v['stream'];
            $result[$k]['free'] = (bool)$v['free'];
            $result[$k]['featured'] = (bool)$v['featured'];
        }
        
        return $result;
		
	}
	
	public function getWeekSchedule($channel=null, Zend_Date $start, Zend_Date $end){
	
        $days = array();
	    do{
	    	$select = $this->db->select()
                ->from( array( 'BC'=>$this->bcTable->getName()), array(
                    'title',
                    'sub_title',
                    'alias',
                    'episode_num',
                    'hash'
                ))
                ->join(array('EVT'=>$this->eventsTable->getName()), "BC.hash = EVT.hash", array(
                    'start',
                    'end',
                    'premiere',
                    'new',
                    'live',
                ))
                ->join( array( 'CH'=>$this->channelsTable->getName()), "`EVT`.`channel`=`CH`.`id`", array(
                    'channel_id'=>'id',
                    'channel_title'=>'title',
                    'channel_alias'=>'alias'))
                ->join( array( 'BCCAT'=>$this->programsCategoriesTable->getName()), "`BC`.`category`=`BCCAT`.`id`", array(
                    'category_id'=>'id',
                    'category_title'=>'title',
                    'category_title_single'=>'title_single',
                    'category_alias'=>'alias'))
                ->where("`EVT`.`start` >= '".$start->toString('YYYY-MM-dd')." 00:00'")
                ->where("`EVT`.`start` < '".$start->toString('YYYY-MM-dd')." 23:59'")
                ->where("`EVT`.`channel` = '".$channel['id']."'")
                ->where("`CH`.`published` = '1'")
                ->group("EVT.start")
                ->order("EVT.start", "ASC")
            ;
            
            $days[$start->toString('U')] = $this->db->fetchAll($select, null, Zend_Db::FETCH_ASSOC);
	    	$start->addDay(1);
	    		
	    } while ( $start->toString('DD')<=$end->toString('DD') );
	    
        foreach ($days as $timestamp=>$day) {
	    	foreach ($day as $k=>$bc) {
                $days[$timestamp][$k]['start'] = new Zend_Date( $bc['start'], 'YYYY-MM-dd HH:mm:ss');  
                $days[$timestamp][$k]['end'] = new Zend_Date( $bc['end'], 'YYYY-MM-dd HH:mm:ss');
                $days[$timestamp][$k]['channel_id'] = (int)$bc['channel_id'];
                $days[$timestamp][$k]['category_id'] = (int)$bc['category_id'];
                $days[$timestamp][$k]['episode_num'] = (!empty($bc['episode_num'])) ? (int)$bc['episode_num'] : null ;
            }
	    	
	    }
        
        return $days;
		
	}
	
	/**
	 * Channels categories list
	 */
	public function channelsCategories(){
	    
		$table = new Xmltv_Model_DbTable_ChannelsCategories();
		$result = $table->fetchAll(null, "title ASC")->toArray();
		return $result;
		
	}
	
	/**
	 *
	 * @param string $alias
	 */
	public function category($alias=null){
		
        $table = new Xmltv_Model_DbTable_ChannelsCategories();
		$result = $table->fetchRow("`alias` = '$alias'")->toArray();
        
        if ($result){
            $result['id'] = (int)$result['id'];
            $result['featured'] = ($result['featured'] != 1) ? false : true ;
        }
        
        return $result;
		
	}
	
	/**
	 * Search for channel
	 *
	 * @param string $string // Search string
	 */
	public function searchChannel( $string=null, $strict=false){
		
	    if ($string){
	        $select = $this->db->select()
	        	->from(array( 'CH'=>$this->channelsTable->getName()), '*')
	        	->joinLeft( array('CHCAT'=>$this->channelsCategoriesTable->getName()), '`CH`.`category`=`CHCAT`.`id`', array(
	        		'category_title'=>'title',
	        		'category_alias'=>'alias',
	        		'category_icon'=>'image'));
	        
	        if ($strict===true){
	            $select->where("`CH`.`title` = '$string'");
	            $result = $this->db->fetchRow( $select );
	        } else {
	            $select->where("`CH`.`title` LIKE '%$string%'");
	            $result = $this->db->fetchAll( $select);
	        }
	        
            return $result;
	        
	    }
	    
	}
	
	/**
	 * Retrieve all channels info
	 */
	public function allChannels($order=null){
		
		$select = $this->db->select()
			->from( array('CH'=>$this->channelsTable->getName()), array(
				'id',
				'title',
				'alias',
				'desc_intro',
				'desc_body',
				'category',
				'featured',
				'icon',
				'format',
				'lang',
				'url',
				'country',
				'adult',
				'keywords',
				'metadesc',
				'video_aspect',
				'video_quality',
				'audio',
			))
			->join( array('CHCAT'=>$this->channelsCategoriesTable->getName()), '`CH`.`category`=`CHCAT`.`id`', array(
				'category_title'=>'title',
				'category_alias'=>'alias',
				'category_image'=>'image')
			)
			->where("`CH`.`published` = TRUE");
		
		if ($order){
		    $select->order( $order );
		}
		
		$result = $this->db->fetchAll( $select );
		
        return $result;
	    
	}
	
	/**
	 * Top channels list
	 *
	 * @param int $amt
	 */
	public function topChannels($amt=10){
		
		$select = $this->db->select()
	    	->from( array('CH'=>$this->channelsTable->getName()), array( 
                'id',
                'title',
                'alias',
                'featured',
                'icon'=>"CONCAT('/images/channel_logo/', CH.icon, '')",
                'free',
                'format',
            ))
            ->join(array("LANG"=>'rtvg_languages'), "`CH`.`lang` = `LANG`.`iso`", array(
                'lang_iso'=>'iso',
                'lang'=>'name',
            ))
            ->join(array("COUNTRY"=>'rtvg_countries'), "`CH`.`country` = `COUNTRY`.`iso`", array(
                'country_iso'=>'iso',
                'country'=>'name',
            ))
            ->join(array('TORRENTTV'=>'rtvg_ref_streams_torrtv'), "`CH`.`id` = `TORRENTTV`.`channel`", array(
                'torrenttv_id'=>'stream'
            ))
            ->joinLeft(array('TVFORSITE'=>'rtvg_ref_streams_tvforsite'), "`CH`.`id` = `TVFORSITE`.`channel`", array(
                'tvforsite_id'=>'stream'
            ))
	    	->joinLeft( array('RATING'=>$this->channelsRatingsTable->getName()), "`CH`.`id` = `RATING`.`channel`", array(
                'hits',
                'rating',
            ))
	    	->where("`CH`.`published` = TRUE")
	    	->order("RATING.hits DESC")
            ->limit($amt)
        ;
        
        $result = $this->db->fetchAll($select);
        
        if ((bool)$result!==false){
            foreach ($result as $k=>$v){
                $result[$k]['id'] = (int)$v['id'];
                $result[$k]['hits'] = (int)$v['hits'];
                $result[$k]['rating'] = (int)$v['rating'];
                $result[$k]['featured'] = (bool)$v['featured'];
                $result[$k]['free'] = (bool)$v['featured'];
                $result[$k]['torrenttv_id'] = (int)$v['torrenttv_id'];
                ksort($result[$k]);
            }
        }
        
        return $result;
	    
	}

	/**
	 * Featured channels list
	 *
	 * @param int $amt
	 */
	public function featuredChannels($amt=null){
		
	    $select = $this->db->select()
    		->from( array( 'CH'=>$this->channelsTable->getName()))
    		->join( array( 'RAT'=>$this->channelsRatingsTable->getName()), "`CH`.`id`=`RAT`.`channel`", null )
	    	->where( "`CH`.`featured` IS TRUE" )
	    	->order( "RAT.hits")
    		->limit( (int)$amt );
	    
	    return $this->db->fetchAll($select);
	    
	}
	
	public function makeAlias($title=null){
	    
	    if (!$title){
	        throw new Zend_Exception( Rtvg_Message::ERR_MISSING_PARAM );
	    }
	    
		//Generate channel alias
		$alias = $title;
		$toDash = new Xmltv_Filter_SeparatorToDash();
		$alias = $toDash->filter( $alias );
		$plusToPlus = new Zend_Filter_Word_SeparatorToSeparator('+', '-плюс-');
		$alias = $plusToPlus->filter( $alias );
		$alias = str_replace('--', '-', trim( $alias, ' -'));
		return $alias;
	}
	
	/**
	 * Unpublish multiple channels at once
	 *
	 * @param  array $channels
	 * @throws Zend_Controller_Action_Exception
	 * @return bool
	 */
	public function unpublishMulti($channels=array()){
	
	    $sql = "UPDATE ".$this->channelsTable->getName()." SET `published`='0' WHERE `id` IN ( ".implode(',', $channels)." )";
		$this->db->query($sql);
		return true;
		 
	}
    
    public function channelFeed($channel=null, $amt=5){
        
        if (!$channel || !is_array($channel)){
            throw new Zend_Exception();
        }
        
        if ($channel['rss_enabled']===true){
            
            $frontendOptions = array(
                'lifetime' => 7200,
                'automatic_serialization' => true
            );
            $backendOptions = array('cache_dir' => realpath(APPLICATION_PATH .'/../cache/Feeds/Channels/'));
            $cache = Zend_Cache::factory(
                'Core', 'File', $frontendOptions, $backendOptions
            );

            try{
                if ((bool)($items = $cache->load(md5($channel['id'].':'.$channel['rss_url'])))===false){
                    $rss = new Zend_Feed_Rss($channel['rss_url']);
                    $i=0;
                    $items = array();
                    foreach ($rss as $item) {
                        if ($i<5){
                            $items[$i]['title'] = $item->title();
                            $items[$i]['link'] = $item->link();
                            $items[$i]['desc'] = $item->description();
                            $items[$i]['category'] = $item->category();
                            $date = new Zend_Date($item->pubDate(), Zend_Date::RFC_1123);
                            $date->setTimezone("Europe/Moscow");
                            $items[$i]['pubDate'] = $date;
                        }
                        $i++;
                    }
                }
            } catch (Exception $e){
                return false;
            }

            return $items;
            
        }
        
        
        
        
        
    }
}

