<?php
/**
 * Description of Application_Model_ProgramsTest
 *
 * @author Hiragana
 */
class Xmltv_Model_BroadcastsTest extends Xmltv_Model_Broadcasts 
{
    
    public function thisWeekBroadcasts($channel_id=null, Zend_Date $week_start, Zend_Date $week_end){
        
        if (!$channel_id) {
            throw new Zend_Exception ('Channel ID not provided');
        }
        
        if (is_array($channel_id)){
            throw new Zend_Exception ('Channel ID not a digit');
        }
        
        $select = $this->db->select()
            ->from(array('BC'=>$this->bcTable->getName()), 'alias')
            ->joinLeft(array('EVT'=>$this->eventsTable->getName()), "`BC`.`hash`=`EVT`.`hash`", null)
            ->where("`EVT`.`start` >= '".$week_start->toString("YYYY-MM-dd 00:00:00")."'")
            ->where("`EVT`.`start` < '".$week_end->toString("YYYY-MM-dd 23:59:59")."'")
            ->where("`EVT`.`channel` = ".$channel_id)
            ->group("BC.alias")
        ;
        
        if (Zend_Registry::get('adult') !== true){
            $select->where(array(
                "`CH`.`adult` IS NULL",
                "`BC`.`age_rating` <= 16 OR `BC`.`age_rating` = 0",
            ));
        }
        
        $result = $this->db->fetchAll($select);
        return $result;
        
    }
    
    public function getTodayBroadcasts($channel_id=null){
        
        if (!$channel_id) {
            throw new Exception ('Channel ID not provided', 500);
        }
        
        $select = $this->db->select()
            ->from(array('BC'=>$this->bcTable->getName()), 'alias')
            ->joinLeft(array('EVT'=>$this->eventsTable->getName()), "`BC`.`hash`=`EVT`.`hash`", null)
            ->where("`EVT`.`start` >= '".Zend_Date::now()->toString("YYYY-MM-dd 00:00:00")."'")
            ->where("`EVT`.`start` < '".Zend_Date::now()->toString("YYYY-MM-dd 23:59:00")."'")
            ->where("`EVT`.`channel` = ".$channel_id)
        ;
        
        if (Zend_Registry::get('adult') !== true){
            $select->where(array(
                "`CH`.`adult` IS NULL",
                "`BC`.`age_rating` <= 16 OR `BC`.`age_rating` = 0",
            ));
        }
        
        $result = $this->db->fetchAll($select);
        return $result;
        
    }
 
}

?>
