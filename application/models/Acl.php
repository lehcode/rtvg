<?php
/**
 *
 * Model for Access Control Lists management
 *
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @version $Id: Acl.php,v 1.8 2013-03-14 06:09:55 developer Exp $
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
	    
	    $default = new Zend_Acl_Resource( 'default:' );
	    $this->add( $default );
	    $this->add( new Zend_Acl_Resource( 'default:frontpage' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:frontpage.index' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:frontpage.single-channel' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:channels' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:channels.category' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:channels.list' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:channels.channel-week' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:channels.new-comments' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:channels.typeahead' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:listings' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:listings.day-listing' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:listings.day-date' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:listings.program-week' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:listings.program-day' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:listings.channel-week' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:listings.category' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:listings.premieres-week' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:sitemap.sitemap' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:videos.show-video' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:user' ), 'default:');
	    $this->add( new Zend_Acl_Resource( 'default:user.login' ), 'default:user' );
	    $this->add( new Zend_Acl_Resource( 'default:user.logout' ), 'default:user' );
	    $this->add( new Zend_Acl_Resource( 'default:user.profile' ), 'default:user' );	    
	    $this->add( new Zend_Acl_Resource( 'default:search' ), $default );	    
	    $this->add( new Zend_Acl_Resource( 'default:search.search' ), 'default:search' );	    
	    $this->add( new Zend_Acl_Resource( 'default:auth' ), $default );
	    // Dummy ACLs to avoid some minor routing error notices    
	    $this->add( new Zend_Acl_Resource( 'default:fonts' ));	    
	    $this->add( new Zend_Acl_Resource( 'default:images' ));	    
	    $this->add( new Zend_Acl_Resource( 'default:img' ));	    
	    
	    $adminModule = new Zend_Acl_Resource( 'admin:' );
	    $this->add( $adminModule );
	    $publisherModule = new Zend_Acl_Resource( 'publisher:' );
	    $this->add( $publisherModule );
	    $this->add( new Zend_Acl_Resource( 'admin:index' ), $adminModule );
	    $this->add( new Zend_Acl_Resource( 'admin:auth' ), $adminModule );
	    $this->add( new Zend_Acl_Resource( 'admin:index.index' ), $adminModule );
	    $this->add( new Zend_Acl_Resource( 'admin:auth.login' ), 'admin:auth' );
	    $this->add( new Zend_Acl_Resource( 'admin:auth.logout' ), 'admin:auth' );
	    $this->add( new Zend_Acl_Resource( 'admin:error.error' ), $adminModule );
	    $this->add( new Zend_Acl_Resource( 'admin:import.index' ), $adminModule );
	    $this->add( new Zend_Acl_Resource( 'admin:import.remote' ), $adminModule );
	    $this->add( new Zend_Acl_Resource( 'admin:user' ), $adminModule );
	    $this->add( new Zend_Acl_Resource( 'admin:user.login' ), 'admin:user' );
	    $this->add( new Zend_Acl_Resource( 'admin:user.profile' ), 'admin:user' );
	    $this->add( new Zend_Acl_Resource( 'admin:listings' ), $adminModule );
	    $this->add( new Zend_Acl_Resource( 'admin:channels' ), $adminModule );
	    
	    
	    $this->deny( self::ROLE_GUEST, null, null );
	    $this->allow( self::ROLE_GUEST, $default, null, new Rtvg_Acl_IsNotBotAssertion() );
	    $this->allow( self::ROLE_GUEST, array( 
	    	'admin:auth.login',
	    	'default:user.login',
	    ), null);
	    $this->allow( null, 'admin:auth', array( 'index', 'login', 'logout') );
	    $this->allow( self::ROLE_PUBLISHER, $publisherModule );
	    $this->allow( self::ROLE_PUBLISHER, $adminModule, array( 'index', 'login', 'logout') );
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



