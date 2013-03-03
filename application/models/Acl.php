<?php
/**
 *
 * Model for Access Control Lists management
 *
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @version $Id: Acl.php,v 1.1 2013-03-03 18:55:38 developer Exp $
 */
class Xmltv_Model_Acl extends Zend_Acl
{
	
	const ROLE_GUEST = 'guest';
	const ROLE_USER = 'member';
	const ROLE_PUBLISHER = 'publisher';
	const ROLE_EDITOR = 'editor';
	const ROLE_ADMIN = 'admin';
	const ROLE_GOD = 'god';
	
	protected static $_instance;
	protected static $_user;
	
	/* Singleton pattern */
	protected function __construct()
	{
	    $this->addRole( new Zend_Acl_Role( self::ROLE_GUEST ));
	    $this->addRole( new Zend_Acl_Role( self::ROLE_USER ), self::ROLE_GUEST );
	    $this->addRole( new Zend_Acl_Role( self::ROLE_PUBLISHER ), self::ROLE_USER );
	    $this->addRole( new Zend_Acl_Role( self::ROLE_EDITOR ), self::ROLE_PUBLISHER );
	    $this->addRole( new Zend_Acl_Role( self::ROLE_ADMIN ), self::ROLE_EDITOR );
	    $this->addRole( new Zend_Acl_Role( self::ROLE_GOD ));
	    
	    try {
	        $this->add( new Zend_Acl_Resource( 'default:user' ));
	        $this->add( new Zend_Acl_Resource( 'default:user.auth' ), 'default:user' );
	        $this->add( new Zend_Acl_Resource( 'default:user.list' ), 'default:user' );
	    } catch (Zend_Acl_Exception $e) {
	        throw new Zend_Exception($e->getMessage(), $e->getCode(), $e);
	    }
	    
	    
	    $this->allow( null, 'default:user', array( 'index', 'profile' ));
	    $this->allow( self::ROLE_GUEST, 'default:user.auth', array( 'login' ));
	    $this->deny( array( self::ROLE_USER ), 'default:user.auth', array( 'login' ));
	    
	    $moduleResource = new Zend_Acl_Resource( 'admin' );
	    $this->add( $moduleResource );
	    
	    $this->allow( null, $moduleResource, array( 'login') );
	    
	    return $this;
	    
	}
	
	public static function setUser( Xmltv_User $user = null )
	{
		if (null === $user) {
			throw new InvalidArgumentException('$user is null');
		}
	
		self::$_user = $user;
	}
	
	/**
	 * 
	 * @return Xmltv_Model_Acl
	 */
	public static function getInstance()
	{
		if (null === self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	public static function resetInstance()
	{
		self::$_instance = null;
		self::getInstance();
	}
	
	public static function getCurrentUser(){
	    
	    return self::$_user;
	}
	
}

