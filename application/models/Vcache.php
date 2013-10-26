<?php
/**
 * Video cache model class
 */
class Xmltv_Model_Vcache extends Xmltv_Model_Abstract 
{
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
            	->from(array('VID'=>$this->vcacheMainTable->getName()), array(
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
                ->join(array('VCAT'=>$this->ytCategoriesTable->getName()), "`VID`.`category` = `VCAT`.`title_en`", null)
                ->join(array('CHCAT'=>  $this->channelsCategoriesTable->getName()), "VCAT.ch_cat_id = CHCAT.id", array(
                    'channel_cat_id'=>'id',
                    'channel_cat_title'=>'title',
                    'channel_cat_alias'=>'alias',
                    'channel_image'=>'image',
                ))
                ->join(array('BCCAT'=>  $this->bcCategoriesTable->getName()), "VCAT.bc_cat_id = BCCAT.id", array(
                    'bc_cat_id'=>'id',
                    'bc_cat_title'=>'title',
                    'bc_cat_title_single'=>'title_single',
                    'bc_cat_alias'=>'alias',
                ))
                ->join(array('CONTCAT'=>  $this->contentCategoriesTable->getName()), "VCAT.content_cat_id = CONTCAT.id", array(
                    'content_cat_id'=>'id',
                    'content_cat_title'=>'title',
                    'content_cat_alias'=>'alias',
                ))
                ->where("VID.rtvg_id = ".$this->db->quote($rtvg_id))
            	->limit(1);
            
            $result = $this->db->fetchRow($select);
            
            if (empty($result)){
                return $result;
            }
            
            $deleteAt = new Zend_Date( $result['delete_at'], 'YYYY-MM-dd HH:mm:ss' );
            if ($deleteAt->compare(Zend_Date::now()) != -1){
            	$this->vcacheListingsTable->delete("`rtvg_id`='$rtvg_id'");
            }
            
            $result['published'] = new Zend_Date($result['published'], 'YYYY-MM-dd');
            $result['duration'] = new Zend_Date($result['duration'], 'HH:mm:ss');
            $result['thumbs'] = unserialize($result['thumbs']);
            $result['views'] = (int)$result['views'];
            $result['channel_cat_id'] = (int)$result['channel_cat_id'];
            $result['bc_cat_id'] = (int)$result['bc_cat_id'];
            $result['content_cat_id'] = (int)$result['content_cat_id'];
            
            return $result;
            
        }
        
    }
    
    /**
     * Save main video for listing item to database
     * 
     * @param Zend_Gdata_YouTube_VideoEntry|array $video
     */
    public function saveMainVideo($video=null){
    	
        if (!$video){
            throw new Zend_Exception( Rtvg_Message::ERR_WRONG_PARAM );
        }
        
        if (!is_array($video)){
            throw new Zend_Exception('$video must be an array');
        }
        
        $row = $this->vcacheMainTable->createRow( $video );
        $row->category = strtolower($video['category']);
        $row->published = $video['published']->toString('YYYY-MM-dd');
        $row->duration = $video['duration']->toString('HH:mm:ss');
        $row->thumbs = serialize($video['thumbs']);
        $row->delete_at = Zend_Date::now()->addDay(7)->toString('YYYY-MM-dd HH:mm:ss');
        
        try {
            $row->save();
        } catch (Exception $e) {
            if($e->getCode()==23000){
                $this->vcacheMainTable->delete("yt_id = ".$this->db->quote($video['yt_id']));
                $row->save();
            }
        }
        
        return $row->toArray();
        
    }
    
    /**
     * Save program listing video to databasse cache
     * 
     * @param  array $video
     * @throws Zend_Exception
     * @return unknown
     */
    public function saveListingVideo($video=null){
    	
        if (!is_array($video)) {
            throw new Zend_Exception( Rtvg_Message::ERR_WRONG_PARAM, 500);
        }
        
        $row = $this->vcacheListingsTable->createRow( $video );
        $row->published = $video['published']->toString( 'YYYY-MM-dd HH:mm:ss' );
        $row->duration = $video['duration']->toString( 'HH:mm:ss' );
        $row->thumbs = Zend_Json::encode( $video['thumbs'] );
        $row->delete_at = Zend_Date::now()->addHour(72)->toString("YYYY-MM-dd HH:mm:ss");
        $row->hash = $video['hash'];
        $row->category = strtolower($video['category']);
        
        if (!$row->category){
            $chCat = $this->channelsCategoriesTable->fetchOne("alias='разное'");
            $ytCat = $this->ytCategoriesTable->fetchOne("ch_cat_id=".(int)$chCat['id']);
            $row->category = $ytCat['title_en'];
        }
        
        try{
            $row->save();
        } catch (Exception $e){
            
            $catExists = $this->ytCategoriesTable->find($row->category);
            if(count($catExists)==0){
                throw new Zend_Exception("Video category '".$row->category."' does not exist");
            } elseif($e->getCode()==23000){
                $this->vcacheMainTable->delete("yt_id = ".$this->db->quote($row->yt_id));
                $row->save();
            } else {
                throw new Zend_Exception("Cannot save row", 500, $e);
            }
            
        }
        
        return $row->toArray();
        
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
        
        foreach ($row->toArray() as $rowK=>$rowVal){
	        $keys[]   = $this->db->quoteIdentifier($rowK);
	        $values[] = "'".str_ireplace("'", '"', $rowVal)."'";
	    }
				    
	    $sql = "INSERT INTO `".$this->vcacheRelatedTable->getName()."` ( ".implode(', ', $keys)." ) 
	    VALUES (".implode(', ', $values).") ON DUPLICATE KEY UPDATE `delete_at`='".$row['delete_at']."'";
        
	    $this->db->query($sql);
        
	    return $row;
        
    }

