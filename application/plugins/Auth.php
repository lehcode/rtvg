<?php
class Xmltv_Plugin_Auth extends Zend_Controller_Plugin_Abstract
{
    protected $_env = 'production';
	private $_identity;

	/**
	 * The ACL object
	 *
	 * @var zend_acl
	 */
	private $_acl;

	/**
	 * The page to direct to if there is a current user 
	 * but resource is not permistted to be accesssed by 
	 * that user.
	 *
	 * @var array
	 */
	private $_noacl = array(
		'module' => 'default',
		'controller' => 'error',
		'action' => 'no-auth');

	/**
	 * Page to direct to if user is not current user
	 *
	 * @var array
	 */
	private $_noauth = array(
		'module' => 'users',
		'controller' => 'auth',
		'action' => 'login');


	/**
	 * validate the current user's request
	 *
	 * @param Zend_Controller_Request_Abstract $request
	 */
	public function preDispatch( Zend_Controller_Request_Abstract $request)
	{
	    
	    //$this->_identity = Bootstrap_Auth::getCurrentUser(  Zend_Registry::get('db_local') );
		
	    $front = Zend_Controller_Front::getInstance();
	    $bs = $front->getParam('bootstrap');
	    $this->_acl = $bs->getResource('acl');
	    $this->_identity = $bs->getResource('user');

		if (!empty($this->_identity)) {
			$role = $this->_identity->role;
		} else {
			$role = null;
		}

		$controller = $request->controller;
		$module     = $request->module;
		$action     = $request->action;

		//go from more specific to less specific
		$moduleLevel = 'default:'.$module;
		$controllerLevel = $moduleLevel . '.' . $controller;
		$privelege = $action;

		if ($this->_acl->has($controllerLevel)) {
			$resource = $controllerLevel;
		} else {
			$resource = $moduleLevel;
		}

		if ($module != 'default' && $controller != 'index') {
			if ($this->_acl->has($resource) && !$this->_acl->isAllowed($role, $resource, $privelege)) {
				if (!$this->_identity) {
					$request->setModuleName($this->_noauth['module']);
					$request->setControllerName($this->_noauth['controller']);
					$request->setActionName($this->_noauth['action']);
					//$request->setParam('authPage', 'login');
				} else {
					$request->setModuleName($this->_noacl['module']);
					$request->setControllerName($this->_noacl['controller']);
					$request->setActionName($this->_noacl['action']);
					//$request->setParam('authPage', 'noauth');
				}
				throw new Exception('Access denied. ' . $resource . '::' . $role, 403);
			}
		}
	}
}