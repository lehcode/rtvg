<?php
/**
 *
 * Model for user management
 *
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @version $Id: Users.php,v 1.5 2013-03-10 02:45:15 developer Exp $
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
    	
    	if (APPLICATION_ENV=='development'){
    		//var_dump($auth->hasIdentity());
    		//die(__FILE__.': '.__LINE__);
    	}
    	
    	if ($auth->hasIdentity()) {
    		$user = $this->usersTable->fetchByOpenId( $auth->getIdentity()->email );
    	} else {
    		$user = $this->usersTable->createRow(array());
    		$user->role = 'guest';
    	}
    	
    	if (APPLICATION_ENV=='development'){
    	    //var_dump($user);
    	    //die(__FILE__.': '.__LINE__);
    	}
    	
    	return $user;
    
    }

    /**
     * 
     * @return Xmltv_User
     */
    public function getUserIdentity(){
    
    	$auth = Zend_Auth::getInstance();
    	return $auth->getIdentity()->email;
    
    }
    
    /**
     * Find user by email
     * 
     * @param  string $email
     * @return Xmltv_User
     */
    public function searchByOpenId($email=null){
    	
        if (APPLICATION_ENV=='development'){
            //var_dump($email);
            //die(__FILE__.': '.__LINE__);
        }
        
        if (!is_string($email)){
            throw new Zend_Exception("Email must be a string!", 500);
        }
        
        return $this->usersTable->fetchByOpenId( $email );
        
    }
    
    public function defaultUser(){
        
        return $this->usersTable->createRow(array());
        
    }
    
    public function createUser(array $data=null){
        
        return $this->usersTable->createRow($data);
        
    }
    
}