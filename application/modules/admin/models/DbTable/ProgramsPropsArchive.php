<?php
/**
 * 
 * Programs properties table
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @package rutvgid
 * @filesource $Source: /home/developer/cvs/rutvgid.ru/application/modules/admin/models/DbTable/ProgramsPropsArchive.php,v $
 * @version $Id: ProgramsPropsArchive.php,v 1.2 2013-04-03 04:08:16 developer Exp $
 */
class Admin_Model_DbTable_ProgramsPropsArchive extends Xmltv_Db_Table_Abstract
{

	protected $_name = 'programs_props';

    /**
     * Constructor
     * @param unknown_type $config
     */
    public function __construct ($config = array()) {
    
    	$this->_db = new Zend_Db_Adapter_Mysqli( Zend_Registry::get('app_config')->resources->multidb->get('archive') );
    	$pfx = Zend_Registry::get('app_config')->resources->multidb->archive->get('tbl_prefix');
    	$this->_name = $pfx.$this->_name;
    
    }
    
    public function getCount(){
    	
    	$select = $this->_db->select();
		$select->from($this->_name, array('count(*) as amount'));
    	try {
			$result = $this->_db->fetchAll($select);
		} catch (Exception $e) {
			echo $e->getMessage();
			die(__FILE__.': '.__LINE__);
			
		}
		return (int)$result[0]['amount'];
    }
    
}

