<?php

class Xmltv_Model_DbTable_ProgramsCategories extends Zend_Db_Table_Abstract
{

    protected $_name = 'programs_categories';

    /**
     * Constructor
     * @param unknown_type $config
     */
    public function __construct ($config = array()) {
    
    	parent::__construct(array('name'=>$this->_name));
    
    	if (isset($config['tbl_prefix'])) {
    		$pfx = $config['tbl_prefix'];
    	} else {
    		$pfx = Zend_Registry::get('app_config')->resources->multidb->local->get('tbl_prefix');
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

