<?php

class Xmltv_Model_DbTable_ChannelsRatings extends Xmltv_Db_Table_Abstract
{

    protected $_name = 'channels_ratings';
    protected $_primary = 'channel';

    /**
     * Constructor
     * @param array $config
     */
    public function __construct ($config = array()) {
    
    	parent::__construct( array('name'=>$this->_name) );	
    
    }
    
    /**
     * Add rating hit to channel
     * @param int $channel_id
     * @throws Zend_Exception
     */
    public function addHit($channel_id){
    	
    	if (!$channel_id){
			throw new Zend_Exception('Не указан $channel_id');
        }
        
        if (!is_int($channel_id)){
            throw new Zend_Exception('Channel ID is not INT');
        }
        
		if (!$row = $this->find($channel_id)->current()){
			$row = $this->createRow(array('channel'=>$channel_id), true);
        }
        
		$row->hits += 1;
		$row->save();
		
    }
    
}

