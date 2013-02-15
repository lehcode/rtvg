<?php
/**
 * Database table for channels info
 *
 * @uses Zend_Db_Table_Abstract
 * @version $Id: Channels.php,v 1.5 2013-02-15 00:44:02 developer Exp $
 */
class Admin_Model_DbTable_Channels extends Xmltv_Model_DbTable_Channels
{
    /**
     * 
     * Fetch channel info by particular table key
     * @param int $id
     * @param string $key
     */
    public function fetchChannel($value=null, $key='id') {
    	
    	if (!$value)
    		return;
    	
    	$value = (int)$value;
    	try{
    		$row = $this->fetchRow("`$key`='$value'");
    	} catch(Zend_Db_Table_Exception $e) {
    		throw new Zend_Exception($e->getMessage(), $e->getCode(), $e);
    	}
    	
    	return $row;
    	
    }
    
    /**
     * 
     * Fetch published channels
     */
    public function fetchPublished(){
    	
    	try{
    		$rows = $this->fetchAll("`published`='1'");
    	} catch(Zend_Db_Table_Exception $e) {
    		throw new Zend_Exception($e->getMessage(), $e->getCode(), $e);
    	}
    	return $rows;
    	
    }

}

