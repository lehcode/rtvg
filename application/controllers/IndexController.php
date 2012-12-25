<?php

/**
 * Frontend index controller
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: IndexController.php,v 1.5 2012-12-25 01:57:52 developer Exp $
 *
 */
class IndexController extends Zend_Controller_Action
{


	public function __call ($method, $arguments) {

		//header( 'HTTP/1.0 404 Not Found' );
		//$this->_helper->layout->setLayout( 'error' );
		//$this->view->render();
		
	}
	
	public function init () {
		$this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
		$ajaxContext = $this->_helper->getHelper( 'AjaxContext' );
		$ajaxContext->addActionContext( 'live', 'json' )
			->initContext();
		$this->initView();
	}


	/**
	 * Redirect to channels listing
	 */
	public function indexAction () {

		$this->_forward( 'index', 'front-page' );
	
	}
	
	/**
	 * 
	 * Front page
	 */
	public function frontpageAction(){
		
		$this->view->assign('is_frontpage', true);
		//die(__FILE__.': '.__LINE__);
	}
	
	/**
	 * 
	 * Offline message
	 */
	public function offlineAction(){
		die("Site is offline");
	}
	
	public function liveAction(){
		die("liveAction");
	}

}

