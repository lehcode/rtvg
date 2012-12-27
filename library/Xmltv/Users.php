<?php
/**
 * 
 * User management model
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @package sosedionline
 * @filesource $Source: /home/developer/cvs/rutvgid.ru/library/Xmltv/Users.php,v $
 * @version $Id: Users.php,v 1.1 2012-12-27 17:00:43 developer Exp $
 */

class Xmltv_Users
{
	/**
	 * 
	 * Application config
	 * @var Zend_Config_Ini
	 */
	protected $app_config;
	
	/**
	 * Database Adapter
	 * @var Zend_Db_Adapter_Mysqli
	 */
	private $_db;
	
	/**
	 * @var App_Model_DbTable_Users
	 */
	private $_user_table;
	
	public function __construct(){
		$this->app_config = new Zend_Config_Ini(APPLICATION_PATH .'/configs/application.ini', APPLICATION_ENV);
		$this->_db = new Zend_Db_Adapter_Mysqli( $this->app_config->resources->db->params );
		$this->_user_table = new Xmltv_Model_DbTable_Users($this->_db);
	}
	
	public function getUser($email=null){
		
		$auth = Zend_Auth::getInstance();
		if ($auth->hasIdentity()) {
			return $this->_user_table->fetchByOpenId( $auth->getIdentity()->open_id );
		} else {
			if ($email)
				return $this->_user_table->fetchByOpenId($email);
			else 
				$user = $this->_user_table->createRow( array() );
				$user->role = 'guest';
				return $user;
		}
		
	}
	
	/**
	 * 
	 * Get basic user information
	 * @param int $uid
	 */
	public function getUserInfo( $uid=null ){
		
		if (!$uid)
			return false;

		$uid = intval($uid);
		return $this->_user_table->fetchRow( "`id`='$uid'" );
			
	}
	
	/**
	 * 
	 * Get User profile
	 * @param int $uid
	 */
	public function getProfile($uid=null){
		
		if (!$uid)
			return false;

		$tblPrefix = $this->app_config->resources->db->params->tbl_prefix;
		$userProfiles = new Zend_Db_Table($tblPrefix.'_user_profiles');
		return $userProfiles->fetchRow("`user_id`='$uid'");
			
	}
	
	
	public function createUser(){
		
		$tblPrefix = $this->app_config->resources->db->params->tbl_prefix;
		$userProfiles = new Zend_Db_Table($tblPrefix.'_user_profiles'); //(array( 'db'=>$this->_db, 'table'=>$tblPrefix.'_user_profiles'));
		$front = Zend_Controller_Front::getInstance();
		$request = $front->getRequest()->getPost();
		
		$userData = $this->_user_table->createRow();
		$userData->open_id = $request['openid'];
		$userData->role = 'user';
		$userData->pw = md5($request['password']);
		
		$now = new Zend_Date();
		$userData->created = $now->toString("YYYY-MM-dd HH:mm:ss");
		$userData->last_access = $now->toString("YYYY-MM-dd HH:mm:ss");
		
		//var_dump($userData);
		
		try {
			$new_id = $this->_user_table->insert($userData->toArray());
		} catch (Exception $e) {
			if ($e->getCode()==1062) {
				throw new Exception("Користувач з такою адресою електронної пошти вже зареєстрований");
			}
		}
		
		$profileInfo = $userProfiles->createRow();
		$profileInfo->user_id = $new_id;
		$profileInfo->f_name  = $request['fname'];
		$profileInfo->m_name  = isset($request['mname']) ? $request['mname'] : '' ;
		$profileInfo->s_name  = $request['sname'];
		$profileInfo->updated = $now->toString("YYYY-MM-dd HH:mm:ss");
		
		try {
			$profileInfo->save();
		} catch (Exception $e) {
			Zend_Debug::dump($e->getMessage());
			exit();
		}
		
		return $userData;
		
	}
	
	

}

