<?php

class ChannelsController extends Zend_Controller_Action
{

	public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
    	$this->view->baseUrl = $this->getRequest()->getBaseUrl();
        $model = new Xmltv_Model_Channels();
        $rows = $model->getPublished();
        $channels=array();
        $c=0;
        foreach ($rows as $row) {
        	$item = $row->toArray();
        	$channels[$c] = $model->fixImage($item, $this->view->baseUrl());
        	$c++;
        }
        $this->view->assign('channels', $channels);
        
        
    }
    
	public function noRouteAction()
	{
		
		header('HTTP/1.0 404 Not Found');
		$this->view->render('404.php');
		
	}
	
	public function __call($method,$arguments)
	{
		
		header('HTTP/1.0 404 Not Found');
		$this->view->render('404.php');
		
	}


}

