<?php 
/**
 * SM's code library
 * 
 * @category    
 * @package     
 * @subpackage  
 * @copyright   Copyright (c) 2009 Pavel V Egorov
 * @author      Pavel V Egorov
 * @link        http://epavel.ru/
 * @since       08.09.2009
 */


class Zend_View_Helper_IsAllowed extends Zend_View_Helper_Abstract
{
    protected $_acl;
    protected $_user;

    public function isAllowed($resource = null, $privelege = null)
    {
        return (bool) $this->getAcl()->isAllowed( $this->getUser()->role, $resource, $privelege );
    }

    /**
     * @return App_Model_Acl
     */
    public function getAcl()
    {
        if (null === $this->_acl) {
            $this->setAcl( Xmltv_Model_Acl::getInstance());
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
            $this->setUser( Xmltv_Bootstrap_Auth::getCurrentUser());
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
