<?php

class Xmltv_Model_Programs
{
	
	public $debug=false;
	private $_table;
	private $_descriptions_table;
	private $_props_table;
	private $siteConfig;
	
	public function __construct($config=array()){
		$siteConfig = Zend_Registry::get('site_config')->site;
		$this->debug = (bool)$siteConfig->get('debug', false);
		$this->_table = new Xmltv_Model_DbTable_Programs();
		$this->_descriptions_table = new Xmltv_Model_DbTable_ProgramsDescriptions();
		$this->_properties_table = new Xmltv_Model_DbTable_ProgramsProps();
		
	}
	
	public function getByAlias($alias='', $channel_id=null, Zend_Date $date){
		
		$where = array(
			"alias LIKE '%$alias%'",
			"start >= '".$date->toString('YYYY-MM-dd HH:mm:00')."'"
		);
		if ($channel_id) {
			$where[] = "ch_id='".$channel_id."'";
		}
		
		//var_dump($where);
		//die(__FILE__.': '.__LINE__);
		
		return $this->_table->fetchRow($where, 'start DESC');
		
	}
	
	public function getCategoryForPeriod(Zend_Date $start=null, Zend_Date $end=null, $type=0){
	
		if (!is_a($start, 'Zend_Date'))
			return;
		if (!is_a($end, 'Zend_Date')) {
			$end = new Zend_Date(null, null,'ru');
		}
		
		die(__FILE__.': '.__LINE__);
			
	}
	
	public function getProgramsForDay(Zend_Date $date=null, $ch_id=null){
		
		//var_dump($date->toString('yyyy-MM-dd'));
		//die(__FILE__.': '.__LINE__);
		
		$day = $date->toString('yyyy-MM-dd');
		$rows = null;
		try {
			$rows = $this->_table->fetchDayItems($ch_id, $date->toString('yyyy-MM-dd'));
		} catch (Exception $e) {
			echo $e->getMessage();
		}
		
		if (!$rows) {
			try {
				$rows = $this->_table->fetchDayItems($ch_id, $date->toString('yyyy-MM-dd'), true);
			} catch (Exception $e) {
				echo $e->getMessage();
			}
		}
		
		//var_dump(count($rows));
		//die(__FILE__.': '.__LINE__);
		
		$list = array();
		$channel_desc=false;
		if (count($rows)>0) {
			foreach ($rows as $k=>$prog) {
				
				$prog_start = new Zend_Date($prog->start);
				$prog_end = new Zend_Date($prog->end);
				$rows[$k]->now_showing = false;
				
				$compare_start = $prog_start->compare(Zend_Date::now());
				$compare_end   = $prog_end->compare(Zend_Date::now());
				
				if (($compare_start==-1 || $compare_start==0) && ($compare_end==1 || $compare_end==0))
				$rows[$k]->now_showing = true;
				
				$rows[$k]->start_timestamp = $prog_start->toString(Zend_Date::TIMESTAMP);
				
			}
		} else {
			return;
		}
		
		return $rows;
		
	}


