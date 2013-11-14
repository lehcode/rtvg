<?php
/**
 * Programs listings functionality
 *
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: Programs.php,v 1.31 2013-04-11 05:19:30 developer Exp $
 *
 */
class Xmltv_Model_Broadcasts extends Xmltv_Model_Abstract
{

    protected $weekDays;
    protected static $videoCache=false;
    protected $programsCategoriesList;
    protected $countriesList = array();    
    
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
        $countries = new Xmltv_Model_DbTable_Countries();
        $cl = $countries->fetchAll()->toArray();
        foreach ($cl as $i){
            $this->countriesList[$i['name']] = $i['iso'];
        }
            
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
    public function getBroadcastsForDay(Zend_Date $date=null, $channel_id=null, $count=null){
        
        $d = new Zend_Date($date);
        
        try{
            $rows = $this->bcTable->fetchDayItems( $channel_id, $date, $count );
        } catch (Exception $e){
            return array();
        }
        
        if (count($rows)>0) {
            $result = array();
            $c=0;
            foreach ($rows as $k=>$prog) {
                if ($count!=null && $c<$count){
                    if ($prog['end']->compare(Zend_Date::now(), 'YYYY-MM-dd HH:mm') >= 0) {
                        $result[$c] = $this->_updateLoadedValues( $prog );
                        $c++;
                    }
                } elseif($count==null){
                    $result[$c] = $this->_updateLoadedValues( $prog );
                    $c++;
                }
            }
            
        } else {
            return array();
        }
        
        /*
        $nowShowing=false;
        foreach ($result as $row){
            if ((bool)$row['now_showing']===true){
                $nowShowing=true;
            }
        }
        if (!$nowShowing){
            $result[0]['now_showing'] = true;
        }
         * */
        
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
        //$result['length']          = ($data['length'] !== null) ? new Zend_Date( $data['length'], 'HH:mm:ss') : null ;
        $result['date']            = ($data['date'] !== null) ? new Zend_Date( $data['date'], 'YYYY-MM-dd') : null ;
        return $result;
    }


    /**
     * 
     * Телепередача в указанный день на канале
     * 
     * @param  string    $alias //Broadcast alias
     * @param  string    $channel_id //ID of channel
     * @param  Zend_Date|null $date //Broadcast date. If not defined will start from current date/time
     * @param  int       $limit // Amount of results to return
     * @throws Zend_Exception
     * @return array|false
     */
    
