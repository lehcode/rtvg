<?php

/**
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @uses Xmltv_Db_Table_Abstract
 * @version $Id: Programs.php,v 1.20 2013-03-14 14:43:23 developer Exp $
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
	public function init() {
		parent::init();
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
		
		$d = new Zend_Date( $date->toString("U"), 'U');
		$channelsTable = new Xmltv_Model_DbTable_Channels();
		$categoriesTable = new Xmltv_Model_DbTable_ProgramsCategories();
		/**
		 * Create SQL query
		 * @var Zend_Db_Select
		 */
		$select = $this->_db->select()
			->from( array( 'prog'=>$this->getName()), array(
				'id',
				'title',
				'sub_title',
				'alias',
				'channel',
				'start',
				'end',
				'category',
				'rating',
				'new',
				//'live',
				'image',
				'last_chance',
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
				//'premiere',
				'date',
				'length',
				'desc',
				'hash',
			))
			->joinLeft( array( 'cat'=>$categoriesTable->getName()), "`prog`.`category`=`cat`.`id`", array( 
				'category_title'=>'title',
				'category_title_single'=>'title_single',
				'category_alias'=>'alias',
			))
			->join( array( 'ch'=>$channelsTable->getName()), "`prog`.`channel`=`ch`.`id`", array(
				'channel_title'=>'title',
				'channel_alias'=>'alias',
			))
			->where( "`prog`.`start` >= '".$d->toString('YYYY-MM-dd')." 00:00'")
			->where( "`prog`.`start` < '".$d->addDay(1)->toString('YYYY-MM-dd')." 00:00'")
			->where( "`prog`.`channel` = '$channel_id'")
			->where( "`ch`.`published` = '1'")
			->group( "prog.hash")
			->order( "prog.start", "ASC");
		
		if (APPLICATION_ENV=='development'){
			parent::debugSelect($select, __METHOD__);
			//die(__FILE__.': '.__LINE__);
		}
			
		
		$result = $this->_db->fetchAll($select, null, Zend_Db::FETCH_ASSOC );
		
		$actorsTable	= new Xmltv_Model_DbTable_Actors();
		$directorsTable = new Xmltv_Model_DbTable_Directors();
		foreach ($result as $k=>$row) {
			
			$result[$k]['start'] = new Zend_Date( $row['start'] );
			$result[$k]['end']   = new Zend_Date( $row['end'] );
			
			/*
			if (isset($row['actors']) && !empty($row['actors'])) {
				if( !is_array($row['actors']) ){
					$where = "`id` IN ( ".$row['actors']." )";
				}
				$result[$k]['actors'] = $actorsTable->fetchAll($where)->toArray();
			}
			
			if (isset($row['directors']) && !empty($row['directors'])) {
				if( !is_array($row['directors']) ){
					$where = "`id` IN ( ".$row['directors']." )";
				}
				$result[$k]['directors'] = $directorsTable->fetchAll($where)->toArray();
			}
			*/
			
			$result[$k]['premiere'] = isset($row['premiere']) ? (bool)$row['premiere'] : false ;
			$result[$k]['live']	    = isset($row['live']) ? (bool)$row['live'] : false ;
			
		}
		
		if (APPLICATION_ENV=='development'){
			//var_dump($result);
			//die(__FILE__.": ".__LINE__);
		}
		
		return $result;
		
	}
	
	

}

