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

	protected $_name = 'programs';
	protected $_primary = 'hash';
	
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
	 * @param int $count // optional
	 */
	public function fetchDayItems($channel_id=null, $date=null, $count=null) {
		
		if (!$channel_id || !$date) {
			throw new Zend_Exception( Rtvg_Message::ERR_MISSING_PARAM );
		}
		
		$categoriesTable = new Xmltv_Model_DbTable_ProgramsCategories();
		
		//$d = new Zend_Date( $date->toString("U"), 'U');
		$channelsTable   = new Xmltv_Model_DbTable_Channels();
		$categoriesTable = new Xmltv_Model_DbTable_ProgramsCategories();
		$now = Zend_Date::now();
		/**
		 * Create SQL query
		 * @var Zend_Db_Select
		 */
		$select = $this->_db->select()
			->from( array( 'prog'=>$this->getName()), array(
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
				'category_id'=>'id',
				'category_title'=>'title',
				'category_title_single'=>'title_single',
				'category_alias'=>'alias',
			))
			->joinLeft( array( 'ch'=>$channelsTable->getName()), "`prog`.`channel`=`ch`.`id`", array(
				'channel_id'=>'id',
				'channel_title'=>'title',
				'channel_alias'=>'alias',
			))
			->where("`prog`.`start` >= '".Zend_Date::now()->toString("YYYY-MM-dd")." 00:00:00'")
			->where("`prog`.`start` < '".Zend_Date::now()->addDay(1)->toString("YYYY-MM-dd")." 00:00'")
			->where( "`prog`.`channel` = ".$this->_db->quote($channel_id))
			->where( "`ch`.`published` = '1'")
			->group( "prog.hash")
			->order( "prog.start", "ASC");
			
			if (isset($count) && is_int($count)){
			    //$select->limit( $count );
			}
		
		if (APPLICATION_ENV=='development'){
			//parent::debugSelect($select, __METHOD__);
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
    
    /**
     * Fetches a new blank row (not from the database).
     *
     * @param  array $data OPTIONAL data to populate in the new row.
     * @param  string $defaultSource OPTIONAL flag to force default values into new row
     * @return Zend_Db_Table_Row_Abstract
     */
    public function createRow(array $data = array(), $defaultSource = null)
    {
        
        if(empty($data) || !$data['hash'] || empty($data['hash'])){
            throw new Zend_Exception("Wrong hash for Broadcast! Data:". ($d=print_r($data, true)));
        }
        
        $cols     = $this->_getCols();
        $defaults = array_combine($cols, array_fill(0, count($cols), null));

        // nothing provided at call-time, take the class value
        if ($defaultSource == null) {
            $defaultSource = $this->_defaultSource;
        }

        if (!in_array($defaultSource, array(self::DEFAULT_CLASS, self::DEFAULT_DB, self::DEFAULT_NONE))) {
            $defaultSource = self::DEFAULT_NONE;
        }

        if ($defaultSource == self::DEFAULT_DB) {
            foreach ($this->_metadata as $metadataName => $metadata) {
                if (($metadata['DEFAULT'] != null) &&
                    ($metadata['NULLABLE'] !== true || ($metadata['NULLABLE'] === true && isset($this->_defaultValues[$metadataName]) && $this->_defaultValues[$metadataName] === true)) &&
                    (!(isset($this->_defaultValues[$metadataName]) && $this->_defaultValues[$metadataName] === false))) {
                    $defaults[$metadataName] = $metadata['DEFAULT'];
                }
            }
        } elseif ($defaultSource == self::DEFAULT_CLASS && $this->_defaultValues) {
            foreach ($this->_defaultValues as $defaultName => $defaultValue) {
                if (array_key_exists($defaultName, $defaults)) {
                    $defaults[$defaultName] = $defaultValue;
                }
            }
        }

        $config = array(
            'table'    => $this,
            'data'     => $defaults,
            'readOnly' => false,
            'stored'   => true
        );

        $rowClass = $this->getRowClass();
        if (!class_exists($rowClass)) {
            require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($rowClass);
        }
        $row = new $rowClass($config);
        $row->setFromArray($data);
        return $row;
    }

}

