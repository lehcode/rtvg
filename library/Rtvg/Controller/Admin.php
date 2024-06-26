<?php
/**
 * Base class for backend controllers
 * 
 * @author Antony Repin <egeshisolutions@gmail.com>
 * @package backend
 * @version $Id$
 *
 */

class Rtvg_Controller_Admin extends Zend_Controller_Action
{
    
    /**
     * @deprecated
     * @var string
     */
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
     * Caching object
     * @var Rtvg_Cache
     */
    protected $cache;
    
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
        //$this->errorUrl = $this->view->baseUrl( 'admin/error/error' );
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
        
        $this->cache = new Rtvg_Cache();
        $e = ((bool)Zend_Registry::get( 'site_config' )->cache->admin->get( 'enabled' ));
        $this->cache->enabled = ($e===true) ? true : false;
        $this->cache->setLifetime( (int)Zend_Registry::get( 'site_config' )->cache->admin->get( 'lifetime' ) );
        $this->cache->setLocation( APPLICATION_PATH.'/../cache' );
        
		if ( $this->isAllowed !== true) {
        	return false;
        }
        
        $this->errorUrl = $this->view->baseUrl( 'admin/error/error' );
        
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