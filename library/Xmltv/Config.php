<?php

class Xmltv_Config
{
	
    /**
     * 
     * @param string $type
     * @param string $mode
     * @return Zend_Config
     */
	public static function getConfig ($type = 'application', $mode = 'production') {

		if( $type=='site' ) {
			return Zend_Registry::get('site_config');
		} else {
			return Zend_Registry::get('app_config');
		}
	
	}

	/*
	public static function getSitesXmlConfig ($group = null) {

		if(  !$group ) throw new Exception( "Нужно название группы сайтов", 500 );
		
		return new Zend_Config_Xml(APPLICATION_PATH.'/configs/sites.xml', $group);
	
	}
	*/
	
	//public static function getDebug() {
	//	return (bool)self::getConfig('site', APPLICATION_ENV )->site->debug;
	//}
	/**
	 * Cache lifetime (seconds)
	 */
	/*
	public static function getCacheLifetime() {
		return (int)Zend_Registry::get('site_config')->cache->system->get('lifetime', 86400);
	}
	public static function getCaching() {
		return (bool)Zend_Registry::get('site_config')->cache->system->get('enabled', false);
	}
	public static function getYoutubeCaching() {
		return (bool)Zend_Registry::get('site_config')->cache->youtube->get('enabled', false);
	}
	public static function getCacheLocation() {
		return (string)Zend_Registry::get('site_config')->cache->system->get('location', ROOT_PATH."/cache");
	}
	*/
	/*
	public static function getProxyHost() {
		return (string)self::getConfig('site', APPLICATION_ENV)->proxy->host;
	}
	public static function getProxyPort() {
		return (int)self::getConfig('site', APPLICATION_ENV)->proxy->port;
	}
	public static function getProxyType() {
		return (string)self::getConfig('site', APPLICATION_ENV)->proxy->type;
	}
	public static function getProxyEnabled() {
		return (bool)self::getConfig('site', APPLICATION_ENV)->proxy->active;
	}
	public static function getProfiling() {
		return (bool)self::getConfig('site', APPLICATION_ENV)->site->profile;
	}
	public static function getKeywords() {
		return (string)self::getConfig('site', APPLICATION_ENV)->site->keywords;
	}
	public static function getSiteTitle() {
		return (string)self::getConfig('site', APPLICATION_ENV)->site->title;
	}
	public static function getSiteDescription() {
		return (string)self::getConfig('site', APPLICATION_ENV)->site->description;
	}
	*/
	

}