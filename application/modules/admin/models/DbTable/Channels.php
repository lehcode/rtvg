<?php
/**
 * Database table for channels info
 *
 * @uses Zend_Db_Table_Abstract
 * @version $Id: Channels.php,v 1.3 2012-12-25 01:57:53 developer Exp $
 */
class Admin_Model_DbTable_Channels extends Xmltv_Model_DbTable_Channels
{
    /**
     * 
     * Fetch channel info by particular table key
     * @param int $id
     * @param string $key
     */
    public function fetchChannel($id=null, $key='ch_id') {
    	
    	if (!$id)
    		return;
    	
    	$id = (int)$id;
    	try{
    		$row = $this->fetchRow("`$key`='$id'");
    	} catch(Zend_Exception $e) {
    		throw new Zend_Exception($e->getMessage(), $e->getCode());
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
    	} catch(Zend_Exception $e) {
    		throw new Zend_Exception($e->getMessage(), $e->getCode());
    	}
    	return $rows;
    	
    }

}

