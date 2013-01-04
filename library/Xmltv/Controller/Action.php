<?php
class Xmltv_Controller_Action extends Zend_Controller_Action
{
    
    const ERR_INVALID_INPUT = 'Неверные данные!';
    
    /**
     *
     * Validator
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
     * Caching object
     * @var Xmltv_Cache
     */
    protected $cache;
    
    /**
     * (non-PHPdoc)
     * @see Zend_Controller_Action::__call()
     */
    
    public function __call ($method, $arguments) {
    	if (APPLICATION_ENV!='production') {
    		header( 'HTTP/1.0 404 Not Found' );
    		$this->_helper->layout->setLayout( 'error' );
    		$this->view->render();
    	}
    }
    
    public function init(){
        $this->validator = $this->_helper->getHelper('requestValidator');
        $this->cache = new Xmltv_Cache();
        
    }
    
    /**
     * Validate nad filter request parameters
     *
     * @throws Zend_Exception
     * @throws Zend_Controller_Action_Exception
     * @return boolean
     */
    protected function requestParamsValid(){
    
    	// Validation routines
    	$this->input = $this->validator->direct(array('isvalidrequest', 'vars'=>$this->_getAllParams()));
    	if ($this->input===false) {
    		if (APPLICATION_ENV=='development'){
    			var_dump($this->_getAllParams());
    			die(__FILE__.': '.__LINE__);
    		} elseif(APPLICATION_ENV!='production'){
    			throw new Zend_Exception(self::ERR_INVALID_INPUT, 500);
    		}
    		$this->_redirect($this->view->url(array(), 'default_error_missing-page'), array('exit'=>true));
    
    	} else {
    
    		foreach ($this->_getAllParams() as $k=>$v){
    			if (!$this->input->isValid($k)) {
    				throw new Zend_Controller_Action_Exception("Invalid ".$k.'!');
    			}
    		}
    
    		return true;
    
    	}
    
    }
    
}