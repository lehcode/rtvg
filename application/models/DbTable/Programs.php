<?php

class Xmltv_Model_DbTable_Programs extends Zend_Db_Table_Abstract
{

	protected $_name = 'rtvg_programs';

	public $debug = false;

	const FETCH_MODE = Zend_Db::FETCH_OBJ;


	public function __construct ($config = array()) {

		parent::__construct( $config );
		
		$siteConfig = Zend_Registry::get( 'site_config' )->site;
		$this->debug = (bool)$siteConfig->get( 'debug', false );
		
		if( $this->debug ) $this->_db->getProfiler()->setEnabled( true );
	
	}


	public function getPremieres (Zend_Date $start, Zend_Date $end) {

		if( $this->debug )
		$profiler = $this->_db->getProfiler();
		
		$select = $this->_db->select()->from( array('p'=>$this->_name), '*' )
			->join( 'rtvg_programs_props', "p.`alias`=rtvg_programs_props.`title_alias` ", 
			array('actors', 'directors', 'premiere', 'premiere_date', 'rating') )
			->join( 'rtvg_programs_descriptions', "p.`alias`=rtvg_programs_descriptions.`title_alias`", 
			array('desc_intro'=>'intro', 'desc_body'=>'body') )
			->join(array('ch'=>'rtvg_channels'), "p.`ch_id`=ch.`ch_id`", array('channel_title'=>'ch.title', 'channel_alias'=>'ch.alias'))
			->join(array('cat'=>'rtvg_programs_categories'), "p.`category`=cat.`id`", array('category_title'=>'cat.title'))
			->where( "`start` >= '" . $start->toString( 'yyyy-MM-dd 00:00:00' ) . "'" )
			->where( "`end` <= '" . $end->toString( 'yyyy-MM-dd 23:59:59' ) . "'" )
			->where( "`new` = '1'" );
		$result = $this->_db->query( $select )->fetchAll( self::FETCH_MODE );
		
		if( $this->debug ) {
			//$query = $profiler->getLastQueryProfile();
			//echo $query->getElapsedSecs().': '.$query->getQuery();
		}
		
		foreach ($result as $k=>$row) {
			$result[$k]->start = new Zend_Date($row->start);
			$result[$k]->end   = new Zend_Date($row->end);
		}
		
		return $result;
	}

}

