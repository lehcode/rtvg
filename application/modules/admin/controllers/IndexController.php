<?php

class Admin_IndexController extends Zend_Controller_Action
{

	public function __call($method, $arguments)
    {
		header('HTTP/1.0 404 Not Found');
		$this->_helper->layout->setLayout('error');
		$this->view->render();
    }
	
    public function init()
    {
       
    }
    
 	public function noRouteAction()
    {
		header('HTTP/1.0 404 Not Found');
		$this->_helper->layout->setLayout('error');
		//$this->view->render();
    }

    public function indexAction()
    {
        $this->_forward('tasks', 'index', 'admin');
    }

    public function loginAction()
    {
    	$this->_helper->layout->setLayout('adminLogin');
    	$this->_forward('tasks', 'index', 'admin');
    	//die(__FILE__.': '.__LINE__);
    	//var_dump($this->getResponse());
    	//die(__FILE__.': '.__LINE__);
    }

    public function tasksAction()
    {
       $this->_helper->layout->setLayout('admin');
    }


}



