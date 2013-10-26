<?php

class Admin_Model_DbTable_Programs extends Xmltv_Model_DbTable_Programs
{
	
    const FETCH_MODE = Zend_Db::FETCH_OBJ;
    
    public function __construct($config=array()){
    	
    	parent::__construct();
		
    	if (isset($config['tbl_prefix'])) {
    		$pfx = (string)$config['tbl_prefix'];
    	} else {
    		$pfx = Zend_Registry::get('app_config')->resources->multidb->local->get('tbl_prefix', 'rtvg_');
    	}
    	
    	$this->setName($this->_name);
    	
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
			die(__FILE__.': '.__LINE__);
			
		}
				
		return (int)$result[0]->amount;
    }

    public function deleteProgramsLinked($start=null, $end=null){
    	
        if ($start && $end) {
            
	    	$propsTable   = new Admin_Model_DbTable_ProgramsProps();
	    	$descsTable   = new Admin_Model_DbTable_ProgramsDescriptions();
	    	$ratingsTable = new Admin_Model_DbTable_ProgramsRatings();
	    	$select = $this->_db->select()
	    		->from(array('prog'=>$this->getName()), array('hash', 'alias'))
	    		->where("`prog`.`start`>='$start' AND `prog`.`start` < '$end'");
	    	
	    	
	    	$result = $this->_db->fetchAll($select);
	    	foreach ($result as $item) {
	    		
	    		if ($descsTable->find($item->hash))
	    			$descsTable->delete("`hash`='".$item->hash."'");
	    		
	    		if ($propsTable->find($item->hash))
	    			$propsTable->delete("`hash`='".$item->hash."'");
	    		
	    		if ($ratingsTable->find($item->hash))
	    			$ratingsTable->delete("`hash`='".$item->hash."'");
	    		
	    		$this->delete("`hash`='".$item->hash."'" );
	    		
	    	}
        }
    	
    }
    
    public function fetchPremieres(Zend_Date $start, Zend_Date $end){
    	
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
		
		
    	return $result;
    	
    }
    
    public function fetchProgramsForPeriod(Zend_Date $start, Zend_Date $end, $category=null){
    	
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
    	
    	return $result;
    }
    
    public function fetchSeries(Zend_Date $start, Zend_Date $end, $category=null, $channels=null){
    	
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
    	
    	return $result;
    	
    }
    
    public function fetchMovies(Zend_Date $start, Zend_Date $end, $category=null, $channels=null){
    	
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
    	
    	return $result;
    	
    }

}

