<?php
/**
 * Frontend Ads controller
 *
 * @author  Antony Repin
 * @version $Id: SmthController.php,v 1.3 2013-04-06 22:35:03 developer Exp $
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
    
    /**
     * Popunder/clickunder
     */
    public function puAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->getResponse()->setHeader( 'Content-type', 'application/javascript' );
            $js = file_get_contents( 'file:///'. APPLICATION_PATH. '/../public/js/ss/advmaker/cu.js');
            $script = (APPLICATION_ENV=='production') ? Rtvg_Compressor_JSMin::minify( $js ) : $js ;
	        $this->view->assign( 'script', $script );
	        $this->render( 'script' );
        } else {
            $this->_forward('error', 'error');
        }
    }
    
    /**
     * Sliding banner
     */
    public function rollinAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->getResponse()->setHeader( 'Content-type', 'application/javascript' );
            $js = file_get_contents( 'file:///'. APPLICATION_PATH. '/../public/js/ss/wizard/rollin.js');
            $script = (APPLICATION_ENV=='production') ? Rtvg_Compressor_JSMin::minify( $js ) : $js ;
	        $this->view->assign( 'script', $script );
	        $this->render( 'script' );
        } else {
            $this->_forward('error', 'error');
        }
    }

    /**
     * VK Message banner
     */
    public function vkMessageAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->getResponse()->setHeader( 'Content-type', 'application/javascript' );
            $js = file_get_contents( 'file:///'. APPLICATION_PATH. '/../public/js/ss/wizard/vk.js');
            $script = (APPLICATION_ENV=='production') ? Rtvg_Compressor_JSMin::minify( $js ) : $js ;
	        $this->view->assign( 'script', $script );
	        $this->render( 'script' );
        } else {
            $this->_forward('error', 'error');
        }
    }
    
    /**
     * Rich media banner
     */
    public function richAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            
        	$this->getResponse()->setHeader( 'Content-type', 'application/javascript' );
        	
        	$files = array(
        		'js/ss/wizard/rich.js',
        		//'js/ss/mrich/rich.js',
        	);
        	
        	$file = $files[0];
        	if (count($files)>1){
        		$file = (int)mt_rand(0, count($files)-1);
        	}
        	
        	$js = file_get_contents( 'file:///'. APPLICATION_PATH. '/../public/'.$files[$file]);
        	$script = (APPLICATION_ENV=='production') ? Rtvg_Compressor_JSMin::minify( $js ) : $js ;
       		$this->view->assign( 'script', $script );
        	$this->render( 'script' );
        } else {
            $this->_forward('error', 'error');
        }
    }
    
}