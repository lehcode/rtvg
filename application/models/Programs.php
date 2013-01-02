<?php

class Xmltv_Model_Programs
{
	
	private $_table;
	private $_descriptions_table;
	private $_props_table;
	/**
	 * Database adapter
	 * @var Zend_Db_Adapter_Mysqli
	 */
	protected $archiveDb;
	/**
	 * Application config
	 * @var Zend_Config_Ini
	 */
	protected $appConfig;
	/**
	 * Site config
	 * @var Zend_Config_Ini
	 */
	protected $siteConfig;
	
	const ERR_MISSING_PARAMS="Пропущены необходимые параметры!";
	
	/**
	 * Constructor
	 * @param array $config
	 */
	public function __construct($config=array()){
		
		if (isset($config['app_config']) && is_a($config['app_config'], 'Zend_Config_Ini')){
			$this->appConfig = $config['app_config'];
		} else {
			$this->appConfig = Zend_Registry::get('app_config');
		}
		if (isset($config['site_config']) && is_a($config['site_config'], 'Zend_Config_Ini')){
			$this->siteConfig = $config['site_config'];
		} else {
			$this->siteConfig = Zend_Registry::get('site_config');
		}
		
		$this->_table = new Xmltv_Model_DbTable_Programs();
		$this->_descriptions_table = new Xmltv_Model_DbTable_ProgramsDescriptions();
		$this->_properties_table = new Xmltv_Model_DbTable_ProgramsProps();
		
		$this->archiveDb = new Zend_Db_Adapter_Mysqli( $this->appConfig->get('resources')->multidb->get('archive') );
		
	}
	
	public function getByAlias($alias='', $channel_id=null, $date){
		
	    if (!$alias || !$channel_id)
	        throw new Zend_Exception(self::ERR_MISSING_PARAMS, 500);
	    
	    $where = array(
			"`alias` LIKE '%$alias%'",
			"`start` >= '".$date->toString('YYYY-MM-dd 00:00:00')."'"
		);
		
		if ($channel_id)
			$where[] = "`ch_id`='".$channel_id."'";
		
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
		exit();
		//die(__FILE__.': '.__LINE__);
			
	}
	
