<?php
/**
 * Check if enough priviliges to access resource
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @version $Id: IsAllowed.php,v 1.2 2013-03-04 17:57:38 developer Exp $
 *
 */
class Zend_Controller_Action_Helper_IsAllowed extends Zend_Controller_Action_Helper_Url
{
	/**
	 * Check access rights
	 * 
	 * @param string $privilige
	 * @param string $module
	 * @param string $controller
	 * @param string $action
	 */
    public function grantAccess( $privilige='index', $module=null, $controller=null, $action=null ){
    	
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
        
        $acl  = Xmltv_Model_Acl::getInstance();
    	$auth = Zend_Auth::getInstance();
    	$role = isset($auth->getIdentity()->role) ? $auth->getIdentity()->role : 'guest';
    	
    	if (strlen($module)) {
    	    if (strlen($action)) {
    	    	$resource = $module.':';
    	    	if (strlen($controller)) {
    	    	    $resource = $module.':'.$controller.'.'.$action;
    	    	} else {
    	    	    throw new Zend_Exception( "Controller name is missing!", 404 );
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
    	    //die(__FILE__.': '.__LINE__);
    	}
    	
    	if( false === $acl->isAllowed( $role, $resource, $privilige )){
    		return false;
    	}
    	return true;
    	
    }
	
    /**
     * (non-PHPdoc)
     * @see Zend_Controller_Action_Helper_Url::direct()
     */
 	public function direct()
    {
    	return $this->grantAccess();
    }
}