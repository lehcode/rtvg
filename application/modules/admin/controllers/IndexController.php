<?php
/**
 * Index controller for admin backend
 * 
 * @author     Antony Repin <egeshisolutions@gmail.com>
 * @subpackage backend
 * @version    $Id: IndexController.php,v 1.9 2013-03-16 20:03:36 developer Exp $
 *
 */

class Admin_IndexController extends Rtvg_Controller_Admin
{

	/**
	 * (non-PHPdoc)
	 * @see Zend_Controller_Action::init()
	 */
	public function init () {
		parent::init();
	}

	/**
	 * 
	 * Redirect to login
	 */
	public function indexAction () {
		
	    if ( $this->isAllowed !== true) {
	    	return $this->_forward('login');
	    }
	    
	    return $this->_forward('control-panel');
	}

	/**
	 * 
	 * Admin login
	 */
	public function loginAction () {

	    if ( $this->isAllowed !== true){
	        return $this->render('login');
	    }
	    
	    return $this->_forward('control-panel');
	    
		//$this->_helper->layout->setLayout( 'adminLogin' );
		//$this->_forward( 'tasks' );
	}

	
	public function controlPanelAction () {

		
	}

}



