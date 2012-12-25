<?php
/**
 * 
 * Controller for archiving tasks
 * @uses Zend_Controller_Action
 * @version $Id: ArchiveController.php,v 1.5 2012-12-25 02:14:18 developer Exp $
 *
 */
class Admin_ArchiveController extends Zend_Controller_Action
{
	/**
	 * 
	 * Request validation universal helper
	 * @var Xmltv_Controller_Action_Helper_RequestValidator
	 */
	protected $validator;
	
	/**
	 * 
	 * Input filtering plugin
	 * @var Zend_Filter_Input
	 */
	protected $input;
	
	
	/**
	 * (non-PHPdoc)
	 * @see Zend_Controller_Action::__call()
	 */
	public function __call ($method, $arguments) {
		throw new Zend_Exception($method." не найден");
	}
	
	
	/**
	 * (non-PHPdoc)
	 * @see Zend_Controller_Action::init()
	 */
	public function init() {
		
        $this->_helper->layout->setLayout('admin');
        $ajaxContext = $this->_helper->getHelper( 'AjaxContext' );
		$ajaxContext->addActionContext( 'archive', 'html' )
			->initContext();
		
		$this->validator = $this->_helper->getHelper('RequestValidator');
		
    }

    /**
     * 
     * Default
     * Redirects to archive page
     */
    public function indexAction() {
        $this->_forward('archive');
    }
    
    /**
     * 
     * Show archive page
     */
 	public function archiveAction() {
        
    }

    /**
     * 
     * Archiving routines
     */
    public function storeAction()
    {
        
    	//var_dump($this->_getAllParams());
    	//die(__FILE__.': '.__LINE__);
		
		$this->input = $this->validator->direct( array( 'isvalidrequest', 'vars'=>$this->_getAllParams() ));
		//var_dump($this->_getAllParams());
		//die(__FILE__.': '.__LINE__);
		if ($this->input->isValid()){
			
			ini_set('max_execution_time', 0);
			/*
			 * Setup dates
			 */
			$start = new Zend_Date($this->input->getEscaped('start_date'), 'dd.MM.yyyy');
			if ($this->_getParam('end_date')=='') {
				echo "Не указана дата окончания периода!";
				exit();
			} 
			$end = new Zend_Date($this->input->getEscaped('end_date'), 'dd.MM.yyyy');
			if ($end->toString("U") > $start->toString("U")) {
				exit("Дата окончания должна быть ранее даты начала!");
			}
			/*
			 * Process
			 */
			$model = new Admin_Model_Programs();
			try {
				$model->archivePrograms($start, $end);
			} catch (Exception $e) {
				echo $e->getMessage();
				exit;
			}
			
			echo "<h3>Готово!</h3>";
			exit();
			
		}
		exit();
    }

}