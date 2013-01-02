<?php
/**
 * 
 * Colect statistics
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @package rutvgid
 * @filesource $Source: /home/developer/cvs/rutvgid.ru/application/plugins/Stats.php,v $
 * @version $Id: Stats.php,v 1.4 2013-01-02 05:07:50 developer Exp $
 *
 */
class Xmltv_Plugin_Stats extends Zend_Controller_Plugin_Abstract 
{
	protected $_env = 'development';
	private $_db;
	
	/**
	 * 
	 * Constructor
	 * @param string $env
	 */
	public function __construct ($env='production') {
		$this->_env = strval($env);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Zend_Controller_Plugin_Abstract::routeStartup()
	 */
	public function routeStartup(Zend_Controller_Request_Abstract $request){
		
		try {
			$front = Zend_Controller_Front::getInstance();
			$bootstrap = $front->getParam("bootstrap");
			if ($bootstrap->hasPluginResource("multidb")) {
				$multidb = $bootstrap->getPluginResource("multidb");
			}
			$db = $multidb->getDb('local');
			$this->_setHttpInfo();
		} catch (Exception $e) {
			throw new Zend_Exception($e->getMessage(), $e->getCode(), $e);
		}
		
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	protected function _setHttpInfo(){
		
		$info  = array(
			'referer'=> isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER']) ?  $_SERVER['HTTP_REFERER'] : '',
			'request_uri'=> isset($_SERVER['REQUEST_URI']) && !empty($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '',
			'redirect_url'=> isset($_SERVER['REDIRECT_URL']) && !empty($_SERVER['REDIRECT_URL']) ?  $_SERVER['REDIRECT_URL'] : '',
			'user_agent'=> isset($_SERVER['HTTP_USER_AGENT']) && !empty($_SERVER['HTTP_USER_AGENT']) ?  $_SERVER['HTTP_USER_AGENT'] : '',
			'method'=> isset($_SERVER['REQUEST_METHOD']) && !empty($_SERVER['REQUEST_METHOD']) ?  $_SERVER['REQUEST_METHOD'] : '',
			'ip'=> isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR']) ?  $_SERVER['REMOTE_ADDR'] : '',
			'host'=> isset($_SERVER['HTTP_HOST']) && !empty($_SERVER['HTTP_HOST']) ?  $_SERVER['HTTP_HOST'] : '',
			'request_time'=> isset($_SERVER['REQUEST_TIME']) && !empty($_SERVER['REQUEST_TIME']) ?  $_SERVER['REQUEST_TIME'] : '',
			'request_time_float'=> isset($_SERVER['REQUEST_TIME_FLOAT']) && !empty($_SERVER['REQUEST_TIME_FLOAT']) ?  $_SERVER['REQUEST_TIME_FLOAT'] : '',
		);
		
		if (isset($info['referer'])){
		    $info['query'] = parse_url($info['referer'], PHP_URL_QUERY);
		}
		
		//var_dump($info);
		//die(__FILE__.': '.__LINE__);
		
		Zend_Registry::set('http_info', $info);
		return true;
		
	}
	
}