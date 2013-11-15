<?php
/**
 * Programs database table class
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @version $Id: Programs.php,v 1.23 2013-04-11 05:21:11 developer Exp $
 *
 */
class Xmltv_Model_DbTable_Programs extends Xmltv_Db_Table_Abstract
{

	protected $_name = 'bc';
	protected $_primary = 'hash';
    protected $_rowClass = 'Rtvg_Broadcast';
    /**
     * @var Xmltv_Model_DbTable_Events 
     */
    private $_eventsTable;
    
    /**
     * @var Xmltv_Model_DbTable_Channels 
     */
    private $_channelsTable;
    
    /**
     * @var Xmltv_Model_DbTable_ProgramsCategories 
     */
    private $_bcCategoriesTable;
    
    
    public function init()
    {
        parent::init();
        $this->_bcCategoriesTable = new Xmltv_Model_DbTable_ProgramsCategories();
        $this->_channelsTable = new Xmltv_Model_DbTable_Channels();
        $this->_eventsTable = new Xmltv_Model_DbTable_Events();
    }
    
    /**
     * Required because parent class is abstract
     */
    public function getBroadcasts($channel_id=null, $date=null, $count=null){
        return $this->fetchDayItems($channel_id, $date, $count);
    }

    /**
	 * 
	 * Load premieres list
	 * @param  Zend_Date $start
	 * @param  Zend_Date $end
	 * @return array
     * @deprecated since version 5.4
	 */
	public function getPremieres (Zend_Date $start, Zend_Date $end) {

		$select = $this->_db->select()->from( array('p'=>$this->_name), '*' )
			->joinLeft( 'rtvg_programs_props', "p.`hash`=rtvg_programs_props.`hash` ", 
			array('actors', 'directors', 'premiere', 'premiere_date', 'rating') )
			->joinLeft( 'rtvg_programs_descriptions', "p.`hash`=rtvg_programs_descriptions.`hash`", 
			array('desc_intro'=>'intro', 'desc_body'=>'body') )
			->joinLeft(array('ch'=>'rtvg_channels'), "p.`ch_id`=ch.`ch_id`", array('channel_title'=>'ch.title', 'channel_alias'=>'LOWER(`ch`.`alias`)'))
			->joinLeft(array('cat'=>'rtvg_programs_categories'), "p.`category`=cat.`id`", array('category_title'=>'cat.title'))
			->where( "`p`.`start` >= '" . $start->toString( 'yyyy-MM-dd 00:00:00' ) . "'" )
			->where( "`p`.`end` <= '" . $end->toString( 'yyyy-MM-dd 00:00:00' ) . "'" )
			->where( "`p`.`new` = '1'" )
			->where( "`ch`.`published` = '1'" )
			->group( "p.alias" )
			->order( "p.start ASC" );
		$result = $this->_db->query( $select )->fetchAll( self::FETCH_MODE );
		
		foreach ($result as $k=>$row) {
			$result[$k]->start = new Zend_Date($row->start);
			$result[$k]->end   = new Zend_Date($row->end);
		}
		
		return $result;
	}
	
	
	/**
	 * @param int $channel_id
	 * @param string $date
	 * @param int $count // optional
	 */
	public function fetchDayItems($channel_id=null, $date=null) {
		
        if (!$channel_id || !$date) {
			throw new Zend_Db_Table_Exception( Rtvg_Message::ERR_MISSING_PARAM );
		}
		
		$select = $this->_db->select()
            ->from( array( 'BC'=>$this->getName()), array(
                'title',
                'sub_title',
                'alias',
                'category',
                'image',
                'episode_num',
                'date',
                'desc',
                'hash',
            ))
            ->joinLeft(array('EVT'=>$this->_eventsTable->getName()), "`BC`.`hash`=`EVT`.`hash`", array(
                'channel',
                'start',
                'end',
                'premiere',
                'new',
                'live'
            ))
            ->joinLeft( array('CAT'=>$this->_bcCategoriesTable->getName()), "`BC`.`category`=`CAT`.`id`", array( 
                'category_id'=>'id',
                'category_title'=>'title',
                'category_title_single'=>'title_single',
                'category_alias'=>'alias',
            ))
            ->joinLeft( array( 'CH'=>$this->_channelsTable->getName()), "`EVT`.`channel`=`CH`.`id`", array(
                'channel_id'=>'id',
                'channel_title'=>'title',
                'channel_alias'=>'alias',
            ))
            ->joinLeft(array('CHC'=>'rtvg_countries'), "`CH`.`country`=`CHC`.`iso`", array(
                'channel_country_name'=>'name',
                'channel_country_iso'=>'iso',
            ))
            ->joinLeft(array('BCC'=>'rtvg_countries'), "`BC`.`country`=`BCC`.iso", array(
                'bc_country_name'=>'name',
                'bc_country_iso'=>'iso',
            ))
            ->where("`EVT`.`start` >= ".$this->_db->quote( Zend_Date::now()->toString("YYYY-MM-dd")." 00:00:00") )
            ->where("`EVT`.`start` < ".$this->_db->quote( Zend_Date::now()->addDay(2)->toString("YYYY-MM-dd")." 00:00") )
            ->where( "`EVT`.`channel` = $channel_id")
            ->where( "`CH`.`published` = TRUE")
            ->group( "EVT.hash")
            ->order( "EVT.start ASC")
        ;
        
        $result = $this->_db->fetchAssoc($select->assemble());
		
        foreach ($result as $k=>$row) {
			$result[$k]['start'] = new Zend_Date( $row['start'] );
			$result[$k]['end']   = new Zend_Date( $row['end'] );
			$result[$k]['premiere'] = isset($row['premiere']) ? (bool)$row['premiere'] : false ;
			$result[$k]['live']	    = isset($row['live']) ? (bool)$row['live'] : false ;
			$result[$k]['new']	    = isset($row['new']) ? (bool)$row['new'] : false ;
		}
		
        return $result;
		
	}

}

