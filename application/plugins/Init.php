<?php

/**
 * Application initialization plugin
 *
 * @uses Zend_Controller_Plugin_Abstract
 * @version $Id: Init.php,v 1.13 2012-12-25 02:14:18 developer Exp $
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

		$this->_initACL();
		$this->_initConfig();
		$this->_initActionHelpers();
		$this->_initAutoloader();
	
	}

	protected function _initStats(){
		
		
		
	}

	public function routeShutdown (Zend_Controller_Request_Abstract $request) {

		$moduleName = $request->getModuleName();
		//var_dump($moduleName);
		//die(__FILE__.": ".__LINE__);
		/*
		switch ($moduleName) {
			case 'admin':
				if( $request->getControllerName() == 'channels' ) 
				$request->setControllerName( 'index' );
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


	


	protected function _initActionHelpers () {
		
		Zend_Controller_Action_HelperBroker::addPath( APPLICATION_PATH.'/controllers/helpers', 'Xmltv_Controller_Action_Helper' );
		
	}


	protected function _initACL () {

		$acl = new Zend_Acl();
		
		// Add groups to the Role registry using Zend_Acl_Role
		// Guest does not inherit access controls
		$acl->addRole( new Zend_Acl_Role( 'guest' ) );
		// registered inherits from guest
		$acl->addRole( new Zend_Acl_Role( 'registered' ), 'guest' );
		// backend does not inherit access controls
		

		$acl->addRole( new Zend_Acl_Role( 'backend' ) );
		// registered inherits from registered and backend
		$acl->addRole( new Zend_Acl_Role( 'staff' ), 
		array('backend', 'registered') );
		// root does not inherit access controls
		$acl->addRole( new Zend_Acl_Role( 'root' ) );
		
		$acl->addResource( new Zend_Acl_Resource( 'admin' ) );
		$acl->addResource( new Zend_Acl_Resource( 'listings' ) );
		
		// Guest may only view content
		$acl->allow( 'guest', array('listings'), 'view' );
		// registered inherits view privilege from guest, but also needs additional
		// privileges
		$acl->allow( 'registered', null, 'view' );
		
		$acl->allow( 'backend', 'admin', array('view', 'submit', 'revise') );
		// staff inherits view, edit, submit, and revise privileges from
		// backend, but also needs additional privileges
		$acl->allow( 'staff', null, 
		array('publish', 'archive', 'delete') );
		// root inherits nothing, but is allowed all privileges
		$acl->allow( 'root' );
		
		//$is_allowed = $acl->isAllowed('guest', null, 'view') ? "allowed" : "denied";
		//var_dump($is_allowed);
		//die(__FILE__.': '.__LINE__);
	}

	
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
	
	protected function _initAutoloader(){
		
		//$autoloader = Zend_Loader_Autoloader::getInstance();
		//$autoloader->pushAutoloader(array('ezcBase', 'autoload'), 'ezc');
		
	}
	

}