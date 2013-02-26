<?php

class Xmltv_Model_DbTable_ChannelsComments extends Xmltv_Db_Table_Abstract
{

	protected $_name = 'channels_comments';
    protected $_primary = 'id';
    
    /**
     * Constructor
     * @param unknown_type $config
     */
    public function __construct ($config = array()) {
    
    	parent::__construct(array(
    		'name'=>$this->_name,
    		'primary'=>$this->_primary,
    	));
    
    }
    
    
    protected function _setup(){
        
    	parent::_setup();
    	$date = new Zend_Date();
    	$this->_defaultValues = array(
    		'id'=>null,
    		'author'=>'',
    		'intro'=>'',
    		'fulltext'=>'',
    		'date_created'=>$date->toString('YYYY-MM-dd HH:mm:ss'),
    		'date_added'=>$date->toString('YYYY-MM-dd HH:mm:ss'),
    		'published'=>1,
    		'src_url'=>'',
    		'feed_url'=>'',
    		'parent_id'=>''
    	);
    	
    }

}

