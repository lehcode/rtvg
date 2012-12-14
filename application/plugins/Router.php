<?php
/**
 * 
 * Routing plugin
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @package rutvgid
 * @filesource $Source: /home/developer/cvs/rutvgid.ru/application/plugins/Router.php,v $
 * @version $Id: Router.php,v 1.1 2012-12-14 03:55:18 developer Exp $
 */

class Xmltv_Plugin_Router extends Zend_Controller_Plugin_Abstract
{
	protected $_env = 'development';
	protected $_request;
	protected $_router;
	
	public function __construct ($env = 'development') {
		$this->setEnv( $env );
	}
	
	/**
	 * 
	 * Routing
	 * @throws Exception
	 */
	public function getRouter () {

		if(  !$this->_router )
			throw new Exception( "Не загружен роутер", 500);
			
		try {
			
			$route = new Zend_Controller_Router_Route( 
			'телепрограмма', 
			array('module'=>'default', 'controller'=>'channels', 'action'=>'list') );
			$this->_router->addRoute( 'default_channels_list', $route );
			
			$route = new Zend_Controller_Router_Route( 
			'телепрограмма/:channel', 
			array('module'=>'default', 'controller'=>'listings', 'action'=>'day-listing'), array('channel'=>null) );
			$this->_router->addRoute( 'default_listings-day-listing', $route );
			
			$route = new Zend_Controller_Router_Route( 
			'телепрограмма/:channel/:date', 
			array('module'=>'default', 'controller'=>'listings', 'action'=>'day-listing') );
			$this->_router->addRoute( 'channel-date', $route );
			
			/*
			$route = new Zend_Controller_Router_Route( 
			'телепрограмма/:channel/:alias/сегодня', 
			array('module'=>'default', 'controller'=>'listings', 'action'=>'program-day') );
			$this->_router->addRoute( 'channel-alias-today', $route );
			*/
			
			$route = new Zend_Controller_Router_Route( 
			'телепрограмма/:channel/:alias/неделя', 
			array('module'=>'default', 'controller'=>'listings', 'action'=>'program-week') );
			$this->_router->addRoute( 'default_listings_program-week', $route );
			
			$route = new Zend_Controller_Router_Route( 
			'телепрограмма/:channel/:alias', 
			array('module'=>'default', 'controller'=>'listings', 'action'=>'program-day'), array('channel'=>null, 'alias'=>null) );
			$this->_router->addRoute( 'channel-alias', $route );
			
			$route = new Zend_Controller_Router_Route( 
			'телепрограмма/:channel/неделя', 
			array('module'=>'default', 'controller'=>'channels', 'action'=>'channel-week') );
			$this->_router->addRoute( 'default_channels_channel-week', $route );
			
			$route = new Zend_Controller_Router_Route( 
			'телепрограмма/:channel/:alias/:date', 
			array('module'=>'default', 'controller'=>'listings', 'action'=>'program-day') );
			$this->_router->addRoute( 'channel-alias-date', $route );
			
			$route = new Zend_Controller_Router_Route( 
			'каналы/:category', 
			array('module'=>'default', 'controller'=>'channels',  'action'=>'category'), array('category'=>null) );
			$this->_router->addRoute( 'default_channels_channel-category', $route );
			
			$route = new Zend_Controller_Router_Route( 
			'видео/онлайн/:alias/:id', 
			array('module'=>'default', 'controller'=>'videos',  'action'=>'show-video'), array('alias'=>null, 'id'=>null) );
			$this->_router->addRoute( 'default_videos_show-video', $route );
			
			$route = new Zend_Controller_Router_Route(
			'горячие-новости',
			array('module'=>'default', 'controller'=>'error', 'action'=>'missing-page') );
			$this->_router->addRoute( 'missing-page', $route );
			
			$route = new Zend_Controller_Router_Route(
			'sitemap.xml',
			array('module'=>'default', 'controller'=>'sitemap', 'action'=>'sitemap') );
			$this->_router->addRoute( 'sitemap', $route );
			
			$route = new Zend_Controller_Router_Route( 
			'телепрограмма/поиск/канал', 
			array('module'=>'default', 'controller'=>'listings', 'action'=>'search') );
			$this->_router->addRoute( 'search-channel', $route );
			
			/*
			 * Compat from card-sharing.org
			 */
			/*
			$route = new Zend_Controller_Router_Route( 
			'видео/онлайн/?:id',
			array('module'=>'default', 'controller'=>'videos',  'action'=>'show-video-compat') );
			$this->_router->addRoute( 'show-video', $route );
			*/
			$route = new Zend_Controller_Router_Route( 
			'видео/тема/:tag', 
			array('module'=>'default', 'controller'=>'videos',  'action'=>'show-tag') );
			$this->_router->addRoute( 'show-tag', $route );
			
			$route = new Zend_Controller_Router_Route( 
			'сериалы', 
			array('module'=>'default', 'controller'=>'series',  'action'=>'series-week') );
			$this->_router->addRoute( 'default_series_series-week', $route );
			
			$route = new Zend_Controller_Router_Route( 
			'фильмы', 
			array('module'=>'default', 'controller'=>'movies',  'action'=>'movies-week') );
			$this->_router->addRoute( 'default_movies_movies-week', $route );

			$route = new Zend_Controller_Router_Route( 
			'премьеры-недели', 
			array('module'=>'default', 'controller'=>'programs',  'action'=>'premieres-week') );
			$this->_router->addRoute( 'default_programs_premieres-week', $route );
			
			$route = new Zend_Controller_Router_Route( 
			'актеры', 
			array('module'=>'default', 'controller'=>'persons',  'action'=>'actors') );
			$this->_router->addRoute( 'default_persons_actors', $route );
			
			$route = new Zend_Controller_Router_Route( 
			'режиссеры', 
			array('module'=>'default', 'controller'=>'persons',  'action'=>'directors') );
			$this->_router->addRoute( 'default_persons_directors', $route );
			
			$route = new Zend_Controller_Router_Route( 
			'комментарии/новый', 
			array('module'=>'default', 'controller'=>'comments',  'action'=>'create') );
			$this->_router->addRoute( 'default_comments_create', $route );
			
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
				//var_dump( $e->getTrace() );
			} else {
				throw new Exception( $e->getMessage() );
			}
			exit();
		}
	
	}
	
	public function routeShutdown (Zend_Controller_Request_Abstract $request) {

		$moduleName = $request->getModuleName();
		//var_dump($moduleName);
		//die(__FILE__.": ".__LINE__);
		
		//var_dump($request->getModuleName());
		//var_dump($request->getControllerName());
		//var_dump($request->getActionName());
		//var_dump($request->getParams());
		//$fc = Zend_Controller_Front::getInstance();
		//var_dump($fc->getParams())
		//die(__FILE__.': '.__LINE__);
		
		switch ($moduleName) {
			case 'admin':
				//if( $request->getControllerName() == 'channels' ) 
				//$request->setControllerName( 'index' );
			break;
			case 'default':
				/*
				if( $request->getControllerName() == 'articles' && $request->getActionName()=='article') {
					$params = $request->getParams();
					if ($params['alias']=='map-news') {
						$request->setActionName( 'map-news' );
						$request->setParam('format', 'json');
						$request->setParam('alias', null);
						$request->setParam('category', null);
						$request->setParam('action', 'map-news');
					}
				}
				*/
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
		
		//var_dump($request->getModuleName());
		//var_dump($request->getControllerName());
		//var_dump($request->getActionName());
		//die(__FILE__.': '.__LINE__);
		
	}
	
	
	
	/**
	 * 
	 * @param Zend_Controller_Router_Rewrite $router
	 */
	public function setRouter ($router) {
		$this->_router = $router;
	}
	
	public function setEnv( $env='development' ) {
		$this->_env = $env;
	}
}