<?php

/**
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: Programs.php,v 1.14 2013-02-15 00:44:02 developer Exp $
 *
 */
class Xmltv_Model_DbTable_Programs extends Xmltv_Db_Table_Abstract
{

	protected $_name = 'programs';

	/**
	 * Constructor
	 * 
	 * @param array $config
	 */
	public function __construct ($config = array()) {

		parent::__construct(array('name'=>$this->_name));
		
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
	public function fetchDayItems($channel_id=null, $date=null, $categories=null, $archived=false) {
		
		if (!$channel_id || !$date || !$categories)
			throw new Zend_Exception(__METHOD__.' '.self::ERR_PARAMETER_MISSING, 500);
		
		$progsTable	   = $this->_name;
		$channelsTable = new Xmltv_Model_DbTable_Channels();
		$categoriesTable = new Xmltv_Model_DbTable_ProgramsCategories();
		
		/**
		 * Load and setup archive DB adapter
		 */
		if ($archived===true) {
			$this->_db = Zend_Registry::get('db_archive');
		}
		
		/**
		 * Create SQL query
		 * @var Zend_Db_Select
		 */
		$select = $this->_db->select()
			->from( array('prog'=>$progsTable), array(
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

		$profile = (bool)Zend_Registry::get('site_config')->site->get('profile');
		if ($profile){
		    
			Zend_Debug::dump($select->assemble());
			//die(__FILE__.": ".__LINE__);
		}
		
		$result = $this->_db->query( $select )->fetchAll( Zend_Db::FETCH_ASSOC );
		
		$actorsTable	= new Xmltv_Model_DbTable_Actors();
		$directorsTable = new Xmltv_Model_DbTable_Directors();
		foreach ($result as $k=>$row) {
			
			$result[$k]->start = new Zend_Date($row->start);
			$result[$k]->end   = new Zend_Date($row->end);
			
			if (!empty($result[$k]->actors)) {
				if( !is_array($result[$k]->actors) ){
					$where = "`id` IN ( ".$result[$k]->actors." )";
				}
				$result[$k]->actors = $actorsTable->fetchAll($where)->toArray();
			}
			
			if (!empty($result[$k]->directors)) {
				if( !is_array($result[$k]->directors) ){
					$where = "`id` IN ( ".$result[$k]->directors." )";
				}
				$result[$k]->directors = $directorsTable->fetchAll($where)->toArray();
			}
			
			$result[$k]->premiere = (bool)$result[$k]->premiere;
			$result[$k]->live	 = (bool)$result[$k]->live;
			
		}
		
		return $result;
		
	}
	
	public function fetchProgramThisDay($program_alias=null, $channel_alias=null, Zend_Date $date){
		
		//var_dump(func_get_args());
		//var_dump($date->toString());
		
		$channels = new Xmltv_Model_DbTable_Channels();
		$ch_id = (int)$channels->find($channel_alias)->current()->ch_id;
		
		$select = $this->_db->select()
			->from( array( 'prog'=>'rtvg_programs' ), '*')
			->joinLeft( array( 'props'=>'rtvg_programs_props'), "`prog`.`hash`=`props`.`hash`", array('actors', 'directors', 'premiere', 'live'))
			->joinLeft( array( 'desc'=>'rtvg_programs_descriptions'), "`prog`.`hash`=`desc`.`hash`", array('intro', 'body'))
			->joinLeft( array('ch'=>$this->_pfx.'channels' ), "`prog`.`ch_id`=`ch`.`ch_id`", array('channel_title'=>'title', 'channel_alias'=>'LOWER(`ch`.`alias`)'))
			->where("`prog`.`alias` LIKE '$program_alias'")
			->where("`prog`.`start` LIKE '".$date->toString('yyyy-MM-dd%')."'")
			->where("`prog`.`ch_id` = '$ch_id'");
		
			
		//var_dump($select->assemble());
		//die(__FILE__.': '.__LINE__);
			
		try {
			$result = $this->_db->fetchAll($select, null, self::FETCH_MODE);
		} catch (Zend_Db_Table_Exception $e) {
			if ($this->debug) {
				throw new Exception($e->getMessage(), $e->getCode(), $e);
			}
		}
		
		//var_dump($result);
		//die(__FILE__.': '.__LINE__);
		
		return $result;
		
	}
	
	

}

