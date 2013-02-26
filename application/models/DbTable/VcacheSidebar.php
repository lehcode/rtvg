<?php

class Xmltv_Model_DbTable_VcacheSidebar extends Xmltv_Db_Table_Abstract
{

    protected $_name = 'vcache_sidebar';

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
    	
        if (!isset($video['desc']) || empty($video['desc'])) {
    	    $video['desc']='';
    	}
    	
    	if (is_a($video['published'], 'Zend_Date')){
    	    $video['published'] = $video['published']->toString('yyyy-MM-dd HH:mm:ss');
    	}
    	
    	if (is_a($video['duration'], 'Zend_Date')){
    	    $video['duration'] = $video['duration']->toString('yyyy-MM-dd HH:mm:ss');
    	}
    	
    	if (is_array($video['thumbs'])){
    	    $video['thumbs'] = Zend_Json::encode($video['thumbs']);
    	}

    	$video['delete_at'] = Zend_Date::now()->addDay(7)->toString('YYYY-MM-dd HH:mm:ss');
    	
    	if (APPLICATION_ENV=='development'){
    		//var_dump($video);
    		//die(__FILE__.': '.__LINE__);
    	}
    	
    	if ($video['rtvg_id'] && !empty($video['rtvg_id'])) {
    	    $values = array();
	    	foreach ($video as $k=>$v){
	    	    $values[] = "`$k`=".$this->_db->quote($v);
	    	}
    	    $sql = "INSERT INTO `".$this->getName()."` VALUES (".implode(',', $values).") ON DUPLICATE KEY UPDATE `delete_at`='".$video['delete_at']."'";
    	    if (APPLICATION_ENV=='development'){
	    	    //var_dump($query);
	    	    //die(__FILE__.': '.__LINE__);
    	    }
    	    
    	    try {
    	        $this->_db->query($sql);
    	    } catch (Zend_Db_Adapter_Mysqli_Exception $e) {
    	        throw new Zend_Exception("Cannot insert into ".$this->getName(), 500);
    	    }
    	    
    	    return true;
    	    
    	}
        
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

