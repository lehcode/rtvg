<?php

/**
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: Programs.php,v 1.4 2012-05-20 08:58:56 dev Exp $
 *
 */
class Xmltv_Model_DbTable_Programs extends Zend_Db_Table_Abstract
{

	protected $_name = 'rtvg_programs';
	private $_debug;
	private $_profiling;

	const FETCH_MODE = Zend_Db::FETCH_OBJ;


	public function __construct ($config = array()) {

		parent::__construct( $config );
		
		$this->_debug = Xmltv_Config::getDebug();
		$this->_profiling = Xmltv_Config::getProfiling();
		
	}


	public function getPremieres (Zend_Date $start, Zend_Date $end) {

		if( $this->_profiling ) {
			$this->_db->getProfiler()->setEnabled( true );
			$profiler = $this->_db->getProfiler();
		}
		
		$select = $this->_db->select()->from( array('p'=>$this->_name), '*' )
			->joinLeft( 'rtvg_programs_props', "p.`hash`=rtvg_programs_props.`hash` ", 
			array('actors', 'directors', 'premiere', 'premiere_date', 'rating') )
			->joinLeft( 'rtvg_programs_descriptions', "p.`hash`=rtvg_programs_descriptions.`hash`", 
			array('desc_intro'=>'intro', 'desc_body'=>'body') )
			->joinLeft(array('ch'=>'rtvg_channels'), "p.`ch_id`=ch.`ch_id`", array('channel_title'=>'ch.title', 'channel_alias'=>'ch.alias'))
			->joinLeft(array('cat'=>'rtvg_programs_categories'), "p.`category`=cat.`id`", array('category_title'=>'cat.title'))
			->where( "p.`start` >= '" . $start->toString( 'yyyy-MM-dd 00:00:00' ) . "'" )
			->where( "p.`end` <= '" . $end->toString( 'yyyy-MM-dd 00:00:00' ) . "'" )
			->where( "p.`new` = '1'" )
			->group( "p.alias" )
			->order( "p.start ASC" );
		$result = $this->_db->query( $select )->fetchAll( self::FETCH_MODE );
		
		if( $this->_profiling ) {
			$query = $profiler->getLastQueryProfile();
			echo 'Method: '.__METHOD__.'<br />Time: '.$query->getElapsedSecs().'<br />Query: '.$query->getQuery().'<br />';
		}
		
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
		throw new Zend_Exception("Не передаг один или более параметров для ".__FUNCTION__, 500);
		
		if( $this->_profiling ) {
			$this->_db->getProfiler()->setEnabled( true );
			$profiler = $this->_db->getProfiler();
		}
		
		$select = $this->_db->select()->from( array('p'=>$this->_name), '*' )
			->joinLeft( 'rtvg_programs_props', "p.`hash`=rtvg_programs_props.`hash` ", 
			array('actors', 'directors', 'premiere', 'premiere_date', 'rating') )
			->joinLeft( 'rtvg_programs_descriptions', "p.`hash`=rtvg_programs_descriptions.`hash`", 
			array('desc_intro'=>'intro', 'desc_body'=>'body') )
			//->joinLeft(array('ch'=>'rtvg_channels'), "p.`ch_id`=ch.`ch_id`", array('channel_title'=>'ch.title', 'channel_alias'=>'ch.alias'))
			->joinLeft(array('cat'=>'rtvg_programs_categories'), "p.`category`=cat.`id`", array('category_title'=>'cat.title'))
			->where( "p.`start` LIKE '$date%'" )
			->where( "p.`ch_id` = '$channel_id'" )
			->order( "p.start ASC" );
		$result = $this->_db->query( $select )->fetchAll( self::FETCH_MODE );
		
		if( $this->_profiling ) {
			$query = $profiler->getLastQueryProfile();
			echo 'Method: '.__METHOD__.'<br />Time: '.$query->getElapsedSecs().'<br />Query: '.$query->getQuery().'<br />';
		}
		
		$serializer = new Zend_Serializer_Adapter_Json();
		foreach ($result as $k=>$row) {
			$result[$k]->start = new Zend_Date($row->start);
			$result[$k]->end   = new Zend_Date($row->end);
			if (!empty($result[$k]->actors)) {
				$ids = $serializer->unserialize($result[$k]->actors);
				$table = new Xmltv_Model_DbTable_Actors();
				$result[$k]->actors = $table->fetchAll("`id` IN ( ".implode(',', $ids)." )")->toArray();
			}
			if (!empty($result[$k]->directors)) {
				$ids = $serializer->unserialize($result[$k]->directors);
				$table = new Xmltv_Model_DbTable_Directors();
				$result[$k]->directors = $table->fetchAll("`id` IN ( ".implode(',', $ids)." )")->toArray();
			}
		}
		
		return $result;
		
	}
	
