<?php
/**
 * Video cache for main video from VideosController::showVideoAction()
 *
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @uses Xmltv_Db_Table_Abstract
 * @version $Id: VcacheMain.php,v 1.7 2013-03-06 03:52:37 developer Exp $
 *
 */
class Xmltv_Model_DbTable_VcacheMain extends Xmltv_Db_Table_Abstract
{

	protected $_name    = 'vcache_main';
	protected $_primary = array('yt_id');
	
	/**
	 * Constructor
	 *
	 * @param array $config
	 */
	public function init () {
	    parent::init();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Zend_Db_Table_Abstract::_setup()
	 */
	protected function _setup(){
	
	    parent::_setup();
	    $this->_defaultValues = array(
			'rtvg_id'=>null,
			'yt_id'=>null,
			'title'=>null,
			'alias'=>null,
			'desc'=>null,
			'views'=>0,
			'published'=>1,
			'duration'=>null,
			'category'=>null,
			'thumbs'=>'',
			'delete_at'=>null
		);
					
	}
	
	/**
	 * Save main video to database cache
	 * 
	 * @param  array $video
	 * @throws Zend_Exception
	 */
	public function store($video=array()){
		
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
		
		$new = $video;
		
		if (is_a($video['published'], 'Zend_Date')){
			$new['published'] = $video['published']->toString('yyyy-MM-dd HH:mm:ss');
		}
		 
		if (is_a($video['duration'], 'Zend_Date')){
			$new['duration'] = $video['duration']->toString('yyyy-MM-dd HH:mm:ss');
		}

		if (is_array($video['thumbs'])){
			$new['thumbs'] = serialize($video['thumbs']);
		}
		
		$new['delete_at'] = $video['delete_at'] = Zend_Date::now()->addDay(7)->toString('YYYY-MM-dd HH:mm:ss');
		
		$new = parent::createRow($new);
		
		foreach ($new->toArray() as $rowKey=>$rowVal){
		    $cols[]   = $this->_db->quoteIdentifier($rowKey);
			$values[] = "'".str_ireplace("'", '"', $rowVal)."'";
		}
		
		$sql = "INSERT INTO `".$this->getName()."` ( ".implode(', ', $cols)." ) 
		VALUES ( ".implode(',', $values)." ) ON DUPLICATE KEY UPDATE `delete_at`='".$new['delete_at']."'";
		
		if (APPLICATION_ENV=='development'){
			var_dump($sql);
			//die(__FILE__.': '.__LINE__);
		}
		
		$this->_db->query($sql);
		
		
		return true;
		
	}
	
	/**
	 * 
	 * @param  array $data
	 * @return array
	 */
	public function create($data){
	    
	    return parent::createRow($data, $this->_defaultValues)->toArray();
	    
	}
	
	/**
	 * 
	 * @param  string $key
	 * @return array
	 */
	public function fetch($key=null){
		
		$select = $this->select(false)
			->from($this->getName())
			->where("`rtvg_id`='$key'");
		
		return $this->fetchRow($select)->toArray();
		
	}

}

