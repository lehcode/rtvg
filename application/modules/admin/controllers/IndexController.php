<?php
/**
 * Index controller for admin backend
 * 
 * @author     toshihir
 * @subpackage backend
 * @version    $Id: IndexController.php,v 1.7 2013-03-11 13:55:37 developer Exp $
 *
 */

class Admin_IndexController extends Rtvg_Controller_Action
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
	 * Redirect to login
	 */
	public function indexAction () {
		
	    if ( $this->isAllowed !== true) {
	    	return $this->_forward('login');
	    }
	    
	    return $this->_forward('tasks');
	}

	/**
	 * 
	 * Admin login
	 */
	public function loginAction () {

	    if ( $this->isAllowed !== true){
	        return $this->render('login');
	    }
	    
	    return $this->_forward('tasks');
	    
		//$this->_helper->layout->setLayout( 'adminLogin' );
		//$this->_forward( 'tasks' );
	}

	
	public function tasksAction () {

		
	}

}



