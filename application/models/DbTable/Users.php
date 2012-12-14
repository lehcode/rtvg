<?php
/**
 * 
 * User data table class
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @package rutvgid
 * @filesource $Source: /home/developer/cvs/rutvgid.ru/application/models/DbTable/Users.php,v $
 * @version $Id: Users.php,v 1.1 2012-12-14 03:56:28 developer Exp $
 */
class Xmltv_Model_DbTable_Users extends Zend_Db_Table_Abstract
{

	protected $_name = '';
	protected $_user;

	protected static $_instance;
	
	const FETCH_MODE = Zend_Db::FETCH_OBJ;
	
	
	public function __construct($config=array()){
		
		parent::__construct($config);
		
		$config = new Zend_Config_Ini(APPLICATION_PATH .'/configs/application.ini', APPLICATION_ENV);
		$this->_setTableName('users', $config->resources->db->params->tbl_prefix);
		
		$db = new Zend_Db_Adapter_Mysqli( $config->resources->db->params );
		$this->_setAdapter($db);
		
		
		
	}
	
	/**
	 * 
	 * Get user info by email
	 * @param string $email
	 */
	public function fetchByOpenId($email=null){
		
		if (!$email)
			return $this->createRow();
		
		return $this->fetchRow("`open_id`='".$email."'");
		
	}
	

	/**
	 * 
	 * Create empty guest user
	 */
	public function getUser(){
		
		$result = $this->createRow();
		
		if (empty($result->role))
			$result->role = 'guest';
		
		return $result;
		
	}
	
	
 	/**
     * 
     * @return App_Model_Users
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
	
	private function _setTableName($name=null, $prefix=null){
		
		if (!$name || !$prefix)
		return false;
		
		$this->_name = $prefix.'_'.$name;
	
	}

}

