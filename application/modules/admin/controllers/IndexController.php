<?php
/**
 * Index controller for admin backend
 * 
 * @author     Antony Repin <egeshisolutions@gmail.com>
 * @package backend
 * @version    $Id: IndexController.php,v 1.11 2013-03-22 17:51:44 developer Exp $
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
	    	$this->_forward('login');
	    	return;
	    }
	    
	    $this->_forward('control-panel');
	}

	/**
	 * 
	 * Admin login
	 */
	public function loginAction () {

	    if ( $this->isAllowed !== true){
	        $this->render('login');
	        return;
	    }
	    
	}

	/**
	 * Backend control panel
	 */
	public function controlPanelAction () 
	{
		//	
	}

}



