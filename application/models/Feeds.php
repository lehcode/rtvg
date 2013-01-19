<?php
/**
 * Модель содержит методы для работы с RSS
 *
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: Feeds.php,v 1.2 2013-01-19 10:11:13 developer Exp $
 *
 */
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