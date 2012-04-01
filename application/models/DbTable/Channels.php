<?php
class Xmltv_Model_DbTable_Channels extends Zend_Db_Table_Abstract
{

    protected $_name = 'rtvg_channels';
    
    public function __construct($config=array()) {
    	parent::__construct(array('name'=>$this->_name));
    }
    
    public function getTypeheadItems(){
    	
    	$select = $this->_db->select()
    		->from($this->_name, array( 'title' ));
    	return $this->_db->query($select)->fetchAll();
    	
    }
	
}

