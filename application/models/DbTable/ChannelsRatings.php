<?php

class Xmltv_Model_DbTable_ChannelsRatings extends Zend_Db_Table_Abstract
{

    protected $_name = 'channels_ratings';

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
    
    public function addHit($channel_id){
    	
    	if (!$channel_id)
			throw new Zend_Exception("Не указан один или более параметров для ".__FUNCTION__, 500);
    	
		if (!$row = $this->find($channel_id)->current())
			$row = $this->createRow(array('ch_id'=>$channel_id), true);
		
		//var_dump($channel_id);
		//var_dump($row);
		//die(__FILE__.': '.__LINE__);
		
		$row->hits+=1;
		$row->save();
		
    }
    
}

