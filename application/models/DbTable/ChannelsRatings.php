<?php

class Xmltv_Model_DbTable_ChannelsRatings extends Xmltv_Db_Table_Abstract
{

    protected $_name = 'channels_ratings';

    /**
     * Constructor
     * @param unknown_type $config
     */
    public function __construct ($config = array()) {
    
    	parent::__construct( array('name'=>$this->_name) );	
    
    }
    
    public function addHit($channel_id){
    	
    	if (!$channel_id)
			throw new Zend_Exception("Не указан один или более параметров для ".__FUNCTION__, 500);
    	
		if (!$row = $this->find($channel_id)->current())
			$row = $this->createRow(array('id'=>$channel_id), true);
		
		$row->hits+=1;
		$row->save();
		
    }
    
}

