<?php



class Admin_MoviesController extends Zend_Controller_Action
	{
		
	private $_siteConfig;
	private $_debug;
	private $_sites;

	public function init () {
		$this->view->setScriptPath(APPLICATION_PATH . '/modules/admin/views/scripts/');
		$this->_helper->layout->setLayout( 'admin' );
		$this->_siteConfig = new Zend_Config_Ini( APPLICATION_PATH . '/configs/site.ini', 'development', 
		array('nestSeparator'=>':') );
		$this->_debug = (bool)$this->_siteConfig->get( 'site.debug', false ) === true ? (bool)$this->_siteConfig->get( 
		'site.debug' ) : false;
		$this->_sites = $this->_getActiveSites();
	}


	public function indexAction () {
		$this->_forward( 'list' );
	}


	public function grabAction () {
		
		$request = $this->_getAllParams();
		if (!isset($request['site'])) {
			$this->_forward('select-site');
			return;
		}
		
		var_dump( $request['site'] );
		die( __FILE__ . ': ' . __LINE__ );
	
	}
	
	public function selectSiteAction(){
		$this->view->assign('sites', $this->_sites);
	}


	private function _getActiveSites () {
		
		$xml_conf = new Zend_Config_Xml( APPLICATION_PATH . '/configs/sites.xml', 
		strtolower( $this->getRequest()->getControllerName() ) );
		$result = array();
		$nodash = new Zend_Filter_Word_SeparatorToSeparator( '.', '-' );
		foreach ($xml_conf as $v) {
			if( (bool)$xml_conf->current()->active === true ) {
				$new = array();
				$new['title'] = $xml_conf->current()->get( 'title' );
				$new['alias'] = $nodash->filter( $new['title'] );
				$new['baseUrl'] = $xml_conf->current()->get( 'baseUrl' );
				$new['startUri'] = $xml_conf->current()->get( 'startUri' );
				if( (bool)$this->_siteConfig->get( 'proxy.active', false ) === true ) {
					$new['proxyHost'] = $this->_siteConfig->get( 'proxy.host', '127.0.0.1' );
					$new['proxyPort'] = $this->_siteConfig->get( 'proxy.port', '8118' );
					$new['proxyType'] = $this->_siteConfig->get( 'proxy.type', 'http' );
				}
				ksort( $new );
				$result[] = $new;
			}
		}
		
		return $result;
	
	}
	

}

