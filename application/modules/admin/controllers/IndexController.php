<?php



/**
 * Index controller for admin backend
 * 
 * @author toshihir
 * @package rutvgid
 *
 */
class Admin_IndexController extends Zend_Controller_Action
	{


	public function __call ($method, $arguments) {
		header( 'HTTP/1.0 404 Not Found' );
		$this->_helper->layout->setLayout( 'error' );
		$this->view->render();
	}


	public function init () {
		//die(__METHOD__);
		$this->_helper->layout->setLayout('admin');
	}


	public function noRouteAction () {
		header('HTTP/1.0 404 Not Found');
		$this->_helper->layout->setLayout('error');
		$this->view->render();
	}


	public function indexAction () {
		$this->_forward( 'tasks' );
	}


	public function loginAction () {
		$this->_helper->layout->setLayout( 'adminLogin' );
		$this->_forward( 'tasks' );
	}


	public function tasksAction () {
		$this->_helper->layout->setLayout( 'admin' );
	}
	

	}



