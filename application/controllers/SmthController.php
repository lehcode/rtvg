<?php
class SmthController extends Rtvg_Controller_Action
{
    public function init()
    {
        
        parent::init();
        
    	/**
		 * Change layout for AJAX requests
		 */
		if ($this->getRequest()->isXmlHttpRequest()) {
		    
		    $ajaxContext = $this->_helper->getHelper( 'AjaxContext' );
		    $ajaxContext
		    	->addActionContext( 'pu', 'html' )
			    ->addActionContext( 'rich', 'html' )
			    ->initContext();
		    $this->_helper->layout->disableLayout();
	   	}
	   	
    }
    
    public function puAction()
    {
    	
    }
    
    public function richAction()
    {
        
    }
    
}