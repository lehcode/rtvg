<?php
/**
 *
 * Manage listing programs
 *
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @package backend
 * @version $Id: ProgramsController.php,v 1.8 2013-03-16 12:46:19 developer Exp $
 */

class Admin_ProgramsController extends Rtvg_Controller_Admin
{

    /**
     * (non-PHPdoc)
     * @see Zend_Controller_Action::init()
     */
    public function init() {
        
        parent::init();
                
		$ajaxContext = $this->_helper->getHelper( 'AjaxContext' );
		$ajaxContext->addActionContext( 'delete-programs', 'json' )
			->initContext();
			
    }

    /**
     * Redirect
     */
    public function indexAction() {
        
        $this->_forward('list');
    }
    
    /**
     * Processing frontend
     */
    public function processingAction(){
        
    }
    
    
    /**
     * 
     */
    public function deleteProgramsAction(){
	
        if ( parent::validateRequest() ){
            
            ini_set('max_execution_time', 0);
            	
            $start = new Zend_Date($this->_getParam('delete_start'), 'dd.MM.YYYY'); 
            $start = $start->toString('YYYY-MM-dd 00:00:00');
            $end   = new Zend_Date($this->_getParam('delete_end'), 'dd.MM.YYYY'); 
            $end   = $end->toString('YYYY-MM-dd 00:00:00');
            $programs = new Admin_Model_Broadcasts();

            if ( (bool)$this->input->getEscaped('deleteinfo')===true) {
            	$programs->deletePrograms($start, $end, true);
            } else {
            	$programs->deletePrograms($start, $end);
            }

            echo "<h3>Готово!</h3>";
            die();
            
        }
        
        die();
	}

    /**
     * @deprecated
     * @param  string $action
     * @return boolean
     */
	private function _parseRequestValid($action=null){
		
		if (!$action){
            throw new Zend_Exception("Action not specified");
        }
		
        $filters = array( '*'=>'StringTrim', '*'=>'StringToLower' );
		$validators = array(
	    	'module'=>array( new Zend_Validate_Regex('/^[a-z]+$/')),
	    	'controller'=>array( new Zend_Validate_Regex('/^[a-z]+$/')),
	    	'action'=>array( new Zend_Validate_Regex('/^[a-z-]+$/')),
	    	'format'=>array( new Zend_Validate_Regex('/^html|json$/')));
		switch ($action) {
			case 'delete-programs':
				$validators['cleanprograms'] = array( new Zend_Validate_Digits());
				$validators['deleteinfo']    = array( new Zend_Validate_Digits() );
				$validators['search_start']  = array( new Zend_Validate_Date(array('format'=>'dd.MM.yyyy', 'locale'=>'ru')) );
				$validators['search_end']    = array( new Zend_Validate_Date(array('format'=>'dd.MM.yyyy', 'locale'=>'ru')) );
				break;
		}
		
		$input = new Zend_Filter_Input($filters, $validators, $this->_request->getParams());
    	if ($input->isValid())
    	return true;
    	
    	return false;
	}

 	public function programsDeleteProgressAction(){
 		
 		$model = new Admin_Model_Broadcasts();
 		exit();
    }

}

