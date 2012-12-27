<?php
/**
 * 
 * Access Control Class
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @package rutvgid
 * @filesource $Source: /home/developer/cvs/rutvgid.ru/library/Xmltv/Attic/Acl.php,v $
 * @version $Id: Acl.php,v 1.1 2012-12-27 17:00:43 developer Exp $
 */
class Xmltv_Acl extends Zend_Acl
{
	const ROLE_GUEST        = 'guest';
    const ROLE_USER         = 'user';
    const ROLE_EDITOR       = 'editor';
    const ROLE_ADMIN        = 'admin';
    const ROLE_GOD          = 'god';
	
	protected static $_instance;
    
	// Singleton pattern
	protected function __construct(){
		
		$this->addRole( new Zend_Acl_Role( self::ROLE_GUEST ) );
        $this->addRole( new Zend_Acl_Role( self::ROLE_USER ), self::ROLE_GUEST );
        $this->addRole( new Zend_Acl_Role( self::ROLE_EDITOR ), self::ROLE_USER );
        $this->addRole( new Zend_Acl_Role( self::ROLE_ADMIN ), self::ROLE_EDITOR );
        $this->addRole( new Zend_Acl_Role( self::ROLE_GOD ) );
		
        $this->allow( self::ROLE_GOD );
        
        $this->addResource( new Zend_Acl_Resource( 'default' ) )
        	->addResource( new Zend_Acl_Resource( 'default:auth' ), 'default' )
        	->addResource( new Zend_Acl_Resource( 'default:auth.logout' ), 'default:auth' )
        	->addResource( new Zend_Acl_Resource( 'default:auth.login' ), 'default:auth' )
        	->addResource( new Zend_Acl_Resource( 'user' ) )
        	->addResource( new Zend_Acl_Resource( 'admin' ));
        
        $this->deny( self::ROLE_GUEST );
        $this->allow( null, array( 'default', 'default:auth') );
        $this->allow( self::ROLE_ADMIN, array('user', 'admin') );
        
        return $this;
        
	}
	
	protected static $_user;
	
	/**
	 * 
	 * @param Application_Model_DbTable_Users $user
	 * @throws InvalidArgumentException
	 */
	public static function setUser(App_Model_Users $user = null)
    {
        if (null === $user) {
            throw new InvalidArgumentException('$user is null');
        }

        self::$_user = $user;
    }
    
    /**
     * 
     * @return Application_Model_Acl
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
    
	
}




