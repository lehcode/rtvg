<?php
/**
 * Application bootstrap
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @version $Id: Bootstrap.php,v 1.31 2013-04-11 05:21:11 developer Exp $
 *
 */

mb_internal_encoding('UTF-8');

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	function run() 
	{
		Zend_Registry::set( 'Zend_Locale', new Zend_Locale( 'ru_RU' ) );
		defined( 'RTVG_VERSION' ) || define( 'RTVG_VERSION', "stable" );
		
		date_default_timezone_set( Zend_Registry::get( 'site_config' )->site->get( 'timezone', 'Europe/Moscow' ) );
		
		Zend_Registry::set( 'db_local', $this->getResource('multidb')->getDefaultDb() );		
		Zend_Layout::startMvc();
		
		//Caching
		$cacheConf = Zend_Registry::get('site_config')->cache->get('system');
		$cache = Zend_Cache::factory( 'Core', 'File', array(  
			'lifetime' => $cacheConf->get('lifetime', 43200),
			'automatic_serialization' => true
		), array( 'cache_dir' => realpath( APPLICATION_PATH.'/..'.$cacheConf->get('location'))) );
		$e = (bool)Zend_Registry::get('site_config')->cache->system->get('enabled');
		$cache->setOption( 'caching', $e );
		Zend_Registry::set('cache', $cache);		
		
		//Front controller
		$router = new Xmltv_Plugin_Router( APPLICATION_ENV );
		$fc = Zend_Controller_Front::getInstance()
			->setParam( 'useDefaultControllerAlways', false )
			->setParam( 'bootstrap', $this )
			->registerPlugin( new Xmltv_Plugin_Auth( APPLICATION_ENV ) )
            ->registerPlugin( $router )
		;
        
        if (APPLICATION_ENV=='testing'){
            $fc->setDefaultControllerName('frontpage');
        }
        
        $router->setRouter($fc->getRouter());
		$fc->setRouter($router->getRouter());
        
		// http://codeutopia.net/blog/2009/03/02/handling-errors-in-zend-framework
		try {
		    $response = $fc->dispatch();
		} catch (Exception $e) {
            if(get_class($e)=='Zend_Loader_PluginLoader_Exception'){
                //skip
            } else {
                throw new Exception($e->getMessage(), 500, $e);
            }
            
		}
		
        if (isset($response) && !$response->isException()) {
			$response->sendHeaders();
			$response->outputBody();
		}
        
	}
    
    protected function _initActionHelpers () {
		
		Zend_Controller_Action_HelperBroker::addPath( APPLICATION_PATH.'/controllers/helpers', 'Xmltv_Controller_Action_Helper' );
		Zend_Controller_Action_HelperBroker::addPath( APPLICATION_PATH.'/controllers/helpers', 'Rtvg_Controller_Action_Helper' );
		
	}
    
    protected function _initDb(){
            return $this->bootstrap('multidb')->getResource('multidb')->getDefaultDb();
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
        
        Zend_Registry::set( 'adult', (bool)$siteConfig->get('frontend')->adult );
		
	}
	
	
	/**
	 * @return Zend_Log
	 */
	protected function _initLog()
	{
		
	    $log = new Zend_Log( new Zend_Log_Writer_Stream( APPLICATION_PATH . '/../log/exceptions.log' ));
		return $log;
		
	}
    
    protected function _initFirebugLog(){
        $l = new Zend_Log( new Zend_Log_Writer_Firebug() );
        Zend_Registry::set('fireLog', $l);
        return $l;
    }
	
	/**
	 * 
	 * @return Zend_Application_Module_Autoloader
	 */
	protected function _initAutoloader () 
	{
        
        
        $autoloader = Zend_Loader_Autoloader::getInstance();
		$autoloader->pushAutoloader( array('ezcBase', 'autoload'), 'ezc' );
        
        $front = $this->bootstrap( "frontController" )->frontController;
		$modules = $front->getControllerDirectory();
		$default = $front->getDefaultModule();
        
        foreach (array_keys( $modules ) as $module) {
			if( $module !== $default ) {
                $moduleloader = new Zend_Application_Module_Autoloader( array(
                    'namespace'=>ucfirst( $module ),
                    'basePath'=>$front->getModuleDirectory( $module )) 
                );
            }
		}
        return $moduleloader;
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
	protected function _initViewSettings()
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
	    
	    $baseCss = $view->baseUrl('css/base.less') ;
	    $view->headLink( array('rel'=>'stylesheet/less', 'href'=>$baseCss), 'APPEND')
            ->headLink( array('rel'=>'stylesheet/less', 'href'=>$view->baseUrl('css/template.less')), 'APPEND')
            ->appendStylesheet($view->baseUrl('css/fonts.css'));

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
        // Check if browser is IE and add stylesheets
	    $browserMsie = false;
	    if (strstr($browser, 'MSIE')) {
	    	$browserMsie = true;
	    	$view->headLink()
	    		->appendStylesheet( $view->baseUrl( 'css/ie.css' ))
	    		->appendStylesheet( $view->baseUrl( 'css/fonts-ie.css' ));
	    }
	    
	    $view->addHelperPath( APPLICATION_PATH.'/views/helpers/', 'Rtvg_View_Helper');
        $view->addHelperPath("ZendX/JQuery/View/Helper",'ZendX_JQuery_View_Helper');
        
	}
    
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
