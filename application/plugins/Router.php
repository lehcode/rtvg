<?php
/**
 * 
 * Routing plugin
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @package rutvgid
 * @filesource $Source: /home/developer/cvs/rutvgid.ru/application/plugins/Router.php,v $
 * @version $Id: Router.php,v 1.7 2013-01-02 16:58:27 developer Exp $
 */

class Xmltv_Plugin_Router extends Zend_Controller_Plugin_Abstract
{
	protected $_env = 'development';
	protected $_request;
	protected $_router;
	
	public function __construct ($env='production') {
		$this->_env = $env;
	}
	
	/**
	 * 
	 * Routing
	 * @throws Exception
	 */
	public function getRouter () {

		if(  !$this->_router )
			throw new Exception( "Не загружен роутер", 500);
			
		$this->_router->addRoute( 'default_frontpage_index', 
		new Zend_Controller_Router_Route( '/', 
		array(
			'module'=>'default',
			'controller'=>'frontpage',
			'action'=>'index')));
		
		$this->_router->addRoute( 'default_channels_list', 
		new Zend_Controller_Router_Route( 'телепрограмма', 
		array(
			'module'=>'default',
			'controller'=>'channels',
			'action'=>'list')) );
			
		$this->_router->addRoute( 'default_channels_channel-week', 
		new Zend_Controller_Router_Route( 'телепрограмма/:channel/неделя',
		array(
			'module'=>'default',
			'controller'=>'channels',
			'action'=>'channel-week'), array(
				'channel'=>'[\p{Cyrillic}\p{Latin}\d-]+')));
			
			
		$this->_router->addRoute( 'default_rumors_recent', 
		new Zend_Controller_Router_Route( 'хроника',
		array(
			'module'=>'default',
			'controller'=>'rumors',
			'action'=>'recent')));
			
		$this->_router->addRoute( 'default_series_week', 
		new Zend_Controller_Router_Route( 'сериалы',
		array(
			'module'=>'default',
			'controller'=>'series',
			'action'=>'week')));
			
		$this->_router->addRoute( 'default_movies_week', 
		new Zend_Controller_Router_Route( 'фильмы',
		array(
			'module'=>'default',
			'controller'=>'movies',
				'action'=>'week')) );
		
		$this->_router->addRoute( 'default_persons_actors', 
		new Zend_Controller_Router_Route( 'актеры',
		array(
			'module'=>'default',
			'controller'=>'persons',
			'action'=>'actors')));
			
		$this->_router->addRoute( 'default_channels_category', 
		new Zend_Controller_Router_Route( 'каналы/:category',
		array(
			'module'=>'default',
			'controller'=>'channels',
			'action'=>'category')));
			
			
		$this->_router->addRoute( 'default_listings_program-week', 
		new Zend_Controller_Router_Route( 'телепрограмма/:channel/:alias/неделя',
		array(
				'module'=>'default',
				'controller'=>'listings',
				'action'=>'program-week'), array(
						'channel'=>'[\p{Cyrillic}\p{Latin}\d-]+',
						'alias'=>'[\p{Cyrillic}\p{Latin}\d-]+',
				)) );

		$this->_router->addRoute( 'default_listings_program-day',
		new Zend_Controller_Router_Route( 'телепрограмма/:channel/:alias/сегодня',
		array(
			'module'=>'default',
			'controller'=>'listings',
			'action'=>'program-day'), array(
				'channel'=>'[\p{Cyrillic}\p{Latin}\d-]+',
				'alias'=>'[\p{Cyrillic}\p{Latin}\d-]+')));
			
		
		$this->_router->addRoute( 'default_listings_program-date',
		new Zend_Controller_Router_Route( 'телепрограмма/:channel/:alias/:date',
			array(
				'module'=>'default',
				'controller'=>'listings',
				'action'=>'program-day'), array(
					'channel'=>'[\p{Cyrillic}\p{Latin}\d-]+',
					'alias'=>'[\p{Cyrillic}\p{Latin}\d-]+',
					'date'=>'([\d]{4}-[\d]{2}-[\d]{2}|[\d]{2}-[\d]{2}-[\d]{4}|сегодня)')));
			
		$this->_router->addRoute( 'default_listings_day-listing', 
		new Zend_Controller_Router_Route( 'телепрограмма/:channel', 
		array(
			'module'=>'default',
			'controller'=>'listings',
			'action'=>'day-listing'), array(
				'channel'=>'[\p{Cyrillic}\p{Latin}\d-]+')));
		
		
		$this->_router->addRoute( 'default_listings_day-date', 
		new Zend_Controller_Router_Route( 'телепрограмма/:channel/:date', 
		array(
			'module'=>'default',
			'controller'=>'listings',
			'action'=>'day-date'),
		array(
			'channel'=>'[\p{Cyrillic}\p{Latin}\d-]+',
			'date'=>'([\d]{4}-[\d]{2}-[\d]{2}|[\d]{2}-[\d]{2}-[\d]{4}|сегодня)')) );
			
		
		$this->_router->addRoute( 'default_videos_show-video',
		new Zend_Controller_Router_Route( 'видео/онлайн/:alias/:id', 
		array(
			'module'=>'default',
			'controller'=>'videos',
			'action'=>'show-video',
			'alias'=>'[\p{Cyrillic}\p{Latin}\d-]+',
			'id'=>'.+')));
			
		$this->_router->addRoute( 'default_error_missing-page', 
		new Zend_Controller_Router_Route( 'горячие-новости',
		array(
			'module'=>'default',
			'controller'=>'error',
			'action'=>'missing-page')));
			
		$this->_router->addRoute( 'sitemap', 
		new Zend_Controller_Router_Route( 'sitemap.xml',
		array(
			'module'=>'default',
			'controller'=>'sitemap',
			'action'=>'sitemap')));
			
		$this->_router->addRoute( 'search-channel', 
		new Zend_Controller_Router_Route( 'телепрограмма/поиск/канал', 
		array(
			'module'=>'default',
			'controller'=>'listings',
			'action'=>'search')));

		$this->_router->addRoute( 'default_programs_premieres-week', 
		new Zend_Controller_Router_Route( 'премьеры-недели', 
		array(
			'module'=>'default',
			'controller'=>'programs',
			'action'=>'premieres-week')));

		$this->_router->addRoute( 'default_persons_directors', 
		new Zend_Controller_Router_Route( 'режиссеры', 
		array(
			'module'=>'default',
			'controller'=>'persons',
			'action'=>'directors')));
			
		$this->_router->addRoute( 'default_comments_create', 
		new Zend_Controller_Router_Route( 'комментарии/новый', 
		array(
			'module'=>'default',
			'controller'=>'comments',
			'action'=>'create')));
			
		$this->_router->addRoute( 'default_torrents_finder', 
		new Zend_Controller_Router_Route( 'скачать/', 
		array(
			'module'=>'default',
			'controller'=>'torrents',
			'action'=>'finder')));
		
		$this->_router->addRoute( 'deault_programs_category', 
		new Zend_Controller_Router_Route( 'передачи/:alias/:timespan', 
		array(
			'module'=>'default',
			'controller'=>'programs',
			'action'=>'category')));
		
		/*
		 * admin routes
		 */
		$route = new Zend_Controller_Router_Route(
			'admin/import/listings/:site',
			array('module'=>'admin', 'controller'=>'import',  'action'=>'remote'), array('site'=>'teleguide') );
		$this->_router->addRoute( 'admin_import_remote', $route );
			
			$route = new Zend_Controller_Router_Route(
			'admin/movies/grab/:site',
			 array('module'=>'admin', 'controller'=>'movies', 'action'=>'grab'));
			$this->_router->addRoute( 'admin/movies/grab', $route );
			
			$this->_router->addRoute( 'admin/login', 
			new Zend_Controller_Router_Route_Static( 'admin', 
			array('module'=>'admin', 'controller'=>'index', 'action'=>'login') ) );
			
			$this->_router->addRoute( 'admin', 
			new Zend_Controller_Router_Route_Static( 'admin/index', array('module'=>'admin', 'controller'=>'index') ) );
			
			$this->_router->addRoute( 'admin/tasks', 
			new Zend_Controller_Router_Route_Static( 'admin/index', array('module'=>'admin', 'controller'=>'index') ) );
			
			$this->_router->addRoute( 'admin_import_parse-programs', 
			new Zend_Controller_Router_Route_Static( 'admin/import/xml-parse-programs', 
			array('module'=>'admin', 'controller'=>'import', 'action'=>'xml-parse-programs') ) );
			
			//var_dump($this->_router);
			
			return $this->_router;
		
		
	
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
			    
			    if ($request->getParam('XDEBUG_PROFILE')){
			        var_dump($request->getParams());
			        die(__FILE__.': '.__LINE__);
			    }
			    
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
	
}