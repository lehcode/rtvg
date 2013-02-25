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

    	$video['delete_at'] = Zend_Date::now()->addHour(24)->toString('YYYY-MM-dd HH:mm:ss');
    	
    	if (APPLICATION_ENV=='development'){
    		//var_dump($video);
    		//die(__FILE__.': '.__LINE__);
    	}
    	
    	if ($video['rtvg_id'] && !empty($video['rtvg_id'])) {
    		$this->insert($video);
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

