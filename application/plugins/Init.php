<?php

/**
 * Application initialization plugin
 *
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @version $Id: Init.php,v 1.29 2013-04-12 06:56:22 developer Exp $
 */
class Xmltv_Plugin_Init extends Zend_Controller_Plugin_Abstract
{

	protected $_env = 'production';
	protected $_request;
	protected $_router;

	/**
	 * Constructor
	 *
	 * @param  string $env Execution environment
	 * @return void
	 */
	public function __construct ($env='production') {
		$this->setEnv( $env );
	}


	public function setEnv ($env='production') {
		$this->_env = $env;
	}


	/**
	 * Route startup hook
	 *
	 * @param  Zend_Controller_Request_Abstract $request
	 * @return void
	 */
	public function routeStartup (Zend_Controller_Request_Abstract $request) {

		$this->_initConfig();
		$this->_initActionHelpers();
		$this->_initAutoloader();
		//$this->_initUserAgent();
		$this->_initNav();
		$this->_initHttpClient();
		
		
		
	}
	

	/**
	 * Initialize controller helpers
	 */
	protected function _initActionHelpers () {
		
		Zend_Controller_Action_HelperBroker::addPath( APPLICATION_PATH.'/controllers/helpers', 'Xmltv_Controller_Helper' );
		Zend_Controller_Action_HelperBroker::addPath( APPLICATION_PATH.'/controllers/helpers', 'Rtvg_Controller_Action_Helper' );
		
	}

	
	/**
	 * Initialize 'site' and 'application' configuration
	 */
	protected function _initConfig () {

		$c = new Zend_Config_Ini( APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV );
		Zend_Registry::set('app_config', $c);
		$c = new Zend_Config_Ini( APPLICATION_PATH . '/configs/site.ini', APPLICATION_ENV );
		Zend_Registry::set( "site_config", $c );
		
	}
	
	/**
	 * Autoload 3rd-party libraries
	 */
	protected function _initAutoloader(){
		
		$autoloader = Zend_Loader_Autoloader::getInstance();
		$autoloader->pushAutoloader( array('ezcBase', 'autoload'), 'ezc' );
		
	}
	
	/**
	 * Initialize navigation
	 * @throws Zend_Exception
	 */
	protected function _initNav(){
		
		$menu = new Zend_Navigation( new Zend_Config_Xml( APPLICATION_PATH . '/configs/nav/fp.xml', 'nav' ) );
		Zend_Registry::set('FpMenu', $menu);
		
		$menu = new Zend_Navigation( new Zend_Config_Xml( APPLICATION_PATH . '/configs/nav/admin/main.xml', 'nav' ) );
		Zend_Registry::set('AdminMenu', $menu);
		
		
	}
	
	protected function _initHttpClient(){
		
		$client = new Zend_Http_Client_Adapter_Curl();
		Zend_Registry::set('http_client', $client);
		
	}
	
}