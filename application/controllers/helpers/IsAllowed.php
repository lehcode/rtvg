<?php
/**
 * Check if enough priviliges to access resource
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @version $Id: IsAllowed.php,v 1.5 2013-03-14 06:46:38 developer Exp $
 *
 */
class Zend_Controller_Action_Helper_IsAllowed extends Zend_Controller_Action_Helper_Abstract
{
    
    private $_acl;
    
	/**
	 * Check access rights
	 * 
	 * @param string $privilege
	 * @param string $module
	 * @param string $controller
	 * @param string $action
	 */
    public function grantAccess( $privilege='index', $module=null, $controller=null, $action=null ){
    	
		$params = $this->getRequest()->getParams();
        if (!$module){
            $module = $params['module'];
        }
        if (!$controller){
            $controller = $params['controller'];
        }
        if (!$action){
            $action = $params['action'];
        }
        
        $front = Zend_Controller_Front::getInstance();
	    $bs = $front->getParam('bootstrap');
	    $acl = $bs->getResource('acl');
	    
    	$auth = Zend_Auth::getInstance();
    	$role = isset($auth->getIdentity()->role) ? $auth->getIdentity()->role : 'guest';
    	
    	if (strlen($module)) {
    		if (strlen($action)) {
    			$resource = $module.':';
    			if (strlen($controller)) {
    				$resource = $module.':'.$controller.'.'.$action;
    			} else {
    				throw new Zend_Exception( Rtvg_Message::ERR_MISSING_CONTROLLER, 404 );
    			}
    		} else {
    			// No action name provided
    			if (strlen($controller)){
    				$resource = $module.':'.$controller;
    			} else {
    				$resource = $module;
    			}
    		}
    	}
    	
    	if (APPLICATION_ENV=='development'){
    		//var_dump($params);
    		//var_dump($resource);
    		//var_dump($acl->isAllowed( $role, $resource, $privilege ));
    		//die(__FILE__.': '.__LINE__);
    	}
    	
    	return (bool)$this->getAcl()->isAllowed( $role, $resource, $privilege );
    	
    }
	
    /**
     * (non-PHPdoc)
     * @see Zend_Controller_Action_Helper_Url::direct()
     */
 	public function direct()
    {
    	return $this->grantAccess();
    }
    
    /**
     * @return App_Model_Acl
     */
    private function getAcl()
    {
    	if (null === $this->_acl) {
    	    
    	    $front = Zend_Controller_Front::getInstance();
    	    $bs = $front->getParam('bootstrap');
    	    $acl = $bs->getResource('acl');
    		$this->setAcl( $acl );
    		
    	}
    	
    	return $this->_acl;
    }
    
    /**
     * @return App_View_Helper_IsAllowed
     */
    private function setAcl(Zend_Acl $acl)
    {
    	$this->_acl = $acl;
    	return $this;
    }
}