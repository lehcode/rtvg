<?php
/**
 * Frontend channels model
 *
 * @author     Antony Repin <egeshisolutions@gmail.com>
 * @subpackage backend
 * @version    $Id: Channels.php,v 1.25 2013-04-03 04:08:16 developer Exp $
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
    protected $catgeoriesTable;

    public function __construct($config=array()){
        
        if (empty($config)){
        	$config['db'] = Zend_Registry::get('app_config')->resources->multidb->local;
        }
        parent::__construct($config);
        $this->channelsTable = new Xmltv_Model_DbTable_Channels();
        $this->ratingsTable = new Xmltv_Model_DbTable_ChannelsRatings();
        $this->categoriesTable = new Xmltv_Model_DbTable_ChannelsCategories();
        $this->commentsTable = new Xmltv_Model_DbTable_ChannelsComments();
        
    }
    
	/**
	 * 
	 * Load all published channels
	 */
	public function getPublished( Zend_View &$view=null ){
		
	    if (!$view){
	        throw new Zend_Exception( Rtvg_Message::ERR_MISSING_PARAM, 500 );
	    }
	    
	    $rows = $this->channelsTable->fetchAll("`published`='1'", 'title ASC');
		$rows = $rows->toArray();
		foreach ($rows as $k=>$row) {
			$rows[$k]['icon'] = $view->baseUrl('images/channel_logo/'.$row['icon']);
		}
		return $rows;
		
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
		
	    $categoriesTable = new Xmltv_Model_DbTable_ChannelsCategories();
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
			->joinLeft( array('cat'=>$categoriesTable->getName()), '`ch`.`category`=`cat`.`id`', array(
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

	public function getByTitle($alias=null){
		
		if (!$alias)
		throw new Zend_Exception("Не указан один или более параметров для ".__FUNCTION__, 500);
		
		$alias = Xmltv_String::strtolower($alias);
		$result = $this->channelsTable->fetchRow(" `title` LIKE '$alias'");
		$result->alias = Xmltv_String::strtolower($result->alias);
		return $result;
		
	}

	public function getById($id=null){
		
		if (!$id)
			throw new Zend_Exception("Не указан один или более параметров для ".__FUNCTION__, 500);
		
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
	    	->from( array( 'prog'=>$this->programsTable->getName()), array(
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
	public function searchChannel( $string=null){
		
	    if ($string){
	        $pfx = Zend_Registry::get('app_config')->resources->multidb->local->get('tbl_prefix');
	        $select = $this->db->select()
	        	->from(array( 'ch'=>$this->channelsTable->getName()), array('*', 'alias'=>'LOWER(ch.alias)'))
	        	->joinLeft( array('cat'=>$pfx.'channels_categories'), '`ch`.`category`=`cat`.`id`', array(
	        		'category_title'=>'title',
	        		'category_alias'=>'alias',
	        		'category_icon'=>'image'))
	        	->where("`ch`.`title` LIKE '%$string%'");
	        
	        if (APPLICATION_ENV=='development'){
		        var_dump($select->assemble());
		        //die(__FILE__.': '.__LINE__);
	        }
	        
	        $result = $this->db->fetchAll( $select);
	        
	        if (APPLICATION_ENV=='development'){
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
		    throw new Zend_Exception($e->getMessage(), $e->getCode(), $e);
		}
		
		if (APPLICATION_ENV=='development'){
			//var_dump($result);
			//die(__FILE__.': '.__LINE__);
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
}

