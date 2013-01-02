<?php
/**
 * Frontend index controller
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: FrontpageController.php,v 1.2 2013-01-02 05:07:49 developer Exp $
 *
 */
class FrontpageController extends Zend_Controller_Action
{
	
    
    /**
     * (non-PHPdoc)
     * @see Zend_Controller_Action::__call()
     */
	public function __call ($method, $arguments) {
	    if (APPLICATION_ENV=='production') {
		    header( 'HTTP/1.0 404 Not Found' );
			$this->_helper->layout->setLayout( 'error' );
			$this->view->render();
	    }
	}
	
	
	
	/**
	 * Render frontpage
	 */
	public function indexAction () {
		
		$this->view->assign('is_frontpage', true);
	
	}
	
}