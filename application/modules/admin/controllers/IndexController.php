<?php
/**
 * Index controller for admin backend
 * 
 * @author  toshihir
 * @package rutvgid
 * @subpackage backend
 * @version $Id: IndexController.php,v 1.5 2012-12-16 15:18:26 developer Exp $
 *
 */

class Admin_IndexController extends Zend_Controller_Action
{


	public function __call ($method, $arguments) {

		header( 'HTTP/1.0 404 Not Found' );
		$this->_helper->layout->setLayout( 'error' );
		$this->view->render();
	}

	/**
	 * (non-PHPdoc)
	 * @see Zend_Controller_Action::init()
	 */
	public function init () {

		$this->_helper->layout->setLayout( 'admin' );
	}

	/**
	 * 
	 * Controller index page
	 */
	public function indexAction () {
		
		$this->_forward( 'tasks' );
	}

	/**
	 * 
	 * Admin login
	 */
	public function loginAction () {

		$this->_helper->layout->setLayout( 'adminLogin' );
		$this->_forward( 'tasks' );
	}

	/**
	 * 
	 * Enter description here ...
	 */
	public function tasksAction () {

		
	}

}



