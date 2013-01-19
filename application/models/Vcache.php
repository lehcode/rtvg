<?php
class Xmltv_Model_Vcache extends Xmltv_Model_Abstract {
    
    protected $mainTable;
    protected $relatedTable;
    
    /**
     * Model constructor
     * 
     * @param array $config
     */
    public function __construct($config=array()){
        
        $config['db'] = Zend_Registry::get('app_config')->resources->multidb->local;
        parent::__construct($config);
        $this->mainTable    = new Xmltv_Model_DbTable_VcacheMain( array('tbl_prefix'=>$this->dbConf->get('tbl_prefix')));
        $this->relatedTable = new Xmltv_Model_DbTable_VcacheRelated( array('tbl_prefix'=>$this->dbConf->get('tbl_prefix')));
        
        /**
         * @todo
         * $this->sidebarTable = new Xmltv_Model_DbTable_VcacheMain();
         */
        
        
    }
    
    /**
     * Load video from cache
     * 
     * @param  string $rtvgId
     * @return object|NULL
     */
    public function getVideo($rtvgId=null){
        
        if ($rtvgId){
            
            $result   = $this->mainTable->fetch($rtvgId);
            
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
                        $this->mainTable->delete("`rtvg_id`='$rtvgId'");
                    } catch (Zend_Db_Table_Exception $e) {
                        throw new Zend_Exception($e->getMessage(), $e->getCode(), $e);
                    }
                    return null;
                }
            }
        }
        
    }
    
    /**
     * 
     * @param Zend_Gdata_YouTube_VideoEntry $video
     */
    public function saveMainVideo($video=null){
    	
        $vModel = new Xmltv_Model_Videos();
        $table = new Xmltv_Model_DbTable_VcacheMain();
        if ($video && is_a($video, 'Zend_Gdata_YouTube_VideoEntry')){
            
            $props = $vModel->objectToArray( $vModel->parseYtEntry($video));
            try {
                $result = $table->store($props);
            } catch (Zend_Db_Table_Exception $e) {
                throw new Zend_Exception($e->getMessage(), $e->getCode(), $e);
            }
            
            return $props;
            
        }
        
    }
    
    
    /**
     * 
     * Save related video entry to database cache
     * 
     * @param  Zend_Gdata_YouTube_VideoEntry $video
     * @param  string                        $yt_parent
     * @throws Zend_Exception
     * @return array
     */
    public function saveRelatedVideo($video=null, $yt_parent=null){
    	
        if ($yt_parent){
        
	        $table  = new Xmltv_Model_DbTable_VcacheRelated();
	        $vModel = new Xmltv_Model_Videos();
	        
	        if ($video && is_a($video, 'Zend_Gdata_YouTube_VideoEntry')){
	            
	            $props = $vModel->objectToArray( $vModel->parseYtEntry($video));
	            $props['yt_parent'] = $yt_parent;
	            
	            try {
	                $result = $table->store($props);
	            } catch (Zend_Db_Table_Exception $e) {
	                return $props;
	            }
	            return $props;
	        }
        }
        
    }
    
    /**
     * Fetch related videos info from DB cache
     * 
     * @param  string $yt_id
     * @return array
     */
    public function getRelated( $yt_id=null, $limit=10 ){
    	
        if($yt_id){
            return $this->relatedTable->fetchAll("`yt_parent`='$yt_id'", null, $limit)->toArray();
        }
        
    }
    
}