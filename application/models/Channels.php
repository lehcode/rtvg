<?php
/**
 * Channels model
 *
 * @version $Id: Channels.php,v 1.13 2013-01-19 10:11:13 developer Exp $
 */
class Xmltv_Model_Channels extends Xmltv_Model_Abstract
{

    public function __construct($config=array()){
        
        $config['db'] = Zend_Registry::get('app_config')->resources->multidb->local;
        parent::__construct($config);
        
        $this->table = new Xmltv_Model_DbTable_Channels();
        
    }
    
	/**
	 * 
	 * Load all published channels
	 */
	public function getPublished(){
		
	    $rows = $this->channelsTable->fetchAll("`published`='1'", 'title ASC');
		$view = new Zend_View();
		foreach ($rows as $row) {
			$row->icon = $view->baseUrl('images/channel_logo/'.$row->icon);
		}
		return $rows->toArray();
		
	}
	
	/**
	 * 
	 * @param  array $channel
	 * @param  string $baseUrl
	 * @return void|string
	 */
	public function createUrl($channel=null, $baseUrl=null){
		
		if (!$channel || $baseUrl===null)
			throw new Zend_Exception( self::ERR_WRONG_PARAMS.__METHOD__, 500);
		
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
			->from( array('ch'=>$this->table->getName()), array('ch'=>'*', 'alias'=>'LOWER(`ch`.`alias`)'))
			->where( "`ch`.`alias` LIKE '$alias'")
			->where( "`ch`.`published`='1'")
			->joinLeft( array('cat'=>$categoriesTable->getName()), '`ch`.`category`=`cat`.`id`', array(
				'category_title'=>'cat.title',
				'category_alias'=>'LOWER(`cat`.`alias`)',
				'category_image'=>'cat.image')
			);
	    
	    // Breakpoint
	    if (APPLICATION_ENV=='development'){
	        $this->debugSelect($select, __METHOD__);
	        //die(__FILE__.': '.__LINE__);
	    }
		
		$result = $this->db->fetchRow( $select, null, Zend_Db::FETCH_OBJ );
		if ($result){
			$result->featured  = (bool)$result->featured;
			$result->published = (bool)$result->published;
			$result->parse     = (bool)$result->parse;
			$result->adult     = (bool)$result->adult;
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
		$result = $this->table->fetchRow(" `title` LIKE '$alias'");
		$result->alias = Xmltv_String::strtolower($result->alias);
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
	 * Add hit to channel popularity
	 * 
	 * @param  int $id // channel ID
	 * @throws Zend_Exception
	 */
	public function addHit($id=null){
		
		if (!$id || !is_int($id))
			throw new Zend_Exception(self::ERR_WRONG_PARAMS, 500);
		
		$this->_hits_table = new Xmltv_Model_DbTable_ChannelsRatings();
		$this->_hits_table->addHit($id);
			
	}
	
	public function getDescription($id=null){
		
		if (!$id)
		throw new Zend_Exception(self::ERR_WRONG_PARAMS, 500);
		
		$props = $this->table->find($id)->current();
		$desc['intro'] = $props->desc_intro;
		$desc['body']  = $props->desc_body;
		return $desc;
		
	}
	
	public function categoryChannels($cat_alias=null){
	
		if (!$cat_alias)
			throw new Zend_Exception(self::ERR_WRONG_PARAMS, 500);
		
		$result = $this->table->fetchCategory($cat_alias);
		$view = new Zend_View();
		foreach ($result as $row) {
			$row->icon = $view->baseUrl('images/channel_logo/'.$row->icon);
		}	
		return $result;
		
	}
	
	public function getWeekSchedule($channel=null, Zend_Date $start, Zend_Date $end){
	
		return $this->table->fetchWeekItems($channel->ch_id, $start, $end);
		
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
		
		if ($alias){
		    $table = new Xmltv_Model_DbTable_ChannelsCategories();
		    return $table->fetchRow("`alias` LIKE '".$alias."'");
		}
	}
	
	/**
	 * Search for channel
	 * 
	 * @param string $string // Search string
	 */
	public function searchChannel( $string=null){
		
	    if ($string){
	        //return $this->table->fetchAll("`title` LIKE '%$string%' OR `alias` LIKE '%$string%' ")->toArray();
	        $pfx = Zend_Registry::get('app_config')->resources->multidb->local->get('tbl_prefix');
	        $select = $this->db->select()
	        	->from(array( 'ch'=>$this->table->getName()), array('*', 'alias'=>'LOWER(ch.alias)'))
	        	->join( array('cat'=>$pfx.'channels_categories'), '`ch`.`category`=`cat`.`id`', array(
	        		'category_title'=>'title',
	        		'category_alias'=>'alias',
	        		'category_icon'=>'image'))
	        	->where("`ch`.`title` LIKE '%$string%'");
	        
	        //var_dump($select->assemble());
	        //die(__FILE__.': '.__LINE__);
	        
	        $result = $this->db->fetchAll( $select);
	        
	        //var_dump($result);
	        //die(__FILE__.': '.__LINE__);
	        return $result;
	        
	    }
	    
	}
	
	/**
	 * Retrieve all channels info
	 */
	public function allChannels(){
		
		$select = $this->db->select()
			->from( array('ch'=>$this->table->getName()), array('ch'=>'*', 'alias'=>'LOWER(`ch`.`alias`)'))
			->joinLeft( array('cat'=>$this->_tbl_pfx.'channels_categories'), '`ch`.`category`=`cat`.`id`', array(
				'category_title'=>'cat.title',
				'category_alias'=>'LOWER(`cat`.`alias`)',
				'category_image'=>'cat.image')
			);
		
		try {
		    $result = $this->db->fetchAll( $select );
		} catch (Zend_Db_Table_Exception $e) {
		    throw new Zend_Exception($e->getMessage(), $e->getCode(), $e);
		}
		
		if ($result)
			return $result->toArray();
	    
	}
	
}