    /**
     * Save video to sidebar DB cache table
     * 
     * @param  array  $video
     * @param  int $channel_id
     * @return boolean|array
     */
    public function saveSidebarVideo($video=null, $channel_id=null){
    	
        $vModel = new Xmltv_Model_Videos();
        
        if (!$video || !$channel_id){
            throw new Zend_Exception( Rtvg_Message::ERR_MISSING_PARAM );
        }
        
        if (is_a( $video, 'Zend_Gdata_YouTube_VideoEntry')){
        	if (($row = $vModel->parseYtEntry($video))===false){
	            return false;
	        }
        } elseif(is_array($video)) {
            $row = $video;
        } else {
            return false;
        }
        
        $row = $this->vcacheSidebarTable->createRow($row);
        
        $row->channel   = $channel_id;
        $row->published = $video['published']->toString('YYYY-MM-dd HH:mm:ss');
        $row->duration  = $video['duration']->toString('HH:mm:ss');
        $row->thumbs    = serialize( $video['thumbs'] );
        $row->delete_at = Zend_Date::now()->addDay(7)->toString( "YYYY-MM-dd HH:mm:ss" );
        
        foreach ($row->toArray() as $rowK=>$rowVal){
	        $keys[]   = $this->db->quoteIdentifier($rowK);
	        $values[] = "'".str_ireplace("'", '"', $rowVal)."'";
	    }
				    
	    $sql = "INSERT INTO `".$this->vcacheSidebarTable->getName()."` ( ".implode(', ', $keys)." ) 
	    VALUES (".implode(', ', $values).") ON DUPLICATE KEY UPDATE `delete_at`='".$row['delete_at']."'";
        
	    $this->db->query($sql);
        
	    return $row->toArray();
        
    }
    
    /**
     * Fetch related videos info from DB cache
     * 
     * @param  string $yt_id
     * @return array
     */
    public function getRelated( $yt_id=null, $limit=10 ){
    	
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
        
        $result = $this->db->fetchAll($select, null, Zend_Db::FETCH_ASSOC);
        
        if (count($result)) {
            return $result;
        }
        
        return false;
        
    }
    
    /**
     * Load sidebar videos from database cache
     * 
     * @param int $channel_id
     */
    public function sidebarVideos( $channel_id=null ){
    	
        if (!$channel_id){
            throw new Zend_Exception( Rtvg_Message::ERR_MISSING_PARAM );
        }

        if (!is_numeric($channel_id)){
            throw new Zend_Exception( Rtvg_Message::ERR_WRONG_PARAM );
        }
        
        $select = $this->db->select()
        	->from(array('vid'=>$this->vcacheSidebarTable->getName()), array(
        		'yt_id',
        		'rtvg_id',
        		'title',
        		'alias',
        		'desc',
        		'views',
        		'published',
        		'duration',
        		'category',
        		'thumbs',
        		'delete_at',
        	))
        	->where("`vid`.`channel`='$channel_id'");
        
        $result = $this->db->fetchAll( $select  );
        
        if (!count($result)){
            return false;
        }
        
        foreach ($result as $k=>$row) {
            $result[$k]['published'] = new Zend_Date( $row['published'], 'YYYY-MM-dd' );
            $result[$k]['duration']  = new Zend_Date( $row['duration'], 'HH:mm:ss' );
            $result[$k]['thumbs']    = unserialize( $row['thumbs'] );
        }
        
        return $result;
        
    }
    
    /**
     * Load listing-related videos for particular day
     * from database cache
     *
     * @param  array $list // Videos
     * @param  string $channel_title
     * @param  Zend_Date $date
     * @throws Zend_Exception
     */
    public function listingRelatedVideos(array $list=null, $channel_title, Zend_Date $date){
    	
        if (empty($list) || !is_array($list)) {
        	throw new Zend_Exception( Rtvg_Message::ERR_WRONG_PARAM, 500);
        }
        
        if ($date->isToday()){
        	foreach ($list as $k=>$li){
        		if ($li['now_showing']===false){
        			unset($list[$k]);
        		} else {
        			break;
        		}
        	}
        }
        
        if (!count($list) || $list==false || !$list){
            return false;
        }
        // Collect hashes
        $hashes = array();
        foreach ($list as $k=>$prog){
        	$hashes[] = $this->db->quote($prog['hash']);
        }
        
        $select = $this->db->select()
            ->from( array('video'=>$this->vcacheListingsTable->getName()), array(
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
        		'delete_at',
        		'hash',
            ))
            ->where( "`video`.`hash` IN ( \n".implode(",\n", $hashes)." )")
        ;
        
        $cached = $this->db->fetchAll( $select, null, Zend_Db::FETCH_ASSOC);
        
        if (count($cached)){
        
        	$now = Zend_Date::now();
        	foreach ($cached as $p){
        		 
        		$deleteAt = new Zend_Date( $p['delete_at'], 'YYYY-MM-dd HH:mm:ss' );
        		if ($now->compare($deleteAt) == -1){ // now is earlier than deletion date
        			$result[$p['hash']] = $p;
        			$result[$p['hash']]['published'] = new Zend_Date( $p['published'], 'YYYY-MM-dd HH:mm:ss');
        			$result[$p['hash']]['duration']  = new Zend_Date( $p['duration'], 'HH:mm:ss');
        			$result[$p['hash']]['thumbs']	 = Zend_Json::decode( $p['thumbs']);
        		} else { 
                    // delete from cache if now is later than deletion date
        			$this->vcacheListingsTable->delete("`hash`='".$p['hash']."'");
        		}
        	}
        }
        
        if (!count($result)){
            return false;
        }
        
        return $result;
        
    }
    
}