<?php

/**
 * Application initialization plugin
 *
 * @uses Zend_Controller_Plugin_Abstract
 * @version $Id: Init.php,v 1.19 2013-03-06 04:54:51 developer Exp $
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
		$this->_initUserAgent();
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
	    
	    if (!preg_match('/^[a-z0-9]+$/', $request->getControllerName())){
	        throw new Zend_Exception( Rtvg_Message::ERR_WRONG_CONTROLLER );
	    }
	    /* 
	    if ($request->getParam('tz')==0){
	        $request->setParam('tz', null);
	    }
	     */
		//$moduleName = $request->getModuleName();
		
		//var_dump($request->getControllerName());
		//die(__FILE__.": ".__LINE__);
		
		/*
		switch ($moduleName) {
			case 'admin':
			    if ($request->getActionName()===null){
			        switch ($request->getControllerName()){
			        	default:
			        	case 'channels':
			        	    $request->setControllerName( 'index' );
			        	    break;
			        }
			        	
			    }
				
			break;
			default:
				//$request->setControllerName( 'frontpage' );
		}
		
		if( $request->getModuleName() == 'admin' ) {
			if( $request->getControllerName() == 'channels' ) 
			$request->setControllerName( 'index' );
		} else {
			//$request->setControllerName( 'frontpage' );
		}
		*/
		
		//var_dump($request->getModuleName());
		//var_dump($request->getControllerName());
		//var_dump($request->getActionName());
		
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
	
	protected function _initViewHelpers(){
	    
		//Initialize and/or retrieve a ViewRenderer object on demand via the helper broker
		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
		$viewRenderer->initView();
		 
		//add the global helper directory path
		$viewRenderer->view->addHelperPath(ROOT_PATH.'/library/Xmltv/View/Helper/');
		
	}
	
	/**
	 * Autoload 3rd-party libraries
	 */
	protected function _initAutoloader(){
		
		//$autoloader = Zend_Loader_Autoloader::getInstance();
		//$autoloader->pushAutoloader(array('ezcBase', 'autoload'), 'ezc');
		
	}
	
	
	/**
	 * Setup user agent
	 */
	protected function _initUserAgent(){
		
	    $nocacheAgents=array(
    		'yandex',
    		'googlebot',
    		'ahrefs',
    		'mail.ru',
    		'rambler',
    		'baidu',
	    );
	    $nocache=false;
	    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '' ;
	    Zend_Registry::set('user_agent', false);
	    if (!empty($ua)){
		    foreach ($nocacheAgents as $a){
		        if (stristr($ua, $a)){
		            $nocache = true;
		    	}
		    }
	    }
	    
	    if ($nocache===false){
	        Zend_Registry::set( 'user_agent', $ua );
	    } else {
	        Zend_Registry::set( 'user_agent', 'PHP/5.3' );
	    }
	    
	}
	
	/**
	 * Initialize navigation
	 * @throws Zend_Exception
	 */
	protected function _initNav(){
	    
    	$menu = new Zend_Navigation( new Zend_Config_Xml( APPLICATION_PATH . '/configs/nav/fp.xml', 'nav' ) );
    	Zend_Registry::set('FpMenu', $menu);
    	
    	//$menu = new Zend_Navigation( new Zend_Config_Xml( APPLICATION_PATH . '/configs/nav/admin.xml', 'nav' ) );
    	//Zend_Registry::set('AdminMenu', $menu);
	    
	    
	}
	
	protected function _initHttpClient(){
		
	    $client = new Zend_Http_Client_Adapter_Curl();
	    Zend_Registry::set('http_client', $client);
	    
	}
	
}