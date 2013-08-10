<?php
/**
 * Programs listings functionality
 *
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: Programs.php,v 1.31 2013-04-11 05:19:30 developer Exp $
 *
 */
class Xmltv_Model_Programs extends Xmltv_Model_Abstract
{

    protected $weekDays;
	protected static $videoCache=false;
	protected $programsCategoriesList;

	protected $countriesList = array(
		'Австралия'=>'au',
		'Аргентина'=>'ar',
		'Бельгия'=>'be',
		'Великобритания'=>'gb',
		'Германия'=>'de',
		'Дания'=>'dk',
		'Индия'=>'in',
		'Индонезия'=>'id',
		'Испания'=>'es',
		'Ирландия'=>'ie',
		'Италия'=>'it',
		'Канада'=>'ca',
		'Китай'=>'cn',
		'Мексика'=>'mx',
		'Нидерланды'=>'nl',
		'Россия'=>'ru',
		'США'=>'us',
		'Украина'=>'ua',
		'Финляндия'=>'fi',
		'Франция'=>'fr',
		'Южная Корея'=>'kp',
		'Япония'=>'jp',
	);
	
	
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
	
	public function getProgramThisDay($alias='', $channel_id=null, Zend_Date $date){
		
	    if (!$alias || !$channel_id)
	        throw new Zend_Exception( Rtvg_Message::ERR_MISSING_PARAM, 500);
	    
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
	 * @param int $count // optional
	 */
	public function getProgramsForDay(Zend_Date $date=null, $channel_id=null, $count=null){
	    
	    $d = new Zend_Date($date);
	    
	    $rows = $this->programsTable->fetchDayItems( $channel_id, $date, $count );
		if (!count($rows)) {
			return false;
		}
		
		
		if (APPLICATION_ENV=='development'){
			//var_dump(count($rows));
			//var_dump($rows);
			//die(__FILE__.': '.__LINE__);
		}
		
		$channel_desc=false;
		if (count($rows)>0) {
		    $result = array();
		    $c=0;
		    foreach ($rows as $k=>$prog) {
		        if ($count!=null && $c<$count){
			        if ($prog['end']->compare(Zend_Date::now(), 'YYYY-MM-dd HH:mm') >= 0) {
			            $result[$c] = $this->_updateLoadedValues( $prog );
			            $result[$c]['now_showing'] = ($c==0) ? true : false ;
		        	    $c++;
		        	}
		        } elseif($count==null){
		            $result[$c] = $this->_updateLoadedValues( $prog );
		            $c++;
		        }
			}
			
			if (APPLICATION_ENV=='development'){
				//var_dump(count($rows));
				//var_dump($result);
				//die(__FILE__.': '.__LINE__);
			}
			
		} else {
			return false;
		}
		
		$nowShowing=false;
		foreach ($result as $row){
		    if ((bool)$row['now_showing']===true){
		        $nowShowing=true;
		    }
		}
		if (!$nowShowing){
		    $result[0]['now_showing'] = true;
		}
		
		return $result;
	}
	
	/**
	 * 
	 * @param  array $data
	 * @throws Zend_Exception
	 * @return array
	 */
	private function _updateLoadedValues($data=array()){
	    
	    if (empty($data)){
	        throw new Zend_Exception( Rtvg_Message::ERR_MISSING_PARAM );
	    }
	    if (!is_array($data)){
	        throw new Zend_Exception( Rtvg_Message::ERR_WRONG_PARAM );
	    }
	    $result = $data;
        $result['start_timestamp'] = $data['start']->toString(Zend_Date::TIMESTAMP);
        $result['length']          = ($data['length'] !== null) ? new Zend_Date( $data['length'], 'HH:mm:ss') : null ;
        $result['date']            = ($data['date'] !== null) ? new Zend_Date( $data['date'], 'YYYY-MM-dd') : null ;
        return $result;
	}


	/**
	 * 
	 * Телепередача в указанный день на канале
	 * 
	 * @param  string    $alias
	 * @param  array     $channel
	 * @param  Zend_Date $date
	 * @param  int       $limit // Amount of results to return
	 * @throws Zend_Exception
	 * @return array
	 */
	
	public function getProgramForDay ($alias=array(), array $channel=null, Zend_Date $date, $limit=false) {
		

	    if (!$alias) {
	    	throw new Zend_Exception( Rtvg_Message::ERR_MISSING_PARAM, 500);
	    }
	     
	    $progStart = is_a($date, 'Zend_Date') ? new Zend_Date( $date->toString() ) : Zend_Date::now() ;
	    $where = array(
	    		"`prog`.`alias` LIKE '%$alias%'",
	    		"`prog`.`start` >= '".$date->toString('YYYY-MM-dd')." 00:00'",
	    		"`prog`.`start` < '".$date->toString('YYYY-MM-dd')." 23:59'",
	    );
	    
	    if ($channel!==null && is_array($channel)){
	    	$where[] = "`prog`.`channel`='".$channel['id']."'";
	    }
	    
	    $where = count($where) ? implode(' AND ', $where) : '' ;
	    $select = $this->db->select()
	    ->from(array('prog'=>$this->programsTable->getName()), array(
	    		//'id',
	    		'title',
	    		'sub_title',
	    		'alias',
	    		//'channel',
	    		'start',
	    		'end',
	    		//'category',
	    		'rating',
	    		'new',
	    		'live',
	    		'image',
	    		//'last_chance',
	    		//'previously_shown',
	    		'country',
	    		'actors',
	    		'directors',
	    		//'writers',
	    		//'adapters',
	    		//'producers',
	    		//'composers',
	    		//'editors',
	    		//'presenters',
	    		//'commentators',
	    		//'guests',
	    		'episode_num',
	    		'premiere',
	    		'date',
	    		'length',
	    		'desc',
	    		'hash',
	    ))
	    ->joinLeft( array('ch'=>$this->channelsTable->getName()), "`prog`.`channel`=`ch`.`id`", array(
	    		'channel_id'=>'id',
	    		'channel_title'=>'title',
	    		'channel_alias'=>'alias',
	    		'channel_desc'=>"CONCAT_WS( ' ', `desc_intro`, `desc_body` )",
	    ))
	    ->joinLeft( array('cat'=>$this->categoriesTable->getName()), "`prog`.`category`=`cat`.`id`", array(
	    		'category_id'=>'id',
	    		'category_title'=>'title',
	    		'category_title_single'=>'title_single',
	    		'category_alias'=>'alias',
	    ))
	    ->where( $where)
	    ->order( 'start DESC');
	    
	    if ($limit===true){
	    	$select->limit($limit);
	    }
	    
	    if (APPLICATION_ENV=='development') {
	    	parent::debugSelect($select, __METHOD__);
	    	//die(__FILE__.': '.__LINE__);
	    }
	    
	    $result = $this->db->fetchAll( $select, null, Zend_db::FETCH_ASSOC );
	    
	    if (APPLICATION_ENV=='development') {
	    	//var_dump($result);
	    	//die(__FILE__.': '.__LINE__);
	    }
	    
	    if (!$result || (count($result)==0)) {
	    	return false;
	    }
	    
	    foreach ($result as $idx=>$row){
	    	$result[$idx]['start']    = new Zend_Date( $row['start'], 'YYYY-MM-dd HH:mm:ss' );
	    	$result[$idx]['end']      = new Zend_Date( $row['end'], 'YYYY-MM-dd HH:mm:ss' );
	    	$result[$idx]['new']      = (bool)$row['new'];
	    	$result[$idx]['live']     = (bool)$row['live'];
	    	$result[$idx]['premiere'] = (bool)$row['premiere'];
	    	$result[$idx]['date']     = (null !== $row['date'])   ? new Zend_Date( $row['date'], 'YYYY-MM-dd HH:mm:ss') : null ;
	    	$result[$idx]['length']   = (null !== $row['length']) ? new Zend_Date( $row['length'], 'HH:mm:ss') : null ;
	    }
	    
	    if (APPLICATION_ENV=='development') {
	    	//var_dump($result);
	    	//die(__FILE__.': '.__LINE__);
	    }
	    
	    if ($limit===1){
	        return $result[0];
	    }
	    
	    return $result;
	    
	}
	
	/**
	 * Подобные программы на этой неделе
	 * 
	 * @param  string $program_alias
	 * @param  Zend_Date $start
	 * @param  Zend_Date $end
	 * @param  int $channel_id
	 * @throws Zend_Exception
	 * @return array
	 */
	public function getSimilarProgramsThisWeek($program_alias=null, Zend_Date $start, Zend_Date $end, $channel_id=null){
		
	    if (!$program_alias) {
			throw new Zend_Exception( Rtvg_Message::ERR_WRONG_PARAM, 500);
		}
		
		$select = $this->db->select()
		->from(array( 'prog'=>$this->table->getName()), array(
			'title',
			'sub_title',
			'alias',
			'start',
			'end',
			'episode_num',
			'hash' ))
		->join( array('channel'=>$this->channelsTable->getName() ), "`prog`.`channel`=`channel`.`id`", array(
			'channel_id'=>'id',
			'channel_title'=>'title',
			'channel_alias'=>'LOWER(`channel`.`alias`)',
			'channel_icon'=>'channel.icon'))
		->joinLeft( array('cat'=>$this->categoriesTable->getName()), "`prog`.`category`=`cat`.`id`", array(
			'category_id'=>'title',
			'category_title'=>'title',
			'category_title_single'=>'title_single',
			'category_alias'=>'LOWER(`cat`.`alias`)' ));
		
		$parts = explode('-', $program_alias);
		$where = array();
		$regex = array();
	    foreach ($parts as $a){
	        if (Xmltv_String::strlen($a)>=7){
	            $r = Xmltv_String::substr($a, 0, Xmltv_String::strlen($a)-2);
	            $regex[] = $r;
	            $where[] = " `prog`.`alias` LIKE '%$r%'";
	        }
	    }
	    $where[]=" `prog`.`alias` LIKE '%$program_alias%'";
	    
	    if (count($where)) {
	        $where = implode(' OR ', $where);
	        $select->where( $where );
	    }
	    
	    $select
			->where( "`prog`.`start` > '".$start->toString('YYYY-MM-dd')." 00:00'" )
			->where( "`prog`.`start` < '".$end->toString('YYYY-MM-dd')." 23:59'" )
			->where( "`channel`.`published`='1'")
			->where( "`channel`.`lang`='ru'")
			->group( "prog.hash")
	    	->order( "prog.channel ASC" )
	    	->order( "prog.start DESC" );	

	    if ($channel_id && is_numeric($channel_id)){
	        $select->where( "`prog`.`channel` != '$channel_id'");
	    }
	    
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
				$result[$k]['start'] = new Zend_Date( $item['start'], 'YYYY-MM-dd HH:mm:ss');
				$result[$k]['end']   = new Zend_Date( $item['end'], 'YYYY-MM-dd HH:mm:ss');
			}
		} else {
		    return false;
		}
		
