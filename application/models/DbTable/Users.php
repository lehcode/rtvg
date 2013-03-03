<?php
/**
 * 
 * User data table class
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @package rutvgid
 * @filesource $Source: /home/developer/cvs/rutvgid.ru/application/models/DbTable/Users.php,v $
 * @version $Id: Users.php,v 1.3 2013-03-03 23:34:13 developer Exp $
 */
class Xmltv_Model_DbTable_Users extends Xmltv_Db_Table_Abstract
{

	protected $_name    = 'users';
	protected $_primary = array('id');
	
	/**
	 * (non-PHPdoc)
	 * @see Zend_Db_Table_Abstract::init()
	 */
    public function init() {
        
        parent::init();
        
        $this->setRowClass('Xmltv_User');
        $this->setRowsetClass('Xmltv_Users');
    	
    	
    }
    
    /**
     * (non-PHPdoc)
     * @see Xmltv_Db_Table_Abstract::_setup()
     */
    protected function _setup(){
    	parent::_setup();
    	$this->_defaultValues = array(
    		'id'=>null,
    		'email'=>'',
    		'display_name'=>'',
    		'real_name'=>'',
    		'last_login'=>null,
    		'login_source'=>'site',
    		'hash'=>'',
    		'role'=>'guest',
    		'created'=>Zend_Date::now()->toString("YYYY-MM-dd HH:mm:ss"),
    	);
    }
    
    /**
     * 
     * @param  string $email
     * @throws Zend_Db_Table_Exception
     * @return string|boolean
     */
    public function fetchByOpenId( $email=null ){
    	
        if (!$email)
            return $this->createRow();
        
        $where = "`email`='$email'";
        $row = $this->fetchRow($where);
        
        if (APPLICATION_ENV=='development'){
            //var_dump($where);
            //var_dump($row);
            //die(__FILE__.': '.__LINE__);
        }
        
        if ($row && is_a($row, 'Xmltv_User')){
        	return $row;
        }
        
        return false;
        
    }
    
    public function createRow($data=array()){
    	
        return parent::createRow($this->_defaultValues);
    }

}





