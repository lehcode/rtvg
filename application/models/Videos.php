<?php
class Xmltv_Model_Videos
{
	public $debug = false;
	
	public function __construct(){
		$siteConfig = Zend_Registry::get('site_config')->site;
		$this->debug = (bool)$siteConfig->get('debug', false);
	}
	
	public function getVideo($id=null, $decode=false){
		
		if (!$id)
		throw new Zend_Exception("Не указан один или более параметров для ".__FUNCTION__, 500);
		
		if ((bool)$decode===true) {
			$id = $this->_decodeId($id);
		} 
		
		//var_dump(func_get_args());
		//var_dump($id);
		//die(__FILE__.': '.__LINE__);
		
		$yt = new Zend_Gdata_YouTube();
		$yt->setMajorProtocolVersion(2);
		
		try {
			if (Xmltv_Config::getCaching()){
				$cache = new Xmltv_Cache();
				$hash = $cache->getHash( __FUNCTION__.'_'.$id);
				if (!$result = $cache->load($hash, 'Function')) {
					$result = $yt->getVideoEntry($id);
					$cache->save($result, $hash, 'Function');
				}
			} else {
				$result = $yt->getVideoEntry($id);
			}
		} catch (Exception $e) {
			echo $e->getMessage();
			exit();
		}
		
		return $result;
		//die(__FILE__.': '.__LINE__);
		
	}
	
	public function getRelatedVideos($orig=null){
		
		if (!$orig)
		throw new Zend_Exception("Не указан один или более параметров для ".__FUNCTION__, 500);
		
		$yt = new Zend_Gdata_YouTube();
		$yt->setMajorProtocolVersion(2);
		try {
			
			$cache = new Xmltv_Cache();
			$hash = $cache->getHash( __FUNCTION__.'_'.$orig->getVideoId());
			
			if (Xmltv_Config::getCaching()){
				if (!$result = $cache->load($hash, 'Function')) {
					$result = $yt->getRelatedVideoFeed($orig->getVideoId());
					$cache->save($result, $hash, 'Function');
				}
			} else {
				$result = $yt->getRelatedVideoFeed($orig->getVideoId());
			}
		} catch (Exception $e) {
			echo $e->getMessage();
		}
		
		return $result;
		
	}
	
	
	private function _decodeId($input=null){
		
		if (!$input)
		throw new Zend_Exception("Не указан один или более параметров для ".__FUNCTION__, 500);
		
		return base64_decode( strrev($input).'=');
		
	}
	
	public function convertTag($input=null){
		
		if (!$input)
		throw new Zend_Exception("Не указан один или более параметров для ".__FUNCTION__, 500);
		
		return Xmltv_String::str_ireplace('-', ' ', $input);
		
	}
	
}