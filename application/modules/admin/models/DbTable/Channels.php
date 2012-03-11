<?php

class Admin_Model_DbTable_Channels extends Zend_Db_Table_Abstract
{

    protected $_name = 'rtvg_channels';

    public function getChannel($id=null, $key='ch_id') {
    	
    	if (!$id)
    	return;
    	$id = (int)$id;
    	try{
    		$row = $this->fetchRow("`$key`='$id'");
    	} catch(Zend_Exception $e) {
    		echo $e->getMessage();
    		return false;
    	}
    	return $row;
    	
    }
    
    public function getAll(){
    	try{
    		$rows = $this->fetchAll();
    	} catch(Zend_Exception $e) {
    		echo $e->getMessage();
    		return false;
    	}
    	return $rows;
    }
    
	public function getPublished(){
    	try{
    		$rows = $this->fetchAll("`published`='1'");
    	} catch(Zend_Exception $e) {
    		echo $e->getMessage();
    		return false;
    	}
    	return $rows;
    }

}

