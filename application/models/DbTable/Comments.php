<?php

class Xmltv_Model_DbTable_Comments extends Zend_Db_Table_Abstract
{

	//protected $_schema = 'rtvg_comments_db';
    protected $_name = 'rtvg_comments';
    protected $_primary = 'id';
    
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

