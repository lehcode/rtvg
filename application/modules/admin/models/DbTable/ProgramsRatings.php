<?php

class Admin_Model_DbTable_ProgramsRatings extends Xmltv_Model_DbTable_ProgramsRatings
{

    
    /**
     * 
     * Constructor
     * @param array $config
     */
    public function __construct($config=array()){
    	
    	parent::__construct(array('name'=>$this->_name));
    	
    }

}