	/**
	 * 
	 * Расписание программ на день для определенного канала
	 * 
	 * @param Zend_Date $date
	 * @param int $ch_id
	 */
	public function getProgramsForDay(Zend_Date $date=null, $ch_id=null){
		
	    $categoriesTable = new Xmltv_Model_DbTable_ProgramsCategories();
	    $cats = $categoriesTable->fetchAll(); 
	    
		if ($date->isToday()){
			try {
				$rows = $this->_table->fetchDayItems($ch_id, $date->toString('yyyy-MM-dd'), $cats);
			} catch (Zend_Db_Table_Exception $e) {
				throw new Zend_Exception($e->getMessage(), $e->getCode());
			}
			//var_dump(count($rows));
			//die(__FILE__.': '.__LINE__);
			if (!count($rows))
				return null;
			
		} else {
			
			$l = (int)Zend_Registry::get('site_config')->listings->history->get('length');
			$maxAgo = new Zend_Date( Zend_Date::now()->subDay($l)->toString('U'), 'U' ) ;
			
			if ($date->compare($maxAgo)==-1){ //More than 2 weeks
				try {
					$rows = $this->_table->fetchDayItems($ch_id, $date->toString('yyyy-MM-dd'), $cats, true);
				} catch (Zend_Db_Table_Exception $e) {
					throw new Zend_Exception($e->getMessage(), $e->getCode());
				}
			} else { //Less or equal than 2 weeks
				try {
					$rows = $this->_table->fetchDayItems($ch_id, $date->toString('yyyy-MM-dd'), $cats);
				} catch (Zend_Db_Table_Exception $e) {
					throw new Zend_Exception($e->getMessage(), $e->getCode());
				}
			}
			if (!count($rows))
				return null;
			
		}
		
		//var_dump(count($rows));
		//die(__FILE__.': '.__LINE__);
		
		$list = array();
		$channel_desc=false;
		if (count($rows)>0) {
			foreach ($rows as $k=>$prog) {
				
				$prog_start = new Zend_Date($prog->start);
				$prog_end   = new Zend_Date($prog->end);
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
		
		//var_dump($rows);
		//die(__FILE__.': '.__LINE__);
		
		return $rows;
		
	}


	/**
	 * Телепередача в указанный день на канале
	 * 
	 * @param  string $prog_alias
	 * @param  string $channel_alias
	 * @param  Zend_Date $date
	 * @throws Zend_Exception
	 * @return array
	 */
	public function getProgramForDay ($prog_alias=null, $channel_alias=null, Zend_Date $date) {
		
		//var_dump(func_get_args());
		//var_dump($date->toString());
		//die(__FILE__.": ".__LINE__);
		
		if(  !$prog_alias ||  !$channel_alias )
			throw new Zend_Exception("ERROR: Пропущен один или более параметров для".__METHOD__, 500);
		
		$result = $this->_table->fetchProgramThisDay($prog_alias, $channel_alias, $date);
		$actors		    = array();
		$directors	    = array();
		$serializer	    = new Zend_Serializer_Adapter_Json();
		$actorsTable	= new Xmltv_Model_DbTable_Actors();
		$directorsTable = new Xmltv_Model_DbTable_Directors();
		
		foreach ($result as $k=>$r) {
			
			if (!empty($r->actors)) {
				if (stristr($r->actors, '[')) {
					$ids = $serializer->unserialize($r->actors);
					$result[$k]->actors = $actorsTable->fetchAll("`id` IN ( ".implode(',', $ids)." )")->toArray();
				} else {
					$result[$k]->actors = $actorsTable->fetchAll("`id` IN ( ".$r->actors." )")->toArray();
				}
				
			}
			
			if (!empty($r->directors)) {
				if (stristr($r->directors, '[')) {
					$ids = $serializer->unserialize($r->directors);
					$result[$k]->directors = $directorsTable->fetchAll("`id` IN ( ".implode(',', $ids)." )")->toArray();
				} else {
					$result[$k]->directors = $directorsTable->fetchAll("`id` IN ( ".$r->directors." )")->toArray();
				}
				
			}
			
		}
		
		return $result;
		
	}
	
	/**
	 * 
	 * Подобные программы на этой неделе
	 * 
	 * @param string $program_alias
	 * @param Zend_Date $date
	 */
	public function getSimilarProgramsThisWeek($program_alias=null, Zend_Date $start, Zend_Date $end){
		
		if (!$program_alias)
			throw new Zend_Exception("Empty program alias!", 500);
		
		$result = $this->_table->fetchSimilarProgramsThisWeek($program_alias, $start, $end);
		foreach ($result as $item){
			$item->start = new Zend_Date($item->start, 'yyyy-MM-dd HH:mm:ss');
			$item->end   = new Zend_Date($item->end, 'yyyy-MM-dd HH:mm:ss');
		}
		
		return $result;
		
	}
	
	public function getProgramThisWeek ($prog_alias=null, $channel_id=null, Zend_Date $start, Zend_Date $end) {
		
		if( !$prog_alias || !$channel_id || !$start || !$end )
			return false;
		
		$result = $this->_table->fetchProgramThisWeek($prog_alias, $channel_id, $start, $end);
		
		$actors		    = array();
		$directors	    = array();
		$serializer	    = new Zend_Serializer_Adapter_Json();
		$actorsTable	= new Xmltv_Model_DbTable_Actors();
		$directorsTable = new Xmltv_Model_DbTable_Directors();
		
		foreach ($result as $k=>$r) {
			
			if (!empty($r->actors)) {
				if (strstr($r->actors, '[')) {
					$ids = $serializer->unserialize($r->actors);
					$r->actors = $actorsTable->fetchAll("`id` IN ( ".implode(',', $ids)." )")->toArray();
				} else {
					$r->actors = $actorsTable->fetchAll("`id` IN ( ".$r->actors." )")->toArray();
				}
			}
			
			if (!empty($r->directors)) {
				if (strstr($r->directors, '[')) {
					$ids = $serializer->unserialize($r->directors);
					$r->directors = $directorsTable->fetchAll("`id` IN ( ".implode(',', $ids)." )")->toArray();
				} else {
					$r->directors = $directorsTable->fetchAll("`id` IN ( ".$r->directors." )")->toArray();
				}
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

	public function addHit($program=null){
		
		if (!is_object($program))
			throw new Zend_Exception(__METHOD__.': '.__LINE__." - Неверный формат!");
		
		try {
			$table = new Xmltv_Model_DbTable_ProgramsRatings();
			$table->addHit($program->hash, $program->ch_id);
		} catch (Exception $e) {
			throw new Zend_Exception($e->getMessage(), $e->getCode(), $e);
		}
		
		return true;
		
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

