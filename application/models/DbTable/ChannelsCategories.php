<?php

class Xmltv_Model_DbTable_ChannelsCategories extends Zend_Db_Table_Abstract
{
	
	protected $_name = 'rtvg_channels_categories';
	
	const FETCH_MODE = Zend_Db::FETCH_OBJ;
	
	public function fetchId($alias = null){
		return $this->fetchRow("`alias`='$alias'");
	}
	
}

