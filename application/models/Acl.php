<?php
/**
 *
 * Model for Access Control Lists management
 *
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @version $Id: Acl.php,v 1.16 2013-04-06 22:35:03 developer Exp $
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
	    $this->add( new Zend_Acl_Resource( 'default:channels.index' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:channels.alias' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:channels.category' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:channels.list' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:channels.channel-week' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:channels.new-comments' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:channels.typeahead' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:content.article' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:content.article-tag' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:content.blog' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:content.blog-category' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:feed.atom' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:feed.index' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:feed.rss' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:listings' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:listings.index' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:listings.day-listing' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:listings.day-date' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:listings.outdated' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:listings.program-week' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:listings.program-day' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:listings.channel-week' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:listings.category' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:listings.premieres-week' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:sitemap.sitemap' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:smth.rich' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:videos.show-video' ), $default );
	    $this->add( new Zend_Acl_Resource( 'default:user' ), 'default:');
	    $this->add( new Zend_Acl_Resource( 'default:user.login' ), 'default:user' );
	    $this->add( new Zend_Acl_Resource( 'default:user.logout' ), 'default:user' );
	    $this->add( new Zend_Acl_Resource( 'default:user.profile' ), 'default:user' );	    
	    $this->add( new Zend_Acl_Resource( 'default:search' ), $default );	    
	    $this->add( new Zend_Acl_Resource( 'default:search.search' ), 'default:search' );	    
	    $this->add( new Zend_Acl_Resource( 'default:auth' ), $default );
	    // Dummy ACLs to avoid some minor routing error notices 
	    $this->add( new Zend_Acl_Resource( 'default:images' ), $default);	    
	    $this->add( new Zend_Acl_Resource( 'default:images.index' ), $default);	    
	    $this->add( new Zend_Acl_Resource( 'default:img' ), $default);
	    $this->add( new Zend_Acl_Resource( 'default:img.index' ), $default);
	    $this->add( new Zend_Acl_Resource( 'default:fonts' ), $default);
	    $this->add( new Zend_Acl_Resource( 'default:fonts.index' ), $default);
	    $this->add( new Zend_Acl_Resource( 'default:css' ), $default);
	    $this->add( new Zend_Acl_Resource( 'default:css.index' ), $default);
	    
	    // Deny errors for everyone
	    $denied1 = new Zend_Acl_Resource( 'default:%25D0%25B2%25D0%25B8%25D0%25B4%25D0%25B5%25D0%25BE.%25D0%25BE%25D0%25BD%25D0%25BB%25D0%25B0%25D0%25B9%25D0%25BD' );	    
	    $this->add( $denied1 );
	    $denied2 = new Zend_Acl_Resource( 'default:%C3%90%C2%BA%C3%90%C2%B0%C3%90%C2%BD%C3%90%C2%B0%C3%90%C2%BB%C3%91%E2%80%B9' );	    
	    $this->add( $denied2 );
	    
	    // Admin resources
	    $adminModule = new Zend_Acl_Resource( 'admin:' );
	    $this->add( $adminModule );
	    $this->add( new Zend_Acl_Resource( 'admin:actors' ), $adminModule );
	    $this->add( new Zend_Acl_Resource( 'admin:archive' ), $adminModule );
	    $this->add( new Zend_Acl_Resource( 'admin:index' ), $adminModule );
	    $this->add( new Zend_Acl_Resource( 'admin:auth' ), $adminModule );
	    $this->add( new Zend_Acl_Resource( 'admin:login' ), $adminModule );
	    $this->add( new Zend_Acl_Resource( 'admin:channels' ), $adminModule );
	    $this->add( new Zend_Acl_Resource( 'admin:comments' ), $adminModule );
	    $this->add( new Zend_Acl_Resource( 'admin:content' ), $adminModule );
	    $this->add( new Zend_Acl_Resource( 'admin:content.article' ), 'admin:content' );
	    $this->add( new Zend_Acl_Resource( 'admin:error.error' ), $adminModule );
	    $this->add( new Zend_Acl_Resource( 'admin:grab' ), $adminModule );
	    $this->add( new Zend_Acl_Resource( 'admin:import' ), $adminModule );
	    $this->add( new Zend_Acl_Resource( 'admin:import.index' ), 'admin:import' );
	    $this->add( new Zend_Acl_Resource( 'admin:import.remote' ), 'admin:import' );
	    $this->add( new Zend_Acl_Resource( 'admin:movies' ), $adminModule );
	    $this->add( new Zend_Acl_Resource( 'admin:programs' ), $adminModule );
	    $this->add( new Zend_Acl_Resource( 'admin:series' ), $adminModule );
	    $this->add( new Zend_Acl_Resource( 'admin:system' ), $adminModule );
	    $this->add( new Zend_Acl_Resource( 'admin:system.cache' ), $adminModule );
	    $this->add( new Zend_Acl_Resource( 'admin:system.phpinfo' ), $adminModule );
	    $this->add( new Zend_Acl_Resource( 'admin:user' ), $adminModule );
	    $this->add( new Zend_Acl_Resource( 'admin:user.login' ), 'admin:user' );
	    $this->add( new Zend_Acl_Resource( 'admin:user.profile' ), 'admin:user' );
        //@TODO update later to only allow authorized access
        if (APPLICATION_ENV=='development'){
            $this->add( new Zend_Acl_Resource( 'default:tests.index' ), $default ); 
        } else {
            $this->add( new Zend_Acl_Resource( 'default:tests.index' ), 'admin:user' );
        }
	    
        
	    
	    // Deny acces to denied (wrong) resources to all
	    $this->deny( null, array(
	    	$denied1,
	    	$denied2,
	    ));
	    
	    // Deny everything
	    $this->deny();
	    
	    // Conditionally allow parts
	    $this->allow( null, $default, null, new Rtvg_Acl_IsNotBotAssertion() );
	    $this->allow( null, $default, null, new Rtvg_Acl_IsNotBadBotAssertion() );
	    
	    
	    // Publisher can access publishing parts and backend login
	    $this->allow( null, array(
	    	'admin:auth',
	    	'admin:login',
	    ));
	    $this->allow(array(self::ROLE_EDITOR, self::ROLE_PUBLISHER), array(
	    	'admin:content',
	    	'admin:actors',
	    	'admin:movies',
	    	'admin:series',
	    	'admin:index',
	    ));
	    $this->deny(array( self::ROLE_EDITOR, self::ROLE_PUBLISHER ), 'admin:content.article', array('income'));
	    
	    // Alow everything to root user
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



