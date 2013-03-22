<?php
/**
 * Database table class
 * serving video cache for listings video
 *
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: VcacheListings.php,v 1.5 2013-03-22 17:51:44 developer Exp $
 *
 */
class Xmltv_Model_DbTable_VcacheListings extends Xmltv_Db_Table_Abstract
{

	protected $_name = 'vcache_listings';
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
		$now = Zend_Date::now();
		$this->_defaultValues = array(
				'rtvg_id'=>null,
				'yt_id'=>null,
				'title'=>null,
				'alias'=>null,
				'desc'=>null,
				'views'=>0,
				'published'=>1,
				'duration'=>null,
				'category'=>'',
				'thumbs'=>'',
				'delete_at'=>$now->addDay(7)->toString('YYYY-MM-dd HH:mm:ss'),
				'hash'=>null
		);
		 
	}
	
	public function fetch($key=null){
		
		$select = $this->select(false)
			->from($this->getName())
			->where("`rtvg_id`='$key'");
		
		return $this->fetchRow($select);
		
	}

}

