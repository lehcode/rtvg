<?php

class Xmltv_Model_DbTable_ProgramsCategories extends Zend_Db_Table_Abstract
{

    protected $_name = 'programs_categories';
    protected static $pfx='';

    /**
     * Constructor
     * @param array $config
     */
    public function __construct ($config = array()) {
    
    	if (isset($config['tbl_prefix'])) {
    		self::$pfx = $config['tbl_prefix'];
    	} else {
    		self::$pfx = Zend_Registry::get('app_config')->resources->multidb->local->get('tbl_prefix');
    	}
    	$this->setName( self::$pfx.$this->_name);
    	parent::__construct( array('name'=>$this->getName()));
    
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

