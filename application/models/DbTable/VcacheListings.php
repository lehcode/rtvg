<?php

class Xmltv_Model_DbTable_VcacheListings extends Xmltv_Db_Table_Abstract
{

	protected $_name = 'vcache_listings';
	protected $_primary='yt_id';
	
	/**
	 * Constructor
	 *
	 * @param array $config
	 */
	public function __construct ($config = array()) {
	
		parent::__construct(array(
			'name'=>$this->_name,
			'primary'=>$this->_primary,
		));
	
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Zend_Db_Table_Abstract::_setup()
	 */
	protected function _setup(){
	
		parent::_setup();
		$now = Zend_Date::now();
		$this->_defaultValues = array(
				'rtvg_id'=>null,
				'yt'=>null,
				'title'=>'',
				'alias'=>'',
				'desc'=>'',
				'views'=>0,
				'published'=>1,
				'duration'=>null,
				'category'=>'',
				'thumbs'=>'',
				'delete_at'=>$now->addDay(7)->toString('YYYY-MM-dd HH:mm:ss'),
				'hash'=>null
		);
		 
	}
	
	public function store($video=array()){
		
		if (empty($video['hash']) || !isset($video['hash'])){
			throw new Zend_Db_Table_Exception("Не указан hash для ".__METHOD__, 500);
		}

		if (empty($video['alias']) || !isset($video['alias'])){
			throw new Zend_Db_Table_Exception("Не указан alias для ".__METHOD__, 500);
		}

		if (empty($video['title']) || !isset($video['title'])){
			throw new Zend_Db_Table_Exception("Не указан alias для ".__METHOD__, 500);
		}

		if (empty($video['rtvg_id']) || !isset($video['rtvg_id'])){
			throw new Zend_Db_Table_Exception("Не указан rtvg_id для ".__METHOD__, 500);
		}
		
		if (empty($video['yt_id']) || !isset($video['yt_id'])){
			throw new Zend_Db_Table_Exception("Не указан yt_id для ".__METHOD__, 500);
		}
		
		if ($video['desc']===null){
			$video['desc']='';
		}
		
		if (is_a($video['published'], 'Zend_Date')){
			$video['published'] = $video['published']->toString('yyyy-MM-dd HH:mm:ss');
		}
		 
		if (is_a($video['duration'], 'Zend_Date')){
			$video['duration'] = $video['duration']->toString('yyyy-MM-dd HH:mm:ss');
		}

		if (is_array($video['thumbs'])){
			$video['thumbs'] = Zend_Json::encode($video['thumbs']);
		}
		
		foreach ($video as $k=>$v){
			$values[] = "`$k`=".$this->_db->quote($v);
		}
		
		$sql = "INSERT INTO `".$this->getName()."` VALUES ( ".implode(',', $values)." ) ON DUPLICATE KEY UPDATE `delete_at`='".$video['delete_at']."'";
		
		if (APPLICATION_ENV=='development'){
			var_dump($sql);
			//die(__FILE__.': '.__LINE__);
		}
		
		try {
			$this->_db->query($sql);
		} catch (Zend_Db_Adapter_Mysqli_Exception $e) {
			throw new Zend_Exception("Cannot insert into ".$this->getName(), 500);
		}
		
		//$this->insert($video);
		
		return true;
		
	}
	
	public function fetch($key=null){
		
		$select = $this->select(false)
			->from($this->getName())
			->where("`rtvg_id`='$key'");
		
		return $this->fetchRow($select);
		
	}

}

