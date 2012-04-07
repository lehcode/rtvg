<?php
defined('APP_STARTED') or die();
class Admin_Model_DbTable_Programs extends Zend_Db_Table_Abstract
{

    protected $_name = 'rtvg_programs';
    protected $_profiling = false;
    
    public function __construct($config=array()){
    	parent::__construct( $config );
    	$this->_profiling = Xmltv_Config::getProfiling();
    	
    }

    public function getProgramsCountForWeek(Zend_Date $start=null, Zend_Date $end=null){
    	
    	//var_dump($this->_db);
    	
   		if( $this->_profiling ) {
			$this->_db->getProfiler()->setEnabled( true );
			$profiler = $this->_db->getProfiler();
		}
    	
		if (!$start && !$end) {
			$d = new Zend_Date(null, null, 'ru');
			do{
				$d->subDay(1);
			} while ($d->toString(Zend_Date::WEEKDAY_DIGIT, 'ru')>1);
			$weekStart = $d;
			
			$d = new Zend_Date(null, null, 'ru');
			do{
				$d->addDay(1);
			} while ($d->toString(Zend_Date::WEEKDAY_DIGIT, 'ru')>0);
			$weekEnd = $d;
		} else {
			$weekStart = $start;
			$weekEnd = $end;
		}
		
    	$select = $this->_db->select();
		$select->from($this->_name, array('count(*) as amount'))
			->where("`start`>='".$weekStart->toString('yyyy-MM-dd')."' AND `end`<='".$weekEnd->toString('yyyy-MM-dd')."'");
		try {
			$result = $this->_db->fetchAll($select);
		} catch (Exception $e) {
			echo $e->getMessage();
			
			//var_dump($e->getTrace());
			die(__FILE__.': '.__LINE__);
			
		}
		
    	if( $this->_profiling ) {
			$query = $profiler->getLastQueryProfile();
			echo 'Method: '.__METHOD__.'<br />Query: '.$query->getQuery().'<br />';
		}
		//var_dump($result);
		return (int)$result[0]['amount'];
    }

}

