<?php
/**
 * Manage listings grabbing
 * 
 * @author     Antony Repin <egeshisolutions@gmail.com>
 * @subpackage backend
 * @version    $Id: GrabController.php,v 1.5 2013-03-16 12:46:19 developer Exp $
 *
 */
class Admin_GrabController extends Rtvg_Controller_Admin
{
	
	public function init()
    {
        parent::init();
    }


	public function indexAction () {
		// display sites choice
	}

    
    public function grabListingsAction(){
    	
    	$filters = array( '*'=>'StringTrim', '*'=>'StringToLower' );
    	$validators = array(
    		'module'=>array( new Zend_Validate_Regex('/^[a-z]+$/u') ),
    		'controller'=>array( new Zend_Validate_Regex('/^[a-z]+$/') ),
    		'action'=>array( new Zend_Validate_Regex('/^[a-z-]+$/') ),
    		'target'=>array( new Zend_Validate_Regex('/^[a-z]+$/') ),
    		'format'=>array( new Zend_Validate_Regex('/^html|json$/') ),
    	);
    	$input = new Zend_Filter_Input($filters, $validators, $this->_request->getParams());
    	if (!$input->isValid()) throw new Exception("Неверные данные", 500);
    	
    	if ($this->_debug) $lifetime = 60;
    	else $lifetime = Xmltv_Config::getCacheLifetime();
    	
    	$model   = new Admin_Model_Grab(array('cache_lifetime'=>$lifetime));
    	$request = $this->_getAllParams();
    	$model->setDebug(true);
    	$model->setSite($request['target']);
    	
    	if (Xmltv_Config::getProxyEnabled()===true) {
    		$model->enableProxy(array('host'=>Xmltv_Config::getProxyHost(), 'port'=>Xmltv_Config::getProxyPort()));
    	}
    	$model->enableCookies(ROOT_PATH.'/cookies/'.$request['target'].'.txt', ROOT_PATH.'/cookies/'.$request['target'].'-jar.txt');
    	
    	if ($request['target']=='vsetvcom')
    	$model->setEncoding('windows-1251');
    	
    	if (Xmltv_Config::getCaching()===true)
    	$model->setCaching(true, Xmltv_Config::getCacheLifetime());
    	
    	$model->setConnectTimeout(30);
    	$model->setChannelsInfo($request['target']);
    	
    	var_dump($model);
    	die(__FILE__.': '.__LINE__);
    }
    
    public function grabMoviesAction()
    {
    	$request = $this->_getAllParams();
    	$siteKey = $this->_getParam('site', null);
    	$debug   = $this->_getParam('debug', 0);
    	
    	
    	
    	//$use_proxy = $this->_getParam('proxy', 0);
    	var_dump($request);
    	var_dump($siteKey);
    	
    	//var_dump(get_include_path());
    	die(__FILE__.': '.__LINE__);
    	
    	$site_table = new Admin_Model_DbTable_Site();
    	$site_model = new Admin_Model_Site();
    	$site = $this->_getClass($siteKey);
    	$site_config = new Zend_Config_Xml(APPLICATION_PATH.'/configs/sites.xml', 'movies');
		$site_config = $site_config->$siteKey;
    	$site->setProxy(array(
			'host'=>$site_config->proxy->host,
			'port'=>$site_config->proxy->port,
			'type'=>$site_config->proxy->type
		));
    	$site->setBaseUrl($site_config->baseUrl);
    	
    	//var_dump($site);
    	//die(__METHOD__);
    	
    	try {
    		$site->fetchPage($site_config->startUri, $site->getEncoding());
    		$site->getAlphaLinks();
			$site->getPaginationLinks();
			$site->getMoviesLinks();
    	} catch (Zend_Exception $e) {
    		echo $e->getMessage();
    		die(__METHOD__.': '.__LINE__);
    	}
    	
    	$i=0;
    	$links = $site->moviesLinks;
    	do {
    		if ($info = $site->getMovieInfo($links[$i]))
    		unset($site->moviesLinks[$i]);
    		else 
    		throw new Exception("Cannot get movie info for URL ".$site->getBaseUrl().$links[$i]);
    		//$site->getMovieInfo();
    		$i++;
    	} while(!empty($site->moviesLinks));
    	//var_dump($site);
		
    	
    	
		die(__METHOD__.': '.__LINE__);
    }
    
    public function grabSeriesAction() {
		die(__METHOD__);
    }
    
    
    

    
}

