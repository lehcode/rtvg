<?php

class Xmltv_Model_DbTable_ProgramsCategories extends Xmltv_Db_Table_Abstract
{

    protected $_name = 'programs_categories';
    protected static $pfx='';

    /**
     * Constructor
     * @param array $config
     */
    public function __construct ($config = array()) {
    
    	parent::__construct( array(
    		'name'=>$this->getName(),
    		'primary'=>$this->_primary
    	));
    
    }

}