		return $result;
		
	}
	
	
	/**
	 * Подобные программы сегодня
	 * 
	 * @param  string    $program_alias
	 * @param  Zend_Date $date
	 * @param  int       $channel_id
	 * @throws Zend_Exception
	 * @return array
	 */
	public function getSimilarProgramsForDay($program_alias=null, Zend_Date $date, $channel_id=null){
		
	    if (!$program_alias) {
			throw new Zend_Exception( Rtvg_Message::ERR_WRONG_PARAM, 500);
		}
		
		$select = $this->db->select()
		->from(array( 'prog'=>$this->table->getName()), array(
			'title',
			'sub_title',
			'alias',
			'start',
			'end',
			'episode_num',
			'hash' ))
		->join( array('channel'=>$this->channelsTable->getName() ), "`prog`.`channel`=`channel`.`id`", array(
			'channel_id'=>'id',
			'channel_title'=>'title',
			'channel_alias'=>'LOWER(`channel`.`alias`)',
			'channel_icon'=>'channel.icon'))
		->joinLeft( array('cat'=>$this->categoriesTable->getName()), "`prog`.`category`=`cat`.`id`", array(
			'category_id'=>'title',
			'category_title'=>'title',
			'category_title_single'=>'title_single',
			'category_alias'=>'LOWER(`cat`.`alias`)' ));
		
		$parts = explode('-', $program_alias);
		$where = array();
		$regex = array();
	    foreach ($parts as $a){
	        if (Xmltv_String::strlen($a)>=7){
	            $r = Xmltv_String::substr($a, 0, Xmltv_String::strlen($a)-2);
	            $regex[] = $r;
	            $where[] = " `prog`.`alias` LIKE '%$r%'";
	        }
	    }
	    $where[]=" `prog`.`alias` LIKE '%$program_alias%'";
	    
	    if (count($where)) {
	        $where = implode(' OR ', $where);
	        $select->where( $where );
	    }
	    
	    $select
			->where( "`prog`.`start` >= '".$date->toString('YYYY-MM-dd')." 00:00:00'" )
			->where( "`channel`.`published`='1'")
			->where( "`channel`.`lang`='ru'")
			->group( "prog.hash")
	    	->order( "prog.channel ASC" )
	    	->order( "prog.start DESC" );	

	    if ($channel_id && is_numeric($channel_id)){
	        $select->where( "`prog`.`channel` = '$channel_id'");
	    }
	    
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
				$result[$k]['start'] = new Zend_Date( $item['start'], 'YYYY-MM-dd HH:mm:ss');
				$result[$k]['end']   = new Zend_Date( $item['end'], 'YYYY-MM-dd HH:mm:ss');
			}
		} else {
		    return false;
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
	 * @return array
	 */
	public function getProgramThisWeek ($prog_alias=null, $channel_id=null, Zend_Date $start, Zend_Date $end) {
		
		if( !$prog_alias || !$channel_id || !$start || !$end ){
			throw new Zend_Exception( Rtvg_Message::ERR_MISSING_PARAM, 500 );
		}
		
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
				'hash'
			))
			->joinLeft( array('channel'=>$this->channelsTable->getName() ), "`prog`.`channel`=`channel`.`id`", array(
				'channel_title'=>'title',
				'channel_alias'=>'LOWER(`channel`.`alias`)'))
			->joinLeft( array('cat'=>$this->categoriesTable->getName() ), "`prog`.`category`=`cat`.`id`", array(
				'category_title'=>'title',
				'category_title_single'=>'title_single',
				'category_alias'=>'LOWER(`cat`.`alias`)'))
			->where( "`prog`.`alias` LIKE '$prog_alias'")
			->where( "`prog`.`start` >= '".$start->toString('YYYY-MM-dd')." 00:00'")
			->where( "`prog`.`start` < '".$end->toString('YYYY-MM-dd')." 23:59'")
			->where( "`prog`.`channel` = '$channel_id'")
			->order( "prog.start DESC" );
		
		if (APPLICATION_ENV=='development'){
			parent::debugSelect($select, __METHOD__);
			//die(__FILE__.': '.__LINE__);	
		}

		$result = $this->db->fetchAll($select, null, Zend_Db::FETCH_ASSOC);
		
		if (!count($result)){
		    return false;
		}
		
		if (APPLICATION_ENV=='development'){
			//var_dump($result);
			//die(__FILE__.': '.__LINE__);
		}
		
		foreach ($result as $k=>$item){
			$result[$k]['start'] = isset($item['start']) && $item['start']!==null ? new Zend_Date( $item['start'], 'yyyy-MM-dd HH:mm:ss') : null ;
			$result[$k]['end']   = isset($item['end']) && $item['end']!==null ? new Zend_Date( $item['end'], 'yyyy-MM-dd HH:mm:ss') : null ;
		}
		
		if (APPLICATION_ENV=='development'){
			//var_dump($result);
			//die(__FILE__.': '.__LINE__);
		}
		
		return $result;
		
	}

	
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
		    
		    // Both ['channel_id'] and ['channel'] can be used here
		    $channel = isset($program['channel_id']) ? (int)$program['channel_id'] : (int)$program['channel'] ;
			$table->addHit($program['alias'], $channel);
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
	
	/**
	 * Programs category listing for week (slow)
	 * 
	 * @param  id        $category_id
	 * @param  Zend_Date $start
	 * @param  Zend_Date $end
	 * @return array
	 */
	public function categoryWeek( $category_id=null, Zend_Date $start, Zend_Date $end){
		
	    $select = $this->db->select()
		    ->from( array('prog'=>$this->programsTable->getName()), array(
		    		//'id',
		    		'title',
		    		'sub_title',
		    		'alias',
		    		'start',
		    		'end',
		    		'rating',
		    		'new',
		    		'live',
		    		'image',
		    		//'last_chance',
		    		//'previously_shown',
		    		'country',
		    		'actors',
		    		'directors',
		    		//'writers',
		    		//'adapters',
		    		//'producers',
		    		//'composers',
		    		//'editors',
		    		//'presenters',
		    		//'commentators',
		    		//'guests',
		    		//'guests',
		    		'episode_num',
		    		'premiere',
		    		'date',
		    		'length',
		    		'desc',
		    		'hash',
		    ))
		    ->joinLeft(array('cat'=>$this->categoriesTable->getName()), "`prog`.`category`=`cat`.`id`", array(
		    	'category_title'=>'title',
		    	'category_title_single'=>'title_single',
		    	'category_alias'=>'alias',
		    ))
		    ->joinLeft(array('ch'=>$this->channelsTable->getName()), "`prog`.`channel`=`ch`.`id`", array(
		    	'channel_id'=>'id',
		    	'channel_title'=>'title',
		    	'channel_alias'=>'alias',
		    ))
	    	->where("`prog`.`category`='$category_id'")
	    	->where("`prog`.`start` >= '".$start->toString('YYYY-MM-dd')." 00:00'")
	    	->where("`prog`.`start` < '".$end->addDay(1)->toString('YYYY-MM-dd')." 00:00'")
	    	->where("`ch`.`published` = '1'")
	    	->group("prog.start")
	    	->order( array( "prog.start ASC", "ch.title ASC" ));
	     
	    if (APPLICATION_ENV=='development'){
	    	parent::debugSelect($select, __METHOD__);
	    	//die(__FILE__.': '.__LINE__);
	    }
	    
	    $result = $this->db->fetchAll($select, null, Zend_Db::FETCH_ASSOC);
	    
	    if (count($result)){
	        foreach ($result as $k=>$p){
	        	$result[$k]['start'] = new Zend_Date( $p['start'], 'YYYY-MM-dd HH:mm:ss');
	        	$result[$k]['end']   = new Zend_Date( $p['end'], 'YYYY-MM-dd HH:mm:ss');
	        } 
	    }
	    
	    if (APPLICATION_ENV=='development'){
	    	//var_dump(count($result));
	    	//die(__FILE__.': '.__LINE__);
	    }
	    
	    return $result;
	    
	}
	
	/**
	 * Data for frontpage listing
	 * 
	 * @param  array $channels
	 * @throws Zend_Exception
	 */
	public function frontpageListing($channels=array()){
		
	    if (!is_array($channels) || empty($channels))
	        throw new Zend_Exception( Rtvg_Message::ERR_WRONG_PARAM, 500);
	    
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
	    );
	    
	    if (is_array($channels) && !empty($channels)){
	        $where[] = "`prog`.`channel` IN ($ids)";
	    }
	    
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
	        parent::debugSelect($select, __METHOD__);
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
	    	if(false !== (bool)($result =  $this->table->fetchRow("`$key` LIKE '$search'"))) {
	    	    return $result;
	    	}
	    } else {
	        if (false !== (bool)($result = $this->table->fetchAll("`$key` LIKE '$search'"))) {
	            return $result;
	        }
	    }
	    
	}
	
	public function categoryDay( $category_id, $date=null){
		
	    if (!$date)
	        $date = Zend_Date::now();
	    
		$select = $this->db->select()
			->from( array('prog'=>$this->table->getName()), array(
				//'id',
				'title',
				'desc',
				'alias',
				'start',
				'end',
				'rating',
				'live',
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
			->where("`prog`.`category`='$category_id'")
			->where("`prog`.`start` >= '".$date->toString('YYYY-MM-dd')." 00:00'")
	    	->where("`prog`.`start` < '".$date->addDay(1)->toString('YYYY-MM-dd')." 00:00'")
	    	->where("`ch`.`published` = '1'")
	    	->group("prog.start")
	    	->order( array( "prog.start ASC", "ch.title ASC" ));
	
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
	
	/**
     * 
     * Load top programs list from DB
     * 
     * @param int $amt
     * @param Zend_Date $week_start
     * @param Zend_Date $week_end
     * @return array
     */
	public function topPrograms($amt=25, Zend_Date $week_start, Zend_Date $week_end){
		
	    $select = $this->db->select()
	    ->from(array('prog'=>$this->programsTable->getName()), array(
	    		//'id',
	    		'title',
	    		'live',
	    		'episode_num',
	    		'premiere',
	    ))
	    ->joinLeft(array('rating'=>$this->programsRatingsTable->getName()), "`prog`.`alias`=`rating`.`alias`", array(
	    		'alias',
	    		'hits'
	    ))
	    ->joinLeft(array('ch'=>$this->channelsTable->getName()), "`rating`.`channel`=`ch`.`id`", array(
	    		'channel_id'=>'ch.id',
	    		'channel_title'=>'ch.title',
	    		'channel_alias'=>'LOWER(`ch`.`alias`)',
	    		'channel_icon'=>'ch.icon'))
	    		->where("`rating`.`channel` IS NOT NULL")
	    		->where("`ch`.`published`='1'")
	    		->where("`prog`.`start` > '".$week_start->toString("YYYY-MM-dd")." 00:00'")
	    		->where("`prog`.`start` <= '".$week_end->toString("YYYY-MM-dd")." 23:59'")
	    		->group("rating.channel")
	    		->order("rating.hits DESC")
	    		->limit((int)$amt);
	    	
	    
	    if (APPLICATION_ENV=='development'){
	    	parent::debugSelect($select, __METHOD__);
	    	//die(__FILE__.': '.__LINE__);
	    }
	    
	    $result = $this->db->fetchAll( $select, null, Zend_Db::FETCH_ASSOC);
	    
	    if (count($result)){
	    	foreach ($result as $k=>$item){
	    		$result[$k]['live']        = (bool)$item['live'];
	    		$result[$k]['episode_num'] = (int)$item['episode_num'];
	    		$result[$k]['premiere']    = (bool)$item['premiere'];
	    	}
	    	return $result;
	    }
	    
	    return false;
	    
	}
	
	/**
	 * 
	 * @param Zend_Date $week_start
	 * @param Zend_Date $week_end
	 */
	public function rssWeek(Zend_Date $week_start=null, Zend_Date $week_end=null){
		 
		$select = $this->db->select()
		->from(array('prog'=>$this->programsTable->getName()), 'alias')
		->join(array('channel'=>$this->channelsTable->getName()), "`prog`.`channel`=`channel`.`id`", array(
				'channel_alias'=>'LOWER(channel.alias)',
		))
		->join(array('rating'=>$this->channelsRatingsTable->getName()), "`prog`.`channel`=`rating`.`id`", null)
		->where("`prog`.`start` >= '".$week_start->toString("YYYY-MM-dd")." 00:00'")
		->where("`prog`.`start` < '".$week_end->toString("YYYY-MM-dd")." 23:59'")
		->where("`channel`.`adult` = 0")
		->group("prog.alias")
		->order("prog.start ASC");
	
		if (APPLICATION_ENV=='development'){
			//parent::debugSelect($select, __METHOD__);
			//die(__FILE__.': '.__LINE__);
		}
	
		$result = $this->db->fetchAll($select, null, Zend_Db::FETCH_ASSOC);
	
		if (!count($result)){
			return false;
		}
		
		foreach ($result as $k=>$row){
		    $encoded = urlencode($row['alias']);
		    if (strlen($encoded)>254){
		        unset($result[$k]);
		    }
		}
	
		return $result;
	
	}
	
	/**
	 * Creates ISO name for Russian country title
	 * 
	 * @param string $ru_title
	 * @return string
	 */
	protected function countryRuToIso($ru_title){
		
	    foreach ($this->countriesList as $ru=>$iso){
	        if (Xmltv_String::strtolower($ru_title)==Xmltv_String::strtolower($ru)){
	            return $iso;
	        }
	    }
	    
	}
	
}

