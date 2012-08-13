<?php

/**
 * Bootstrap
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: Bootstrap.php,v 1.7 2012-08-13 13:20:15 developer Exp $
 *
 */

mb_internal_encoding('UTF-8');

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

	public $debug = false;

	function run() {

		Zend_Registry::set( 'Zend_Locale', new Zend_Locale( 'ru_RU' ) );
		
		defined( 'DATE_MYSQL' ) || define( 'DATE_MYSQL', Zend_Date::YEAR . '-' . Zend_Date::MONTH . '-' . Zend_Date::DAY . ' ' . Zend_Date::HOUR . ':' . Zend_Date::MINUTE . ':' . Zend_Date::SECOND );
		defined( 'DATE_MYSQL_SHORT' ) || define( 'DATE_MYSQL_SHORT', Zend_Date::YEAR . '-' . Zend_Date::MONTH . '-' . Zend_Date::DAY );
		defined( 'ROOT_PATH' ) || define( 'ROOT_PATH', str_replace( '/application', '', APPLICATION_PATH ) );
		defined( 'APP_STARTED' ) || define( 'APP_STARTED', str_replace( '/application', '', APPLICATION_PATH ) );
		
		$config = new Zend_Config_Ini( APPLICATION_PATH . '/configs/site.ini', APPLICATION_ENV );
		$debug = (bool)$config->site->get( 'debug', false );
		
		date_default_timezone_set($config->site->get('timezone', 'Europe/Moscow'));
		
		$this->bootstrap('db')->getResource('db')->setFetchMode(Zend_DB::FETCH_OBJ);
		
		$init = new Xmltv_Plugin_Init( APPLICATION_ENV );
		$fc = Zend_Controller_Front::getInstance()
			->setParam( 'useDefaultControllerAlways', false )
			->registerPlugin( $init );
		
		if( APPLICATION_ENV == 'production' ) {
			$fc->throwExceptions( false ); // disable ErrorController and logging
			$fc->returnResponse (true);
		} else {
			$fc->throwExceptions( true ); // enable ErrorController and logging
			$fc->returnResponse (false);
		}
		
		$init->setRouter( $fc->getRouter() );
		$fc->setRouter( $init->getRouter() );		
		
		//var_dump( $fc->getParams() );
		
		try {
			$response = $fc->dispatch();
		} catch (Exception $e) {
			
			if( $debug ) {
				echo $e->getMessage();
				Zend_Debug::dump($e->getTrace());
			}
			
			$log = new Zend_Log( 
			new Zend_Log_Writer_Stream( ROOT_PATH . '/log/exceptions.log' ) );
			$log->debug( $e->getMessage() . PHP_EOL . $e->getTraceAsString() );
			
		}
		
		if (isset($response)) {
			if( $response->isException() ) {
				$exception = $response->getException();
				//var_dump($response);
				//var_dump($exception);
				//die(__FILE__.': '.__LINE__);
				//$log = new Zend_Log(  new Zend_Log_Writer_Stream( ROOT_PATH . '/log/exceptions.log' ) );
				//$log->debug(  $exception->getMessage() . PHP_EOL . $exception->getTraceAsString() );
			} else {
				$response->sendHeaders();
				$response->outputBody();
			}
		}
				
	}
	
	protected function _initLog(){
		defined( 'ROOT_PATH' ) || define( 'ROOT_PATH', str_replace( '/application', '', APPLICATION_PATH ) );
		$front = $this->bootstrap( "frontController" )->frontController;
		try {
			$log = new Zend_Log( new Zend_Log_Writer_Stream( ROOT_PATH . '/log/exceptions.log' ) );
		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}
	

	protected function _initAutoloader () {

		$front = $this->bootstrap( "frontController" )->frontController;
		$modules = $front->getControllerDirectory();
		$default = $front->getDefaultModule();
		foreach (array_keys( $modules ) as $module) {
			if( $module === $default ) continue;
			$moduleloader = new Zend_Application_Module_Autoloader( array('namespace'=>ucfirst( $module ), 'basePath'=>$front->getModuleDirectory( $module )) );
		}
	}


	protected function _initJquery () {

		$this->bootstrap( 'view' );
		$view = $this->getResource( 'view' );
		$view->addHelperPath( "ZendX/JQuery/View/Helper", "ZendX_JQuery_View_Helper" );
	}

}

