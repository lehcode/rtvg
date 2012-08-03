<?php
class Xmltv_Model_Feeds
{
	
	protected $siteConfig;
	
	public function __construct(){
		$siteConfig = Zend_Registry::get('site_config')->site;
		//$this->debug = (bool)$siteConfig->get('debug', false);
	}
	
	public function getFeedsList($topic=''){
		
		
		
	}
}