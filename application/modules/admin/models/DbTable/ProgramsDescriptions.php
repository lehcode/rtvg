<?php

class Admin_Model_DbTable_ProgramsDescriptions extends Zend_Db_Table_Abstract
{

    protected $_name = 'rtvg_programs_descriptions';

	public function __construct($config=array()){
    	parent::__construct( $config );
    	
    }

    public function getCount(){
    	$select = $this->_db->select();
		$select->from($this->_name, array('count(*) as amount'));
    	try {
			$result = $this->_db->fetchAll($select);
		} catch (Exception $e) {
			echo $e->getMessage();
			die(__FILE__.': '.__LINE__);
			
		}
		return (int)$result[0]['amount'];
    }
    
}

