<?php
/**
 * Channels model
 *
 * @version $Id: Channels.php,v 1.7 2012-08-13 13:20:15 developer Exp $
 */
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
		$cache = new Xmltv_Cache(array('location'=>'/cache/Listings'));
		$hash = $cache->getHash(__FUNCTION__);
		try {
			if (Xmltv_Config::getCaching()){
				if (!$rows = $cache->load($hash, 'Core', 'Listings')) {
					$rows = $table->fetchAll("`published`='1' AND `parse`='1' ", 'title ASC');
					$cache->save($rows, $hash, 'Core', 'Listings');
				}
			} else {
				$rows = $table->fetchAll("`published`='1' AND `parse`='1' ", 'title ASC');
			}
		} catch (Exception $e) {
			echo $e->getMessage();
		}
		/*
		try {
			$rows = $table->fetchAll("`published`='1' AND `parse`='1' ", 'title ASC');
		} catch (Zend_Exception $e) {
			echo $e->getMessage();
			die(__CLASS__.':'.__METHOD__);
		}
		*/
		return $rows;
	}
	
	public function fixImage($channel=null, $baseUrl=null){
		
		if (!$channel || $baseUrl===null)
		return;
		
		$channel->icon = $baseUrl.'/images/channel_logo/'.$channel->icon;
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

	public function getByTitle($alias=null){
		
		if (!$alias)
		throw new Zend_Exception("Не указан один или более параметров для ".__FUNCTION__, 500);
		
		$alias = Xmltv_String::strtolower($alias);
		$result = $this->_table->fetchRow(" `title` LIKE '$alias'");
		$result->alias = Xmltv_String::strtolower($result->alias);
		return $result;
		
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
		return $channels;
		
	}
	
	public function getWeekSchedule($channel=null, Zend_Date $start, Zend_Date $end){
	
		if (!$channel) {
			throw new Zend_Exception(__METHOD__." - Не указан канал", 500);
		}
		if (!is_a($start, 'Zend_Date') || !is_a($end, 'Zend_Date')) {
			throw new Zend_Exception(__METHOD__." - Неверная дата", 500);
		}
		
		$cache = new Xmltv_Cache(array('location'=>'/cache/Listings'));
		$hash = $cache->getHash(__FUNCTION__.'_'.$channel->ch_id.'_week_'.$start->toString("yyyyMMdd"));
		try {
			if (Xmltv_Config::getCaching()){
				if (!$schedule = $cache->load($hash, 'Core', 'Listings')) {
					$schedule = $this->_table->fetchWeekItems($channel->ch_id, $start, $end);
					$cache->save($schedule, $hash, 'Core', 'Listings');
				}
			} else {
				$schedule = $this->_table->fetchWeekItems($channel->ch_id, $start, $end);
			}
		} catch (Exception $e) {
			echo $e->getMessage();
		}
		
		return $schedule;
		
	}
	
}

