<?php

/**
 * Frontend index controller
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: IndexController.php,v 1.6 2013-01-02 05:07:49 developer Exp $
 *
 */
class IndexController extends Zend_Controller_Action
{

	/**
	 * (non-PHPdoc)
	 * @see Zend_Controller_Action::__call()
	 */
	public function __call ($method, $arguments) {
	    
		if (APPLICATION_PATH=='production') {
			header( 'HTTP/1.0 404 Not Found' );
			$this->_helper->layout->setLayout( 'error' );
			$this->view->render();
		}
		
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Zend_Controller_Action::init()
	 */
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
	 * Offline message
	 */
	public function offlineAction(){
		die("Site is offline");
	}

}

