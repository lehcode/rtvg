<?php
/**
 * View helper to check if user is alowed to access
 * particular parts of view script output
 *
 * @version $Id: IsAllowed.php,v 1.1 2013-03-11 13:54:36 developer Exp $
 * @version $Id: IsAllowed.php,v 1.1 2013-03-11 13:54:36 developer Exp $
 *
 */
class Admin_View_Helper_IsAllowed extends Zend_View_Helper_Abstract
{
    protected $_acl;
    protected $_user;
    
    public function isAllowed($resource = null, $privilege = null)
    {
    	return (bool)$this->getAcl()->isAllowed( $this->getUser()->role, $resource, $privilege );
    }
    
    /**
     * @return App_Model_Acl
     */
    public function getAcl()
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
    public function setAcl(Zend_Acl $acl)
    {
    	$this->_acl = $acl;
    	return $this;
    }
    
    /**
     * @return Users_Model_User
     */
    public function getUser()
    {
    	if (null === $this->_user) {
    		$this->setUser( Bootstrap_Auth::getCurrentUser() );
    	}
    	return $this->_user;
    }
    
    /**
     * @return App_View_Helper_IsAllowed
     */
    public function setUser(Xmltv_User $user)
    {
    	$this->_user = $user;
    	return $this;
    }
}