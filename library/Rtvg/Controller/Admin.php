<?php
/**
 * Base class for backend controllers
 * 
 * @author     Antony Repin <egeshisolutions@gmail.com>
 * @subpackage backend
 * @version    $Id: Admin.php,v 1.6 2013-04-03 18:18:05 developer Exp $
 *
 */

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
    
    /**
     * Main model for controller
     */
    protected $mainModel;
    
    /**
     * (non-PHPdoc)
     * @see Zend_Controller_Action::__call()
     */
    public function __call($methodName, $args)
    {
        parent::__call($methodName, $args);
    }
    
    public function init(){
        
        $this->isAllowed = $this->_helper->getHelper('IsAllowed')->direct( 'grantAccess', array( 'privilege'=>$this->_getParam('action'), 'module'=>'admin' ) );
        $this->errorUrl = $this->view->baseUrl( 'admin/error/error' );
        $this->_helper->layout->setLayout( 'admin' );
        $this->_validator = $this->_helper->getHelper('RequestValidator');
        $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
        
        $this->initView();
        $this->view->addScriptPath( APPLICATION_PATH."/layouts/scripts/admin/" );
        $this->view->addHelperPath( APPLICATION_PATH."/views/helpers/", 'Rtvg_View_Helper');
        $this->view->assign('pageclass', 'admin');
        
        /**
         * Load bootstrap
         * @var Bootstrap
         */
        $bootstrap = $this->getInvokeArg('bootstrap');
        $this->user = $bootstrap->getResource('user');
        $this->view->assign('user', $this->user);
        $this->view->inlineScript()
        	->prependFile('http://twitter.github.com/bootstrap/assets/js/bootstrap-dropdown.js');
        
		if (APPLICATION_ENV=='development'){
			//var_dump($this->user);
			//var_dump($this->_getAllParams());
			//die(__FILE__.': '.__LINE__);
		}
        
        if ( $this->isAllowed !== true) {
        	return false;
        }
        
        
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
			foreach ($options as $o=>$v)
			    	$vars[$o]=$v;
		}
		
		foreach ($this->_getAllParams() as $k=>$p){
			$vars[$k]=$p;
		}
		
		// Validation routines
		$this->input = $this->_validator->direct( array('isvalidrequest', 'vars'=>$this->_getAllParams()));
		foreach ($this->input->getMessages() as $error=>$text){
			$this->_flashMessenger->addMessage($text);
		}
		if ($this->input===false) {
			$this->_redirect( $this->view->url( array(
				'params'=>$this->_getAllParams(),
				'hide_sidebar'=>'right'), 'default_error_invalid-input'), array('exit'=>true));
			
		}
	
	}
	
	
	protected function validateForm(Zend_Form $form, array $data) {
		if(!$form->isValid($data)) {
			foreach($form->getMessages() as $field => $message) {
				foreach($message as $error) {
					$this->FormErrors[] = array($field => $error);
				}
			}
			return false;
		}
		return true;
	}
	
	/**
	 *
	 * @return Zend_Log
	 */
	public function getLog()
	{
		if (!$bootstrap = $this->getInvokeArg('bootstrap'))
			return false;
	
		if (!$bootstrap->hasResource('Log')) {
			return false;
		}
		/**
		 *
		 * @var Zend_Log
		 */
		$log = $bootstrap->getResource('Log');
		return $log;
	
	}
    
}