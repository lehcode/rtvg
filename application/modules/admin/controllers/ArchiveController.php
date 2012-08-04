<?php
class Admin_ArchiveController extends Zend_Controller_Action
{

	public function __call ($method, $arguments) {
		throw new Exception($method." не найден", 500);
		die();
	}
	
	public function init()
    {
        $this->_helper->layout->setLayout('admin');
        $ajaxContext = $this->_helper->getHelper( 'AjaxContext' );
		$ajaxContext->addActionContext( 'archive', 'html' )
			->initContext();
    }

    public function indexAction()
    {
        $this->_forward('archive');
    }
    
 	public function archiveAction()
    {
        //$requestVars = $this->_getAllParams();
		//var_dump($requestVars);
    }

    public function storeAction()
    {
        
    	$requestVars = $this->_getAllParams();
		var_dump($requestVars);
		
		if ($this->_parseRequestValid($this->_getParam('action'))===true){
			
			ini_set('max_execution_time', 0);
			
			$start = new Zend_Date($this->_getParam('start_date'), 'dd.MM.yyyy');
			
			if ($this->_getParam('end_date')=='') {
				echo "Не указана дата окончания периода!";
				exit();
			} 
			$end = new Zend_Date($this->_getParam('end_date'), 'dd.MM.yyyy');
			
			if ($end->toString("U") > $start->toString("U")) {
				echo "Дата окончания должна быть ранее даты начала!";
				exit();
			}
			
			$programs = new Admin_Model_Programs();
			
			//var_dump($start);
			//var_dump($end);
			//die(__FILE__.": ".__LINE__);
			
			try {
				$programs->archivePrograms($start, $end);
			} catch (Exception $e) {
				echo $e->getMessage();
				exit;
			}
			
			echo "<h3>Готово!</h3>";
			exit();
			
		}
		exit();
    }
    
    private function _parseRequestValid($action=null){
    	
    	if (!$action)
			throw new Exception(__METHOD__." - No action defined", 500);

		//var_dump($action);
		//die(__FILE__.": ".__LINE__);
			
		$filters = array( '*'=>'StringTrim', '*'=>'StringToLower' );
		$validators = array(
	    	'module'=>array( new Zend_Validate_Regex('/^[a-z]+$/')),
	    	'controller'=>array( new Zend_Validate_Regex('/^[a-z]+$/')),
	    	'action'=>array( new Zend_Validate_Regex('/^[a-z-]+$/')),
	    	'format'=>array( new Zend_Validate_Regex('/^html|json$/')));
		switch ($action) {
			case 'store':
				$validators['start_date']  = array( new Zend_Validate_Date(array('format'=>'dd.MM.yyyy', 'locale'=>'ru')) );
				//$validators['end_date']    = array( new Zend_Validate_Date(array('format'=>'dd.MM.yyyy', 'locale'=>'ru')) );
				break;
		}
		
		$input = new Zend_Filter_Input($filters, $validators, $this->_request->getParams());
    	if ($input->isValid())
    	return true;
    	
    	return false;
    	
    }


}