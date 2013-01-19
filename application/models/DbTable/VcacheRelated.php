<?php

class Xmltv_Model_DbTable_VcacheRelated extends Xmltv_Db_Table_Abstract
{

    protected $_name = 'vcache_related';

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct ($config = array()) {
    
    	parent::__construct(array('name'=>$this->_name));
    
    }
    
    /**
     * 
     * @param  array $video
     * @return NA
     */
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
    
    /**
     * 
     * @param  string $key
     * @return array
     */
    public function fetch($key=null){
    	
        return $this->fetchRow("`rtvg_id`='$key'")->toArray();
        
    }

}

