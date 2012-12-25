<?php

class Admin_Model_DbTable_ProgramsDescriptionsArchive extends Zend_Db_Table_Abstract
{

    protected $_name='programs_descriptions';

    /**
     * 
     * Constructor
     * @param array $config
     */
	public function __construct($config=array()){
		
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
	/**
	 * @return the $_name
	 */
	public function getName() {
		return $this->_name;
	}

	/**
	 * @param field_type $_name
	 */
	public function setName($_name) {
		$this->_name = $_name;
	}

    
}

