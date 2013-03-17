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
    
    /**
     * Current data
     * @var Xmltv_User
     */
    protected $user;
    
    /**
     * FlashMessenger
     *
     * @var Zend_Controller_Action_Helper_FlashMessenger
     */
    protected $_flashMessenger;
    
    /**
     * Access checking action helper
     * @var Zend_Controller_Action_Helper_IsAllowed
     */
    protected $isAllowed;
    
    public function init(){
        
        $this->isAllowed = $this->_helper->getHelper('IsAllowed')->direct( 'grantAccess', array( 'privilege'=>$this->_getParam('action'), 'module'=>'admin' ) );
        $this->errorUrl = $this->view->baseUrl( 'admin/error/error' );
        $this->_helper->layout->setLayout( 'admin' );
        $this->_validator = $this->_helper->getHelper('RequestValidator');
        $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
        $this->view->addScriptPath(APPLICATION_PATH."/layouts/scripts/admin/");
        
        $this->initView();
        
        /**
         * Load bootstrap
         * @var Bootstrap
         */
        $bootstrap = $this->getInvokeArg('bootstrap');
        
        $this->user = $bootstrap->getResource('user');
        $this->view->assign('user', $this->user);
        
		if (APPLICATION_ENV=='development'){
			//var_dump($this->user);
			//var_dump($this->_getAllParams());
			//die(__FILE__.': '.__LINE__);
		}
        
        if ( $this->isAllowed !== true) {
        	return false;
        }
        
        if ($this->validateRequest()){
        	return false;
        }
        
        $this->view->addScriptPath("/path/to/your/view/scripts/");
        
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
    		} elseif(APPLICATION_ENV!='production'){
    			throw new Zend_Exception(self::ERR_INVALID_INPUT, 404);
    		}
    		
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
    
    /**
	 * (non-PHPdoc)
	 * @see Zend_Controller_Action::__call()
	 */
	public function __call ($method, $arguments) {
		throw new Zend_Exception( Rtvg_Message::ERR_METHOD_NOT_FOUND, 404);
	}
    
}