<?php
class Zend_Controller_Action_Helper_IsAllowed extends Zend_Controller_Action_Helper_Url
{
	
	public function grantAccess( $module=null, $controller=null, $action=null, $privilige='index' ){
    	
		//var_dump(func_get_args());
		//die(__FILE__.': '.__LINE__);
		
		if (!$module && !$controller && !$action) {
			return false;
		}
		
    	$acl  = App_Model_Acl::getInstance();
    	$auth = Zend_Auth::getInstance();
    	$role = isset($auth->getIdentity()->role) ? $auth->getIdentity()->role : 'guest';
    	
    	$resource='';
    	if (!empty($module)) {
    		if ($controller)
    		$resource .= $module.':';
    		else
    		$resource .= $module;
    	}
    	
    	if (!empty($controller)) {
    		if (isset($action))
    		$resource .= $controller.'.';
    		else 
    		$resource .= $controller;
    	}
    	
    	if( false === $acl->isAllowed( $role, $resource.$action, $privilige )){
    		return false;
    	}
    	return true;
    	
    }
	
 	public function direct( $module=null, $controller=null, $action=null, $privilige='index' )
    {
    	//$fc   = Zend_Controller_Front::getInstance();
    	//$vars = $fc->getRequest();
    	
    	//var_dump( $vars->get('module') );
        return $this->grantAccess( $module, $controller, $action, $privilige );
    }
}