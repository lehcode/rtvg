<?php
/**
 * Check if enough priviliges to access resource
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @version $Id: Banner.php,v 1.1 2013-03-08 04:05:18 developer Exp $
 *
 */
class Zend_Controller_Action_Helper_Banner extends Zend_Controller_Action_Helper_Url
{
	/**
	 * Pick random ad
	 * 
	 * @param array $options
	 */
    public function random( array $options=null ){
    	
        die(__FILE__.': '.__LINE__);
        
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
     * @param string $method
     * @param array  $params
     * @throws Zend_Exception
     */
 	public function direct($method=null, array $params=null)
    {
        
        if (!$method){
            throw new Zend_Exception( 'Не указан $method' );
        }
        
    	return $this->$method( $params );
    }
}