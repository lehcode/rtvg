<?php

/**
 * Bootstrap
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: Bootstrap.php,v 1.10 2013-01-02 05:07:49 developer Exp $
 *
 */

/**
 * @todo http://yandex.ru/yandsearch?text=%22365+%D0%B4%D0%BD%D0%B5%D0%B9%22&tld=ua&lr=143&filter=mobile_apps //мобильные приложения на Яндексе
 * @todo http://help.yandex.ru/webmaster/?id=1116426 //микроразметка видео
 * @todo http://help.yandex.ru/webmaster/?id=1122760 //микроразметка картинок
 * @todo http://tv-mania.narod.ru/actordb.htm //база данных актеров
 * @todo http://wap.filmz.ru/ //фильмы
 * @todo http://www.filmz.ru/films/0/ //фильмы
 * @todo http://riw.ru/riw.rss //новости RSS
 * 
 */
mb_internal_encoding('UTF-8');
//mysqli_set_charset('utf8');

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

	public $debug = false;

	function run() {
		
		if (APPLICATION_ENV=='development')
			Zend_Session::$_unitTestEnabled = true;
		
		Zend_Registry::set( 'Zend_Locale', new Zend_Locale( 'ru_RU' ) );
		defined( 'ROOT_PATH' ) || define( 'ROOT_PATH', str_replace( '/application', '', APPLICATION_PATH ) );
		
		date_default_timezone_set( Zend_Registry::get('site_config')->site->get( 'timezone', 'Europe/Moscow' ) );
		
		$db = $this->bootstrap('multidb')->getResource('multidb')->getDb('local');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);
		Zend_Registry::set('db_local', $db);
		
		$db = $this->bootstrap('multidb')->getResource('multidb')->getDb('archive');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);
		Zend_Registry::set('db_archive', $db);

		Zend_Layout::startMvc();
		
		/*
		 * Caching
		 */
		$cacheConf = Zend_Registry::get('site_config')->cache->get('system');
		$cache = Zend_Cache::factory( 'Core', 'File', array(  
			'lifetime' => $cacheConf->get('lifetime', 43200),
			'automatic_serialization' => true
		), array( 
			'cache_dir' => $cacheConf->get('location', ROOT_PATH.'/cache')
		));
		$cache->setOption('caching', (bool)Zend_Registry::get('site_config')->cache->system->get('enabled', true));
		Zend_Registry::set('cache', $cache);
		
		/*
		 * Front controller
		 */
		$router = new Xmltv_Plugin_Router( APPLICATION_ENV );
		$fc = Zend_Controller_Front::getInstance()
			->setParam( 'useDefaultControllerAlways', true )
			->setParam( 'bootstrap', $this )
			->registerPlugin( $router )
			->registerPlugin( new Xmltv_Plugin_Init( APPLICATION_ENV ) )
			->registerPlugin( new Xmltv_Plugin_Stats( APPLICATION_ENV ) )
			->returnResponse( true );
		
		/*
		 * http://codeutopia.net/blog/2009/03/02/handling-errors-in-zend-framework/
		 */
		
		if( APPLICATION_ENV != 'development' ) {
			$fc->throwExceptions( false ); //Enable ErrorController and logging
		} else {
			$fc->throwExceptions( true ); //Disable ErrorController and logging
		}
		
		
		$router->setRouter($fc->getRouter());
		$fc->setRouter($router->getRouter());		
		
		try {
			$response = $fc->dispatch();
			if( $response && $response->isException() ) {
				
				$exceptions = $response->getException();
				foreach ($exceptions as $e){
					var_dump($e);
				}
				die(__FILE__.': '.__LINE__);
				//var_dump($response);
				//var_dump($exception);
				//die(__FILE__.': '.__LINE__);
				
				/*
				$mail = new Zend_Mail();
				$mail->setBodyText( $exception->getTraceAsString() );
				$mail->setFrom('robot@rutvgid.ru', 'Robot');
				$mail->addTo('egeshi@gmail.com', 'Admin');
				$mail->setSubject($exception->getMessage());
				$mail->send();
				*/
				
				//var_dump($exception);
				//die(__FILE__.': '.__LINE__);
				
				//$log = new Zend_Log( new Zend_Log_Writer_Stream( APPLICATION_PATH . '/../log/exceptions.log' ));
				//$log->debug( $exception->getMessage() . PHP_EOL . $exception->getTraceAsString() );
				
			
			}
			
		} catch (Zend_Exception $e) {
			if( APPLICATION_ENV == 'development' ) {
				echo $e->getMessage();
				echo $e->getCode();
				Zend_Debug::dump($e->getTrace());
			} else {
				
				
				$mail = new Zend_Mail();
				$mail->setBodyText( $e->getTraceAsString() );
				$mail->setFrom('robot@rutvgid.ru', 'Robot');
				$mail->addTo('egeshi@gmail.com', 'Admin');
				$mail->setSubject($e->getMessage());
				if (APPLICATION_ENV=='testing') {
					$t = new Zend_Mail_Transport_File(array('path'=>ROOT_PATH.'/log/mail'));
				}
				$mail->send($t);
				
				
				if( APPLICATION_ENV!='production' ){
					$log = new Zend_Log( new Zend_Log_Writer_Stream( APPLICATION_PATH . '/../log/testing.log' ));
					$log->debug( $e->getMessage() . PHP_EOL . print_r( $e->getTraceAsString(), true ) );
				} else {
					$log = new Zend_Log( new Zend_Log_Writer_Stream( APPLICATION_PATH . '/../log/exceptions.log' ));
					$log->debug( $e->getMessage() . PHP_EOL . print_r( $e->getTraceAsString(), true ) );
				}
				
				
			}
			
		}
		
		
		if (isset($response)) {
			$response->sendHeaders();
			$response->outputBody();
		}
		
				
	}
	
	/**
	 * 
	 */
	protected function _initConfig(){
		
		$appConfig = new Zend_Config_Ini( APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV );
		Zend_Registry::set('app_config', $appConfig);
		$siteConfig = new Zend_Config_Ini( APPLICATION_PATH . '/configs/site.ini', APPLICATION_ENV );
		Zend_Registry::set('site_config', $siteConfig);
		
	}
	
	/**
	 * 
	 */
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
			$moduleloader = new Zend_Application_Module_Autoloader( array(
				'namespace'=>ucfirst( $module ),
				'basePath'=>$front->getModuleDirectory( $module )) 
			);
		}
	}


	protected function _initJquery () {

		$this->bootstrap( 'view' );
		$view = $this->getResource( 'view' );
		$view->addHelperPath( "ZendX/JQuery/View/Helper", "ZendX_JQuery_View_Helper" );
	}
	
	/**
	 * @todo _initStats()
	 */
	
}


