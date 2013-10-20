<?php
/**
 * 
 * Routing plugin
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @version $Id: Router.php,v 1.20 2013-04-12 06:56:22 developer Exp $
 */

class Xmltv_Plugin_Router extends Zend_Controller_Plugin_Abstract
{
	protected $_env = 'production';
	protected $_request;
	protected $_router;
	
	const ALIAS_REGEX = '[^&\/][\p{Common}\p{Cyrillic}\p{Latin}\d_-]+';
	const VIDEO_ALIAS_REGEX = '[^&\/][\p{Common}\p{Cyrillic}\p{Latin}\d_-]+';
	const CHANNEL_ALIAS_REGEX = '[\p{Cyrillic}\p{Latin}\d-]+';
	const DATE_REGEX = '\d{4}-\d{2}-\d{2}|\d{2}-\d{2}-\d{4}';
	const ISO_DATE_REGEX = '\d{2}-\d{2}-\d{4}';
	
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
	 * 
	 * Routing
	 * @throws Exception
	 */
	public function getRouter () {

		if(!$this->_router) {
			throw new Exception( "Не загружен роутер", 500);
        }
        
        $this->_router->addRoute( 'default', new Zend_Controller_Router_Route( '',  array(
			'module'=>'default',
			'controller'=>'frontpage',
			'action'=>'index'))
        );
        
        $this->_router->addRoute( 'default_channels_list', new Zend_Controller_Router_Route( 'телепрограмма', array(
			'module'=>'default',
			'controller'=>'channels',
			'action'=>'list'))
        );
        
        $this->_router->addRoute( 'default_channels_index', new Zend_Controller_Router_Route( 'каналы/', array(
			'module'=>'default',
			'controller'=>'channels',
			'action'=>'index'))
        );
			
		$this->_router->addRoute( 'default_channels_channel-week', new Zend_Controller_Router_Route( 'телепрограмма/:channel/неделя', array(
			'module'=>'default',
			'controller'=>'channels',
			'action'=>'channel-week'),
		array(
			'channel' => self::CHANNEL_ALIAS_REGEX)));
			
			
		//$this->_router->addRoute( 'default_rumors_recent', new Zend_Controller_Router_Route( 'хроника', array(
		//	'module'=>'default',
		//	'controller'=>'rumors',
		//	'action'=>'recent')));
			
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
						'channel'=>self::CHANNEL_ALIAS_REGEX,
						'alias'=>self::ALIAS_REGEX,
				)) );
		
		
		$this->_router->addRoute( 'default_listings_program-day',
		new Zend_Controller_Router_Route( 'телепрограмма/:channel/:alias/сегодня',
		array(
			'module'=>'default',
			'controller'=>'listings',
			'action'=>'program-day',
			'channel'=>self::CHANNEL_ALIAS_REGEX,
			'alias'=>self::ALIAS_REGEX
		)));
		
		
		$this->_router->addRoute( 'default_listings_program-date',
		new Zend_Controller_Router_Route( 'телепрограмма/:channel/:alias/:date',
			array(
				'module'=>'default',
				'controller'=>'listings',
				'action'=>'program-day',
				'channel'=>self::CHANNEL_ALIAS_REGEX,
			), array(
				'alias'=>self::ALIAS_REGEX,
				'date'=>'('.self::DATE_REGEX.'|сегодня)'
			))
		);
			
		$this->_router->addRoute( 'default_listings_day-listing', 
			new Zend_Controller_Router_Route( 'телепрограмма/:channel', array(
				'module'=>'default',
				'controller'=>'listings',
				'action'=>'day-listing'
			), 
			array(
				'channel'=>self::CHANNEL_ALIAS_REGEX)));
		
		
		$this->_router->addRoute( 'default_listings_day-date', 
			new Zend_Controller_Router_Route( 'телепрограмма/:channel/:date', array(
				'module'=>'default',
				'controller'=>'listings',
				'action'=>'day-date'
			),
			array(
				'channel'=>self::CHANNEL_ALIAS_REGEX,
				'date'=>'('.self::DATE_REGEX.'|сегодня)')));
			
		$this->_router->addRoute( 'default_listings_premieres-week', 
		new Zend_Controller_Router_Route( 'телепрограмма/премьеры/:timespan', array(
				'module'=>'default',
				'controller'=>'listings',
				'action'=>'premieres-week',
				'timespan'=>'сегодня|неделя|'.self::ISO_DATE_REGEX,
			)));
		
		$this->_router->addRoute( 'default_videos_show-video',
			new Zend_Controller_Router_Route( 'видео/онлайн/:alias/:id', array(
				'module'=>'default',
				'controller'=>'videos',
				'action'=>'show-video',
				'alias'=>self::VIDEO_ALIAS_REGEX,
				'id'=>'[\w\d]{12}')));
		
		$this->_router->addRoute( 'default_sitemap_sitemap', 
			new Zend_Controller_Router_Route( 'sitemap.xml', array(
				'module'=>'default',
				'controller'=>'sitemap',
				'action'=>'sitemap')));
			
		$this->_router->addRoute( 'default_search_search', 
			new Zend_Controller_Router_Route( 'телепрограмма/поиск', array(
				'module'=>'default',
				'controller'=>'search',
				'action'=>'search')));

		$this->_router->addRoute( 'default_programs_premieres-week', 
			new Zend_Controller_Router_Route( 'премьеры-недели', array(
				'module'=>'default',
				'controller'=>'programs',
				'action'=>'premieres-week')));

		$this->_router->addRoute( 'default_persons_directors', 
			new Zend_Controller_Router_Route( 'режиссеры', array(
				'module'=>'default',
				'controller'=>'persons',
				'action'=>'directors')));
			
		$this->_router->addRoute( 'default_comments_create', 
			new Zend_Controller_Router_Route( 'комментарии/новый', array(
				'module'=>'default',
				'controller'=>'comments',
				'action'=>'create')));
		
		$this->_router->addRoute( 'default_listings_category', 
			new Zend_Controller_Router_Route( ':timespan/:category/', array(
				'module'=>'default',
				'controller'=>'listings',
				'action'=>'category',
			), array(
				'category'=>self::ALIAS_REGEX,
				'timespan'=>'(сегодня|неделя)'
			)));
		
		$this->_router->addRoute( 'default_user_login', 
			new Zend_Controller_Router_Route( 'login', array(
				'module'=>'default',
				'controller'=>'user',
				'action'=>'login',
			)));
		
		$this->_router->addRoute( 'default_user_profile', 
			new Zend_Controller_Router_Route( 'моя-страница', array(
				'module'=>'default',
				'controller'=>'user',
				'action'=>'profile',
			)));
		
		$this->_router->addRoute( 'default_user_logout', 
			new Zend_Controller_Router_Route( 'logout', array(
				'module'=>'default',
				'controller'=>'user',
				'action'=>'logout',
			)));
		
		$this->_router->addRoute( 'default_content_blog', 
			new Zend_Controller_Router_Route( 'новости', array(
				'module'=>'default',
				'controller'=>'content',
				'action'=>'blog',
			)));
		
		
		$this->_router->addRoute( 'default_content_blog-category', 
			new Zend_Controller_Router_Route( 'новости/:content_cat', array(
				'module'=>'default',
				'controller'=>'content',
				'action'=>'blog-category',
			),
			array(
				'category'=>self::ALIAS_REGEX
			)));
		
		$this->_router->addRoute( 'default_content_article', 
			new Zend_Controller_Router_Route( 'новости/:category_id/:article_alias', array(
				'module'=>'default',
				'controller'=>'content',
				'action'=>'article',
			), array(
				'category_id'=>'[0-9]+',
				'article_alias'=>self::ALIAS_REGEX
			)));
		
		
		$this->_router->addRoute( 'default_content_tag', 
			new Zend_Controller_Router_Route( 'тема/:tag', array(
				'module'=>'default',
				'controller'=>'content',
				'action'=>'article-tag',
			),
			array(
				'tag'=>self::ALIAS_REGEX
			)));
		
		$this->_router->addRoute( 'default_script_vk-message',
		new Zend_Controller_Router_Route( 'msg.js',
			array(
				'module'=>'default',
				'controller'=>'smth',
				'action'=>'vk-message'
			)));
		
		$this->_router->addRoute( 'default_script_richmedia',
		new Zend_Controller_Router_Route( 'rich.js',
			array(
				'module'=>'default',
				'controller'=>'smth',
				'action'=>'rich'
			)));
		
		
		$this->_router->addRoute( 'default_script_slider',
		new Zend_Controller_Router_Route( 'sb.js',
			array(
				'module'=>'default',
				'controller'=>'smth',
				'action'=>'rollin'
			)));
		
		$this->_router->addRoute( 'default_feed_atom',
		new Zend_Controller_Router_Route( 'feed/atom/:channel/:timespan',
			array(
				'module'=>'default',
				'controller'=>'feed',
				'action'=>'atom',
				'channel'=>null,
				//'timespan'=>null,
			),array(
				'channel'=>'\d+',
			)));
		
		$this->_router->addRoute( 'default_feed_rss',
		new Zend_Controller_Router_Route( 'feed/rss/:channel/:timespan',
			array(
				'module'=>'default',
				'controller'=>'feed',
				'action'=>'rss',
				'channel'=>null,
				//'timespan'=>null,
			)));
		
		// @todo 
		// Полный список программ в определенный день
		// В отличие от используемого по умолчанию default_listings_day-date
		/*
		$this->_router->addRoute( 'default_listings_day_complete', 
		new Zend_Controller_Router_Route( 'передачи/:channel/:date', 
		array(
			'module'=>'default',
			'controller'=>'listings',
			'action'=>'day-complete')));
		*/
		
		
		//admin routes
        $this->_router->addRoute( 'admin_index_index', 
		new Zend_Controller_Router_Route( 'admin',
		array(
			'module'=>'admin',
			'controller'=>'index',
			'action'=>'index')));
		
		$this->_router->addRoute( 'admin_import_remote', 
		new Zend_Controller_Router_Route( 'admin/import/listings/:site',
		array(
			'module'=>'admin',
			'controller'=>'import',
			'action'=>'remote'),
		array('site'=>'teleguide')));

		$this->_router->addRoute( 'admin_import_parse-programs',
		new Zend_Controller_Router_Route_Static( 'admin/import/xml-parse-programs',
		array(
			'module'=>'admin',
			'controller'=>'import',
			'action'=>'xml-parse-programs')));
		
		$this->_router->addRoute( 'admin_programs_delete-programs',
		new Zend_Controller_Router_Route( 'admin/programs/delete-programs/format/html',
		array(
			'module'=>'admin',
			'controller'=>'programs',
			'action'=>'delete-programs'),
		array(
			'delete_start'=>'/\d{2}-\d{2}-\d{4}/',
		)));
		
		
		$this->_router->addRoute( 'admin_grab_do',
            new Zend_Controller_Router_Route( 'admin/grab-site',
            array(
                'module'=>'admin',
                'controller'=>'grab',
                'action'=>'do'),
            array(
                'site'=>'/\p{Ll}\d{1,16}/u',
                'weekstart'=>'/\d{2}\.\d{2}\.\d{2}/',
            ))
            );
        
        $this->_router->addRoute('admin_auth_login',
            new Zend_Controller_Router_Route('admin/auth/login'),
            array(
                'module'=>'admin',
                'controller'=>'auth',
                'action'=>'login',
            ));
        
		return $this->_router;
	
	}
	
	
	
	/**
	 * 
	 * @param Zend_Controller_Router_Rewrite $router
	 */
	public function setRouter ($router) {
		$this->_router = $router;
    }
	
}