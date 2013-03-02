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
     * @param  string $rtvg_id
     * @return array|bool
     */
    public function getVideo($rtvg_id=null){
        
        if ($rtvg_id){
            
            $select = $this->db->select()
            	->from(array('v'=>$this->vcacheMainTable->getName()), array(
            		'rtvg_id',
            		'yt_id',
            		'title',
            		'alias',
            		'desc',
            		'views',
            		'published',
            		'duration',
            		'category',
            		'thumbs',
            	))
            	->where("`v`.`rtvg_id`='$rtvg_id'")
            	->limit(1);
            
            if (APPLICATION_ENV=='development'){
                parent::debugSelect($select, __METHOD__);
                //die(__FILE__.': '.__LINE__);
            }
            
            $video = $this->db->fetchRow($select, null, Zend_Db::FETCH_ASSOC);
            if ($video) {
                
                $now = Zend_Date::now();
                $deleteAt = new Zend_Date( $video['delete_at'], 'YYYY-MM-dd HH:mm:ss' );
                if ($deleteAt->compare($now)>-1){
                	$this->vcacheListingsTable->delete("`rtvg_id`='$rtvg_id'");
                }
                
                $video['published'] = new Zend_Date($video['published'], 'YYYY-MM-dd HH:mm:ss');
                $video['duration']  = new Zend_Date($video['duration'], 'HH:mm:ss');
                $video['thumbs']    = Zend_Json::decode($video['thumbs']);
                
                return $video;
            } 
            
            return false;
            
            
        }
        
    }
    
    /**
     * Save main video for listing item to database
     * 
     * @param Zend_Gdata_YouTube_VideoEntry $video
     */
    public function saveMainVideo($video=null){
    	
        $vModel = new Xmltv_Model_Videos();
        $newRow = $this->vcacheMainTable->createRow( $vModel->parseYtEntry($video));
        $newRow->save();
        return $newRow->toArray();
        
    }
    
    public function saveListingVideo($video=null, $time_hash=null){
    	
        if (!is_array($video) || !$time_hash) {
            throw new Zend_Exception(parent::ERR_WRONG_PARAMS.__METHOD__, 500);
        }
        
        if (APPLICATION_ENV=='development'){
            //var_dump(func_get_args());
            //die(__FILE__.': '.__LINE__);
        }
        
        $row = $video;
        $row['published'] = $video['published']->toString('YYYY-MM-dd HH:mm:ss');
        $row['duration']  = $video['duration']->toString('HH:mm:ss');
        $row['thumbs']    = Zend_Json::encode($video['thumbs']);
        $row['delete_at'] = Zend_Date::now()->addHour(12)->toString("YYYY-MM-dd HH:mm:ss");
        $row['hash'] = $time_hash;
        $row = $this->vcacheListingsTable->createRow( $row);
        
        foreach ($row as $rowK=>$rowVal){
	        $keys[]   = $this->db->quoteIdentifier($rowK);
	        $values[] = "'".str_ireplace("'", '"', $rowVal)."'";
	    }
				    
	    $sql = "INSERT INTO `".$this->vcacheListingsTable->getName()."` ( ".implode(', ', $keys)." ) 
	    VALUES (".implode(', ', $values).") ON DUPLICATE KEY UPDATE `delete_at`='".$row['delete_at']."'";
        
	    if (APPLICATION_ENV=='development'){
	        Zend_Debug::dump($sql);
	        //die(__FILE__.': '.__LINE__);
	    }
	    
	    $this->db->query($sql);
	    
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
    	
        
        $vModel = new Xmltv_Model_Videos();
        
        if ($video && $yt_parent && is_a($video, 'Zend_Gdata_YouTube_VideoEntry')){
        	if (($row = $vModel->parseYtEntry($video))===false){
	            return false;
	        }
        } elseif(is_array($video)) {
            $row = $video;
        } else {
            return false;
        }
        
        $row = $this->vcacheRelatedTable->createRow($row);
        
        $row->yt_parent = $yt_parent;
        $row->published = $row->published->toString('YYYY-MM-dd HH:mm:ss');
        $row->duration  = $row->duration->toString('HH:mm:ss');
        $row->thumbs    = Zend_Json::encode($row->thumbs);
        $row->delete_at = Zend_Date::now()->addDay(7)->toString("YYYY-MM-dd HH:mm:ss");
        
        if (APPLICATION_ENV=='development'){
        	//var_dump($row->toArray());
        	//die(__FILE__.': '.__LINE__);
        }
        
        foreach ($row->toArray() as $rowK=>$rowVal){
	        $keys[]   = $this->db->quoteIdentifier($rowK);
	        $values[] = "'".str_ireplace("'", '"', $rowVal)."'";
	    }
				    
	    $sql = "INSERT INTO `".$this->vcacheRelatedTable->getName()."` ( ".implode(', ', $keys)." ) 
	    VALUES (".implode(', ', $values).") ON DUPLICATE KEY UPDATE `delete_at`='".$row['delete_at']."'";
        
	    if (APPLICATION_ENV=='development'){
	        Zend_Debug::dump($sql);
	        //die(__FILE__.': '.__LINE__);
	    }
	    
	    $this->db->query($sql);
        
	    return $row;
        
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