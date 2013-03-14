<?php
/**
 * Model which is serving sitemaps
 *
 * @version $Id: Sitemap.php,v 1.1 2013-03-14 11:42:05 developer Exp $
 */
class Xmltv_Model_Sitemap extends Xmltv_Model_Abstract
{
    
    public function __construct($config=array()){
        
        parent::__construct($config);
        
    }
    
    public function weekListing(Zend_Date $week_start=null, Zend_Date $week_end=null){
    	
        $select = $this->db->select()
        	->from(array('prog'=>$this->programsTable->getName()), 'alias')
        	->join(array('channel'=>$this->channelsTable->getName()), "`prog`.`channel`=`channel`.`id`", array(
        		'channel_alias'=>'LOWER(channel.alias)',
        	))
        	->join(array('rating'=>$this->channelsRatingsTable->getName()), "`prog`.`channel`=`rating`.`id`", null)
        	->where("`prog`.`start` >= '".$week_start->toString("YYYY-MM-dd")." 00:00'")
        	->where("`prog`.`start` < '".$week_end->toString("YYYY-MM-dd")." 23:59'")
        	->where("`channel`.`adult` = 0")
        	->group("prog.alias")
        	->order("prog.start ASC");
        
        if (APPLICATION_ENV=='development'){
            //parent::debugSelect($select, __METHOD__);
            //die(__FILE__.': '.__LINE__);
        }
        
        $result = $this->db->fetchAll($select, null, Zend_Db::FETCH_ASSOC);
        
        if (!count($result)){
            return false;
        }
        
        return $result;
        
    }
    
}