	public function fetchProgramThisDay($program_alias=null, $channel_alias=null, Zend_Date $date){
		
		if(Xmltv_Config::getProfiling()) {
			$this->_db->getProfiler()->setEnabled( true );
			$profiler = $this->_db->getProfiler();
		}
		
		$channels = new Xmltv_Model_DbTable_Channels();
		$ch_id = (int)$channels->find($channel_alias)->current()->ch_id;
		
		$select = $this->_db->select()
			->from('rtvg_programs', '*')
			->joinLeft("rtvg_programs_props", "rtvg_programs_props.`hash` = rtvg_programs.`hash`", array('actors', 'directors', 'premiere', 'live'))
			->joinLeft("rtvg_programs_descriptions", "rtvg_programs_descriptions.`hash` = rtvg_programs.`hash`", array('intro', 'body'))
			->where("rtvg_programs.`alias` LIKE '$program_alias'")
			->where("rtvg_programs.`start` LIKE '".$date->toString('yyyy-MM-dd')."%'")
			->where("rtvg_programs.`ch_id` = '$ch_id'");
		
		try {
			$result = $this->_db->fetchAll($select);
		} catch (Exception $e) {
			if ($this->debug) {
				echo $e->getMessage();
				var_dump($e->getTrace());
				exit();
			}
		}
		
		if(Xmltv_Config::getProfiling()) {
			$query = $profiler->getLastQueryProfile();
			echo $query->getElapsedSecs().': '.$query->getQuery();
		}
		
		return $result;
		
	}
	
	public function fetchProgramThisWeek($program_alias=null, $channel_alias=null, Zend_Date $date){
		
		if(Xmltv_Config::getProfiling()) {
			$this->_db->getProfiler()->setEnabled( true );
			$profiler = $this->_db->getProfiler();
		}
		
		$channels = new Xmltv_Model_DbTable_Channels();
		$ch_id = (int)$channels->find($channel_alias)->current()->ch_id;
		
		$select = $this->_db->select()
			->from('rtvg_programs', '*')
			->joinLeft("rtvg_programs_props", "rtvg_programs_props.`hash` = rtvg_programs.`hash`", array('actors', 'directors', 'premiere', 'live'))
			->joinLeft("rtvg_programs_descriptions", "rtvg_programs_descriptions.`hash` = rtvg_programs.`hash`", array('intro', 'body'))
			->where("rtvg_programs.`alias` LIKE '$program_alias'")
			->where("rtvg_programs.`start` >= '".$date->toString('yyyy-MM-dd HH:mm:ss')."'")
			->where("rtvg_programs.`ch_id` = '$ch_id'");
		
		try {
			$result = $this->_db->fetchAll($select);
		} catch (Exception $e) {
			if ($this->debug) {
				echo $e->getMessage();
				var_dump($e->getTrace());
				exit();
			}
		}
		
		if(Xmltv_Config::getProfiling()) {
			$query = $profiler->getLastQueryProfile();
			echo $query->getElapsedSecs().': '.$query->getQuery();
		}
		
		return $result;
		
	}

}

