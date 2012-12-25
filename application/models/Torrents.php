<?php
class Xmltv_Model_Torrents
{
	private $_table;
	private $_db;
	private $_tbl_pfx;
		
	public function __construct(){
		
		$dbConf         = Zend_Registry::get('app_config')->resources->multidb->get('local');
		$this->_tbl_pfx = $dbConf->get('tbl_prefix');
		$this->_table   = new Xmltv_Model_DbTable_Channels( array('tbl_prefix'=>$this->_tbl_pfx) );
		$this->_db      = new Zend_Db_Adapter_Mysqli( $dbConf );		
		
	}
}