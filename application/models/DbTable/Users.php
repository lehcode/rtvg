<?php
/**
 * 
 * User data table class
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @version $Id: Users.php,v 1.8 2013-04-03 04:08:16 developer Exp $
 */
class Xmltv_Model_DbTable_Users extends Xmltv_Db_Table_Abstract
{

    protected $_name    = 'users';
	/**
	 * Table prefix
	 * @var string
	 */
	protected $_pfx = '';
	protected $_primary = array('id');
	protected $_defaultValues = array(
		'id'=>0,
		'email'=>'',
		'display_name'=>'',
		'real_name'=>'',
		'online'=>0,
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
        
        $this->_pfx = (string)Zend_Registry::get('app_config')->resources->multidb->local->get('tbl_prefix');
        
        if (!$this->_pfx){
        	throw new Zend_Exception(self::ERR_WRONG_DB_PREFIX);
        }
        
        $this->setName($this->_pfx.$this->_name);
        
        $this->setRowClass('Xmltv_User');
        $this->setRowsetClass('Xmltv_Users');
    	$this->_defaultValues['created'] = Zend_Date::now()->toString("YYYY-MM-dd HH:mm:ss");
    	
    }
    
    /**
     * (non-PHPdoc)
     * @see Zend_Db_Table_Abstract::createRow()
     */
    public function createRow(array $data=null, $defautSource=null){
    	 
    	$rowData = parent::createRow($data, $defautSource);
    	
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
    
    public function setOnline(array $userdata=null){
    	
        $userdata['online'] = 1;
        $this->update( $userdata, "`id`='".(int)$userdata['id']."'" );
        
    }

}





