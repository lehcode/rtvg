<?php
/**
 * 
 * Controller for archiving tasks
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @package backend
 * @version $Id: ArchiveController.php,v 1.6 2013-03-16 12:46:19 developer Exp $
 *
 */
class Admin_ArchiveController extends Rtvg_Controller_Admin
{
	
	/**
	 * (non-PHPdoc)
	 * @see Zend_Controller_Action::init()
	 */
	public function init() 
	{
	    parent::init();
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
			$model = new Admin_Model_Broadcasts();
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