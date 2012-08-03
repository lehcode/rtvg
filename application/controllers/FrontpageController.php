<?php
/**
 * Frontend index controller
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: FrontpageController.php,v 1.1 2012-08-03 00:16:56 developer Exp $
 *
 */
class FrontpageController extends Zend_Controller_Action
{
	
	public function __call ($method, $arguments) {
		//die(__FILE__.': '.__LINE__);
		header( 'HTTP/1.0 404 Not Found' );
		$this->_helper->layout->setLayout( 'error' );
		$this->view->render();
	}
	
	
	
	public function indexAction () {
		
		$this->view->assign('is_frontpage', true);
	
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