    public function getBroadcastThisDay ($alias=null, $channel_id=null, $date=null, $limit=null) {
        
        if (!$alias) {
            throw new Zend_Exception( '$alias is not set'. ' alias', 500);
        }
        
        if (!is_numeric($channel_id)) {
            throw new Zend_Exception( '$channel_id is not a digit', 500);
        }
         
        $where = array(
            "`BC`.`alias` LIKE '%$alias%'",
            "`EVT`.`start` < '".Zend_Date::now()->toString('YYYY-MM-dd 23:59:00')."'",
            
        );
        
        if (!$date){
            if (APPLICATION_ENV=='testing'){
                $where[] = "`EVT`.`start` >= '".Zend_Date::now()->toString('YYYY-MM-dd 00:00:00')."'";
            } else {
                $where[] = "`EVT`.`start` >= '".Zend_Date::now()->toString('YYYY-MM-dd HH:mm:00')."'";
            }
        } else {
            if (APPLICATION_ENV=='testing'){
                $where[] = "`EVT`.`start` >= '".$date->toString('YYYY-MM-dd 00:00:00')."'";
            } else {
                $where[] = "`EVT`.`start` >= '".$date->toString('YYYY-MM-dd HH:mm:00')."'";
            }
        }
        
        $where[] = "`EVT`.`channel`= ".(int)$channel_id;
        
        $select = $this->db->select()
            ->from(array('BC'=>$this->bcTable->getName()), array(
                'title',
                'sub_title',
                'alias',
                'age_rating',
                'country',
                'episode_num',
                'desc',
                'hash',
            ))
            ->joinLeft(array('EVT'=>$this->eventsTable->getName()), "`BC`.`hash`=`EVT`.`hash`", array(
                'premiere',
                'new',
                'live',
            ))
            ->joinLeft( array('CH'=>$this->channelsTable->getName()), "`EVT`.`channel`=`CH`.`id`", array(
                'channel_id'=>'id',
                'channel_title'=>'title',
                'channel_alias'=>'alias',
                'channel_desc'=>"CONCAT_WS( ' ', `desc_intro`, `desc_body` )",
            ))
            ->joinLeft( array('BCCAT'=>$this->bcCategoriesTable->getName()), "`BC`.`category`=`BCCAT`.`id`", array(
                'category_id'=>'id',
                'category_title'=>'title',
                'category_title_single'=>'title_single',
                'category_alias'=>'alias',
            ))
        ;
        
        if (!empty($where)){
            foreach ($where as $w){
                $select->where($w);
            }
        }
        
        if ($limit>0){
            $select->limit($limit)
                ->order( 'EVT.start DESC');
        } else {
            $select->limit(1);
        }
        
        $result = $this->db->fetchAll( $select );
        
        if (!$result || (count($result)==0)) {
            return false;
        }
        
        foreach ($result as $idx=>$row){
            $result[$idx]['start']    = new Zend_Date( $row['start'], 'YYYY-MM-dd HH:mm:ss' );
            $result[$idx]['end']      = new Zend_Date( $row['end'], 'YYYY-MM-dd HH:mm:ss' );
            $result[$idx]['new']      = (bool)$row['new'];
            $result[$idx]['live']     = (bool)$row['live'];
            $result[$idx]['premiere'] = (bool)$row['premiere'];
            $result[$idx]['episode_num'] = (int)$row['episode_num'];
            if (strlen($row['hash'])!=32){
                throw new Zend_Exception("Data inregrity broken");
            }
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
    public function similarBroadcastsThisWeek($program_alias=null, Zend_Date $start, Zend_Date $end, $channel_id=null){
        
        if (!$program_alias) {
            throw new Zend_Exception( Rtvg_Message::ERR_WRONG_PARAM, 500);
        }
        
        $select = $this->db->select()
        ->from(array( 'BC'=>$this->table->getName()), array(
            'title',
            'sub_title',
            'alias',
            'desc',
            'age_rating',
            'episode_num',
            'hash' ))
        ->joinLeft(array('EVT'=>$this->eventsTable->getName()), "BC.hash=EVT.hash")
        ->joinLeft( array('CH'=>$this->channelsTable->getName() ), "`EVT`.`channel`=`CH`.`id`", array(
            'channel_id'=>'id',
            'channel_title'=>'title',
            'channel_alias'=>'LOWER(`CH`.`alias`)',
            'channel_icon'=>'icon'))
        ->joinLeft( array('BCCAT'=>$this->bcCategoriesTable->getName()), "`BC`.`category`=`BCCAT`.`id`", array(
            'category_id'=>'title',
            'category_title'=>'title',
            'category_title_single'=>'title_single',
            'category_alias'=>'LOWER(`BCCAT`.`alias`)' ));
        
        $parts = explode('-', $program_alias);
        $where = array();
        $regex = array();
        foreach ($parts as $a){
            if (Xmltv_String::strlen($a)>=7){
                $r = Xmltv_String::substr($a, 0, Xmltv_String::strlen($a)-2);
                $regex[] = $r;
                $where[] = " `BC`.`alias` LIKE '%$r%'";
            }
        }
        $where[]=" `BC`.`alias` LIKE '%$program_alias%'";
        
        if (count($where)) {
            $where = implode(' OR ', $where);
            $select->where( $where );
        }
        
        $select
            ->where( "`EVT`.`start` > '".$start->toString('YYYY-MM-dd')." 00:00'" )
            ->where( "`EVT`.`start` < '".$end->toString('YYYY-MM-dd')." 23:59'" )
            ->where( "`CH`.`published`='1'")
            ->where( "`CH`.`lang`='ru'")
            ->group( "EVT.start")
            ->order( "EVT.channel ASC" )
            ->order( "EVT.start DESC" );    

        if ($channel_id && is_numeric($channel_id)){
            $select->where( "`EVT`.`channel` != '$channel_id'");
        }
        
        $result = $this->db->fetchAll($select);
        
        if (count($result)){
            foreach ($result as $k=>$item){
                $result[$k]['start'] = new Zend_Date( $item['start'], 'YYYY-MM-dd HH:mm:ss');
                $result[$k]['end']   = new Zend_Date( $item['end'], 'YYYY-MM-dd HH:mm:ss');
                $result[$k]['age_rating'] = (int)$item['age_rating'];
                $result[$k]['episode_num'] = (int)$item['episode_num'];
                $result[$k]['channel_id'] = (int)$item['channel_id'];
                $result[$k]['category_id'] = (int)$item['category_id'];
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
    public function getSimilarProgramsForDay(Zend_Date $date, $program_alias = null, $channel_id = null){
        
        if (!$program_alias) {
            throw new Zend_Exception( Rtvg_Message::ERR_WRONG_PARAM, 500);
        }
        
        $select = $this->db->select()
            ->from(array( 'BC'=>$this->table->getName()), array(
                'title',
                'sub_title',
                'alias',
                'age_rating',
                'episode_num',
                'hash' 
            ))
            ->join(array('EVT'=>$this->eventsTable->getName()), "`BC`.`hash`=`EVT`.`hash`", array(
                'start',
                'end',
                'premiere',
                'new',
                'live',
            ))
            ->join( array('CH'=>$this->channelsTable->getName() ), "`EVT`.`channel`=`CH`.`id`", array(
                'channel_id'=>'id',
                'channel_title'=>'title',
                'channel_alias'=>'alias',
                'channel_icon'=>'icon'
            ))
            ->join( array('BCCAT'=>$this->bcCategoriesTable->getName()), "`BC`.`category`=`BCCAT`.`id`", array(
                'category_id'=>'title',
                'category_title'=>'title',
                'category_title_single'=>'title_single',
                'category_alias'=>'alias'
            ))
            ->where( "`EVT`.`start` >= '".$date->toString('YYYY-MM-dd 00:00:00')."'" )
            ->where( "`CH`.`published`='1'")
            ->order( "EVT.channel ASC" )
            ->order( "EVT.start DESC" )
            ->where( "`EVT`.`channel` = ".(int)$channel_id)
        ;
        
        
        
        $parts = explode('-', $program_alias);
        $where = array();
        $regex = array();
        foreach ($parts as $a){
            if (Xmltv_String::strlen($a)>=5 && !is_numeric($a)){
                $r = Xmltv_String::substr($a, 0, Xmltv_String::strlen($a)-3);
                $regex[] = $r;
                $where[] = " `BC`.`alias` LIKE '%$r%'";
            }
        }
        $where[]=" `BC`.`alias` LIKE '%$program_alias%'";
        
        $where = implode(' OR ', $where);
        $select->where( $where );
        
        $result = $this->db->fetchAll($select);
        
        if (count($result)){
            foreach ($result as $k=>$item){
                $result[$k]['start'] = new Zend_Date( $item['start'], 'YYYY-MM-dd HH:mm:ss');
                $result[$k]['end']   = new Zend_Date( $item['end'], 'YYYY-MM-dd HH:mm:ss');
                $result[$k]['premiere'] = (bool)$item['premiere'];
                $result[$k]['new'] = (bool)$item['new'];
                $result[$k]['live'] = (bool)$item['live'];
                $result[$k]['age_rating'] = (int)$item['age_rating'];
                $result[$k]['channel_id'] = (int)$item['channel_id'];
            }
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
    public function broadcastThisWeek ($prog_alias=null, $channel_id=null, Zend_Date $start, Zend_Date $end) {
        
        if( !$prog_alias || !$channel_id || !$start || !$end ){
            throw new Zend_Exception( Rtvg_Message::ERR_MISSING_PARAM, 500 );
        }
        
        /**
         * @var Zend_Db_Select
         */
        $select = $this->db->select()
            ->from(array( 'BC'=>'rtvg_bc'), array(
                'title',
                'sub_title',
                'alias',
                'episode_num',
                'hash'
            ))
            ->joinLeft(array("EVT"=>$this->eventsTable->getName()), "BC.hash=EVT.hash", array(
                'channel',
                'start',
                'end',
                'premiere',
                'live',
                'new'
            ))
            ->joinLeft( array('CH'=>$this->channelsTable->getName() ), "`EVT`.`channel`=`CH`.`id`", array(
                'channel_title'=>'title',
                'channel_alias'=>'LOWER(`CH`.`alias`)'))
            ->joinLeft( array('BCCAT'=>$this->bcCategoriesTable->getName() ), "`BC`.`category`=`BCCAT`.`id`", array(
                'category_title'=>'title',
                'category_title_single'=>'title_single',
                'category_alias'=>'LOWER(`BCCAT`.`alias`)'))
            ->where( "`BC`.`alias` LIKE '$prog_alias'")
            ->where( "`EVT`.`start` >= '".$start->toString('YYYY-MM-dd')." 00:00'")
            ->where( "`EVT`.`start` < '".$end->toString('YYYY-MM-dd')." 23:59'")
            ->where( "`EVT`.`channel` = '$channel_id'")
            ->order( "EVT.start DESC" );
        
        $result = $this->db->fetchAll($select, null, Zend_Db::FETCH_ASSOC);
        
        if (!count($result)){
            return false;
        }
        
        foreach ($result as $k=>$item){
            $result[$k]['start'] = isset($item['start']) && $item['start']!==null ? new Zend_Date( $item['start'], 'yyyy-MM-dd HH:mm:ss') : null ;
            $result[$k]['end']   = isset($item['end']) && $item['end']!==null ? new Zend_Date( $item['end'], 'yyyy-MM-dd HH:mm:ss') : null ;
            $result[$k]['episode_num'] = isset($item['episode_num']) ? (int)$item['episode_num'] : 0 ;
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
    public function addHit($hash=null){
        
        $table = new Xmltv_Model_DbTable_ProgramsRatings();
        $table->addHit($hash);
        
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
     * Programs category listing for week
     * 
     * @param  id        $category_id
     * @param  Zend_Date $start
     * @param  Zend_Date $end
     * @return array
     */
    public function categoryWeek( $category_id=null, Zend_Date $start, Zend_Date $end){
        
        $select = $this->db->select()
            ->from( array('BC'=>$this->bcTable->getName()), array(
                    'title',
                    'sub_title',
                    'alias',
                    'desc',
                    'age_rating',
                    'country',
                    'episode_num',
                    'hash',
            ))
            ->joinLeft(array("EVT"=>$this->eventsTable->getName()), "`BC`.`hash`=`EVT`.`hash`", array(
                'start',
                'end',
                'premiere',
                'new',
                'live',
            ))
            ->joinLeft(array('BCCAT'=>$this->bcCategoriesTable->getName()), "`BC`.`category`=`BCCAT`.`id`", array(
                'category_title'=>'title',
                'category_title_single'=>'title_single',
                'category_alias'=>'alias',
            ))
            ->joinLeft(array('CH'=>$this->channelsTable->getName()), "`EVT`.`channel`=`CH`.`id`", array(
                'channel_id'=>'id',
                'channel_title'=>'title',
                'channel_alias'=>'alias',
            ))
            ->where("`BC`.`category`='$category_id'")
            ->where("`EVT`.`start` >= '".$start->toString('YYYY-MM-dd 00:00:00')."'")
            ->where("`EVT`.`start` < '".$end->toString('YYYY-MM-dd 23:59:00')."'")
            ->where("`CH`.`published` = TRUE")
            ->group("EVT.start")
            ->order( array( "EVT.start ASC", "CH.title ASC" ))
        ;
         
        $result = $this->db->fetchAll($select);
        
        if (count($result)){
            foreach ($result as $k=>$p){
                $result[$k]['start'] = new Zend_Date( $p['start'], 'YYYY-MM-dd HH:mm:ss');
                $result[$k]['end']   = new Zend_Date( $p['end'], 'YYYY-MM-dd HH:mm:ss');
                $result[$k]['age_rating'] = (int)$p['age_rating'];
                $result[$k]['episode_num'] = (int)$p['episode_num'];
            } 
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
        
        if (empty($channels)){
            throw new Zend_Exception( "Channels cannot be empty" );
        }
        if (!is_array($channels)){
            throw new Zend_Exception( "Channels must be an array" );
        }
        
        //var_dump(func_get_args());
        //die(__FILE__ . ': ' . __LINE__);
        
        $select = $this->db->select()
            ->from( array('BC'=>$this->bcTable->getName()), array(
                'hash',
                'title',
                'sub_title',
                'alias',
                'age_rating',
            ))
            ->join(array('EVT'=>$this->eventsTable->getName()), "`BC`.`hash`=`EVT`.`hash`", array(
                'start',
                'end',
                'channel'
            ))
            ->join( array('BCCAT'=>$this->bcCategoriesTable->getName()), "`BC`.`category` = `BCCAT`.`id`", array(
                'category'=>'id',
                'category_title'=>'title',
                'category_alias'=>'alias',
                'category_single'=>'title_single'
            ))
            ->join( array('CH'=>$this->channelsTable->getName()), "`EVT`.`channel` = `CH`.`id`", array(
                'channel'=>'id',
                'channel_title'=>'title',
                'channel_alias'=>'alias',
                'adult',
            ))
            ->join(array('CHRAT'=>$this->channelsRatingsTable->getName()), "`CH`.`id` = `CHRAT`.`channel`", null)
            ->where("`EVT`.`start` >= ".$this->db->quote(Zend_Date::now()->toString("YYYY-MM-dd 00:00:00")))
            ->where("`EVT`.`start` < ".$this->db->quote(Zend_Date::now()->addHour(6)->toString("YYYY-MM-dd HH:mm:00")))
            ->group("EVT.start")
            ->order(array(
                "EVT.channel ASC", 
                "EVT.start ASC",
                "CHRAT.hits DESC",
            ));
        
        if ((bool)Zend_Registry::get('adult') !== true) {
            $select->where("`CH`.`adult` IS NULL");
            $select->where("`BC`.`age_rating` <= 16 OR `BC`.`age_rating` = 0");
        }
        
        if (is_array($channels) && !empty($channels)){
            $ids = array();
            foreach ($channels as $i){
                $ids[] = "'".$i['id']."'";
            }
            $select->where("EVT.channel IN (".implode(",", $ids).")");
        }
        
        $result = $this->db->fetchAll($select->assemble());
        
        if (!empty($result)){
            $items = array();
            $now = Zend_Date::now();
            foreach ($result as $k=>$d){
                $end = new Zend_Date($d['end']);
                if ($end->compare($now) >= 0) {
                    $items[$d['channel']][$d['hash']] = $d;
                    $items[$d['channel']][$d['hash']]['start'] = new Zend_Date($d['start'], 'YYYY-MM-dd HH:mm:ss');
                    $items[$d['channel']][$d['hash']]['end']   = new Zend_Date($d['end'], 'YYYY-MM-dd HH:mm:ss');
                    $items[$d['channel']][$d['hash']]['age_rating'] = (int)$d['age_rating']>0 ? (int)$d['age_rating']>0 : null ;
                    $items[$d['channel']][$d['hash']]['channel'] = (int)$d['channel'];
                    $items[$d['channel']][$d['hash']]['category'] = (int)$d['category'];
                }
            }
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
        
        if (!$date){
            $date = Zend_Date::now();
        }
        
        $select = $this->db->select()
            ->from( array('BC'=>$this->table->getName()), array(
                'title',
                'alias',
                'desc',
                'age_rating',
                'image',
                'country',
                'episode_num',
                'hash',
            ))
            ->joinLeft(array('EVT'=>$this->eventsTable->getName()), "`BC`.`hash` = `EVT`.`hash`", array(
                'start',
                'end',
                'premiere',
                'new',
                'live',
            ))
            ->joinLeft(array('BCCAT'=>$this->bcCategoriesTable->getName()), "`BC`.`category`=`BCCAT`.`id`", array(
                'prog_category_title'=>'title',
                'prog_category_alias'=>'alias',
                'prog_category_single'=>'title_single',
            ))
            ->joinLeft(array('CH'=>$this->channelsTable->getName()), "`EVT`.`channel`=`CH`.`id`", array(
                'channel_title'=>'title',
                'channel_alias'=>'alias',
            ))
            ->joinLeft(array('CHCAT'=>$this->channelsCategoriesTable->getName()), "`CH`.`category`=`CHCAT`.`id`", array(
                'channel_category_title'=>'title',
                'channel_category_alias'=>'alias',
            ))
            ->where("`BC`.`category`='$category_id'")
            ->where("`EVT`.`start` >= '".$date->toString('YYYY-MM-dd 00:00:00')."'")
            ->where("`EVT`.`start` < '".$date->toString('YYYY-MM-dd 23:59:00')."'")
            ->where("`CH`.`published` = '1'")
            ->group("EVT.start")
            ->order( array( "EVT.start ASC", "CH.title ASC" ));
    
        $result = $this->db->fetchAssoc($select->assemble());
        
        foreach ($result as $k=>$p){
            $result[$k]['start'] = new Zend_Date($p['start'], 'YYYY-MM-dd HH:mm:ss');
            $result[$k]['end']   = new Zend_Date($p['end'], 'YYYY-MM-dd HH:mm:ss');
            $result[$k]['age_rating'] = (int)$p['age_rating'];
            $result[$k]['episode_num'] = (int)$p['episode_num'];
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
            return $this->bcCategoriesTable->fetchAll(null, $order)->toArray();
        } else {
            return $this->bcCategoriesTable->fetchAll()->toArray();
        }
        
    }
    
    /**
     * 
     * Load top broadcasts list
     * 
     * @param int $amt
     * @param Zend_Date $week_start // Optional
     * @param Zend_Date $week_end // Optional
     * @return array
     */
    public function topBroadcasts($amt=25, $week_start=null, $week_end=null){
        
        if (!$week_start || !$week_end){
            $weekDays = new Zend_Controller_Action_Helper_WeekDays();
            if (!$week_start){
                $week_start = $weekDays->getStart(Zend_Date::now());
            }
            if (!$week_end){
                $week_end   = $weekDays->getEnd(Zend_Date::now());
            }
        }
        
        $select = $this->db->select()
            ->from(array('BC'=>$this->bcTable->getName()), array(
                'title',
                'alias',
                'desc',
                'episode_num',
                'age_rating',
            ))
            ->join(array('EVT'=>$this->eventsTable->getName()), "`BC`.`hash` = `EVT`.`hash`", array(
                'live',
                'premiere',
                'new'
            ))
            ->join(array('RT'=>$this->bcRatingsTable->getName()), "`BC`.`hash`=`RT`.`hash`", array(
                'hits',
                'star_rating'
            ))
            ->join(array('CH'=>$this->channelsTable->getName()), "`CH`.`id`=`EVT`.`channel`", array(
                'channel_id'=>'CH.id',
                'channel_title'=>'CH.title',
                'channel_alias'=>'LOWER(`CH`.`alias`)',
                'channel_icon'=>'CH.icon'
            ))
            ->join(array('BCCAT'=>$this->bcCategoriesTable->getName()), "`BCCAT`.`id`=`BC`.`category`", array(
                'category_title'=>'title_single',
                'category_title_multi'=>'title',
                'category_alias'=>'alias',
            ))
            ->join(array('CHCAT'=>$this->channelsCategoriesTable->getName()), "`CH`.`category`=`CHCAT`.`id`", array(
                'channel_category_title'=>'title',
                'channel_category_alias'=>'alias',
                'channel_category_icon'=>'image',
            ))
            ->where("`CH`.`published` = TRUE")
            ->where("`EVT`.`start` >= '".$week_start->toString("YYYY-MM-dd 00:00:00")."'")
            ->where("`EVT`.`start` < '".$week_end->toString("YYYY-MM-dd 23:59:00")."'")
            ->group("BC.alias")
            ->order("RT.hits DESC")
            ->limit((int)$amt)
        ;
        
        if ((bool)Zend_Registry::get('adult') !== true){
            $select->where("`CH`.`adult` IS NULL");
            $select->where("`BC`.`age_rating` <= 16 OR `BC`.`age_rating` = 0");
        }
        
        $result = $this->db->fetchAll($select);
        
        if (count($result)){
            foreach ($result as $k=>$item){
                $result[$k]['live'] = (bool)$item['live'];
                $result[$k]['episode_num'] = (int)$item['episode_num']>0 ? (int)$item['episode_num'] : null ;
                $result[$k]['premiere'] = (bool)$item['premiere'];
                $result[$k]['new'] = (bool)$item['new'];
                $result[$k]['hits'] = (int)$item['hits'];
                $result[$k]['star_rating'] = floatval($item['star_rating']);
                $result[$k]['channel_id'] = (int)$item['channel_id'];
                $result[$k]['age_rating'] = (int)$item['age_rating'];
                ksort($result[$k]);
            }
        }
        
        return $result;
        
    }
    
    /**
     * 
     * @param Zend_Date $week_start
     * @param Zend_Date $week_end
     */
    public function rssWeek(Zend_Date $week_start=null, Zend_Date $week_end=null){
        
        $select = $this->db->select()
            ->from(array('BC'=>$this->bcTable->getName()), array(
                'alias'
            ))
            ->joinLeft(array('EVT'=>$this->eventsTable->getName()), "`BC`.`hash`=`EVT`.`hash`", null)
            ->joinLeft(array('CH'=>$this->channelsTable->getName()), "`EVT`.`channel`=`CH`.`id`", array(
                    'channel_alias'=>'alias',
            ))
            ->joinLeft(array('RT'=>$this->channelsRatingsTable->getName()), "`EVT`.`channel`=`RT`.`channel`", null)
            ->where("`EVT`.`start` >= '".$week_start->toString("YYYY-MM-dd 00:00:00")."'")
            ->where("`EVT`.`start` < '".$week_end->toString("YYYY-MM-dd 23:59:59")."'")
            ->order("EVT.start ASC")
        ;
        
        if ((bool)Zend_Registry::get('adult') !== true) {
            $select->where("`CH`.`adult` IS NULL");
            $select->where("`BC`.`age_rating` <= 16 OR `BC`.`age_rating` = 0");
        }
    
        $result = $this->db->fetchAll($select);
        
        if (count($result)){
            foreach ($result as $k=>$row){
                $encoded = urlencode($row['alias']);
                if (strlen($encoded)>254){
                    unset($result[$k]);
                }
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
            if(Xmltv_String::strtolower($ru_title)==Xmltv_String::strtolower($ru)){
                return $iso;
            }
        }
    }
    
    /**
	 * Current date from request variable
	 */
	public function listingDate(Zend_Filter_Input $validator){
        
        if (!$validator) {
            throw new Zend_Controller_Action_Exception("Validator not passed", 500);
        }
		
	    $now = Zend_Date::now();
		if (preg_match('/^[\d]{2}-[\d]{2}-[\d]{4}$/', $validator->getEscaped('date'))) {
			$d = new Zend_Date( new Zend_Date( $validator->getEscaped('date'), 'dd-MM-YYYY' ), 'dd-MM-YYYY' );
		} elseif (preg_match('/^[\d]{4}-[\d]{2}-[\d]{2}$/', $validator->getEscaped('date'))) {
			$d = new Zend_Date( new Zend_Date( $validator->getEscaped('date'), 'YYYY-MM-dd' ), 'YYYY-MM-dd' );
			
		}
		
		if (isset($d) && ($d->compare($now, 'DD')!=0)) {
		    $date = $d->toString('YYYY-MM-dd');
		    $time = $now->toString('HH:mm:ss');
		    return new Zend_Date( $date.' '.$time, 'YYYY-MM-dd HH:mm:ss' );
		} else {
			return $now;
		}
		
		return $d;
		
	}
    
    public function getCategoryByAlias($alias){
        
        return $this->bcCategoriesTable->fetchRow("alias='$alias'");
        
    }
    
}

