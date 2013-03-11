<?php
class Rtvg_Controller_Admin extends Zend_Controller_Action
{
    
    protected $errorUrl;
    
    /**
     *
     * Validator
     * @var Xmltv_Controller_Action_Helper_RequestValidator
     */
    protected $_validator;
    
    /**
     *
     * Input filtering plugin
     * @var Zend_Filter_Input
     */
    protected $input;
    
    public function init(){
        
        $this->isAllowed = $this->_helper->getHelper('IsAllowed')->direct();
        $this->errorUrl = $this->view->baseUrl( 'admin/error/error' );
        $this->_helper->layout->setLayout( 'admin' );
        $this->_validator = $this->_helper->getHelper('RequestValidator');
        $this->initView();
        
    }
    
    /**
     * Validate and filter request parameters
     *
     * @throws Zend_Exception
     * @throws Zend_Controller_Action_Exception
     * @return boolean
     */
    protected function validateRequest($options=array()){
    
    	if (!empty($options)){
    		foreach ($options as $o=>$v){
    			$vars[$o]=$v;
    		}
    	}
    
    	foreach ($this->_getAllParams() as $k=>$p){
    		$vars[$k]=$p;
    	}
    
    	// Validation routines
    	$this->input = $this->_validator->direct( array('isvalidrequest', 'vars'=>$vars));
    	if ($this->input===false) {
    		if (APPLICATION_ENV=='development'){
    			echo "Wrong input!";
    			Zend_Debug::dump($this->input->getMessages());
    			//die(__FILE__.': '.__LINE__);
    		} elseif(APPLICATION_ENV!='production'){
    			throw new Zend_Exception(self::ERR_INVALID_INPUT, 404);
    			//$this->view->assign('messages', $this->input->getMessages());
    			//$this->render();
    		}
    		/*
    			$this->_redirect( $this->view->url( array(
    					'params'=>$this->_getAllParams(),
    					'hide_sidebar'=>'right'), 'default_error_invalid-input'), array('exit'=>true));
    		*/
    	} else {
    			
    		$invalid=array();
    		foreach ($this->_getAllParams() as $k=>$v){
    			if (!$this->input->isValid($k)) {
    				$invalid[$k] = $this->_getParam($k);
    			}
    		}
    			
    		if (APPLICATION_ENV=='development'){
    			foreach ($this->_getAllParams() as $k=>$v){
    				if (!$this->input->isValid($k)) {
    					throw new Zend_Controller_Action_Exception("Invalid ".$k.'! Value: '.$invalid[$k]);
    				}
    			}
    		}
    			
    		return true;
    
    	}
    
    }
    
    
    
}