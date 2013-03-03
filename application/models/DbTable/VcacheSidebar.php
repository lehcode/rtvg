<?php
/**
 * Video cache for sidebar videos
 *
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: VcacheSidebar.php,v 1.3 2013-03-03 23:34:13 developer Exp $
 *
 */
class Xmltv_Model_DbTable_VcacheSidebar extends Xmltv_Db_Table_Abstract
{

	protected $_name	= 'vcache_sidebar';
	protected $_primary = 'rtvg_id';

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
			'rtvg_id'=>null,
			'yt_id'=>null,
			'channel'=>null,
			'title'=>null,
			'alias'=>null,
			'desc'=>'',
			'views'=>0,
			'published'=>1,
			'duration'=>null,
			'category'=>null,
			'thumbs'=>'',
			'delete_at'=>$now->addDay(7)->toString('YYYY-MM-dd HH:mm:ss'),
		);
	
	}
	
	/**
	 * Save new row to database
	 * 
	 * @param  array $video
	 * @return NA
	 */
	public function store($video=array()){
		
		if (!isset($video['desc']) || empty($video['desc'])) {
			return;
		}
		
		$new = $video;
		
		if (is_a($video['published'], 'Zend_Date')){
			$new['published'] = $video['published']->toString('yyyy-MM-dd HH:mm:ss');
		}
		
		if (is_a($video['duration'], 'Zend_Date')){
			$new['duration'] = $video['duration']->toString('yyyy-MM-dd HH:mm:ss');
		}
		
		if (is_array($video['thumbs'])){
			$new['thumbs'] = Zend_Json::encode($video['thumbs']);
		}
		
		$video['delete_at'] = Zend_Date::now()->addDay(1);
		$new['delete_at']   = $video['delete_at']->toString('YYYY-MM-dd HH:mm:ss');
		
		if (APPLICATION_ENV=='development'){
			//var_dump($new);
			//die(__FILE__.': '.__LINE__);
		}
		
		$keys = array();
		$values = array();
		foreach ($new as $k=>$v){
			 $keys[] = $this->_db->quoteIdentifier($k);
			 $values[] = "'".str_ireplace("'", '"', $v)."'";
		}
		$sql = "INSERT INTO `".$this->getName()."` (".implode(',', $keys).") VALUES (".implode(',', $values).") 
		ON DUPLICATE KEY UPDATE `delete_at`='".$new['delete_at']."'";
		
		if (APPLICATION_ENV=='development'){
			var_dump($sql);
			//die(__FILE__.': '.__LINE__);
		}
		
		$this->_db->query($sql);
		
		return $video;
		
	}
	
	/**
	 * 
	 * @param  string $key
	 * @return array
	 */
	public function fetch($key=null){
		
		return $this->fetchRow("`rtvg_id`='$key'")->toArray();
		
	}

}

