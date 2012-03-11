<?php

class Xmltv_Model_DbTable_Channels extends Zend_Db_Table_Abstract
{

    protected $_name = 'rtvg_channels';
	/*
	public function fetchAllPublished(){
		try {
			$rows = parent::fetchAll("`published`='1' AND `parse`='1' ");
		} catch (Zend_Exception $e) {
			echo $e->getMessage();
			die(__METHOD__);
		}
		return $rows;
	}
	*/
}

