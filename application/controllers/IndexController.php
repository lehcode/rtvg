<?php

/**
 * @author toshihir
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

		$this->_forward( 'index', 'channels' );
	
	}
	
	/**
	 * Process wrong routing
	 */
	public function noRouteAction () {
		
		header( 'HTTP/1.0 404 Not Found' );
		$this->_helper->layout->setLayout( 'error' );
		$this->view->render();
	}


}

