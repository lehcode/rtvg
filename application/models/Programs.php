<?php
/**
 * Programs listings functionality
 *
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: Programs.php,v 1.14 2013-01-19 10:11:13 developer Exp $
 *
 */
class Xmltv_Model_Programs extends Xmltv_Model_Abstract
{
	
	protected $weekDays;
	protected static $videoCache=false;
	
	const ERR_MISSING_PARAMS="Пропущены необходимые параметры!";
	
	/**
	 * Constructor
	 * @param array $config
	 */
	public function __construct($config=array()){
		
	    $config['db'] = Zend_Registry::get('app_config')->resources->multidb->local;
        parent::__construct($config);
        
        if (isset($config['video_cache']) && is_bool($config['video_cache'])){
            self::$videoCache = $config['video_cache'];
        }
        
	    $this->table = new Xmltv_Model_DbTable_Programs();
	    $this->weekDays = isset($config['week_days']) ? $config['week_days'] : null ;
			
	}
	
	/**
	 * Поиск передачи по ее псевдониму и номеру канала,
	 * начиная с указанных даты/времени до конца дня
	 * 
	 * @param  string    $alias
	 * @param  int       $channel_id
	 * @param  Zend_Date $date
	 * @throws Zend_Exception
	 */
	
	public function getSingle($alias='', $channel_id=null, Zend_Date $date){
		
	    if (!$alias || !$channel_id)
	        throw new Zend_Exception(self::ERR_MISSING_PARAMS, 500);
	    
	    $progStart = new Zend_Date( $date->toString() );
	    $where = array(
			"`prog`.`alias` LIKE '%$alias%'",
			"`prog`.`start` >= '".$date->toString('YYYY-MM-dd 00:00:00')."'",
			"`prog`.`start` < '".$date->toString('YYYY-MM-dd 23:59:59')."'",
		);
		
		if ($channel_id){
			$where[] = "`prog`.`ch_id`='".$channel_id."'";
		}
		
		$where = count($where) ? implode(' AND ', $where) : '' ;
		$select = $this->db->select()
			->from(array('prog'=>$this->table->getName()), '*')
			->where($where)
			->order('start DESC');
		
		if (APPLICATION_ENV=='development') {
			parent::debugSelect($select, __METHOD__);
			//die(__FILE__.': '.__LINE__);
		}
		
		$result = $result = $this->db->fetchRow($select);
		if (!$result)
		return array();
		else
		return $result;
		
	}
	
	/**
	 * Поиск передачи по ее псевдониму и номеру канала,
	 * начиная с указанных даты/времени до конца дня
	 * 
	 * @param  string    $alias
	 * @param  int       $channel_id
	 * @param  Zend_Date $date
	 * @throws Zend_Exception
	 */
	
	public function getProgramThisDay($alias='', $channel_id=null, Zend_Date $date){
		
	    if (!$alias || !$channel_id)
	        throw new Zend_Exception(self::ERR_MISSING_PARAMS, 500);
	    
	    $progStart = new Zend_Date( $date->toString() );
	    //var_dump($progStart->toString());
	    //die(__FILE__.': '.__LINE__);
	    $where = array(
			"prog.alias LIKE '%$alias%'",
			"prog.start >= '".$date->toString('YYYY-MM-dd hh:MM:00')."'",
			"prog.end < '".$date->toString('YYYY-MM-dd 23:59:59')."'",
		);
		
		if ($channel_id){
			$where[] = "prog.ch_id='".$channel_id."'";
		}
		
		$where = count($where) ? implode(' AND ', $where) : '' ;
		$select = $this->db->select()
			->from(array('prog'=>$this->table->getName()), '*')
			->where($where)
			->order('start DESC');
		
		if (APPLICATION_ENV=='development') {
			var_dump($select->assemble());
			//die(__FILE__.': '.__LINE__);
		}
		
		$result = $result = $this->db->fetchAll($select);
		return $result[0];
		
	}
	
	
	public function getCategoryForPeriod(Zend_Date $start=null, Zend_Date $end=null, $type=0){
	
		if (!is_a($start, 'Zend_Date'))
			return;
		if (!is_a($end, 'Zend_Date')) {
			$end = new Zend_Date(null, null,'ru');
		}
		//exit();
		die(__FILE__.': '.__LINE__);
			
	}
	
