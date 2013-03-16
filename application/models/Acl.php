<?php
/**
 *
 * Model for Access Control Lists management
 *
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @version $Id: Acl.php,v 1.10 2013-03-16 14:22:04 developer Exp $
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
	    
	    // Deny errors for everyone
	    $denied1 = new Zend_Acl_Resource( 'default:%25D0%25B2%25D0%25B8%25D0%25B4%25D0%25B5%25D0%25BE.%25D0%25BE%25D0%25BD%25D0%25BB%25D0%25B0%25D0%25B9%25D0%25BD' );	    
	    $this->add( $denied1 );
	    $denied2 = new Zend_Acl_Resource( 'default:%C3%90%C2%BA%C3%90%C2%B0%C3%90%C2%BD%C3%90%C2%B0%C3%90%C2%BB%C3%91%E2%80%B9' );	    
	    $this->add( $denied2 );
	    
	    // Admin resources
	    $adminModule = new Zend_Acl_Resource( 'admin:' );
	    $this->add( $adminModule );
	    $this->add( new Zend_Acl_Resource( 'admin:index' ), $adminModule );
	    $this->add( new Zend_Acl_Resource( 'admin:auth' ), $adminModule );
	    $this->add( new Zend_Acl_Resource( 'admin:error.error' ), $adminModule );
	    $this->add( new Zend_Acl_Resource( 'admin:import.index' ), $adminModule );
	    $this->add( new Zend_Acl_Resource( 'admin:import.remote' ), $adminModule );
	    $this->add( new Zend_Acl_Resource( 'admin:user' ), $adminModule );
	    $this->add( new Zend_Acl_Resource( 'admin:user.login' ), 'admin:user' );
	    $this->add( new Zend_Acl_Resource( 'admin:user.profile' ), 'admin:user' );
	    $this->add( new Zend_Acl_Resource( 'admin:programs' ), $adminModule );
	    $this->add( new Zend_Acl_Resource( 'admin:channels' ), $adminModule );
	    $this->add( new Zend_Acl_Resource( 'admin:content' ), $adminModule );
	    
	    // Deny acces to denied (wrong) resources to all
	    $this->deny( null, array(
	    	$denied1,
	    	$denied2,
	    ));
	    
	    // Deny everything to guests
	    $this->deny( self::ROLE_GUEST );
	    
	    // Conditionally allow parts to guests
	    $this->allow( self::ROLE_GUEST, $default, null, new Rtvg_Acl_IsNotBotAssertion() );
	    
	    // Let humans can try to login/logout
	    $this->allow( null,  'admin:auth', null, new Rtvg_Acl_IsNotBotAssertion() );
	    
	    // Publisher can access publishing parts
	    // and backend login
	    $this->allow( array(self::ROLE_EDITOR), array(
	    	'admin:content',
	    	'admin:auth',
	    	'admin:index',
	    ));
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



