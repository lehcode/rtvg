<?php

class Admin_DuplicatesController extends Zend_Controller_Action
{
	
    public function init()
    {
        $this->_helper->layout->setLayout('admin');
	    $request = $this->_request->getParams();
			$filters = array(
	    		'module'=>'StringTrim',
	    		'controller'=>'StringTrim',
	    		'action'=>'StringTrim'
	    	);
	    	$validators = array(
	    		'module'=>array(
	    			new Zend_Validate_Regex('/^[a-z]+$/u')),
	    		'controller'=>array(
	    			new Zend_Validate_Regex('/^[a-z]+$/')),
	    		'action'=>array(
	    			new Zend_Validate_Regex('/^[a-z]+$/')),	
	    	);
	    	$input = new Zend_Filter_Input($filters, $validators, $request);
	    	if (!$input->isValid())
	    	return false;
	    	
	    	$this->view->setScriptPath(APPLICATION_PATH . '/modules/admin/views/scripts/');
    }

    public function indexAction()
    {
        // action body
    }

    public function actorsAction()
    {
    	ini_set('max_execution_time', 0);
    	$model = new Admin_Model_Actors();
    	$dupes  = $model->getDuplicates();
    	$this->view->assign('origin', $dupes['origin']);
    	$this->view->assign('clones', $dupes['clones']);
    	$total = $dupes['total'];
    	$this->view->assign('total', $dupes['total']);
    	$total = $dupes['processed'];
    	$this->view->assign('processed', $dupes['processed']);
    	$processed_pct = round($dupes['processed']/(($dupes['total']/100)), 2);
    	$this->view->assign('processed_pct', $processed_pct);
		$this->render('actors-duplicates');
       
    }

    public function actorsNamesAction()
    {
        ini_set('max_execution_time', 0);
    	$model = new Admin_Model_Actors();
    	$model->fixNames();
    	$this->_forward('index');
    }
    
	public function deleteActorAction(){
		
		$request = $this->_request->getParams();
		$filter_id = new Zend_Filter_Digits();
	   	$id = $filter_id->filter($request['id']);
		$actors = new Admin_Model_DbTable_Actors();
		
		try{
			$actors->delete("`id`='$id'");
		} catch (Exception $e) {
			echo $e->getMessage();
			die(__FILE__.': '.__LINE__);
		}
		
		$this->_forward('actors');
		
	}


}





