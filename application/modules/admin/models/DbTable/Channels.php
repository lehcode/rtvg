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
    	
    	if (!$value){
    		return false;
        }
        
    	$value = (int)$value;
        $row = $this->fetchRow("`$key`='$value'");
    	return $row;
    	
    }
    
    /**
     * 
     * Fetch published channels
     */
    public function fetchPublished(){
    	
    	return $this->fetchAll("`published`='1'");
    	
    }

}

