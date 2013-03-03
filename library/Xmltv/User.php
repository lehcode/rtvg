<?php
class Xmltv_User extends Zend_Db_Table_Row_Abstract
{

    private static $authorized=false;
    
    public function init(){
        
        if ($this->role!='guest'){
            self::$authorized = true;
        }
    }
    
    /**
     * Check if user is guest or is 
     * authenticated user
     * 
     * @return boolean
     */
    public function authorized(){
        
        if ($this->role!='guest'){
        	return true;
        }
        
        if (self::$authorized===false){
            return false;
        }
        return false;
    }
    
    /**
     * Get user info
     * @return Xmltv_User
     */
	public function getIdentity(){
		return $this;
	}
	
	public function reset(){
	    
	    $this->setFromArray(array(
	    	'id'=>null,
	    	'email'=>'',
	    	'display_name'=>'',
	    	'real_name'=>'',
	    	'last_login'=>null,
	    	'login_source'=>'site',
	    	'hash'=>'',
	    	'role'=>'guest',
	    	'created'=>Zend_Date::now()->toString("YYYY-MM-dd HH:mm:ss"),
	    ));
	}

}