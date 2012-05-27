<?php

/**
 * Application initialization plugin
 *
 * @uses Zend_Controller_Plugin_Abstract
 * @version $Id: Init.php,v 1.8 2012-05-27 20:05:50 dev Exp $
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
		$this->_initAutoloader();
	
	}


	public function routeShutdown (Zend_Controller_Request_Abstract $request) {

		$moduleName = $request->getModuleName();
		//var_dump($moduleName);
		switch ($moduleName) {
			case 'admin':
				if( $request->getControllerName() == 'channels' ) 
				$request->setControllerName( 'index' );
			break;
			default:
		}
		
		if( $request->getModuleName() == 'admin' ) {
			if( $request->getControllerName() == 'channels' ) 
			$request->setControllerName( 'index' );
		}
		
		//var_dump($request->getModuleName());
		//var_dump($request->getControllerName());
		//var_dump($request->getActionName());
		
	}


	public function getRouter () {

		if(  !$this->_router )
		throw new Exception( "Не загружен роутер", 500);
		
		try {
			
			$route = new Zend_Controller_Router_Route_Regex( 'телепрограмма\/?$', 
			array('module'=>'default', 'controller'=>'channels', 'action'=>'list') );
			$this->_router->addRoute( 'channels', $route );
			
			$route = new Zend_Controller_Router_Route( 'телепрограмма/:channel', 
			array('module'=>'default', 'controller'=>'listings', 'action'=>'day-listing') );
			$this->_router->addRoute( 'channel-day-listing', $route );
			
			$route = new Zend_Controller_Router_Route( 'телепрограмма/:channel/:date', 
			array('module'=>'default', 'controller'=>'listings', 'action'=>'day-date') );
			$this->_router->addRoute( 'channel-date-listing', $route );
			/*
			$route = new Zend_Controller_Router_Route( 
			'телепрограмма/:channel/:program/сегодня', 
			array('module'=>'default', 'controller'=>'listings', 'action'=>'program-day') );
			$this->_router->addRoute( 'program-day', $route );
			*/
			$route = new Zend_Controller_Router_Route( 
			'телепрограмма/:channel/:program/:date', 
			array('module'=>'default', 'controller'=>'listings', 'action'=>'program-day') );
			$this->_router->addRoute( 'program-day-listing', $route );
			
			$route = new Zend_Controller_Router_Route( 
			'телепрограмма/:channel/неделя', 
			array('module'=>'default', 'controller'=>'channels', 'action'=>'channel-week') );
			$this->_router->addRoute( 'channel-week-listing', $route );
			
			$route = new Zend_Controller_Router_Route( 
			'телепрограмма/:channel/:program/неделя', 
			array('module'=>'default', 'controller'=>'listings', 'action'=>'program-week') );
			$this->_router->addRoute( 'program-week-listing', $route );
			
			$route = new Zend_Controller_Router_Route( 
			'каналы/:category', 
			array('module'=>'default', 'controller'=>'channels',  'action'=>'category') );
			$this->_router->addRoute( 'channels-category', $route );
			
			$route = new Zend_Controller_Router_Route( 
			'видео/онлайн/:alias/:id', 
			array('module'=>'default', 'controller'=>'videos',  'action'=>'show-video') );
			$this->_router->addRoute( 'show-video', $route );
			
			/*
			 * Compat from card-sharing.org
			 */
			$route = new Zend_Controller_Router_Route( 
			'видео/онлайн',
			array('module'=>'default', 'controller'=>'videos',  'action'=>'show-video-compat') );
			$this->_router->addRoute( 'show-video', $route );
			
			$route = new Zend_Controller_Router_Route( 
			'видео/тема/:tag', 
			array('module'=>'default', 'controller'=>'videos',  'action'=>'show-tag') );
			$this->_router->addRoute( 'show-tag', $route );
			
			$route = new Zend_Controller_Router_Route( 
			'сериалы', 
			array('module'=>'default', 'controller'=>'series',  'action'=>'series-week') );
			$this->_router->addRoute( 'series-week', $route );
			
			$route = new Zend_Controller_Router_Route( 
			'фильмы', 
			array('module'=>'default', 'controller'=>'movies',  'action'=>'movies-week') );
			$this->_router->addRoute( 'movies-week', $route );
			
			/*
			 * admin routes
			 */
			$route = new Zend_Controller_Router_Route( 'admin/movies/grab/:site',  array('module'=>'admin', 'controller'=>'movies', 'action'=>'grab'));
			$this->_router->addRoute( 'admin/movies/grab', $route );
			$this->_router->addRoute( 'admin/login', 
			new Zend_Controller_Router_Route_Static( 'admin', 
			array('module'=>'admin', 'controller'=>'index', 'action'=>'login') ) );
			$this->_router->addRoute( 'admin', 
			new Zend_Controller_Router_Route_Static( 'admin/index', array('module'=>'admin', 'controller'=>'index') ) );
			$this->_router->addRoute( 'admin/tasks', 
			new Zend_Controller_Router_Route_Static( 'admin/index', array('module'=>'admin', 'controller'=>'index') ) );
			
			return $this->_router;
		
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


	protected function _initActionHelpers () {

		Zend_Controller_Action_HelperBroker::addPath( APPLICATION_PATH . '/controllers/helpers', 'Xmltv_Controller_Helper' );
		
		Zend_Controller_Action_HelperBroker::addPath( APPLICATION_PATH . '/modules/admin/controllers/helpers', 'Admin_Controller_Helper' );
		
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

		//var_dump(APPLICATION_ENV);
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
		
		$autoloader = Zend_Loader_Autoloader::getInstance();
		$autoloader->pushAutoloader(array('ezcBase', 'autoload'), 'ezc');
		
	}
	

}