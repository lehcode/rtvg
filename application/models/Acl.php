<?php
/**
 *
 * Model for Access Control Lists management
 *
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @version $Id: Acl.php,v 1.3 2013-03-04 17:57:38 developer Exp $
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
	    
	    $this->add( new Zend_Acl_Resource( 'default:' ));
	    $this->add( new Zend_Acl_Resource( 'default:user' ), 'default:');
	    $this->add( new Zend_Acl_Resource( 'default:frontpage' ), 'default:');
	    $this->add( new Zend_Acl_Resource( 'default:frontpage.index' ), 'default:frontpage');
	    $this->add( new Zend_Acl_Resource( 'default:frontpage.single-channel' ), 'default:frontpage');
	    $this->add( new Zend_Acl_Resource( 'default:channels' ), 'default:');
	    $this->add( new Zend_Acl_Resource( 'default:channels.category' ), 'default:channels');
	    $this->add( new Zend_Acl_Resource( 'default:channels.list' ), 'default:channels');
	    $this->add( new Zend_Acl_Resource( 'default:channels.channel-week' ), 'default:channels');
	    $this->add( new Zend_Acl_Resource( 'default:listings' ), 'default:');
	    $this->add( new Zend_Acl_Resource( 'default:listings.day-listing' ), 'default:listings');
	    $this->add( new Zend_Acl_Resource( 'default:listings.day-date' ), 'default:listings.day-listing');
	    $this->add( new Zend_Acl_Resource( 'default:listings.program-week' ), 'default:listings');
	    $this->add( new Zend_Acl_Resource( 'default:listings.channel-week' ), 'default:listings');
	    $this->add( new Zend_Acl_Resource( 'default:listings.category' ), 'default:listings');
	    $this->add( new Zend_Acl_Resource( 'default:videos.show-video' ), 'default:');
	    $this->add( new Zend_Acl_Resource( 'default:user.login' ), 'default:user' );
	    $this->add( new Zend_Acl_Resource( 'default:user.logout' ), 'default:user' );
	    $this->add( new Zend_Acl_Resource( 'default:user.profile' ), 'default:user' );	    
	    
	    $adminModule = new Zend_Acl_Resource( 'admin:' );
	    $this->add( $adminModule );
	    $publisherModule = new Zend_Acl_Resource( 'publisher:' );
	    $this->add( $publisherModule );
	    $this->add( new Zend_Acl_Resource( 'admin:index' ), 'admin:' );
	    $this->add( new Zend_Acl_Resource( 'admin:index.index' ), 'admin:index' );
	    $this->add( new Zend_Acl_Resource( 'admin:user' ), 'admin:' );
	    $this->add( new Zend_Acl_Resource( 'admin:user.login' ), 'admin:user' );
	    $this->add( new Zend_Acl_Resource( 'admin:user.profile' ), 'admin:user' );
	    
	    $this->allow( null, 'default:', null );
	    $this->deny( array(self::ROLE_GUEST, self::ROLE_USER), $adminModule );
	    $this->allow( self::ROLE_PUBLISHER, $publisherModule );
	    $this->allow( self::ROLE_PUBLISHER, $adminModule, array( 'login', 'logout', 'publish') );
	    
	    
	    $this->allow( self::ROLE_GOD );
	    
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

