<?php

class Xmltv_Model_DbTable_VcacheMain extends Xmltv_Db_Table_Abstract
{

    protected $_name = 'vcache_main';

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct ($config = array()) {
    
    	parent::__construct(array('name'=>$this->_name));
    
    }
    
    public function store($video=array()){
        
    	if ($video['desc']===null){
    	    $video['desc']='';
    	}
    	
    	try {
    	    $this->insert($video);
    	} catch (Exception $e) {
    	    return;
    	}
    	
    	return true;
        
    }
    
    public function fetch($key=null){
    	
        $select = $this->select(false)
	        ->from($this->getName())
	        ->where("`rtvg_id`='$key'");
        
        return $this->fetchRow($select);
        
    }

}

