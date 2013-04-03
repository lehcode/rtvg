<?php
/**
 * Frontend Ads controller
 *
 * @author  Antony Repin
 * @version $Id: SmthController.php,v 1.2 2013-04-03 18:18:05 developer Exp $
 *
 */
class SmthController extends Rtvg_Controller_Action
{
    
	public function init()
    {
        
        parent::init();
        
    	/**
		 * Change layout for AJAX requests
		 */
		if ($this->getRequest()->isXmlHttpRequest()) {
		    $ajaxContext = $this->_helper->getHelper('contextSwitch');
		    $ajaxContext->initContext();
		    $this->_helper->layout->disableLayout();
	   	}
	   	
    }
    
    public function puAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->getResponse()->setHeader( 'Content-type', 'application/javascript' );
            $js =file_get_contents( 'file:///'. APPLICATION_PATH. '/../public/js/ss/mu/cu.js');
            $script = (APPLICATION_ENV=='production') ? Rtvg_Compressor_JSMin::minify( $js ) : $js ;
	        $this->view->assign( 'script', $script );
	        $this->render( 'script' );
        } else {
            $this->_forward('error', 'error');
        }
    }
    
    public function richAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
        	$this->getResponse()->setHeader( 'Content-type', 'application/javascript' );
        	$js = file_get_contents( 'file:///'. APPLICATION_PATH. '/../public/js/ss/wizard/rich.js');
        	$script = (APPLICATION_ENV=='production') ? Rtvg_Compressor_JSMin::minify( $js ) : $js ;
       		$this->view->assign( 'script', $script );
        	$this->render( 'script' );
        } else {
            $this->_forward('error', 'error');
        }
    }
    
}