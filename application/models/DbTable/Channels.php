<?php
/**
 * Database table for channels model
 *
 * @uses Zend_Db_Table_Abstract
 * @version $Id: Channels.php,v 1.7 2012-05-27 20:05:50 dev Exp $
 */
class Xmltv_Model_DbTable_Channels extends Zend_Db_Table_Abstract
{

    protected $_name = 'rtvg_channels';
    private $_debug;
	private $_profiling;
	private $_profiler;
	
	const FETCH_MODE = Zend_Db::FETCH_OBJ;
	
    public function __construct($config=array()) {
    	
    	parent::__construct(array('name'=>$this->_name));
    	
    	$this->_debug     = Xmltv_Config::getDebug();
		$this->_profiling = Xmltv_Config::getProfiling();
		
    }
    
    public function getTypeheadItems(){
    	
    	try {
    		$select = $this->_db->select()->from($this->_name, array( 'title' ));
    		$result = $this->_db->query($select)->fetchAll(self::FETCH_MODE);
    	} catch (Exception $e) {
    		echo $e->getMessage();
    	}
    	return $result;
    	
    }
    
    public function getFeatured($order=null, $total=20, $by_hits=true){
    	
    	if (!$order)
    	$order='ch_id';
    	
    	$this->_initProfiler();
    	
    	try {
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
	    	
	    	$result = $this->_db->query($select)->fetchAll(self::FETCH_MODE);
    	} catch (Exception $e) {
    		$e->getMessage();
    	}
    	
    	$this->_profileQuery();
		
		return $result;
    	
    }
    
    public function fetchCategory($alias=null){
    	
    	if (!$alias)
		throw new Zend_Exception("Не указан один или более параметров для ".__FUNCTION__, 500);
		
    	$this->_initProfiler();
		
    	$select = $this->_db->select()
    		->from( $this->_name, '*' )
    		->joinLeft('rtvg_channels_categories', "$this->_name.`category`=rtvg_channels_categories.`id`", array());
    	$select->where( "rtvg_channels_categories.`alias`='$alias'" );
    	$select->order("$this->_name.title ASC");
    	
    	$result = $this->_db->query($select)->fetchAll( );
    	
    	$this->_profileQuery();
		
    	return $result;
    	
    }
    
    public function fetchWeekItems($ch_id, Zend_Date $start, Zend_Date $end){
    	
    	$this->_initProfiler();
		$days = array();
		do{
			$select = $this->_db->select()
				->from('rtvg_programs', '*')
				->joinLeft("rtvg_programs_props", "rtvg_programs_props.`hash` = rtvg_programs.`hash`", array('actors', 'directors', 'premiere', 'live'))
				->joinLeft("rtvg_programs_descriptions", "rtvg_programs_descriptions.`hash` = rtvg_programs.`hash`", array('intro', 'body'))
				->where("rtvg_programs.`start` LIKE '%".$start->toString('yyyy-MM-dd')."%'")
				->where("rtvg_programs.`ch_id` = '$ch_id'")
				->order("start", "ASC");
			
			try {
				$days[$start->toString('U')] = $this->_db->fetchAll($select, null, self::FETCH_MODE);
			} catch (Exception $e) {
				if ($this->debug) {
					echo $e->getMessage();
					var_dump($e->getTrace());
					exit();
				}
			}
			
			$this->_profileQuery();
			$start->addDay(1);
			
		} while ($start->toString('yyyy-MM-dd')!=$end->toString('yyyy-MM-dd'));
		
		foreach ($days as $day) {
			foreach ($day as $program) {
				$program->start = new Zend_Date($program->start);
				$program->end   = new Zend_Date($program->end);
			}
		}
				
		return $days;
    	
    }
    
    private function _initProfiler(){
    	
    	if( $this->_profiling ) {
			$this->_db->getProfiler()->setEnabled( true );
			$this->_profiler = $this->_db->getProfiler();
		}
    	
    }
    
    private function _profileQuery(){
    	
    	if( $this->_profiling ) {
			$query = $this->_profiler->getLastQueryProfile();
			echo 'Method: '.__METHOD__.'<br />Time: '.$query->getElapsedSecs().'<br />Query: '.$query->getQuery().'<br />';
		}
    	
    }
	
}

