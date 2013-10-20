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
                'icon'
            ))
            ->where('`CH`.`published` = 1')
        ;
        
        if (Zend_Registry::get('adult')!==true){
            $select->where("`CH`.`adult` = '0'");
        }
        
        $channels = $this->db->fetchAll($select->assemble());
        
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
        
        $view = new Zend_View();
        foreach ($channels as $k=>$ch){
            $channels[$k]['icon'] = $view->baseUrl('images/channel_logo/'.$ch['icon']);
        }
        
        return $channels;
		
	}
	
	/**
	 *
	 * @param  array $channel
	 * @param  string $baseUrl
	 * @return void|string
	 */
	public function createUrl($channel=null, $baseUrl=null){
		
		if (!$channel || $baseUrl===null)
			throw new Zend_Exception( Rtvg_Message::ERR_WRONG_PARAM.__METHOD__, 500);
		
		$channel['icon'] = $baseUrl.'/images/channel_logo/'.$channel['icon'];
		return $channel;
	}
	
	/**
	 *
	 * @param  string $alias
	 * @return stdClass
	 */
	public function getByAlias($alias=null){
		
	    $select = $this->db->select()
			->from( array('ch'=>$this->channelsTable->getName()), array(
				'id',
				'title',
				'alias',
				'desc_intro',
				'desc_body',
				'category',
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
			->where( "`ch`.`alias` LIKE '$alias'")
			->where( "`ch`.`published`='1'")
			->joinLeft( array('cat'=>$this->channelsCategoriesTable->getName()), '`ch`.`category`=`cat`.`id`', array(
				'category_title'=>'cat.title',
				'category_alias'=>'LOWER(`cat`.`alias`)',
				'category_image'=>'cat.image')
			);
	    
	    // Breakpoint
	    if (APPLICATION_ENV=='development') {
	        $this->debugSelect($select, __METHOD__);
	        //die(__FILE__.': '.__LINE__);
	    }
		
		$result = $this->db->fetchRow( $select, null, Zend_Db::FETCH_ASSOC );
		
		if ($result){
			$result['adult'] = (bool)$result['adult'];
		}
		
		
		// Breakpoint
		if (APPLICATION_ENV=='development'){
			//var_dump($result);
			//die(__FILE__.': '.__LINE__);
		}

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
			throw new Zend_Exception( Rtvg_Message::ERR_MISSING_PARAM, 500);
		}
		
		$result = $this->channelsTable->fetchRow(" `title` LIKE '$title'");
		$result->alias = Xmltv_String::strtolower($result->alias);
		
		if (APPLICATION_ENV=='development'){
			//var_dump($result);
			//die(__FILE__.': '.__LINE__);
		}
		
		return $result;
		
	}

	public function getById($id=null){
		
		if (!$id) {
			throw new Zend_Exception("Не указан один или более параметров для ".__FUNCTION__, 500);
		}
		
		$result = $this->channelsTable->fetchRow("`id`='$id'")->toArray();
		$result['alias'] = Xmltv_String::strtolower($result['alias']);
		$result['start'] = new Zend_Date($result['start'], 'yyyy-MM-dd HH:mm:ss');
		$result['end']   = new Zend_Date($result['end'], 'yyyy-MM-dd HH:mm:ss');
		
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
		
		if (!$id || !is_numeric($id)) {
			throw new Zend_Exception( Rtvg_Message::ERR_WRONG_PARAM, 500);
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
	
		if (!$cat_alias)
			throw new Zend_Exception( Rtvg_Message::ERR_WRONG_PARAM, 500);
		
		
		$select = $this->db->select()
			->from(array('ch'=>$this->channelsTable->getName()), '*')
			->join(array('cat'=>$this->channelsCategoriesTable->getName()), "`ch`.`category`=`cat`.`id`", array())
			->where("`cat`.`alias` LIKE '$cat_alias'")
			->where("`ch`.`published`='1'")
			->order("ch.title ASC");
		
		if (APPLICATION_ENV=='development'){
		    parent::debugSelect($select, __METHOD__);
		    //die(__FILE__.': '.__LINE__);
		}
		
		$result = $this->db->fetchAll($select);
		
		if ($result===false || empty($result)){
	    	return false;
	    }
	    
	    if (APPLICATION_ENV=='development'){
	    	//Zend_Debug::dump($result);
	    	//die(__FILE__.': '.__LINE__);
	    }
	    
	    return $result;
		
	}
	
	public function getWeekSchedule($channel=null, Zend_Date $start, Zend_Date $end){
	
	    if (APPLICATION_ENV=='development'){
	        //var_dump(func_get_args());
	        //die(__FILE__.': '.__LINE__);
	    }
	    
	    $days = array();
	    do{
	    	$select = $this->db->select()
	    	->from( array( 'prog'=>$this->broadcasts->getName()), array(
	    		'title',
	    		'sub_title',
	    		'alias',
	    		'start',
	    		'end',
	    		'episode_num',
	    		'hash'
	    	))
	    	->joinLeft( array( 'ch'=>$this->channelsTable->getName()), "`prog`.`channel`=`ch`.`id`", array(
	    		'channel_id'=>'id',
	    		'channel_title'=>'title',
	    		'channel_alias'=>'alias'))
	    	->joinLeft( array( 'pc'=>$this->programsCategoriesTable->getName()), "`prog`.`category`=`pc`.`id`", array(
	    		'category_id'=>'id',
	    		'category_title'=>'title',
	    		'category_title_single'=>'title_single',
	    		'category_alias'=>'alias'))
    		->where("`prog`.`start` >= '".$start->toString('yyyy-MM-dd')." 00:00'")
    		->where("`prog`.`start` < '".$start->toString('yyyy-MM-dd')." 23:59'")
    		->where("`prog`.`channel` = '".$channel['id']."'")
    		->where("`ch`.`published` = '1'")
    		->order("prog.start", "ASC");
	    		
	    	if (APPLICATION_ENV=='development'){
	    		parent::debugSelect($select, __METHOD__);
	    		//die(__FILE__.': '.__LINE__);
	    	}
	    		
	    	$days[$start->toString('U')] = $this->db->fetchAll($select, null, Zend_Db::FETCH_ASSOC);
	    	$start->addDay(1);
	    		
	    } while ( $start->toString('DD')<=$end->toString('DD') );
	    
	    if (APPLICATION_ENV=='development'){
	    	//Zend_Debug::dump($days);
	    	//die(__FILE__.': '.__LINE__);
	    }
	    
	    foreach ($days as $timestamp=>$day) {
	    	if (!empty($day)){
	    		//Zend_Debug::dump($day);
	    		//die(__FILE__.': '.__LINE__);
	    		foreach ($day as $k=>$program) {
	    			$days[$timestamp][$k]['start'] = new Zend_Date( $program['start'], 'yyyy-MM-dd HH:mm:ss');
	    			$days[$timestamp][$k]['end']   = new Zend_Date( $program['end'], 'yyyy-MM-dd HH:mm:ss');
	    			//Zend_Debug::dump($days[$timestamp][$k]);
	    			//die(__FILE__.': '.__LINE__);
	    		}
	    	}
	    }
	    
	    if (APPLICATION_ENV=='development'){
	    	//Zend_Debug::dump($days);
	    	//die(__FILE__.': '.__LINE__);
	    };
	    
	    return $days;
		
	}
	
	/**
	 * Channels categories list
	 */
	public function channelsCategories(){
	    
		$table = new Xmltv_Model_DbTable_ChannelsCategories();
		$result = $table->fetchAll(null, "title ASC")->toArray();
		$allChannels = array(
				'id'=>'',
				'title'=>'Все каналы',
				'alias'=>'',
				'image'=>'all-channels.gif',
		);
		array_push( $result, $allChannels );
		return $result;
		
	}
	
	/**
	 *
	 * @param string $alias
	 */
	public function category($alias=null){
		
		if (null !== $alias){
		    $table = new Xmltv_Model_DbTable_ChannelsCategories();
		    return $table->fetchRow("`alias` LIKE '".$alias."'");
		} else {
			return false;
		}
	}
	
	/**
	 * Search for channel
	 *
	 * @param string $string // Search string
	 */
	public function searchChannel( $string=null, $strict=false){
		
	    if ($string){
	        $pfx = Zend_Registry::get('app_config')->resources->multidb->local->get('tbl_prefix');
	        $select = $this->db->select()
	        	->from(array( 'ch'=>$this->channelsTable->getName()), array('*', 'alias'=>'LOWER(ch.alias)'))
	        	->joinLeft( array('cat'=>$pfx.'channels_categories'), '`ch`.`category`=`cat`.`id`', array(
	        		'category_title'=>'title',
	        		'category_alias'=>'alias',
	        		'category_icon'=>'image'));
	        
	        if ($strict===true){
	            $select->where("`ch`.`title` = '$string'");
	            $result = $this->db->fetchRow( $select );
	        } else {
	            $select->where("`ch`.`title` LIKE '%$string%'");
	            $result = $this->db->fetchAll( $select);
	        }
	        
	        
	        if (APPLICATION_ENV=='development'){
		        var_dump($select->assemble());
		        //var_dump($result);
		        //die(__FILE__.': '.__LINE__);
	        }
	        
	        return $result;
	        
	    }
	    
	}
	
	/**
	 * Retrieve all channels info
	 */
	public function allChannels($order=null){
		
		$select = $this->db->select()
			->from( array('ch'=>$this->channelsTable->getName()), array(
				'id',
				'title',
				'alias'=>'LOWER(`ch`.`alias`)',
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
			->joinLeft( array('cat'=>$this->channelsCategoriesTable->getName()), '`ch`.`category`=`cat`.`id`', array(
				'category_title'=>'cat.title',
				'category_alias'=>'LOWER(`cat`.`alias`)',
				'category_image'=>'cat.image')
			)
			->where("`ch`.`published`='1'");
		
		if ($order){
		    $select->order( $order );
		}
		
		if (APPLICATION_ENV=='development'){
		    parent::debugSelect($select, __METHOD__);
		}
		
		try {
		    $result = $this->db->fetchAll( $select );
		} catch (Zend_Db_Table_Exception $e) {
		    throw new Zend_Exception($e->getMessage());
		}
		
		return $result;
	    
	}
	
	/**
	 * Top channels list
	 *
	 * @param int $amt
	 */
	public function topChannels($amt=10){
		
		$select = $this->db->select()
	    	->from( array('ch'=>$this->channelsTable->getName()),
	    		array( 'id', 'title', 'alias'=>'LOWER(`ch`.`alias`)', 'featured', 'icon', ''))
	    	->join( array('r'=>$this->channelsRatingsTable->getName()), "`r`.`id`=`ch`.`id`", array('hits'))
	    	->join( array('cat'=>$this->channelsCategoriesTable->getName()), "`ch`.`category`=`cat`.`id`",
	    		array('category_title'=>'title', 'category_alias'=>'alias', 'category_image'=>'image'))
	    	->limit($amt)
	    	->where("`ch`.`published`='1'")
	    	->order("r.hits DESC");
	    	
	    if (APPLICATION_ENV=='development'){
	        parent::debugSelect($select, __METHOD__);
	        //die(__FILE__.': '.__LINE__);
	    }
	    
	    $result = $this->db->fetchAssoc($select);
	    
	    if (APPLICATION_ENV=='development'){
	    	//Zend_Debug::dump($result);
	    	//die(__FILE__.': '.__LINE__);
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
    		->from( array( 'ch'=>$this->channelsTable->getName()), array(
    			'id',
    			'title',
    			'alias'=>'LOWER(`ch`.`alias`)'
    		))
    		->join( array( 'rating'=>$this->channelsRatingsTable->getName()), "`ch`.`id`=`rating`.`id`", null )
	    	->where( "`ch`.`featured`='1'" )
	    	->order( "rating.hits")
    		->limit( (int)$amt );
	    
	    if (APPLICATION_ENV=='development'){
	        parent::debugSelect($select, __METHOD__);
		    //die(__FILE__.': '.__LINE__);
	    }
	    
	    $result = $this->db->fetchAll($select, null, Zend_Db::FETCH_ASSOC);
	    
	    return $result;
	    
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
	
	    var_dump($channels);
	    die(__FILE__.': '.__LINE__);
	    
		$sql = "UPDATE ".$this->channelsTable->getName()." SET `published`='0' WHERE `id` IN ( ".implode(',', $channels)." )";
		$this->db->query($sql);
		return true;
		 
	}
}

