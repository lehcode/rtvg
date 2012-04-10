<?php
class Xmltv_Model_DbTable_Channels extends Zend_Db_Table_Abstract
{

    protected $_name = 'rtvg_channels';
    
    public function __construct($config=array()) {
    	parent::__construct(array('name'=>$this->_name));
    }
    
    public function getTypeheadItems(){
    	
    	$select = $this->_db->select()
    		->from($this->_name, array( 'title' ));
    	return $this->_db->query($select)->fetchAll();
    	
    }
    
    public function getFeatured($order=null, $total=20, $by_hits=true){
    	
    	if (!$order)
    	$order='ch_id';
    	
    	$select = $this->_db->select()
    		->from( $this->_name, '*' )
    		->joinLeft('rtvg_channels_ratings', "$this->_name.`ch_id`=rtvg_channels_ratings.`ch_id`");
    		
    	$select->where( "`featured`='1'" )->limit($total);
    	
    	if (!$by_hits)
    	$select->order("$order ASC");
    	else {
    		$select->order("rtvg_channels_ratings.hits DESC");
    		$select->order("$this->_name.title ASC");
    	}
    	
    	return $this->_db->query($select)->fetchAll();
    }
	
}

