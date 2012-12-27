<?php

/**
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: Programs.php,v 1.10 2012-12-27 17:04:37 developer Exp $
 *
 */
class Xmltv_Model_DbTable_Programs extends Zend_Db_Table_Abstract
{

	protected $_name = 'programs';
	protected $_pfx = '';

	const FETCH_MODE = Zend_Db::FETCH_OBJ;
	const ERR_PARAMETER_MISSING = "Пропущен параметр!";

	public function __construct ($config = array()) {

		parent::__construct(array('name'=>$this->_name));
		
    	if (isset($config['tbl_prefix'])) {
    		$this->_pfx = (string)$config['tbl_prefix'];
    	} else {
    		$this->_pfx = (string)Zend_Registry::get('app_config')->resources->multidb->local->get('tbl_prefix');
    	} 
    	$this->setName($this->_pfx.$this->_name);
		
	}

	/**
	 * 
	 * Enter description here ...
	 * @param  array $program_alias
	 * @throws Zend_Exception
	 */
	public function fetchSimilarProgramsThisWeek($program_alias=array(), Zend_Date $start, Zend_Date $end){
		
		$select = $this->_db->select()
			->from(array( 'prog'=>$this->_pfx.'programs'), '*')
			->joinLeft(array( 'prop'=>$this->_pfx.'programs_props' ), "`prog`.`hash`=`prop`.`hash`", array('actors', 'directors', 'premiere', 'live'))
			->joinLeft(array('desc'=>$this->_pfx.'programs_descriptions' ), "`prog`.`hash`=`desc`.`hash`", array('desc_intro'=>'intro', 'desc_body'=>'body'))
			->joinLeft(array('ch'=>$this->_pfx.'channels' ), "`prog`.`ch_id`=`ch`.`ch_id`", array(
				'channel_title'=>'title',
				'channel_alias'=>'LOWER(`ch`.`alias`)',
				'channel_icon'=>'ch.icon'));
		
		$select
			->where( "`prog`.`alias` LIKE '%$program_alias%'" )
			->where( "`prog`.`start` > '".$start->toString('yyyy-MM-dd 00:00:00')."'" )
			->where( "`prog`.`start` < '".$end->toString('yyyy-MM-dd 23:59:59')."'" )
			->where( "`ch`.`published` = '1'" )
			->order('prog.start DESC');	
			
		//var_dump($select->assemble());
		//die(__FILE__.': '.__LINE__);
			
		$result = $this->_db->fetchAll($select);
		
		if (count($result)){
			foreach ($result as $a){
				$a->channel_alias = Xmltv_String::strtolower($a->channel_alias);
			}
		}
		
		return $result;
		
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
	public function fetchDayItems($channel_id=null, $date=null, $archived=false) {
		
		if (!$channel_id || !$date)
			throw new Zend_Exception(self::ERR_PARAMETER_MISSING, 500);
		
		$progsTable = $this->_name;
		$descsTable = new Xmltv_Model_DbTable_ProgramsDescriptions();
		$channelsTable  = new Xmltv_Model_DbTable_Channels();
		$propsTable  = new Xmltv_Model_DbTable_ProgramsProps();
		$categoriesTable  = new Xmltv_Model_DbTable_ProgramsCategories();
		if ($archived===true) {
			$this->_db = Zend_Registry::get('db_archive');
			$pfx = Zend_Registry::get('app_config')->resources->multidb->archive->tbl_prefix;
			$progsTable = $pfx.'programs';
			$descsTable = $pfx.'programs_descriptions';
		}
		
		$select = $this->_db->select()
			->from( array('prog'=>$progsTable), array('*', 'prog_rating'=>'rating') )
			->joinLeft( array( 'props'=>$propsTable->getName()), "`prog`.`hash`=`props`.`hash`", 
				array('actors', 'directors', 'premiere', 'premiere_date') )
			->joinLeft( array('desc'=>$descsTable->getName()), "`prog`.`hash`=`desc`.`hash`", 
				array('desc_intro'=>'intro', 'desc_body'=>'body') );
			
		if (!$archived) {
			$select
				->joinLeft(array('cat'=>$categoriesTable->getName()), "`prog`.`category`=`cat`.`id`", array('category_title'=>'title'));
		}
		$select
			->where( "`prog`.`start` LIKE '$date%'" )
			->where( "`prog`.`ch_id` = '$channel_id'" )
			->order( "prog.start ASC" );
			
		//var_dump($select->assemble());
		//die(__FILE__.": ".__LINE__);
		
		$result = $this->_db->query( $select )->fetchAll( self::FETCH_MODE );
		
		$serializer = new Zend_Serializer_Adapter_Json();
		foreach ($result as $k=>$row) {
			$result[$k]->start = new Zend_Date($row->start);
			$result[$k]->end   = new Zend_Date($row->end);
			if (!empty($result[$k]->actors)) {
			    if (preg_match('/^\[.+\]$/', $result[$k]->actors)){
			        $where = "`id` IN ( ".implode(',', $serializer->unserialize($result[$k]->actors))." )";
			    } else {
			        $where = "`id` IN ( ".$result[$k]->actors." )";
			    }
				$table = new Xmltv_Model_DbTable_Actors();
				$result[$k]->actors = $table->fetchAll($where)->toArray();
			}
			if (!empty($result[$k]->directors)) {
			    if (preg_match('/^\[.+\]$/', $result[$k]->directors)){
			        $where = "`id` IN ( ".implode(',', $serializer->unserialize($result[$k]->directors))." )";
			    } else {
			        $where = "`id` IN ( ".$result[$k]->directors." )";
			    }
				$table = new Xmltv_Model_DbTable_Directors();
				$result[$k]->directors = $table->fetchAll($where)->toArray();
			}
			$result[$k]->premiere = (bool)$result[$k]->premiere;
			$result[$k]->live     = (bool)$result[$k]->live;
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
	
	public function fetchProgramThisWeek($program_alias=null, $channel_id=null, Zend_Date $start, Zend_Date $end){
		
	    /**
		 * @var Zend_Db_Select
		 */
		$select = $this->_db->select()
			->from(array( 'prog'=>'rtvg_programs'), '*')
			->joinLeft( array( 'prop'=>$this->_pfx.'programs_props' ), "`prog`.`hash`=`prop`.`hash`", array('actors', 'directors', 'premiere', 'live'))
			->joinLeft( array( 'desc'=>$this->_pfx.'programs_descriptions' ), "`prog`.`hash`=`desc`.`hash`", array('desc_intro'=>'intro', 'desc_body'=>'body'))
			->joinLeft( array('ch'=>$this->_pfx.'channels' ), "`prog`.`ch_id`=`ch`.`ch_id`", array('channel_title'=>'title', 'channel_alias'=>'LOWER(`ch`.`alias`)'))
			->where( "`prog`.`alias` LIKE '$program_alias'")
			->where( "`prog`.`start` >= '".$start->toString('yyyy-MM-dd 00:00:00')."'")
			->where( "`prog`.`start` < '".$end->toString('yyyy-MM-dd 00:00:00')."'")
			->where( "`prog`.`ch_id` = '$channel_id'")
			->order( "prog.start DESC" );
		
		//var_dump($select->assemble());
		//die(__FILE__.': '.__LINE__);	
			
		try {
			$result = $this->_db->fetchAll($select);
		} catch (Exception $e) {
			throw new Zend_Exception($e->getMessage(), $e->getCode(), $e);
		}
		
		
		
		
		return $result;
		
	}
	
	/**
	 * @return string
	 */
	public function getName() {
		return $this->_name;
	}

	/**
	 * @param string $string
	 */
	public function setName($string=null) {
		$this->_name = $string;
	}

}

