<?php

class Admin_Model_DbTable_ProgramsArchive extends Zend_Db_Table_Abstract
{

    protected $_name='programs';
    
    const FETCH_MODE = Zend_Db::FETCH_OBJ;
    
    /**
     * 
     * Constructor
     * @param array $config
     */
    public function __construct($config=array()){
		
    	$this->_db = new Zend_Db_Adapter_Mysqli( Zend_Registry::get('app_config')->resources->multidb->get('archive') );
    	$pfx = Zend_Registry::get('app_config')->resources->multidb->archive->get('tbl_prefix');
    	$this->_name = $pfx.$this->_name;
    	
    }

    public function getProgramsCountForWeek(Zend_Date $start=null, Zend_Date $end=null){
    	
    	if (!$start && !$end) {
			$d = new Zend_Date();
			do{
				if ($d->toString(Zend_Date::WEEKDAY_DIGIT, 'ru')!=1) {
					$d->subDay(1);
				}
			} while ($d->toString(Zend_Date::WEEKDAY_DIGIT, 'ru')!=1);
			$weekStart = $d;
			
			$d = new Zend_Date();
			do{
				if ($d->toString(Zend_Date::WEEKDAY_DIGIT, 'ru')!=0) {
					$d->addDay(1);
				}
			} while ($d->toString(Zend_Date::WEEKDAY_DIGIT, 'ru')!=0);
			$weekEnd = $d;
		} else {
			$weekStart = $start;
			$weekEnd = $end;
		}
		
    	$select = $this->_db->select();
		$select->from($this->_name, array('count(*) as amount'))
			->where("`start`>='".$weekStart->toString('yyyy-MM-dd')."' AND `start`<'".$weekEnd->toString('yyyy-MM-dd')." 23:59:59'");
		try {
			$result = $this->_db->fetchAll($select);
		} catch (Exception $e) {
			echo $e->getMessage();
			
			//var_dump($e->getTrace());
			die(__FILE__.': '.__LINE__);
			
		}
		/*
    	if( $this->_profiling ) {
			$query = $profiler->getLastQueryProfile();
			echo 'Method: '.__METHOD__.'<br />Query: '.$query->getQuery().'<br />';
		}
		*/
		//var_dump($result);
		return (int)$result[0]['amount'];
    }

    public function deleteProgramsWithInfo(Zend_Date $start, Zend_Date $end){
    	
    	$props = new Admin_Model_DbTable_ProgramsProps();
    	$descs = new Admin_Model_DbTable_ProgramsDescriptions();
    	$ratings = new Zend_Db_Table(array('name'=>'rtvg_programs_ratings'));
    	
    	//var_dump($ratings->fetchAll());
    	//die(__FILE__.': '.__LINE__);
    	
    	$programs = $this->fetchAll(array(
    		"`start`>='".$start->toString('yyyy-MM-dd')." 00:00:00'",
			"`start` < '".$end->toString('yyyy-MM-dd')." 23:59:59'"
    	));
    	foreach ($programs as $item) {
    		
    		if ($f = $descs->find($item['hash']))
    		$descs->delete("`hash`='".$item['hash']."'");
    		
    		if ($p = $props->find($item['hash']))
    		$props->delete("`hash`='".$item['hash']."'");
    		
    		if ($r = $ratings->find($item['alias']))
    		$ratings->delete("`alias`='".$item['alias']."'");
    		
    		$this->delete("`hash`='".$item['hash']."'" );
    		
    		//var_dump($d->toArray());
    		//die(__FILE__.': '.__LINE__);
    		
    	}
    	
    	//die(__FILE__.': '.__LINE__);
    	
    }
    
    public function fetchPremieres(Zend_Date $start, Zend_Date $end){
    	
    	if( $this->_profiling ) {
			$this->_db->getProfiler()->setEnabled( true );
			$profiler = $this->_db->getProfiler();
		}
		
		$select = $this->_db->select()->from(array('prog'=>$this->_name), '*')
			->where("prog.`start` >= '".$start->toString('yyyy-MM-dd')." 00:00:00'")
			->where("prog.`start` < '".$end->toString('yyyy-MM-dd')." 23:59:59'")
			->joinLeft(array('d'=>'rtvg_programs_descriptions'), "prog.`hash` = d.`hash`", array('desc_intro'=>'intro', 'desc_body'=>'body'))
			->joinLeft(array('prop'=>'rtvg_programs_props'), "prog.`hash`=prop.`hash`", array())
			->where("prog.`title` LIKE '%премьера%'")
			->order("prog.ch_id ASC")->order("prog.start ASC");
		
			try {
				$result = $this->_db->query( $select )->fetchAll( self::FETCH_MODE );
			} catch (Exception $e) {
				echo $e->getMessage();
			}
		
		
    	if( $this->_profiling ) {
			$query = $profiler->getLastQueryProfile();
			echo 'Method: '.__METHOD__.'<br />Time: '.$query->getElapsedSecs().'<br />Query: '.$query->getQuery().'<br />';
		}
		
		return $result;
    	
    }
    
