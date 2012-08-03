<?php

class Xmltv_Config
{
	
	//protected $mode;
	
	function __construct(){
		
	}
	
	public static function getConfig ($type = 'application', $mode = 'development') {

		if( $type == 'site' ) 
		return new Zend_Config_Ini( APPLICATION_PATH . '/configs/site.ini', $mode );
		
		return new Zend_Config_Ini( APPLICATION_PATH . '/configs/application.ini', $mode );
	
	}


	public static function getSitesXmlConfig ($group = null) {

		if(  !$group ) throw new Exception( "Нужно название группы сайтов", 500 );
		
		return new Zend_Config_Xml(APPLICATION_PATH.'/configs/sites.xml', $group);
	
	}
	
	public static function getDebug() {
		return (bool)self::getConfig('site', APPLICATION_ENV )->site->debug;
	}
	public static function getCacheLifetime() {
		return (int)self::getConfig('site', APPLICATION_ENV)->cache->lifetime;
	}
	public static function getCaching() {
		return (bool)self::getConfig('site', APPLICATION_ENV)->cache->caching;
	}
	public static function getYoutubeCaching() {
		return (bool)self::getConfig('site', APPLICATION_ENV)->cache->youtubecaching;
	}
	public static function getCacheLocation() {
		return (string)self::getConfig('site', APPLICATION_ENV)->cache->location;
	}
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
	
	

}