<?php
/**
 * Programs listings functionality
 *
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: Programs.php,v 1.16 2013-02-25 11:40:40 developer Exp $
 *
 */
class Xmltv_Model_Programs extends Xmltv_Model_Abstract
{
	
	protected $weekDays;
	protected static $videoCache=false;
	protected $programsCategoriesList;
	
	const ERR_MISSING_PARAMS="Пропущены необходимые параметры!";
	
	/**
	 * Constructor
	 * @param array $config
	 */
	public function __construct($config=array()){
		
	    parent::__construct($config);
        
        if (isset($config['video_cache']) && is_bool($config['video_cache'])){
            self::$videoCache = $config['video_cache'];
        }
        
        /**
         * Model's main table
         * @var Xmltv_Model_DbTable_Programs
         */
	    $this->table    = new Xmltv_Model_DbTable_Programs();
	    $this->weekDays = isset($config['week_days']) ? $config['week_days'] : null ;
	    $this->programsCategoriesList = $this->getCategoriesList();
			
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
			parent::debugSelect($select, __METHOD__);
			//die(__FILE__.': '.__LINE__);
		}
		
		$result = $result = $this->db->fetchAll($select);
		return $result[0];
		
	}
	
	/**
	 * 
	 * @param Zend_Date $start
	 * @param Zend_Date $end
	 * @param int       $type
	 */
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
	public function getProgramsForDay(Zend_Date $date=null, $channel_id=null){
	    
	    $table   = new Xmltv_Model_DbTable_ProgramsCategories();
	    $cats    = $table->fetchAll();
	    
	    if ($date->isToday()){
			
	        $select = $this->db->select()
		        ->from( array( 'prog'=>$this->table->getName()), '*')
		        ->joinLeft( array( 'channel'=>$this->channelsTable->getName()), "`prog`.`channel`=`channel`.`id`", array( 'channel_id'=>'id'))
		        ->where("`prog`.`start` >= '".$date->toString('yyyy-MM-dd')." 00:00'")
		        ->where("`prog`.`start` < '".$date->toString('yyyy-MM-dd')." 23:59'")
		        ->where("`prog`.`channel` = '$channel_id'")
		        ->where("`channel`.`published` = '1'")
		        ->group("prog.start")
		        ->order("prog.start", "ASC");
	        $rows = $this->db->fetchAssoc($select);
	        
	        if (APPLICATION_ENV=='development'){
	            parent::debugSelect($select, __METHOD__);
	            //die(__FILE__.': '.__LINE__);
	        }
	        
	        if (!count($rows))
				return null;
			
		} else {
		    
		    $l = (int)Zend_Registry::get('site_config')->listings->history->get('length');
			$maxAgo = new Zend_Date( Zend_Date::now()->subDay($l)->toString('U'), 'U' ) ;
			
			if ($date->compare($maxAgo)==-1){ //More than x days
				$rows = $this->table->fetchDayItems( $channel_id, $date->toString('yyyy-MM-dd'), $cats, true);
			} else { //Less or equal than x days
				$rows = $this->table->fetchDayItems( $channel_id, $date->toString('yyyy-MM-dd'), $cats);
			}
			
			if (!count($rows))
				return null;
			
		}
		
		
		if (APPLICATION_ENV=='development'){
			//var_dump(count($rows));
			//die(__FILE__.': '.__LINE__);
		}
		
		$list = array();
		$channel_desc=false;
		if (count($rows)>0) {
		    $video = false;
			foreach ($rows as $k=>$prog) {
				
				$prog_start = isset($prog['start']) && !empty($prog['start']) ? new Zend_Date($prog['start']) : Zend_Date::now();
				$prog_end   = isset($prog['end']) && !empty($prog['end']) ? new Zend_Date($prog['end']) : Zend_Date::now();
				$rows[$k]['now_showing'] = false;
				$rows[$k]['start'] = $prog_start;
				$rows[$k]['end']   = $prog_end;
				
				$compare_start = $prog_start->compare(Zend_Date::now());
				$compare_end   = $prog_end->compare(Zend_Date::now());
				
				if (($compare_start==-1 || $compare_start==0) && ($compare_end==1 || $compare_end==0)){
					$rows[$k]['now_showing'] = true;
					$rows[$k]['fetch_video'] = true;
					$video = true;
				}
				
				$rows[$k]['fetch_video'] = $video;
				$rows[$k]['start_timestamp'] = $prog_start->toString(Zend_Date::TIMESTAMP);
				
			}
		} else {
			return;
		}
		
		if (APPLICATION_ENV=='development'){
			//var_dump($rows);
			//die(__FILE__.': '.__LINE__);
		}
		
		return $rows;
		
	}


	/**
	 * 
	 * Телепередача в указанный день на канале
	 * 
	 * @param  string    $prog_alias
	 * @param  string    $channel_alias
	 * @param  Zend_Date $date
	 * @throws Zend_Exception
	 * @return array
	 */
	public function getProgramForDay ($prog_alias=null, $channel_alias=null, Zend_Date $date) {
				
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
	 * @param string    $program_alias
	 * @param Zend_Date $date
	 */
	public function getSimilarProgramsThisWeek($program_alias=null, Zend_Date $start, Zend_Date $end, $orig_channel=null){
		
		if (!$program_alias || !$orig_channel)
			throw new Zend_Exception( parent::ERR_WRONG_PARAMS.__METHOD__, 500);
		
		$select = $this->db->select()
		->from(array( 'prog'=>$this->table->getName()), array(
			'title',
			'sub_title',
			'alias',
			'channel',
			'start',
			'end',
			'episode_num',
			'hash' ))
		->joinLeft( array('channel'=>$this->channelsTable->getName() ), "`prog`.`channel`=`channel`.`id`", array(
			'channel_title'=>'title',
			'channel_alias'=>'LOWER(`channel`.`alias`)',
			'channel_icon'=>'channel.icon'))
		->joinLeft( array('cat'=>$this->categoriesTable->getName()), "`prog`.`category`=`cat`.`id`", array(
			'category_title'=>'title',
			'category_title_single'=>'title_single',
			'category_alias'=>'LOWER(`cat`.`alias`)' ));
		
		$parts = explode('-', $program_alias);
	    foreach ($parts as $a){
	        if (Xmltv_String::strlen($a)>3){
	            $regex = "^".$a."[ ]?.*";
	        	$pieces[]=" `prog`.`alias` REGEXP('$regex')";
	        }
	    }
	    if (count($pieces)) {
	        $like = implode(' OR ', $pieces);
	        $select->where( $like );
	    }
	    
	    $select
			->where( "`prog`.`start` > '".$start->toString('yyyy-MM-dd')." 00:00'" )
			->where( "`prog`.`start` < '".$end->toString('yyyy-MM-dd')." 23:59'" )
			->where( "`prog`.`alias` LIKE '%$program_alias%'" )
			->where( "`channel`.`published` = '1'")
			->where( "`channel`.`lang` = 'ru'")
			->where( "`prog`.`channel` != '$orig_channel'")
			->order( array( "channel_title ASC", "prog.start DESC"));	

		if (APPLICATION_ENV=='development'){
			parent::debugSelect($select, __METHOD__);
			//die(__FILE__.': '.__LINE__);
		}
			
		$result = $this->db->fetchAll($select);
		
		if (APPLICATION_ENV=='development'){
			//var_dump($result);
			//die(__FILE__.': '.__LINE__);
		}
		
		if (count($result)){
			foreach ($result as $k=>$item){
				$result[$k]['start'] = new Zend_Date( $item['start'], 'yyyy-MM-dd HH:mm:ss');
				$result[$k]['end']   = new Zend_Date( $item['end'], 'yyyy-MM-dd HH:mm:ss');
			}
		}
		
		if (APPLICATION_ENV=='development'){
			//var_dump($result);
			//die(__FILE__.': '.__LINE__);
		}
		
		return $result;
		
	}
	
	/**
	 * Week listing for particular program
	 * 
	 * @param  string    $prog_alias
	 * @param  int       $channel_id
	 * @param  Zend_Date $start
	 * @param  Zend_Date $end
	 * @throws Zend_Exception
	 */
	public function getProgramThisWeek ($prog_alias=null, $channel_id=null, Zend_Date $start, Zend_Date $end) {
		
		if( !$prog_alias || !$channel_id || !$start || !$end )
			return false;
		
		/**
		 * @var Zend_Db_Select
		 */
		$select = $this->db->select()
			->from(array( 'prog'=>'rtvg_programs'), array(
				'title',
				'sub_title',
				'alias',
				'channel',
				'start',
				'end',
				'episode_num',
				'hash'))
			->join( array('channel'=>$this->channelsTable->getName() ), "`prog`.`channel`=`channel`.`id`", array(
				'channel_title'=>'title',
				'channel_alias'=>'LOWER(`channel`.`alias`)'))
			->joinLeft( array('cat'=>$this->categoriesTable->getName() ), "`prog`.`category`=`cat`.`id`", array(
				'category_title'=>'title',
				'category_title_single'=>'title_single',
				'category_alias'=>'LOWER(`cat`.`alias`)'))
			->where( "`prog`.`alias` LIKE '$prog_alias'")
			->where( "`prog`.`start` >= '".Zend_Date::now()->toString('yyyy-MM-dd')." 00:00'")
			->where( "`prog`.`start` < '".$end->toString('yyyy-MM-dd')." 23:59'")
			->where( "`prog`.`channel` = '$channel_id'")
			->order( "prog.start ASC" );
		
		if (APPLICATION_ENV=='development'){
			parent::debugSelect($select, __METHOD__);
			//die(__FILE__.': '.__LINE__);	
		}

		try {
			$result = $this->db->fetchAll($select, null, Zend_Db::FETCH_ASSOC);
		} catch (Zend_Db_Adapter_Mysqli_Exception $e) {
			throw new Zend_Exception($e->getMessage(), $e->getCode(), $e);
		}
		
		foreach ($result as $k=>$item){
			$result[$k]['start'] = new Zend_Date( $item['start'], 'yyyy-MM-dd HH:mm:ss');
			$result[$k]['end']   = new Zend_Date( $item['end'], 'yyyy-MM-dd HH:mm:ss');
		}
		
		if (APPLICATION_ENV=='development'){
			//var_dump($result);
			//die(__FILE__.': '.__LINE__);
		}
		
		return $result;
		
	}

	/*
	public function getPremieres (Zend_Date $start, Zend_Date $end) {

		if( !is_a( $end, 'Zend_Date' ) )
		return;
		
		if( !is_a( $start, 'Zend_Date' ) )
		$start = new Zend_Date();
		
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
	*/
	
	/**
	 * Get total programs count
	 */
	public function getItemsCount(){
		return $this->table->getCount();
	}

	/**
	 * Новый просмотр программы для рейтинга
	 * 
	 * @param  array|object $program
	 * @throws Zend_Exception
	 */
	public function addHit($program=null){
		
	    $table = new Xmltv_Model_DbTable_ProgramsRatings();
		if (is_array($program)) {
			$table->addHit($program['alias'], $program['channel']);
			return true;
		} elseif (is_object($program)){
		    $table->addHit($program->alias, $program->channel);
		    return true;
		}
		
		return false;
		
	}
	
	/**
	 * Calculate wekk start and week end
	 * 
	 * @param Zend_Date $week_start
	 * @param Zend_Date $week_end
	 */
	public function getWeekDates($week_start=null, $week_end=null){
	
		$result = array('start', 'end');
		
		if (!$week_start) {
			$d = new Zend_Date();
		}
		
		do{
			if ($d->toString(Zend_Date::WEEKDAY_DIGIT, 'ru')>1)
			$d->subDay(1);
		} while ($d->toString(Zend_Date::WEEKDAY_DIGIT, 'ru')>1);
		
		$result['start'] = $d;
		
		if (!$week_end) {
			$d = new Zend_Date();
		} else {
			$d = new Zend_Date($week_end);
		}
		
		do{
			$d->addDay(1);
		} while ($d->toString(Zend_Date::WEEKDAY_DIGIT, 'ru')!=1);
		$result['end'] = $d;
		
		return $result;
		
	}
	
	public function categoryWeek( $category_id=null, Zend_Date $start, Zend_Date $end){
		
	    $select = $this->db->select()
	    ->from( array('prog'=>$this->table->getName()), array(
	    		'hash',
	    		'prog_title'=>'title',
	    		'prog_sub_title'=>'sub_title',
	    		'prog_alias'=>'LOWER(`prog`.`alias`)',
	    		'prog_start'=>'start',
	    		'prog_end'=>'end',
	    		'rating',
	    		'live'
	    ));
	     
	    if (APPLICATION_ENV=='development'){
	    	parent::debugSelect($select, __METHOD__);
	    	die(__FILE__.': '.__LINE__);
	    }
	    
	}
	
	/**
	 * Data for frontpage listing
	 * 
	 * @param  array $channels
	 * @throws Zend_Exception
	 */
	public function frontpageListing($channels=array()){
		
	    if (!is_array($channels) || empty($channels))
	        throw new Zend_Exception(self::ERR_WRONG_PARAMS.__METHOD__, 500);
	    
	    $ids = array();
	    foreach ($channels as $i){
	        $ids[] = "'".$i['id']."'";
	    }
	    $ids = implode(",", $ids);
	    
	    if (APPLICATION_ENV=='development'){
	    	//var_dump($ids);
	    }
	    
	    $where = array(
	    	"`prog`.`start` >= '".Zend_Date::now()->toString("YYYY-MM-dd")." 00:00:00'",
	    	"`prog`.`start` < '".Zend_Date::now()->addHour(6)->toString("YYYY-MM-dd HH:mm").":00'",
	    	"`prog`.`channel` IN ($ids)",
	    );
	    $where = implode(" AND ", $where);
	    
	    $select = $this->db->select()
	    	->from( array('prog'=>$this->table->getName()), array(
	    		'hash',
	    		'title',
	    		'sub_title',
	    		'alias'=>'LOWER(`prog`.`alias`)',
	    		'start',
	    		'end',
	    		'rating',
	    		'live' ))
	    	->joinLeft( array('cat'=>$this->categoriesTable->getName()), "`prog`.`category`=`cat`.`id`", 
	    		array(
		    		'category'=>'id',
		    		'category_title'=>'title',
		    		'category_alias'=>'alias',
		    		'category_single'=>'title_single'))
	    	->join( array('ch'=>$this->channelsTable->getName()), "`prog`.`channel`=`ch`.`id`", 
	    		array(
	    			'channel'=>'id',
	    			'channel_title'=>'title',
	    			'channel_alias'=>'LOWER(`ch`.`alias`)'))
	    	->where( $where )
	    	->group( "prog.start" )
	    	->order(array("prog.channel ASC", "prog.start ASC"));
	    
	    if (APPLICATION_ENV=='development'){
	        echo "<b>".__METHOD__."</b>";
	        Zend_Debug::dump($select->assemble());
	        //die(__FILE__.': '.__LINE__);
	    }
	    
	    $result = $this->db->fetchAssoc($select);
	    $items = array();
	    if (!empty($result)){
	        $now = Zend_Date::now();
	        foreach ($result as $k=>$d){
	            $end = new Zend_Date($d['end']);
	            if ($end->compare($now) == -1) {
	                unset($result[$k]);
	            } else {
	                $items[$d['channel']][$k] = $d;
	                $items[$d['channel']][$k]['start'] = new Zend_Date($d['start']);
	                $items[$d['channel']][$k]['end']   = new Zend_Date($d['end']);
	            }
	            
	        }
	    }
	    
	    if (APPLICATION_ENV=='development'){
	    	//Zend_Debug::dump($items);
	    	//die(__FILE__.': '.__LINE__);
	    }
	    
	    return $items;
	    
	}
	
	/**
	 * Поиск прораммы
	 * @param string $key
	 * @param string $search
	 */
	public function search($key, $search, $single=true){
		
	    if (!$key){
	        throw new Zend_Exception("Не указано где искать в ".__METHOD__, 500);
	    }
	    if (!$search){
	        throw new Zend_Exception("Не указано что искать в ".__METHOD__, 500);
	    }
	    
	    if ($single){
	    	return $this->table->fetchRow("`$key` LIKE '$search'")->toArray();
	    } else {
	        return $this->table->fetchAll("`$key` LIKE '$search'")->toArray();
	    }
	    
	}
	
	public function categoryDay( $category_id, $date=null){
		
	    if (!$date)
	        $date = Zend_Date::now();
	    
		$select = $this->db->select()
		->from( array('prog'=>$this->table->getName()), array(
				'title',
				'sub_title',
				'desc',
				'prog_alias'=>'LOWER(`prog`.`alias`)',
				'start',
				'end',
				'rating',
				'live',
				'prog_category'=>'category',
				'prog_channel'=>'channel',
				'hash',
		))
		->joinLeft(array('cat'=>$this->categoriesTable->getName()), "`prog`.`category`=`cat`.`id`", array(
			'prog_category_title'=>'title',
			'prog_category_alias'=>'alias',
			'prog_category_single'=>'title_single',
		))
		->joinLeft(array('ch'=>$this->channelsTable->getName()), "`prog`.`channel`=`ch`.`id`", array(
			'channel_title'=>'title',
			'channel_alias'=>'alias',
		))
		->joinLeft(array('ch_cat'=>$this->channelsCategoriesTable->getName()), "`prog`.`channel`=`ch_cat`.`id`", array(
				'channel_category_title'=>'title',
				'channel_category_alias'=>'alias',
		))
		->group("prog.start")
		->where("`prog`.`category`='$category_id'")
		->where("`prog`.`start` >= '".$date->toString('yyyy-MM-dd HH:mm')."'")
		->where("`prog`.`start` < '".$date->toString('yyyy-MM-dd')." 23:59'");
	
		if (APPLICATION_ENV=='development'){
			parent::debugSelect($select, __METHOD__);
			//die(__FILE__.': '.__LINE__);
		}
		
		$result = $this->db->fetchAssoc($select->assemble());
		
		if (APPLICATION_ENV=='development'){
			//var_dump($result);
			//die(__FILE__.': '.__LINE__);
		}
		
		foreach ($result as $k=>$p){
		    $result[$k]['start'] = new Zend_Date($p['start'], 'yyyy-MM-dd HH:mm:ss');
		    $result[$k]['end']   = new Zend_Date($p['end'], 'yyyy-MM-dd HH:mm:ss');
		}
		
		return $result;
		 
	}
	
	/**
	 * Load categories list from database
	 * 
	 * @param  string $order
	 * @return array
	 */
	public function getCategoriesList($order=null){
		
	    if ($order){
	        return $this->categoriesTable->fetchAll(null, $order)->toArray();
	    } else {
	        return $this->categoriesTable->fetchAll()->toArray();
	    }
	    
	}
	
}

