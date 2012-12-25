<?php

class Xmltv_Model_DbTable_Comments extends Zend_Db_Table_Abstract
{

	protected $_name = 'comments';
    protected $_primary = 'id';
    
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
    
    protected function _setup(){
    	parent::_setup();
    	$date = new Zend_Date();
    	$this->_defaultValues = array(
    		'id'=>null,
    		'author'=>'',
    		'intro'=>'',
    		'fulltext'=>'',
    		'date_created'=>$date->toString('yyyy-MM-dd hh:mm:ss'),
    		'date_added'=>$date->toString('yyyy-MM-dd hh:mm:ss'),
    		'published'=>1,
    		'src_url'=>'',
    		'feed_url'=>'',
    		'parent_type'=>'',
    		'parent_id'=>''
    	
    	);
    }

}

