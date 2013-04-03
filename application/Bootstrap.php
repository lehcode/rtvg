<?php
/**
 * Application bootstrap
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @version $Id: Bootstrap.php,v 1.28 2013-04-03 18:18:05 developer Exp $
 *
 */

mb_internal_encoding('UTF-8');

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

	public $debug = false;

	function run() 
	{
		
		if (APPLICATION_ENV=='testing') {
			Zend_Session::$_unitTestEnabled = true;
		}
		
		Zend_Registry::set( 'Zend_Locale', new Zend_Locale( 'ru_RU' ) );
		defined( 'ROOT_PATH' ) || define( 'ROOT_PATH', str_replace( '/application', '', APPLICATION_PATH ) );
		Zend_Registry::set( 'rtvg_version', 'beta5' );
		
		date_default_timezone_set( Zend_Registry::get( 'site_config' )->site->get( 'timezone', 'Europe/Moscow' ) );
		
		Zend_Registry::set( 'db_local', $this->getResource('multidb')->getDefaultDb() );
		Zend_Registry::set( 'db_archive', $this->getResource('multidb')->getDb('archive') );
		
		Zend_Layout::startMvc();
		
		/*
		 * Caching
		 */
		$cacheConf = Zend_Registry::get('site_config')->cache->get('system');
		$cache = Zend_Cache::factory( 'Core', 'File', array(  
			'lifetime' => $cacheConf->get('lifetime', 43200),
			'automatic_serialization' => true
		), array( 'cache_dir' => ROOT_PATH.$cacheConf->get('location')));
		$e = (bool)Zend_Registry::get('site_config')->cache->system->get('enabled');
		$cache->setOption( 'caching', $e );
		Zend_Registry::set('cache', $cache);
		
		// Place this in your bootstrap file before dispatching your front controller
		$consoleWriter = new Zend_Log_Writer_Firebug();
		Zend_Registry::set( 'console_log', new Zend_Log( $consoleWriter ) );
		
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
			->registerPlugin( new Xmltv_Plugin_Auth( APPLICATION_ENV ) )
			->returnResponse( true )
			->throwExceptions( false );
		
		
		if (APPLICATION_ENV=='development'){
		    $fc->returnResponse( true )
				->throwExceptions( true );
		}
		
		$router->setRouter($fc->getRouter());
		$fc->setRouter($router->getRouter());
		$log = $this->bootstrap()->getResource('Log');
		
		/*
		 * http://codeutopia.net/blog/2009/03/02/handling-errors-in-zend-framework/
		*/
		try {
		    
		    $response = $fc->dispatch();
		    
		    if( $response && $response->isException() ) {
		    	//die(__FILE__.': '.__LIN);
		    	$exceptions = $response->getException();
		    	foreach ($exceptions as $e){
		    		$log->log( $e->getMessage(), Zend_Log::DEBUG, $e->getTraceAsString() );
		    		if (APPLICATION_ENV=='development'){
		    			throw new Zend_Exception($e->getMessage(), $e->getCode(), $e);
		    		}
		    	}
		    }
		} catch (Exception $e) {
		    
		    if( APPLICATION_ENV == 'development' ) {
		    	die($e->getMessage());
		    } else {
		        $log->log( $e->getMessage(), Zend_Log::DEBUG, $e->getTraceAsString() );
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
	protected function _initConfig()
	{
		
		$appConfig = new Zend_Config_Ini( APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV );
		Zend_Registry::set('app_config', $appConfig);
		$siteConfig = new Zend_Config_Ini( APPLICATION_PATH . '/configs/site.ini', APPLICATION_ENV );
		Zend_Registry::set('site_config', $siteConfig);
		
	}
	
	
	/**
	 * @return Zend_Log
	 */
	protected function _initLog()
	{
		
	    if (APPLICATION_ENV=='testing'){
	        $log = new Zend_Log( new Zend_Log_Writer_Stream( APPLICATION_PATH . '/../log/testing.log' ));
		} else {
		    $log = new Zend_Log( new Zend_Log_Writer_Stream( APPLICATION_PATH . '/../log/exceptions.log' ));
		}
		return $log;
		
	}
	
	/**
	 * 
	 * @return Zend_Application_Module_Autoloader
	 */
	protected function _initAutoloader () 
	{

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
		return $moduleloader;
	}


	/**
	 * Load jQuery libraries
	 */
	protected function _initJquery () 
	{

		$this->bootstrap( 'view' );
		$view = $this->getResource( 'view' );
		
		$view->addHelperPath( "ZendX/JQuery/View/Helper", "ZendX_JQuery_View_Helper" );
		
		$viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();
		$viewRenderer->setView($view);
		Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);
		
		
	}
	
	/**
	 * @todo _initStats()
	 */
	
	/**
	 * Initialize user
	 */
	protected function _initUser()
	{
	    $db = $this->bootstrap('multidb')->getResource('multidb')->getDb('local');
	    $db->setFetchMode( Zend_DB::FETCH_OBJ );
	    $auth = Zend_Auth::getInstance();
		
		if ($auth->hasIdentity()) {
		    
		    $users = new Xmltv_Model_Users();
		    
		    try {
		        
		        $openId = $auth->getIdentity();
		        if (is_object($openId)){
		            $openId = $openId->email;
		        }
		        
		        if (($user = $users->searchByOpenId( $openId ))!==false) {
		        	if ((time() - (int)strtotime($user->last_login)) > 60*5) {
		        		$user->last_login = Zend_Date::now()->toString('YYYY-MM-dd HH:mm:ss');
		        		$user->online=1;
		        		$user->save();
		        	}
		        } 
		        
		    } catch (Zend_Exception $e) {
		        throw new Zend_Exception($e->getMessage(), $e->getCode(), $e);
		    }
		}
		
		if (isset($user) && ($user !== false)) {
		    $user = Bootstrap_Auth::setCurrentUser($user);
		} else {
			$user = Bootstrap_Auth::getCurrentUser($db);
		}
		
		return $user;
		
	}
	
	/**
	 * Initialize ACLs
	 * 
	 * @return Xmltv_Model_Acl
	 */
	protected function _initAcl()
	{
	    $db = $this->bootstrap('multidb')->getResource('multidb')->getDb('local');
	    $db->setFetchMode( Zend_DB::FETCH_OBJ );
		$acl = Xmltv_Model_Acl::getInstance();
		Zend_View_Helper_Navigation_HelperAbstract::setDefaultAcl( $acl );
		Zend_View_Helper_Navigation_HelperAbstract::setDefaultRole( Bootstrap_Auth::getCurrentUser($db)->role );		
		return $acl;
	}
	
	/**
	 * Setup core HTML view configuration
	 * @param array $config
	 */
	protected function _initViewsettings()
	{	
	    $this->bootstrap('view');
	    $view = $this->getResource('view');
	    $view->doctype( 'HTML5' );
	    $view->setEncoding( 'UTF-8' );
	    $view->headMeta()
	    	->setHttpEquiv( 'Content-Type', 'text/html;charset=utf-8' )
	    	->setHttpEquiv( 'X-UA-Compatible', 'IE=9;IE=8;' )
	    	->setName('viewport', 'width=device-width, initial-scale=1.0');
	    $view->headTitle()
	    	->setSeparator(' :: ' )
	    	->prepend( "rutvgid.ru" );
	    
	    $baseCss = (APPLICATION_ENV=='production') ? $view->baseUrl('css/base.min.less') : $view->baseUrl('css/base.less') ;
	    $view->headLink( array('rel'=>'stylesheet/less', 'href'=>$baseCss), 'APPEND');
	    $view->headLink( array('rel'=>'stylesheet/less', 'href'=>$view->baseUrl('css/template.less')), 'APPEND');
	    $view->headLink()
	    	//->prependStylesheet( $view->baseUrl('css/base.css'))
	    	->appendStylesheet($view->baseUrl('css/fonts.css'));
	    	//->appendStylesheet( $view->baseUrl('js/tip/jquery.tooltip.css'));

	    $view->headScript()
	    	->setAllowArbitraryAttributes(true)
	    	->prependFile( $view->baseUrl( 'js/less.min.js' ));
	    
	    if (APPLICATION_ENV=='development'){
	    	$view->headScript()->prependFile( $view->baseUrl( 'js/bs/base.js' ));
	    } else {
	    	$view->headScript()->prependFile( $view->baseUrl( 'js/bs/base.min.js' ));
	    }
	    
	    
	    // Get browser
	    $browser = $view->userAgent()->getUserAgent();
	    if (APPLICATION_ENV=='development'){
	    	//var_dump($this->userAgent());
	    	//die(__FILE__.': '.__LINE__);
	    	 
	    }
	    // Check if browser is IE and add stylesheets
	    $browserMsie = false;
	    if (strstr($browser, 'MSIE')) {
	    	$browserMsie = true;
	    	$view->headLink()
	    		->appendStylesheet( $view->baseUrl( 'css/ie.css' ))
	    		->appendStylesheet( $view->baseUrl( 'css/fonts-ie.css' ));
	    }
	    
	    $view->inlineScript()
	    	->prependFile( $this->view->baseUrl('js/bs/alert.js') );
	    
	    
	    //$view->addHelperPath( APPLICATION_PATH.'/../library/views/helpers/' );
	    $view->addHelperPath( APPLICATION_PATH.'/views/helpers/', 'Rtvg_View_Helper');
	}
	
	
}


/**
 * 
 *
 * @author takeshi
 * @uses   Bootstrap
 *
 */
class Bootstrap_Auth extends Bootstrap
{
	/**
	 * @var Xmltv_User
	 */
	protected static $_currentUser;

	/**
	 *
	 * @param unknown_type $application
	 */
	public function __construct($application)
	{
		parent::__construct($application);
	}

	public static function setCurrentUser( Xmltv_User $user)
	{
		self::$_currentUser = $user;
		return $user;
	}

	/**
	 * @return Xmltv_Model_User
	 * @param  Zend_Db_Adapter $db
	 * @return Xmltv_User
	 */
	public static function getCurrentUser($db=null)
	{
	  
		if (APPLICATION_ENV=='development'){
			//var_dump(self::$_currentUser);
			//die(__FILE__.': '.__LINE__);
		}
	  
		if (null === self::$_currentUser) {

			if (isset($db)){
				$model = new Xmltv_Model_Users( array( 'db'=>$db ));
			} else {
				$model = new Xmltv_Model_Users();
			}

			self::setCurrentUser( $model->getUser() );
		}
		return self::$_currentUser;
	}

	/**
	 * @return App_Model_User
	 */
	public static function getCurrentUserId()
	{
		$user = self::getCurrentUser();
		return $user->getId();
	}

}
