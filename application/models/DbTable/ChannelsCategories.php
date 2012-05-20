<?php

class Xmltv_Model_DbTable_ChannelsCategories extends Zend_Db_Table_Abstract
{

    protected $_name = 'rtvg_channels_categories';
    
    public function fetchId($alias = null){
    	
    	return $this->fetchRow("`alias`='$alias'");
    	
    }
    
}

