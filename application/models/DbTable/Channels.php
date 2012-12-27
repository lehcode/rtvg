<?php
/**
 * Database table for channels info
 *
 * @uses Zend_Db_Table_Abstract
 * @version $Id: Channels.php,v 1.11 2012-12-27 17:04:37 developer Exp $
 */

class Xmltv_Model_DbTable_Channels extends Zend_Db_Table_Abstract
{

    protected $_name = 'channels';
    protected $_pfx;
	
	const FETCH_MODE = Zend_Db::FETCH_OBJ;
	
    public function __construct($config=array()) {
    	
    	parent::__construct(array('name'=>$this->_name));
		
    	if (isset($config['tbl_prefix'])) {
    		$pfx = (string)$config['tbl_prefix'];
    	} else {
    		$pfx = Zend_Registry::get('app_config')->resources->multidb->local->get('tbl_prefix', 'rtvg_');
    	}
    	$this->_pfx = $pfx;
    	$this->setName($this->_pfx.$this->_name);
		
    }
    
    public function getTypeaheadItems(){
    	
    	try {
    		$select = $this->_db->select()->from($this->_name, array( 'title' ));
    		$result = $this->_db->query($select)->fetchAll(self::FETCH_MODE);
    	} catch (Exception $e) {
    		echo $e->getMessage();
    	}
    	return $result;
    	
    }
    
    public function getFeatured($order=null, $total=20, $by_hits=true){
    	
    	if (!$order)
    		$order='ch_id';
    	
    	//$this->_initProfiler();
    	
    	try {
    		$select = $this->_db->select()
    		->from( array( 'channel'=>$this->_name ), '*' )
    		->joinLeft( array( 'rating'=>'rtvg_channels_ratings'), "channel.`ch_id`=rating.`ch_id`");
	    	$select->where( "channel.`featured`='1'" )->limit($total);
	    	
	    	if (!$by_hits)
	    	$select->order("$order ASC");
	    	else {
	    		$select->order("rating.hits DESC");
	    		$select->order("channel.title ASC");
	    	}
	    	
	    	//var_dump($select->assemble());
	    	//die(__FILE__.': '.__LINE__);
	    	
	    	$result = $this->_db->query($select)->fetchAll(self::FETCH_MODE);
	    		    	
    	} catch (Zend_Db_Table_Exception $e) {
    		throw new Zend_Exception($e->getMessage(), $e->getCode(), $e);
    	}
    	
    	//var_dump($result);
	    //die(__FILE__.': '.__LINE__);
		
		return $result;
    	
    }
    
    public function fetchCategory($alias=null){
    	
    	if (!$alias)
			throw new Zend_Exception("Не указан один или более параметров для ".__FUNCTION__, 500);
		
    	$select = $this->select()
    		->from(array('ch'=>$this->_name), '*')
    		->join(array('cat'=>'rtvg_channels_categories'), "`ch`.`category`=`cat`.`id`", array())
    		->where("`cat`.`alias` LIKE '$alias'")
    		->where("`ch`.`published`='1'")
    		->order("ch.title ASC");
    	
    	$result = $this->fetchAll($select);
    	return $result;
    	
    }
    
    public function fetchWeekItems($ch_id, Zend_Date $start, Zend_Date $end){
    	
    	//$this->_initProfiler();
		$days = array();
		do{
			$select = $this->_db->select()
				->from( array( 'prog'=>$this->_pfx.'programs'), '*')
				->joinLeft( array( 'props'=>$this->_pfx."programs_props"), "`prog`.`hash`=`props`.`hash`", array('actors', 'directors', 'premiere', 'live'))
				->joinLeft( array( 'desc'=>$this->_pfx."programs_descriptions"), "`prog`.`hash`=`desc`.`hash`", array('desc_intro'=>'intro', 'desc_body'=>'body'))
				->joinLeft( array( 'ch'=>$this->_pfx."channels"), "`prog`.`ch_id`=`ch`.`ch_id`", array('ch_id'))
				->where("`prog`.`start` LIKE '".$start->toString('yyyy-MM-dd')."%'")
				->where("`prog`.`ch_id` = '$ch_id'")
				->where("`ch`.`published` = '1'")
				->order("prog.start", "ASC");
			
			//var_dump($select->assemble());
			//die(__FILE__.': '.__LINE__);
				
			try {
				$days[$start->toString('U')] = $this->_db->fetchAll($select, null, self::FETCH_MODE);
			} catch (Zend_Db_Adapter_Exception $e) {
				throw new Zend_Exception($e->getMessage(), $e->getCode(), $e);
			}
			
			//$this->_profileQuery();
			$start->addDay(1);
			
		} while ( $start->compare($end, 'dd', 'ru')!=1 );
		//var_dump($end->toString('YYYY-MM-dd'));
		//$safeTag = Zend_Controller_Action_HelperBroker::getStaticHelper('safeTag');
		
		foreach ($days as $day) {
			foreach ($day as $program) {
				$program->start = new Zend_Date($program->start);
				$program->end   = new Zend_Date($program->end);
			}
		}

		//var_dump($days);
		//die(__FILE__.': '.__LINE__);
		
		return $days;
    	
    }
    
    private function _initProfiler(){
    	
    	if( $this->_profiling ) {
			$this->_db->getProfiler()->setEnabled( true );
			$this->_profiler = $this->_db->getProfiler();
		}
    	
    }
    
    private function _profileQuery(){
    	
    	if( $this->_profiling ) {
			$query = $this->_profiler->getLastQueryProfile();
			echo 'Method: '.__METHOD__.'<br />Time: '.$query->getElapsedSecs().'<br />Query: '.$query->getQuery().'<br />';
		}
    	
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

