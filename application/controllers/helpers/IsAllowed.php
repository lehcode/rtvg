<?php
/**
 * Check if enough priviliges to access resource
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @version $Id: IsAllowed.php,v 1.3 2013-03-10 02:45:15 developer Exp $
 *
 */
class Zend_Controller_Action_Helper_IsAllowed extends Zend_Controller_Action_Helper_Abstract
{
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
        
        $acl = Zend_Registry::get('ACL');
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
    	
    	return (bool) $this->getAcl()->isAllowed( $role, $resource, $privilege );
    	
    	
    	/* 
    	if (APPLICATION_ENV=='development'){
    	    var_dump($params);
    	    var_dump($resource);
    	    var_dump($acl->isAllowed( $role, $resource, $privilege ));
    	    die(__FILE__.': '.__LINE__);
    	}
    	 */
    	/* 
    	if ( false === $acl->isAllowed( $role, $resource, $privilege )){
    		return false;
    	}
    	return true;
    	 */
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
    		$this->setAcl( Zend_Registry::get('ACL') );
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