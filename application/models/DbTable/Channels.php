<?php
/**
 * Database table for channels info
 *
 * @uses Zend_Db_Table_Abstract
 * @version $Id: Channels.php,v 1.13 2013-02-15 00:44:02 developer Exp $
 */

class Xmltv_Model_DbTable_Channels extends Xmltv_Db_Table_Abstract
{

    protected $_name = 'channels';
    protected $channelsRatingsTable;
    protected $channelsCategoriesTable;
	
	const FETCH_MODE = Zend_Db::FETCH_OBJ;
	
	/**
	 * Constructor
	 * 
	 * @param array $config
	 */
    public function __construct($config=array()) {
    	
    	parent::__construct(array('name'=>$this->_name));
		$this->channelsRatingsTable = new Xmltv_Model_DbTable_ChannelsRatings();
		$this->channelsCategoriesTable = new Xmltv_Model_DbTable_ChannelsCategories();
    }
    
    /**
     * Load featired channels list
     * 
     * @param  string $order
     * @param  int    $total
     * @param  bool   $by_hits
     * @throws Zend_Exception
     * @return mixed
     */
    public function featuredChannels($total=20, $order='id', $by_hits=true){
    	
    	$select = $this->_db->select()
    		->from( array( 'ch'=>$this->getName() ), array( 'id', 'title', 'alias'=>'LOWER(`ch`.`alias`)') )
    		->join( array( 'r'=>$this->channelsRatingsTable->getName()), "`ch`.`id`=`r`.`id`", array('hits') )
	    	//->where( "`ch`.`featured`='1'" )
    		->limit( $total );
	    
	    if (!$by_hits){
	    	$select->order("$order ASC");
	    } else {
    		$select->order("r.hits DESC");
    		$select->order("ch.title ASC");
	    }
	    
	    if (APPLICATION_ENV=='development'){
	        echo "<b>".__METHOD__."</b>";
		    Zend_Debug::dump($select->assemble());
		    //die(__FILE__.': '.__LINE__);
	    }
	    
	    $result = $this->_db->query($select)->fetchAll(Zend_Db::FETCH_ASSOC);	    	
    	
	    if (APPLICATION_ENV=='development'){
	    	//Zend_Debug::dump($result);
		    //die(__FILE__.': '.__LINE__);
	    }
	    
	    return $result;
    	
    }
    
    /**
     * 
     * @param  string $alias
     * @throws Zend_Exception
     * @return Zend_Db_Table_Rowset
     */
    public function fetchCategory($alias=null){
    	
    	if (!$alias)
			throw new Zend_Exception("Не указан один или более параметров для ".__FUNCTION__, 500);
		
    	$select = $this->select()
    		->from(array('ch'=>$this->getName()), '*')
    		->join(array('cat'=>$this->channelsCategoriesTable->getName()), "`ch`.`category`=`cat`.`id`", array())
    		->where("`cat`.`alias` LIKE '$alias'")
    		->where("`ch`.`published`='1'")
    		->order("ch.title ASC");
    	
    	$result = $this->fetchAll($select);
    	return $result;
    	
    }
    
    /**
     * 
     * @param  int       $ch_id
     * @param  Zend_Date $start
     * @param  Zend_Date $end
     * @param  array     $tables
     * @throws Zend_Exception
     */
    public function fetchWeekItems($ch_id, Zend_Date $start, Zend_Date $end, $tables=array()){
    	
        if (APPLICATION_ENV=='development'){
        	//Zend_Debug::dump(func_get_args());
        	//die(__FILE__.': '.__LINE__);
        }
        
        if (empty($tables))
            throw new Zend_Exception(parent::ERR_PARAMETER_MISSING.__METHOD__, 500);
        if (!isset($tables['programs']))
            throw new Zend_Exception(parent::ERR_PARAMETER_MISSING.__METHOD__, 500);
        if (!isset($tables['channels']))
            throw new Zend_Exception(parent::ERR_PARAMETER_MISSING.__METHOD__, 500);
        
    	$days = array();
    	
		do{
			$select = $this->_db->select()
				->from( array( 'prog'=>$tables['programs']->getName()), array(
					'title',
					'sub_title',
					'alias',
					'start',
					'end',
					'episode_num',
					'hash'
				))
				->joinLeft( array( 'ch'=>$this->getName()), "`prog`.`channel`=`ch`.`id`", array( 
					'channel_id'=>'id',
					'channel_title'=>'title',
					'channel_alias'=>'alias'))
				->where("`prog`.`start` >= '".$start->toString('yyyy-MM-dd')." 00:00'")
				->where("`prog`.`start` < '".$start->toString('yyyy-MM-dd')." 23:59'")
				->where("`prog`.`channel` = '$ch_id'")
				->where("`ch`.`published` = '1'")
				->order("prog.start", "ASC");
			
			if (APPLICATION_ENV=='development'){
			    Zend_Debug::dump($select->assemble());
			    //die(__FILE__.': '.__LINE__);
			}
			
			try {
				$days[$start->toString('U')] = $this->_db->fetchAll($select, null, Zend_Db::FETCH_ASSOC);
			} catch (Zend_Db_Adapter_Exception $e) {
				throw new Zend_Exception($e->getMessage(), $e->getCode(), $e);
			}
			
			$start->addDay(1);
			
		} while ( $start->compare($end, 'dd', 'ru')!=1 );
		
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
	 * 
	 * @param unknown_type $amt
	 */
	public function topChannels($amt=20, $offset=0){
		
	    die(__FILE__.': '.__LINE__);
	    
	    $ratings = new Xmltv_Model_DbTable_ChannelsRatings();
	    $select = $this->select(true)
	    	->from(array('ch'=>$this->getName()))
	    	->join(array('r'=>$ratings->getName()))
	    	->limit($amt);
	    	
	    if (APPLICATION_ENV=='development'){
	        Zend_Debug::dump($select->assemble());
	        die(__FILE__.': '.__LINE__);
	    }
	    
	}

	
	
}

