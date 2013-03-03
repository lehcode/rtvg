<?php
/**
 *
 * Model for user management
 *
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @package rutvgid
 * @version $Id: Users.php,v 1.1 2013-03-03 18:55:38 developer Exp $
 */
class Xmltv_Model_Users extends Xmltv_Model_Abstract
{
    
    protected $usersTable;
    
    /**
     * Constructor
     * 
     * @param array $config
     */
    function __construct( $config=array() ){
        
        parent::__construct($config);
        $this->initTables();
        
    }
    
    /**
     * Initialize model tables
     */
    protected function initTables(){
        
        $this->usersTable = new Xmltv_Model_DbTable_Users();
        
    }
    
    /**
     * 
     * @return Xmltv_User
     */
    public function getUser(){
    
    	$auth = Zend_Auth::getInstance();
    	
    	if ($auth->hasIdentity()) {
    		$user = $this->usersTable->fetchByOpenId( $auth->getIdentity()->open_id );
    	} else {
    		$user = $this->usersTable->createRow();
    		$user->role = 'guest';
    	}
    	
    	if (APPLICATION_ENV=='development'){
    	    //var_dump($user);
    	    //die(__FILE__.': '.__LINE__);
    	}
    	
    	return $user;
    
    }
    
    /**
     * Find user by email
     * 
     * @param  string $email
     * @return Xmltv_User
     */
    public function searchByOpenId($email=null){
    	
        return $this->usersTable->fetchByOpenId( $email );
        
    }
    
    /**
     * Authenticate user by credentials
     * 
     * @param int $user_id
     * @param Zend_Controller_Response_Http $response
     */
    public function authenticate($openid=null, Zend_Controller_Response_Http $response){
    	
        if (APPLICATION_ENV=='development'){
            //var_dump(func_get_args());
            //die(__FILE__.': '.__LINE__);
        }
        
        /**
         * 
         * @var Xmltv_User
         */
        $user = $this->usersTable->fetchByOpenId( $openid );
        
        if (APPLICATION_ENV=='development'){
            var_dump($user);
            die(__FILE__.': '.__LINE__);
        }
        
        $auth = Zend_Auth::getInstance();
        $auth->getStorage()->write( $user->getIdentity() );
        
    }
}