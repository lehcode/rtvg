<?php

/**
 * Application initialization plugin
 *
 * @uses Zend_Controller_Plugin_Abstract
 */
class Xmltv_Plugin_Init extends Zend_Controller_Plugin_Abstract
{

	private $_env = 'production';

	protected $_request;

	public $router;


	/**
	 * Constructor
	 *
	 * @param  string $env Execution environment
	 * @return void
	 */
	public function __construct ($env = 'production') {

		$this->setEnv( $env );
	
	}


	public function setEnv ($env = 'production') {

		$this->_env = $env;
	
	}


	/**
	 * Route startup hook
	 *
	 * @param  Zend_Controller_Request_Abstract $request
	 * @return void
	 */
	public function routeStartup (Zend_Controller_Request_Abstract $request) {

		if( $this->_env == 'production' ) {
			$this->_initACL();
		}
		$this->_initConfig();
		$this->_initActionHelpers();
	
	}


	public function routeShutdown (Zend_Controller_Request_Abstract $request) {

		$controllerName = $this->getRequest()->getControllerName();
		
		switch ($controllerName) {
			default:
				$request->setControllerName( 'channels' );
				//$request->setActionNameName( 'list' );
			break;
			
			case 'channels':
				//$request->setActionNameName( 'list' );
			break;
			case 'listings':
			case 'program':
			break;
		}
	
	}


	/**
	 * @param $router 
	 */
	public function setRouter ($router) {

		$this->router = $router;
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


	protected function _initActionHelpers () {

		Zend_Controller_Action_HelperBroker::addPath( 
		APPLICATION_PATH . '/controllers/helpers' );
		Zend_Controller_Action_HelperBroker::addPath( 
		APPLICATION_PATH . '/modules/admin/controllers/helpers' );
	}


	protected function _initConfig () {
		//var_dump(APPLICATION_ENV);
		$c = new Zend_Config_Ini( APPLICATION_PATH . '/configs/site.ini', APPLICATION_ENV );
		Zend_Registry::set( "site_config", $c );
	}


	public function getRouter () {

		if(  !$this->router ) return false;
		try {
			$route = new Zend_Controller_Router_Route_Regex( 'телепрограмма\/?$', 
			array('module'=>'default', 'controller'=>'channels', 'action'=>'list') );
			$this->router->addRoute( 'channels', $route );
			
			$route = new Zend_Controller_Router_Route('телепрограмма/:alias', 
				array('module'=>'default', 'controller'=>'listings', 'action'=>'day'));
			$this->router->addRoute( 'day-today', $route );
			
			$route = new Zend_Controller_Router_Route('телепрограмма/:alias/:date', 
				array('module'=>'default', 'controller'=>'listings', 'action'=>'day'));
			$this->router->addRoute( 'day-date', $route );
			
			$route = new Zend_Controller_Router_Route('телепрограмма/:alias/неделя/:date', 
				array('module'=>'default', 'controller'=>'listings', 'action'=>'channel-week'));
			$this->router->addRoute( 'channel-week', $route );
			
			//var_dump($route->assemble(array('mode' => 'month', 'value' => '5')));
			/*
			$route = new Zend_Controller_Router_Route_Regex( 'телепрограмма\/.+\/?$', 
			array('module'=>'default', 'controller'=>'listings', 'action'=>'day') );
			$this->router->addRoute( 'телепрограмма/:alias', $route );
			*/
			/*
			$route = new Zend_Controller_Router_Route_Regex( 'телепрограмма\/.+\/?([0-9]{4}-[0-9]{2}-[0-9]{2}-[0-9]{2})?\/?$', 
			array('module'=>'default', 'controller'=>'channel', 'action'=>'day'), 'телепрограмма/:channel/:date' );
			$this->router->addRoute( 'телепрограмма/:channel/', $route );
			/
			/*
			$route = new Zend_Controller_Router_Route_Regex( 'телепрограмма\/.+\/.+\/неделя\/?$', 
			array('controller'=>'channel', 'action'=>'week'), 'телепрограмма/:channel/:date/неделя' );
			$router->addRoute( 'телепрограмма/:channel/:date/неделя/', $route);
			*/
			return $this->router;
		
		} catch (Exception $e) {
			if( $this->debug ) {
				echo $e->getMessage();
				var_dump( $e->getTrace() );
			} else {
				throw new Exception( $e->getMessage() );
			}
			exit( __FILE__ . ': ' . __LINE__ );
		}
	
	}

}