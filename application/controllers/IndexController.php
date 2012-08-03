<?php

/**
 * Frontend index controller
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: IndexController.php,v 1.4 2012-08-03 00:16:56 developer Exp $
 *
 */
class IndexController extends Zend_Controller_Action
{


	public function __call ($method, $arguments) {

		header( 'HTTP/1.0 404 Not Found' );
		$this->_helper->layout->setLayout( 'error' );
		$this->view->render();
	}
	
	public function init () {

	}


	/**
	 * Redirect to channels listing
	 */
	public function indexAction () {

		$this->_forward( 'index', 'frontpage' );
	
	}
	
	/**
	 * Process wrong routing
	 */
	public function noRouteAction () {
		
		header( 'HTTP/1.0 404 Not Found' );
		$this->_helper->layout->setLayout( 'error' );
		$this->view->render();
	}
	
	
	public function frontpageAction(){
		//
		$this->view->assign('is_frontpage', true);
		//die(__FILE__.': '.__LINE__);
	}
	
	/*
	public function missingPageAction(){
		die(__FILE__.': '.__LINE__);
		//$this->view->render();
	}
	*/

}

