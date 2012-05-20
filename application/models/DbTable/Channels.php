<?php
class Xmltv_Model_DbTable_Channels extends Zend_Db_Table_Abstract
{

    protected $_name = 'rtvg_channels';
    private $_debug;
	private $_profiling;
	
	const FETCH_MODE = Zend_Db::FETCH_OBJ;
	
    public function __construct($config=array()) {
    	
    	parent::__construct(array('name'=>$this->_name));
    	
    	$this->_debug = Xmltv_Config::getDebug();
		$this->_profiling = Xmltv_Config::getProfiling();
		
    }
    
    public function getTypeheadItems(){
    	
    	$select = $this->_db->select()
    		->from($this->_name, array( 'title' ));
    	return $this->_db->query($select)->fetchAll();
    	
    }
    
    public function getFeatured($order=null, $total=20, $by_hits=true){
    	
    	if (!$order)
    	$order='ch_id';
    	
    	$select = $this->_db->select()
    		->from( $this->_name, '*' )
    		->joinLeft('rtvg_channels_ratings', "$this->_name.`ch_id`=rtvg_channels_ratings.`ch_id`");
    		
    	$select->where( "`featured`='1'" )->limit($total);
    	
    	if (!$by_hits)
    	$select->order("$order ASC");
    	else {
    		$select->order("rtvg_channels_ratings.hits DESC");
    		$select->order("$this->_name.title ASC");
    	}
    	
    	return $this->_db->query($select)->fetchAll();
    }
    
    public function fetchCategory($alias=null){
    	
    	if (!$alias)
		throw new Zend_Exception("Не указан один или более параметров для ".__FUNCTION__, 500);
		/*
    	if( $this->_profiling ) {
			$this->_db->getProfiler()->setEnabled( true );
			$profiler = $this->_db->getProfiler();
		}
		*/
    	$select = $this->_db->select()
    		->from( $this->_name, '*' )
    		->joinLeft('rtvg_channels_categories', "$this->_name.`category`=rtvg_channels_categories.`id`", array());
    	$select->where( "rtvg_channels_categories.`alias`='$alias'" );
    	$select->order("$this->_name.title ASC");
    	
    	$result = $this->_db->query($select)->fetchAll( );
    	/*
    	if( $this->_profiling ) {
			$query = $profiler->getLastQueryProfile();
			echo 'Method: '.__METHOD__.'<br />Time: '.$query->getElapsedSecs().'<br />Query: '.$query->getQuery().'<br />';
		}
		*/
    	return $result;
    	
    }
    
    public function fetchWeekItems($ch_id, Zend_Date $start, Zend_Date $end){
    	
    	//var_dump(func_get_args());
    	
    	if(Xmltv_Config::getProfiling()) {
			$this->_db->getProfiler()->setEnabled( true );
			$profiler = $this->_db->getProfiler();
		}
		
		$select = $this->_db->select()
			->from('rtvg_programs', '*')
			->joinLeft("rtvg_programs_props", "rtvg_programs_props.`hash` = rtvg_programs.`hash`", array('actors', 'directors', 'premiere', 'live'))
			->joinLeft("rtvg_programs_descriptions", "rtvg_programs_descriptions.`hash` = rtvg_programs.`hash`", array('intro', 'body'))
			->where("rtvg_programs.`start` >= '".$start->toString('yyyy-MM-dd 00:00:00')."'")
			->where("rtvg_programs.`start` < '".$end->toString('yyyy-MM-dd 00:00:00')."'")
			->where("rtvg_programs.`ch_id` = '$ch_id'")
			->order("start", "ASC");
		
		try {
			$result = $this->_db->fetchAll($select);
		} catch (Exception $e) {
			if ($this->debug) {
				echo $e->getMessage();
				var_dump($e->getTrace());
				exit();
			}
		}
		
		if(Xmltv_Config::getProfiling()) {
			$query = $profiler->getLastQueryProfile();
			echo $query->getElapsedSecs().': '.$query->getQuery();
		}
		
		return $result;
    	
    	//die(__FILE__.': '.__LINE__);
    	
    }
	
}

