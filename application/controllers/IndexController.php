<?php

/**
 * Default frontend controller
 * 
 * @author  Antony Repin
 * @uses    Zend_Controller_Action
 * @version $Id: IndexController.php,v 1.8 2013-03-06 21:59:19 developer Exp $
 *
 */
class IndexController extends Rtvg_Controller_Action
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
	    
		$this->_forward( 'frontpage', 'index' );
		
	}
	
	/**
	 * 
	 * Offline message
	 */
	public function offlineAction(){
		die("Site is offline");
	}

}