	/**
	 * 
	 * Расписание программ на день для определенного канала
	 * 
	 * @param Zend_Date $date
	 * @param int $ch_id
	 */
	public function getProgramsForDay(Zend_Date $date=null, $ch_id=null){
	    
	    $table = new Xmltv_Model_DbTable_ProgramsCategories();
	    $cats = $table->fetchAll();
	    
	    if ($date->isToday()){
			
	        $rows = $this->table->fetchDayItems( $ch_id, $date->toString('yyyy-MM-dd'), $cats);
	        if (!count($rows))
				return null;
			
		} else {
		    
		    $l = (int)Zend_Registry::get('site_config')->listings->history->get('length');
			$maxAgo = new Zend_Date( Zend_Date::now()->subDay($l)->toString('U'), 'U' ) ;
			
			if ($date->compare($maxAgo)==-1){ //More than x days
				$rows = $this->table->fetchDayItems( $ch_id, $date->toString('yyyy-MM-dd'), $cats, true);
			} else { //Less or equal than x days
				$rows = $this->table->fetchDayItems( $ch_id, $date->toString('yyyy-MM-dd'), $cats);
			}
			
			if (!count($rows))
				return null;
			
		}
		
		//var_dump(count($rows));
		//die(__FILE__.': '.__LINE__);
		
		$list = array();
		$channel_desc=false;
		if (count($rows)>0) {
		    $video = false;
			foreach ($rows as $k=>$prog) {
				
				$prog_start = new Zend_Date($prog->start);
				$prog_end   = new Zend_Date($prog->end);
				$rows[$k]->now_showing = false;
				
				$compare_start = $prog_start->compare(Zend_Date::now());
				$compare_end   = $prog_end->compare(Zend_Date::now());
				
				if (($compare_start==-1 || $compare_start==0) && ($compare_end==1 || $compare_end==0)){
					$rows[$k]->now_showing = true;
					$rows[$k]->fetch_video = true;
					$video = true;
				}
				
				$rows[$k]->fetch_video = $video;
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
		
		$result = $this->table->fetchProgramThisDay($prog_alias, $channel_alias, $date);
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
	public function getSimilarProgramsThisWeek($program_alias=null, Zend_Date $start, Zend_Date $end, $orig_channel=null){
		
		if (!$program_alias || !$orig_channel)
			throw new Zend_Exception( parent::ERR_WRONG_PARAMS.__METHOD__, 500);
		
		$parts = explode('-', $program_alias);
	    foreach ($parts as $a){
	        if (Xmltv_String::strlen($a)>3){
	            $regex = ".*".$a."[ ]?.*";
	        	$pieces[]=" `prog`.`alias` REGEXP('$regex')";
	        }
	    }
	    $like = implode(' OR ', $pieces);
	    
	    $select = $this->db->select()
			->from(array( 'prog'=>$this->table->getName()), '*')
			->joinLeft( array('ch'=>$this->channelsTable->getName() ), "`prog`.`ch_id`=`ch`.`ch_id`", array(
				'channel_title'=>'title',
				'channel_alias'=>'LOWER(`ch`.`alias`)',
				'channel_icon'=>'ch.icon'))
	    	->joinLeft( array('cat'=>$this->categoriesTable->getName()), "`prog`.`category`=`cat`.`id`", array(
	    		'category_title'=>'title',
	    		'category_title_single'=>'title_single',
	    		'category_alias'=>'LOWER(`cat`.`alias`)' ));
		
		$select
			->where( $like )
			->where( "`prog`.`start` > '".$start->toString('yyyy-MM-dd')." 00:00:00'" )
			->where( "`prog`.`start` < '".$end->toString('yyyy-MM-dd')." 23:59:00'" )
			->where( "`ch`.`published`='1' AND `prog`.`ch_id`!='$orig_channel'" )
			->order( array("prog.start DESC", "prog.ch_id ASC"));	

		if (APPLICATION_ENV=='development'){
			parent::debugSelect($select, __METHOD__);
			//die(__FILE__.': '.__LINE__);
		}
			
		$result = $this->db->fetchAll($select);
		
		if ($result){
		    $propsTable    = new Xmltv_Model_DbTable_ProgramsProps();
		    $descsTable    = new Xmltv_Model_DbTable_ProgramsDescriptions();
		    foreach ($result as $k=>$r){
		        $select = $this->db->select()
		        	->from(array('props'=>$propsTable->getName()))
		        	->where("`hash`='".$r['hash']."'");
		        
		        if ($newResult = $this->db->fetchRow($select)){
		            $result[$k]['actors']    = $newResult['actors'];
		            $result[$k]['directors'] = $newResult['directors'];
		            $result[$k]['premiere']  = (bool)$newResult['premiere'];
		            $result[$k]['live']      = (bool)$newResult['live'];
		        }
		        
		        $select = $this->db->select()
		        ->from(array('props'=>$descsTable->getName()))
		        ->where("`hash`='".$r['hash']."'");
		        if ($newResult = $this->db->fetchRow($select)){
		            $result[$k]['desc_intro'] = $newResult['intro'];
		            $result[$k]['desc_body']  = $newResult['body'];
		        }
		        
		    }
		}
		
		foreach ($result as $k=>$item){
			$result[$k]['start'] = new Zend_Date( $item->start, 'yyyy-MM-dd HH:mm:ss');
			$result[$k]['end']   = new Zend_Date( $item->end, 'yyyy-MM-dd HH:mm:ss');
		}
		
		if (APPLICATION_ENV=='development'){
			//var_dump($result);
			//die(__FILE__.': '.__LINE__);
		}
		
		return $result;
		
	}
	
	public function getProgramThisWeek ($prog_alias=null, $channel_id=null, Zend_Date $start, Zend_Date $end) {
		
		if( !$prog_alias || !$channel_id || !$start || !$end )
			return false;
		
		/**
		 * @var Zend_Db_Select
		 */
		$select = $this->db->select()
			->from(array( 'prog'=>'rtvg_programs'), '*')
			->joinLeft( array( 'prop'=>$this->propertiesTable->getName() ), "`prog`.`hash`=`prop`.`hash`", array(
				'actors',
				'directors',
				'premiere',
				'live'
			))->joinLeft( array( 'desc'=>$this->descriptionsTable->getName() ), "`prog`.`hash`=`desc`.`hash`", array(
				'desc_intro'=>'intro',
				'desc_body'=>'body'
			))->joinLeft( array('ch'=>$this->channelsTable->getName() ), "`prog`.`ch_id`=`ch`.`ch_id`", array(
				'channel_title'=>'title',
				'channel_alias'=>'LOWER(`ch`.`alias`)'
			))->joinLeft( array('cat'=>$this->categoriesTable->getName() ), "`prog`.`category`=`cat`.`id`", array(
				'category_title'=>'title',
				'category_title_single'=>'title_single',
				'category_alias'=>'LOWER(`cat`.`alias`)'
			))
			->where( "`prog`.`alias` LIKE '$prog_alias'")
			->where( "`prog`.`start` >= '".$start->toString('yyyy-MM-dd 00:00:00')."'")
			->where( "`prog`.`start` < '".$end->toString('yyyy-MM-dd 23:59:00')."'")
			->where( "`prog`.`ch_id` = '$channel_id'")
			->order( "prog.start DESC" );
		
		if (APPLICATION_ENV=='development'){
			parent::debugSelect($select, __METHOD__);
			//die(__FILE__.': '.__LINE__);	
		}
			
		try {
			$result = $this->db->fetchAll($select);
		} catch (Zend_Db_Adapter_Mysqli_Exception $e) {
			throw new Zend_Exception($e->getMessage(), $e->getCode(), $e);
		}
		
		if (APPLICATION_ENV=='development'){
			//var_dump($result);
			//die(__FILE__.': '.__LINE__);
		}
		
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
				$r = $this->table->getPremieres($start, $end);
				$cache->save($r, $hash, 'Function');
			} 
		} else {
			$r = $this->table->getPremieres($start, $end);
		}
		
		//var_dump($r);
		//die(__FILE__.": ".__LINE__);
		
		return $r;
	
	}
	
	private function makePersonName($info=null){
	
	}
	
	public function getItemsCount(){
		return $this->table->getCount();
	}

	/**
	 * Новый просмотр программы для рейтинга
	 * 
	 * @param unknown_type $program
	 * @throws Zend_Exception
	 */
	public function addHit($program=null){
		
		if (is_array($program)) {
			$table = new Xmltv_Model_DbTable_ProgramsRatings();
			$table->addHit($program['hash'], $program['ch_id']);
			return true;
		} elseif (is_object($program)){
		    $table = new Xmltv_Model_DbTable_ProgramsRatings();
		    $table->addHit($program->hash, $program->ch_id);
		    return true;
		}
		
		return false;
		
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
	
	public function categoryWeek( $category=null, Zend_Date $start, Zend_Date $end){
		die();
	    
	}
	
}