	public function getProgramForDay ($prog_alias=null, $channel_alias=null, Zend_Date $date) {
		
		//var_dump(func_get_args());
		//var_dump($date->toString());
		//die(__FILE__.": ".__LINE__);
		
		if(  !$prog_alias ||  !$channel_alias )
		throw new Zend_Exception("ERROR: Пропущен один или более параметров для".__METHOD__, 500);
		
		try {
			if (Xmltv_Config::getCaching()) {
				$subDir = 'Listings';
				$cache = new Xmltv_Cache(array('location'=>"/cache/$subDir"));
				$hash = $cache->getHash(__METHOD__.'_'.md5($prog_alias.$channel_alias).'_'.$date->toString('yyyyMMdd'));
				if (!$result = $cache->load($hash, 'Core', $subDir)){
					$result = $this->_table->fetchProgramThisDay($prog_alias, $channel_alias, $date);
					$cache->save($result, $hash, 'Core', $subDir);
				} 
			} else {
				$result = $this->_table->fetchProgramThisDay($prog_alias, $channel_alias, $date);
			}
		} catch (Exception $e) {
			echo $e->getMessage();
		}
		
		$actors         = array();
		$directors      = array();
		$serializer     = new Zend_Serializer_Adapter_Json();
		$actorsTable    = new Xmltv_Model_DbTable_Actors();
		$directorsTable = new Xmltv_Model_DbTable_Directors();
		
		foreach ($result as $k=>$r) {
			
			if (!empty($r->actors)) {
				$ids = $serializer->unserialize($r->actors);
				$result[$k]->actors = $actorsTable->fetchAll("`id` IN ( ".implode(',', $ids)." )")->toArray();
			}
			
			if (!empty($r->directors)) {
				$ids = $serializer->unserialize($r->directors);
				$result[$k]->directors = $directorsTable->fetchAll("`id` IN ( ".implode(',', $ids)." )")->toArray();
			}
			
		}
		
		return $result;
		
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param string $program_alias
	 * @param Zend_Date $date
	 */
	public function getSimilarProgramsThisWeek($program_alias='', Zend_Date $date){
		
		//var_dump(func_get_args());
		//die(__FILE__.': '.__LINE__);
		
		if (empty($program_alias)) {
			throw new Zend_Exception(__METHOD__." - Empty program alias");
			return false;
		}
		$a = array();
		if (strstr($program_alias, '-'))
			$a = explode('-', $program_alias);
		else 
			$a[]=$program_alias;
		
		//var_dump(func_get_args());
		//die(__FILE__.': '.__LINE__);	
			
		return $this->_table->fetchSimilarProgramsThisWeek($a, $date);
		
	}
	
	public function getProgramThisWeek ($prog_alias=null, $channel_id=null, Zend_Date $date) {
		
		if(  !$prog_alias ||  !$channel_id )
			throw new Zend_Exception("ERROR: Пропущен один или более параметров для".__METHOD__, 500);
		
		if (!$date)
			$date = new Zend_Date(null, null, 'ru');
		
		$result = $this->_table->fetchProgramThisWeek($prog_alias, $channel_id, $date);
		
		$actors         = array();
		$directors      = array();
		$serializer     = new Zend_Serializer_Adapter_Json();
		$actorsTable    = new Xmltv_Model_DbTable_Actors();
		$directorsTable = new Xmltv_Model_DbTable_Directors();
		
		foreach ($result as $k=>$r) {
			
			if (!empty($r->actors)) {
				$ids = $serializer->unserialize($r->actors);
				$r->actors = $actorsTable->fetchAll("`id` IN ( ".implode(',', $ids)." )")->toArray();
			}
			
			if (!empty($r->directors)) {
				$ids = $serializer->unserialize($r->directors);
				$r->directors = $directorsTable->fetchAll("`id` IN ( ".implode(',', $ids)." )")->toArray();
			}
			
		}
		
		//var_dump($result);
		//die(__FILE__.': '.__LINE__);
		
		return $result;
		
	}


	public function getPremieres (Zend_Date $start, Zend_Date $end) {

		if( !is_a( $end, 'Zend_Date' ) )
		return;
		
		if( !is_a( $start, 'Zend_Date' ) )
		$start = new Zend_Date();
		
		//var_dump(Xmltv_Config::getCaching());
		//die(__FILE__.": ".__LINE__);
		
		if (Xmltv_Config::getCaching()) {
			$cache = new Xmltv_Cache();
			$hash = $cache->getHash(__METHOD__);
			if (!$r = $cache->load($hash, 'Function')){
				$r = $this->_table->getPremieres($start, $end);
				$cache->save($r, $hash, 'Function');
			} 
		} else {
			$r = $this->_table->getPremieres($start, $end);
		}
		
		//var_dump($r);
		//die(__FILE__.": ".__LINE__);
		
		return $r;
	
	}
	
	private function makePersonName($info=null){
	
	}
	
	public function getItemsCount(){
		return $this->_table->getCount();
	}

	public function addHit($target=null){
		$table = new Xmltv_Model_DbTable_ProgramsRatings();
		try {
			$table->addHit($target);
		} catch (Exception $e) {
			echo $e->getMessage();
			if (Xmltv_Config::getDebug())
			Zend_Debug::dump(func_get_args());
		}
		
	}
	
	
	public function getWeekDates($week_start=null, $week_end=null){
	
		$result = array('start', 'end');
		
		if (!$week_start)
		$d = new Zend_Date();
		
		do{
			if ($d->toString(Zend_Date::WEEKDAY_DIGIT, 'ru')>1)
			$d->subDay(1);
			//var_dump($d->toString(Zend_Date::WEEKDAY_DIGIT, 'ru'));
		} while ($d->toString(Zend_Date::WEEKDAY_DIGIT, 'ru')>1);
		$result['start'] = $d;
		
		if (!$week_end)
		$d = new Zend_Date();
		else
		$d = new Zend_Date($week_end);
		
		do{
			$d->addDay(1);
		} while ($d->toString(Zend_Date::WEEKDAY_DIGIT, 'ru')!=1);
		$result['end'] = $d;
		
		return $result;
		
	}
	
}

