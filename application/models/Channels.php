<?php

class Xmltv_Model_Channels
{
	
	public $debug=false;
	private $_table;
	private $_hits_table;
	
	public function __construct(){
		$siteConfig = Zend_Registry::get('site_config')->site;
		$this->debug = (bool)$siteConfig->get('debug', false);
		$this->_table = new Xmltv_Model_DbTable_Channels();
		$this->_hits_table = new Xmltv_Model_DbTable_ChannelsRatings();
	}

	public function getPublished(){
		$table = new Xmltv_Model_DbTable_Channels();
		try {
			$rows = $table->fetchAll("`published`='1' AND `parse`='1' ", 'title ASC');
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
		//var_dump($channel);
		return $channel;
	}
	
	public function createUrl($channel=null, $baseUrl=null){
		
		if (!$channel || $baseUrl===null)
		return;
		
		$channel['icon'] = $baseUrl.'/images/channel_logo/'.$channel['icon'];
		return $channel;
	}
	
	public function getByAlias($alias=null){
		
		if (!$alias)
		throw new Zend_Exception("Не указан один или более параметров для ".__FUNCTION__, 500);
		
		return $this->_table->fetchRow("`alias` LIKE '$alias'");
		
	}
	
	public function getTypeheadItems(){
		
		try {
			$result = $this->_table->getTypeheadItems();
		} catch (Exception $e) {
			echo $e->getMessage();
		}
		return $result;
		
		
	}
	
	public function addHit($id=null){
		
		if (!$id)
		throw new Zend_Exception("Не указан один или более параметров для ".__FUNCTION__, 500);
		
		$this->_hits_table->addHit($id);
			
	}
	
	public function getDescription($id=null){
		
		if (!$id)
		throw new Zend_Exception("Не указан один или более параметров для ".__FUNCTION__, 500);
		
		$props = $this->_table->find($id)->current();
		$desc['intro'] = $props->desc_intro;
		$desc['body']  = $props->desc_body;
		return $desc;
		
	}
	
	public function getCategory($cat_alias=null){
	
		if (!$cat_alias)
		throw new Zend_Exception("Не указан один или более параметров для ".__FUNCTION__, 500);
		
		$channels = $this->_table->fetchCategory($cat_alias);
		
		//var_dump($channels);
		//die(__FILE__.': '.__LINE__);
		
		return $channels;
		
		
	}
	
	public function getWeekSchedule(){
	
		//if (!$channel)
		//throw new Zend_Exception("Не указан один или более параметров для ".__FUNCTION__, 500);
		
		$schedule = $this->_table->fetchWeekItems(Zend_Registry::get('ch_id'), Zend_Registry::get('week_start'), Zend_Registry::get('week_end'));
		
		//var_dump($schedule);
		//die(__FILE__.': '.__LINE__);
		
		return $schedule;
		
	}
	
}

