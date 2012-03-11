<?php
/*
set_include_path(
        rtrim(get_include_path(),':') . ':' . APPLICATION_PATH.'/models'
);
*/
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	
	protected function _initAutoload(){
		
		$front   = $this->bootstrap("frontController")->frontController;
		$modules = $front->getControllerDirectory();
		$default = $front->getDefaultModule();
		
		foreach (array_keys($modules) as $module) {
		    if ($module === $default) {
		        continue;
		    }
		
		    $moduleloader = new Zend_Application_Module_Autoloader(array(
		        'namespace' => ucfirst($module),
		        'basePath'  => $front->getModuleDirectory($module)));
		}
		//var_dump($moduleloader);
		//var_dump($default);
		//var_dump($modules);
		//die();
				
	}
	
	protected function _initJquery() {
		$this->bootstrap('view');
		$view = $this->getResource('view');
		$view->addHelperPath("ZendX/JQuery/View/Helper", "ZendX_JQuery_View_Helper");
	}
	
	protected function _initConfig() {
		$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/site.ini', 'production'); 
		Zend_Registry::set("config", $config);
	}
	
	function run(){
		
		Zend_Registry::set('Zend_Locale', new Zend_Locale('ru_RU'));
		
		defined('DATE_MYSQL') || define(DATE_MYSQL, Zend_Date::YEAR.'-'.Zend_Date::MONTH.'-'.Zend_Date::DAY.' '.Zend_Date::HOUR.':'.Zend_Date::MINUTE.':'.Zend_Date::SECOND);
		defined('DATE_MYSQL_SHORT') || define(DATE_MYSQL_SHORT, Zend_Date::YEAR.'-'.Zend_Date::MONTH.'-'.Zend_Date::DAY);
		
		$router = new Zend_Controller_Router_Rewrite();
		$router->addRoute('телепрограмма', new Zend_Controller_Router_Route(
			'телепрограмма/:alias',
			array(
				'module' => 'default',
				'controller' => 'channel',
				'action' => 'day')
			),null,null,'ru'
		);
		/*
		$router->addRoute('телепрограмма/день', new Zend_Controller_Router_Route(
			'телепрограмма/:alias/:date',
			array(
				'module' => 'default',
				'controller' => 'channel',
				'action' => 'day')
			),null,null,'ru'
		);
		*/
		$router->addRoute('телепрограмма/неделя', new Zend_Controller_Router_Route(
			'телепрограмма/:alias/:date/неделя',
			array(
				'module' => 'default',
				'controller' => 'channel',
				'action' => 'week')
			),null,null,'ru'
		);
		$router->addRoute('admin/grab/movies', new Zend_Controller_Router_Route(
			'admin/grab/movies/:site',
			array(
				'module' => 'admin',
				'controller' => 'grab',
				'action' => 'movies')
			),null,null,'ru'
		);
		
		$fc = Zend_Controller_Front::getInstance()
			->throwExceptions(true)
			->setParam('useDefaultControllerAlways', false)
			->setRouter($router);
		//$fc->registerPlugin(new Xmltv_ModelLoader());
		
		try {
			$response = $fc->dispatch();
		} catch (Exception $e) {
			echo $e->getMessage();
			die();
		}
		
	}
	
}

