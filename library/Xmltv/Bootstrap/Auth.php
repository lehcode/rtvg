<?php
/**
 *
 * @author takeshi
 * @uses   Bootstrap
 *
 */
class Xmltv_Bootstrap_Auth extends Bootstrap
{
	/**
	 * @var Xmltv_User
	 */
	protected static $_currentUser;

	/**
	 *
	 * @param unknown_type $application
	 */
	public function __construct($application)
	{
		parent::__construct($application);
	}

	public static function setCurrentUser( Xmltv_User $user)
	{
		self::$_currentUser = $user;
	}

	/**
	 * @return Xmltv_Model_User
	 * @param  Zend_Db_Adapter $db
	 * @return Xmltv_User
	 */
	public static function getCurrentUser($db)
	{
	    
	    if (APPLICATION_ENV=='development'){
	        //var_dump(self::$_currentUser);
	        //die(__FILE__.': '.__LINE__);
	    }
	    
		if (null === self::$_currentUser) {
			$model = new Xmltv_Model_Users( array( 'db'=>$db ));
			self::setCurrentUser( $model->getUser() );
		}
		return self::$_currentUser;
	}

	/**
	 * @return App_Model_User
	 */
	public static function getCurrentUserId()
	{
		$user = self::getCurrentUser();
		return $user->getId();
	}

}