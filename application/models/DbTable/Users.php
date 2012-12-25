<?php
/**
 * 
 * User data table class
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @package rutvgid
 * @filesource $Source: /home/developer/cvs/rutvgid.ru/application/models/DbTable/Users.php,v $
 * @version $Id: Users.php,v 1.2 2012-12-25 01:57:53 developer Exp $
 */
class Xmltv_Model_DbTable_Users extends Zend_Db_Table_Abstract
{

	protected $_name = 'users';
	protected $_user;

	protected static $_instance;
	
	const FETCH_MODE = Zend_Db::FETCH_OBJ;
	
	
	/**
     * Constructor
     * @param unknown_type $config
     */
    public function __construct ($config = array()) {
    
    	parent::__construct(array('name'=>$this->_name));
    
    	if (isset($config['tbl_prefix'])) {
    		$pfx = $config['tbl_prefix'];
    	} else {
    		$pfx = Zend_Registry::get('app_config')->resources->multidb->local->get('tbl_prefix');
    	}
    	$this->setName($pfx.$this->_name);
    
    }
    
    /**
     * @return string
     */
    public function getName() {
    	return $this->_name;
    }
    
    /**
     * @param string $string
     */
    public function setName($string=null) {
    	$this->_name = $string;
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

