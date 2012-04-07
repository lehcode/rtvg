<?php

class Xmltv_Model_DbTable_ChannelsRatings extends Zend_Db_Table_Abstract
{

    protected $_name = 'rtvg_channels_ratings';

    public function addHit($channel_id){
    	
    	if (!$channel_id)
		throw new Zend_Exception("Не указан один или более параметров для ".__FUNCTION__, 500);
    	
		if (!$row = $this->find($channel_id)->current())
		$row = $this->createRow(array('ch_id'=>$channel_id), true);
		
		//var_dump($channel_id);
		//var_dump($row);
		//die(__FILE__.': '.__LINE__);
		
		$row->hits+=1;
		$row->save();
		
    }
    
}

