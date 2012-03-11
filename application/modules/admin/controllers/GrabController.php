<?php

class Admin_GrabController extends Zend_Controller_Action
{

    public function init()
    {
    	//var_dump($this->_helper);
    	//die();
        $this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
    }

    public function moviesAction()
    {
    	$request = $this->_getAllParams();
    	$siteKey = $this->_getParam('site', null);
    	$debug   = $this->_getParam('debug', 0);
    	
    	
    	
    	//$use_proxy = $this->_getParam('proxy', 0);
    	var_dump($request);
    	var_dump($siteKey);
    	
    	//var_dump(get_include_path());
    	//die();
    	
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
    
    public function seriesAction()
    {
		die(__METHOD__);
    }
    
    public function xmlAction()
    {
		die(__METHOD__);
    }


	private function _getClass($key=null) {
		if(!$key)
		return;
		$siteClass = "Xmltv_Site_" . str_replace(' ', '', ucwords(str_replace('-', ' ', $key)));
		$siteObj = new $siteClass;	
		if($siteObj)
		return $siteObj;
    }
}

