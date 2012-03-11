<?php

class Xmltv_Model_Channels
{

	public function getPublished(){
		$table = new Xmltv_Model_DbTable_Channels();
		try {
			$rows = $table->fetchAll("`published`='1' AND `parse`='1' ");
		} catch (Zend_Exception $e) {
			echo $e->getMessage();
			die(__CLASS__.':'.__METHOD__);
		}
		return $rows;
	}
	
	public function fixImage($channel=null, $baseUrl=null){
		
		if (!$channel || $baseUrl===null)
		return;
		
		$channel['icon'] = $baseUrl.'/images/channel_logo/'.$channel['icon'];
		return $channel;
	}
	
	public function createUrl($channel=null, $baseUrl=null){
		
		if (!$channel || $baseUrl===null)
		return;
		
		$channel['icon'] = $baseUrl.'/images/channel_logo/'.$channel['icon'];
		return $channel;
	}
}

