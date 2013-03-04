<?php
/**
 * Index controller for admin backend
 * 
 * @author     toshihir
 * @subpackage backend
 * @version    $Id: IndexController.php,v 1.6 2013-03-04 17:57:39 developer Exp $
 *
 */

class Admin_IndexController extends Xmltv_Controller_Action
{


	/**
	 * (non-PHPdoc)
	 * @see Zend_Controller_Action::init()
	 */
	public function init () {
		parent::init();
		$this->_helper->layout->setLayout( 'admin' );
	}

	/**
	 * 
	 * Controller index page
	 */
	public function indexAction () {
		
	    if (APPLICATION_ENV=='development'){
	        var_dump(parent::$user);
	        die(__FILE__.': '.__LINE__);
	    }
	}

	/**
	 * 
	 * Admin login
	 */
	public function loginAction () {

		$this->_helper->layout->setLayout( 'adminLogin' );
		$this->_forward( 'tasks' );
	}

	
	public function tasksAction () {

		
	}

}



