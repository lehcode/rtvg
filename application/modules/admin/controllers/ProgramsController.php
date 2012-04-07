<?php

class Admin_ProgramsController extends Zend_Controller_Action
{

    public function init()
    {
        $this->_helper->layout->setLayout('admin');
        $ajaxContext = $this->_helper->getHelper( 'AjaxContext' );
		$ajaxContext->addActionContext( 'premieres-search', 'html' )
			->initContext();
    }

    public function indexAction()
    {
        $this->_forward('list');
    }
    
    public function premieresAction(){
    	
    }

    


}

