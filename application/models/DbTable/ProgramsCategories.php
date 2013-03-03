<?php

class Xmltv_Model_DbTable_ProgramsCategories extends Xmltv_Db_Table_Abstract
{

    protected $_name = 'programs_categories';
    protected $_primary = 'id';

    /**
     * Initialize class
     */
    public function init() {
        parent::init();
    }
    
    /**
     * (non-PHPdoc)
     * @see Xmltv_Db_Table_Abstract::_setup()
     */
    protected function _setup(){
        
    	parent::_setup();
    	$this->_defaultValues = array(
    			'id'=>null,
    			'title'=>null,
    			'title_single'=>null,
    			'alias'=>null,
    			'movie'=>0,
    			'series'=>0,
    			'cartoon'=>0,
    			'sport'=>0,
    			'news'=>0,
    			'params'=>'',
    	);
    }

}

