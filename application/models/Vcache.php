<?php
class Xmltv_Model_Vcache extends Xmltv_Model_Abstract {
        
    /**
     * Model constructor
     * 
     * @param array $config
     */
    public function __construct($config=array()){
        
        $config['db'] = Zend_Registry::get('app_config')->resources->multidb->local;
        parent::__construct($config);
        
    }
    
    /**
     * Load video from cache
     * 
     * @param  string $rtvgId
     * @return object|NULL
     */
    public function getVideo($rtvgId=null){
        
        if ($rtvgId){
            
            $result = $this->vcacheMainTable->fetch($rtvgId);
            
            if (!$result) { 
                return false;
            } else { 
                $now = Zend_Date::now();
                
                //var_dump($result);
                //die(__FILE__.': '.__LINE__);
                
                $deleteAt = new Zend_Date( $result->delete_at, 'dd-MM-YYYY HH:mm:ss' );
                if ($deleteAt->compare($now)==-1){
                	return $result->toArray();
                } else {
                    try {
                        $this->vcacheMainTable->delete("`rtvg_id`='$rtvgId'");
                    } catch (Zend_Db_Table_Exception $e) {
                        throw new Zend_Exception($e->getMessage(), $e->getCode(), $e);
                    }
                    return null;
                }
            }
        }
        
    }
    
    /**
     * Save main video for listing item to database
     * 
     * @param Zend_Gdata_YouTube_VideoEntry $video
     */
    public function saveMainVideo($video=null){
    	
        $vModel = new Xmltv_Model_Videos();
        
        if ($video && is_a($video, 'Zend_Gdata_YouTube_VideoEntry')){
            $video = $vModel->parseYtEntry($video);
        }
        
        if (APPLICATION_ENV=='development'){
            //var_dump($video);
            //die(__FILE__.': '.__LINE__);
        }
        
        $result = $this->vcacheMainTable->store( $video);
        return $video;
        
    }
    
    
    /**
     * 
     * Save related video entry to database cache
     * 
     * @param  Zend_Gdata_YouTube_VideoEntry|array $video
     * @param  string                        $yt_parent
     * @throws Zend_Exception
     * @return array
     */
    public function saveRelatedVideo($video=null, $yt_parent=null){
    	
        if (APPLICATION_ENV=='development'){
        	//var_dump(func_get_args());
        	//die(__FILE__.': '.__LINE__);
        }
        
        
        if ($video && $yt_parent && is_a($video, 'Zend_Gdata_YouTube_VideoEntry')){
        
	        $vModel = new Xmltv_Model_Videos();
	        $video = $vModel->parseYtEntry($video);
	        if (($video = $vModel->parseYtEntry($video))!==false){
            	$video['yt_parent'] = $yt_parent;
            	if (APPLICATION_ENV=='development'){
            		var_dump($video);
            		die(__FILE__.': '.__LINE__);
            	}
	        }
	        
        } elseif(is_array($video)) {
            try {
                $video = $this->vcacheRelatedTable->store($video);
            } catch (Zend_Db_Table_Exception $e) {
                die(__FILE__.': '.__LINE__);
            }
            
        } else {
            $video = false;
        }
        
        return $video;
        
    }
    
    /**
     * Fetch related videos info from DB cache
     * 
     * @param  string $yt_id
     * @return array
     */
    public function getRelated( $yt_id=null, $limit=10 ){
    	
        /*
        if($yt_id){
            $result = $this->vcacheRelatedTable->fetchAll("`yt_parent`='$yt_id'", null, $limit)->toArray();
        }
        */
        
        $select = $this->db->select()
        	->from(array('v'=>$this->vcacheRelatedTable->getName()), array(
        		'rtvg_id',
        		'yt_id',
        		'title',
        		'alias',
        		'desc',
        		'views',
        		'published',
        		'duration',
        		'thumbs',
        	))
        	->where("`yt_parent`='$yt_id'");
        
        if (APPLICATION_ENV=='development'){
            parent::debugSelect($select, __METHOD__);
        }
        
        $result = $this->db->fetchAll($select, null, Zend_Db::FETCH_ASSOC);
        
        if (APPLICATION_ENV=='development'){
        	//var_dump($result);
        	//die(__FILE__.': '.__LINE__);
        }

        if (count($result)) {
            return $result;
        }
        
        return false;
        
    }
    
}