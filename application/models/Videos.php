<?php
class Xmltv_Model_Videos
{
	public $debug = false;
	
	public function __construct(){
		$siteConfig = Zend_Registry::get('site_config')->site;
		$this->debug = (bool)$siteConfig->get('debug', false);
	}
	
	
	
	
	
	
	public function convertTag($input=null){
		
		if (!$input)
		throw new Zend_Exception("Не указан один или более параметров для ".__FUNCTION__, 500);
		
		return Xmltv_String::str_ireplace('-', ' ', $input);
		
	}
	
}