<?php
defined('APP_STARTED') or die();
class Admin_Model_Site 
{
		
	public $siteKey;
	public $siteProps;
	
	public function setSiteKey($siteKey) {
		$this->siteKey = $siteKey;
	}
		
}

