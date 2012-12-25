<?php
/**
 * Database table for storing torrents info
 *
 * @uses Zend_Db_Table_Abstract
 * @version $Id: Torrents.php,v 1.1 2012-12-25 02:05:57 developer Exp $
 */

class Xmltv_Model_DbTable_Torrents extends Zend_Db_Table_Abstract
{

    protected $_name = 'torrents';
	
	const FETCH_MODE = Zend_Db::FETCH_OBJ;
	
    public function __construct($config=array()) {
    	
    	parent::__construct(array('name'=>$this->_name));
		
    	if (isset($config['tbl_prefix'])) {
    		$pfx = (string)$config['tbl_prefix'];
    	} else {
    		$pfx = Zend_Registry::get('app_config')->resources->multidb->local->get('tbl_prefix', 'rtvg_');
    	}
    	$this->setName($pfx.$this->_name);
		
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

