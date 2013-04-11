<?php

/**
 * Application initialization plugin
 *
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @version $Id: Init.php,v 1.28 2013-04-11 05:21:11 developer Exp $
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
	 * (non-PHPdoc)
	 * @see Zend_Controller_Plugin_Abstract::routeShutdown()
	 */
	public function routeShutdown( Zend_Controller_Request_Abstract $request) {

		if (APPLICATION_ENV=='development'){
			//var_dump($request->getParams());
			//die(__FILE__.': '.__LINE__);
		}
		
		$modules = array(
			'default',
			'admin'
		);
		if (!in_array($request->getModuleName(), $modules)) {
			throw new Zend_Exception( Rtvg_Message::ERR_NOT_FOUND, 404 );
		}
		
		$controllerName = $request->getControllerName();
		
		if($request->getModuleName() == 'admin') {
			ini_set('display_errors', 1);
			error_reporting(E_ALL ^ E_NOTICE);
			//var_dump(ini_get('error_reporting'));
			/*
			foreach( array('E_ALL', 'E_NOTICE', '~E_NOTICE', 'E_ALL&~E_NOTICE') as $s) {
				eval("\$v=$s;");
				printf("%20s = dec %10u\n", $s, $v, $v);
			}
			*/
		}
		
		if (preg_match('/[^a-z0-9%-]+$/', $controllerName)){
			
			if ($controllerName=='%25D0%25B2%25D0%25B8%25D0%25B4%25D0%25B5%25D0%25BE' ||
				$controllerName == '%25d0%25b2%25d0%25b8%25d0%25b4%25d0%25b5%25d0%25be'){
				
				$request->setControllerName('videos');
				$action = $request->getActionName();
				
				switch ($action){
					
					case '%25D0%25BE%25D0%25BD%25D0%25BB%25D0%25B0%25D0%25B9%25D0%25BD':
					case '%25d0%25be%25d0%25bd%25d0%25bb%25d0%25b0%25d0%25b9%25d0%25bd':
					case 'видео':
					case '%D0%BE%D0%BD%D0%BB%D0%B0%D0%B9%D0%BD':
					case '%d0%be%d0%bd%d0%bb%d0%b0%d0%b9%d0%bd':
						$request->setActionName('show-video');
					break;
					
					default:
						throw new Zend_Exception( Rtvg_Message::ERR_NOT_FOUND.': '.$controllerName, 404 );
					break;
				}
				
			} elseif($controllerName=='%D0%B2%D0%B8%D0%B4%D0%B5%D0%BE'){
				$request->setControllerName('videos');
				if ($request->getActionName()=='%D1%82%D0%B5%D0%BC%D0%B0'){
					throw new Zend_Exception( Rtvg_Message::ERR_NOT_FOUND, 404 );
				}
				$request->setActionName('show-video');
			} elseif($controllerName=='видео'){
				$request->setControllerName('videos');
				$request->setActionName('show-video');
			} elseif($controllerName=='%C3%90%C2%BA%C3%90%C2%B0%C3%90%C2%BD%C3%90%C2%B0%C3%90%C2%BB%C3%91%E2%80%B9' ||
					$controllerName=='fonts' ||
					$controllerName=='images' ||
					$controllerName=='img' ||
					$controllerName=='css'){
				throw new Zend_Exception( Rtvg_Message::ERR_NOT_FOUND, 404 );
			} else {
				throw new Zend_Exception( Rtvg_Message::ERR_NOT_FOUND, 404 );
			}
		}
		
	}


	

	/**
	 * Initialize controller helpers
	 */
	protected function _initActionHelpers () {
		
		Zend_Controller_Action_HelperBroker::addPath( APPLICATION_PATH.'/controllers/helpers', 'Xmltv_Controller_Action_Helper' );
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
	 * @param $router 
	 */
	public function setRouter ($router) {

		$this->_router = $router;
	}
	
	/**
	 * Autoload 3rd-party libraries
	 */
	protected function _initAutoloader(){
		
		//$autoloader = Zend_Loader_Autoloader::getInstance();
		//$autoloader->pushAutoloader(array('ezcBase', 'autoload'), 'ezc');
		
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