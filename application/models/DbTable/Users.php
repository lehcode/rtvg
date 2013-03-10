<?php
/**
 * 
 * User data table class
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @package rutvgid
 * @filesource $Source: /home/developer/cvs/rutvgid.ru/application/models/DbTable/Users.php,v $
 * @version $Id: Users.php,v 1.4 2013-03-10 02:45:15 developer Exp $
 */
class Xmltv_Model_DbTable_Users extends Xmltv_Db_Table_Abstract
{

	protected $_name    = 'users';
	protected $_primary = array('id');
	protected $_defaultValues = array(
		'id'=>0,
		'email'=>'',
		'display_name'=>'',
		'real_name'=>'',
		'last_login'=>null,
		'login_source'=>'site',
		'hash'=>'',
		'role'=>'guest',
		'created'=>null,
	);
	
	/**
	 * (non-PHPdoc)
	 * @see Zend_Db_Table_Abstract::init()
	 */
    public function init() {
        
        parent::init();
        
        $this->setRowClass('Xmltv_User');
        $this->setRowsetClass('Xmltv_Users');
    	$this->_defaultValues['created'] = Zend_Date::now()->toString("YYYY-MM-dd HH:mm:ss");
    	
    }
    
    /**
     * (non-PHPdoc)
     * @see Zend_Db_Table_Abstract::createRow()
     */
    public function createRow(array $data=null){
    	 
    	$rowData = parent::createRow($data);
    	
    	foreach ($this->_defaultValues as $dK=>$dV){
    		if (!$rowData->$dK) {
    			$rowData->$dK = $dV;
    		}
    	}
    	
    	return $rowData;
    	
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

}