    public function fetchProgramsForPeriod(Zend_Date $start, Zend_Date $end, $category=null){
    	
    	if( $this->_profiling ) {
			$this->_db->getProfiler()->setEnabled( true );
			$profiler = $this->_db->getProfiler();
		}
    	
		$select = $this->_db->select()
			->from(array('prog'=>$this->_name), '*')
			->where("prog.`start` >= '".$start->toString('yyyy-MM-dd')." 00:00:00'")
			->where("prog.`start` < '".$end->toString('yyyy-MM-dd')." 23:59:59'")
			->joinLeft(array('d'=>'rtvg_programs_descriptions'), "prog.hash = d.hash", array('desc_intro'=>'intro', 'desc_body'=>'body'))
			->joinLeft(array('prop'=>'rtvg_programs_props'), "prog.`hash`=prop.`hash`", array());
		
		if (!empty($category)) {
			
			if (is_array($category))
			$where = "prog.`category` IN ( " . implode(',', $category) . " )";
			else
			$where = "prog.`category` = '$category'";
			
			$select->where($where);
		}
		
		$select->order("prog.ch_id ASC")->order("prog.start ASC");
		$result = $this->_db->query( $select )->fetchAll( self::FETCH_MODE );
    	
    	if( $this->_profiling ) {
			$query = $profiler->getLastQueryProfile();
			echo 'Method: '.__METHOD__.'<br />Time: '.$query->getElapsedSecs().'<br />Query: '.$query->getQuery().'<br />';
		}
		
    	//var_dump(count($result));
    	//die(__FILE__.': '.__LINE__);
    	
    	return $result;
    }
    
    public function fetchSeries(Zend_Date $start, Zend_Date $end, $category=null, $channels=null){
    	
		if( $this->_profiling ) {
			$this->_db->getProfiler()->setEnabled( true );
			$profiler = $this->_db->getProfiler();
		}
    	
		$select = $this->_db->select()
			->from(array('prog'=>$this->_name), '*')
			->where("prog.`start` >= '".$start->toString('yyyy-MM-dd')." 00:00:00'")
			->where("prog.`start` < '".$end->toString('yyyy-MM-dd')." 23:59:59'")
			->joinLeft(array('d'=>'rtvg_programs_descriptions'), "prog.hash = d.hash", array('desc_intro'=>'intro', 'desc_body'=>'body'))
			->joinLeft(array('prop'=>'rtvg_programs_props'), "prog.`hash`=prop.`hash`", array());
		
		if (!empty($category)) {
			
			if (is_array($category)) {
				$where = "prog.`category` IN ( " . implode(',', $category) . " ) OR prog.`title` LIKE '%сериал%'";
			} else {
				$where = "prog.`category` = '$category' OR prog.`title` LIKE '%сериал%'";
			}
		} else {
			$where = "prog.`title` LIKE '%сериал%'";
		}
		
    	if (!empty($channels)) {
			$ids = count($channels)>1 ? implode(',', $channels) : $channels[0];
			$where .= " OR prog.`ch_id` IN ( $ids ) ";
		}
		
		$select->where($where);
		$select->order("prog.ch_id ASC")->order("prog.start ASC");
		
		$result = $this->_db->query( $select )->fetchAll( self::FETCH_MODE );
    	
    	if( $this->_profiling ) {
			$query = $profiler->getLastQueryProfile();
			echo 'Method: '.__METHOD__.'<br />Time: '.$query->getElapsedSecs().'<br />Query: '.$query->getQuery().'<br />';
		}
		//var_dump(count($result));
    	//die(__FILE__.': '.__LINE__);
		return $result;
    	
    }
    
    public function fetchMovies(Zend_Date $start, Zend_Date $end, $category=null, $channels=null){
    	
		if( $this->_profiling ) {
			$this->_db->getProfiler()->setEnabled( true );
			$profiler = $this->_db->getProfiler();
		}
    	
		$select = $this->_db->select()
			->from(array('prog'=>$this->_name), '*')
			->where("prog.`start` >= '".$start->toString('yyyy-MM-dd')." 00:00:00'")
			->where("prog.`start` < '".$end->toString('yyyy-MM-dd')." 23:59:59'")
			->joinLeft(array('d'=>'rtvg_programs_descriptions'), "prog.hash = d.hash", array('desc_intro'=>'intro', 'desc_body'=>'body'))
			->joinLeft(array('prop'=>'rtvg_programs_props'), "prog.`hash`=prop.`hash`", array());
		
		if (!empty($category)) {
			
			if (is_array($category)) {
				$where = "prog.`category` IN ( " . implode(',', $category) . " ) OR prog.`title` LIKE '%фильм%'";
			} else {
				$where = "prog.`category` = '$category' OR prog.`title` LIKE '%фильм%'";
			}
		} else {
			$where = "prog.`title` LIKE '%фильм%'";
		}
		
    	if (!empty($channels)) {
			$ids = count($channels)>1 ? implode(',', $channels) : $channels[0];
			$where .= " OR prog.`ch_id` IN ( $ids ) ";
		}
		
		$select->where($where);
		$select->order("prog.ch_id ASC")->order("prog.start ASC");
		
		$result = $this->_db->query( $select )->fetchAll( self::FETCH_MODE );
    	
    	if( $this->_profiling ) {
			$query = $profiler->getLastQueryProfile();
			echo 'Method: '.__METHOD__.'<br />Time: '.$query->getElapsedSecs().'<br />Query: '.$query->getQuery().'<br />';
		}
		//var_dump(count($result));
    	//die(__FILE__.': '.__LINE__);
		return $result;
    	
    }
}

