<?php

/**
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: Programs.php,v 1.17 2013-03-01 19:37:58 developer Exp $
 *
 */
class Xmltv_Model_DbTable_Programs extends Xmltv_Db_Table_Abstract
{

	protected $_name = 'programs';
	protected $_primary = 'id';

	/**
	 * Constructor
	 * 
	 * @param array $config
	 */
	public function __construct ($config = array()) {

		parent::__construct(array('name'=>$this->_name));
		
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Zend_Db_Table_Abstract::_setup()
	 */
	protected function _setup(){
	
		parent::_setup();
		$now = Zend_Date::now();
		$this->_defaultValues = array(
				'id'=>0,
				'title'=>'',
				'sub_title'=>'',
				'alias'=>'',
				'channel'=>null,
				'start'=>null,
				'end'=>null,
				'category'=>null,
				'rating'=>null,
				'new'=>0,
				'live'=>0,
				'image'=>'',
				'last_chance'=>0,
				'previously_shown'=>null,
				'country'=>'',
				'actors'=>'',
				'directors'=>'',
				'writers'=>'',
				'adapters'=>'',
				'producers'=>'',
				'composers'=>'',
				'editors'=>'',
				'presenters'=>'',
				'commentators'=>'',
				'guests'=>'',
				'episode_num'=>null,
				'premiere'=>0,
				'date'=>null,
				'length'=>null,
				'desc'=>'',
				'hash'=>'',
		);
			
	}
	
	/**
	 * 
	 * Load premieres list
	 * @param  Zend_Date $start
	 * @param  Zend_Date $end
	 * @return array
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
	 */
	public function fetchDayItems($channel_id=null, $date=null) {
		
		if (!$channel_id || !$date)
			throw new Zend_Exception(parent::ERR_PARAMETER_MISSING.__METHOD__, 500);
		
		$categoriesTable = new Xmltv_Model_DbTable_ProgramsCategories();
		
		/**
		 * Create SQL query
		 * @var Zend_Db_Select
		 */
		$select = $this->_db->select()
			->from( array('prog'=>$this->getName()), array(
				'title',
				'sub_title',
				'alias',
				'channel',
				'start',
				'end',
				'episode_num',
				'hash',
				'rating'
			))
			->join( array('cat'=>$categoriesTable->getName()), "`prog`.`category` = `cat`.`id`", array(
				'category_id'=>'id',
				'category_title'=>'title',
				'category_alias'=>'alias',
			))
			->where( "`prog`.`start` >= '$date 00:00'" )
			->where( "`prog`.`start` < '$date 23:59'" )
			->where( "`prog`.`channel` = '$channel_id'" )
			->group( "prog.start" )
			->order( "prog.start ASC" );

		if (APPLICATION_ENV=='development'){
		    parent::debugSelect($select, __METHOD__);
			//die(__FILE__.": ".__LINE__);
		}
		
		$result = $this->_db->query( $select )->fetchAll( Zend_Db::FETCH_ASSOC );
		
		$actorsTable	= new Xmltv_Model_DbTable_Actors();
		$directorsTable = new Xmltv_Model_DbTable_Directors();
		foreach ($result as $k=>$row) {
			
			$result[$k]['start'] = new Zend_Date($row['start']);
			$result[$k]['end']   = new Zend_Date($row['end']);
			
			if (!empty($result[$k]->actors)) {
				if( !is_array($result[$k]->actors) ){
					$where = "`id` IN ( ".$result[$k]['actors']." )";
				}
				$result[$k]->actors = $actorsTable->fetchAll($where)->toArray();
			}
			
			if (!empty($result[$k]->directors)) {
				if( !is_array($result[$k]['directors']) ){
					$where = "`id` IN ( ".$result[$k]['directors']." )";
				}
				$result[$k]['directors'] = $directorsTable->fetchAll($where)->toArray();
			}
			
			$result[$k]['premiere'] = (bool)$result[$k]['premiere'];
			$result[$k]['live']	 = (bool)$result[$k]['live'];
			
		}
		
		if (APPLICATION_ENV=='development'){
			//var_dump($result);
			//die(__FILE__.": ".__LINE__);
		}
		
		return $result;
		
	}
	
	

}

