<?php

/**
 * Bootstrap
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: Bootstrap.php,v 1.2 2012-03-29 18:16:52 dev Exp $
 *
 */
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

	public $debug = false;

	function run () {

		Zend_Registry::set( 'Zend_Locale', new Zend_Locale( 'ru_RU' ) );
		
		defined( 'DATE_MYSQL' ) || define( DATE_MYSQL, 
		Zend_Date::YEAR . '-' . Zend_Date::MONTH . '-' . Zend_Date::DAY . ' ' . Zend_Date::HOUR .
		 ':' . Zend_Date::MINUTE . ':' . Zend_Date::SECOND );
		defined( 'DATE_MYSQL_SHORT' ) || define( DATE_MYSQL_SHORT, 
		Zend_Date::YEAR . '-' . Zend_Date::MONTH . '-' . Zend_Date::DAY );
		defined( 'ROOT_PATH' ) || define( ROOT_PATH, 
		str_replace( '/application', '', APPLICATION_PATH ) );
		defined( 'APP_STARTED' ) || define( APP_STARTED, 
		str_replace( '/application', '', APPLICATION_PATH ) );
		
		$config = new Zend_Config_Ini( APPLICATION_PATH . '/configs/site.ini', 
		APPLICATION_ENV );
		$this->debug = (bool)$config->site->get( 'site.debug', false );
		
		//var_dump(APPLICATION_ENV);
		//var_dump($this->debug);
		//die(__FILE__ . ': ' . __LINE__);
		
		$init = new Xmltv_Plugin_Init( APPLICATION_ENV );
		$fc = Zend_Controller_Front::getInstance()
			->setParam( 'useDefaultControllerAlways', false )
			->registerPlugin( $init );
			
		if( $this->debug )
			$fc->throwExceptions( true ); // disable ErrorController
		else
			$fc->throwExceptions( false ); // enable ErrorController
		
		$init->setRouter( $fc->getRouter() );
		$fc->setRouter( $init->getRouter() );
		
		try {
			$fc->dispatch();
		} catch (Exception $e) {
			if ($this->debug) {
				echo $e->getMessage();
				var_dump($e->getTrace());
				exit(__FILE__ . ': ' . __LINE__);
			}
		}
		
	}


	protected function _initAutoloader () {

		$front = $this->bootstrap( "frontController" )->frontController;
		$modules = $front->getControllerDirectory();
		$default = $front->getDefaultModule();
		foreach (array_keys( $modules ) as $module) {
			if( $module === $default ) continue;
			$moduleloader = new Zend_Application_Module_Autoloader( 
			array('namespace'=>ucfirst( $module ), 
			'basePath'=>$front->getModuleDirectory( $module )) );
		}
	}


	protected function _initJquery () {

		$this->bootstrap( 'view' );
		$view = $this->getResource( 'view' );
		$view->addHelperPath( "ZendX/JQuery/View/Helper", "ZendX_JQuery_View_Helper" );
	}

	/*
	private function _getRoutesBackend () {

		$fc = $this->bootstrap( "frontController" )->frontController;
		$r = new Zend_Controller_Router_Rewrite();
		
		$r->addRoute( 'admin/movies/grab', 
		new Zend_Controller_Router_Route( 'admin/movies/grab/:site', 
		array('module'=>'admin', 'controller'=>'movies', 'action'=>'grab') ) );
		$r->addRoute( 'admin/login', 
		new Zend_Controller_Router_Route_Static( 'admin', 
		array('module'=>'admin', 'controller'=>'index', 'action'=>'login') ) );
		$r->addRoute( 'admin', 
		new Zend_Controller_Router_Route_Static( 'admin/index', array('module'=>'admin', 'controller'=>'index') ) );
		$r->addRoute( 'admin/tasks', 
		new Zend_Controller_Router_Route_Static( 'admin/index', array('module'=>'admin', 'controller'=>'index') ) );
		
		$fc->setRouter($r);
	}
	*/

	
	/*
	protected function _initRouter () {

		$fc = $this->bootstrap( "frontController" )->frontController;
		
		
		$fc->setRouter( $router );
	}
	*/

	

}

