<?php
/**
 * Description of Application_Model_ProgramsTest
 *
 * @author Hiragana
 */
class Xmltv_Model_ProgramsTest extends Xmltv_Model_Programs 
{
    
    public function thisWeekBroadcasts($channel_id=null, $week_start, $week_end){
        
        if (!$channel_id) {
            throw new Exception ('Channel ID not provided', 500);
        }
        
        $select = $this->db->select()
            ->from(array('BC'=>$this->bcTable->getName()), 'alias')
            ->joinLeft(array('EVT'=>$this->eventsTable->getName()), "`BC`.`hash`=`EVT`.`hash`", null)
            ->where("`EVT`.`start` >= '".$week_start->toString("YYYY-MM-dd 00:00:00")."'")
            ->where("`EVT`.`start` < '".$week_end->toString("YYYY-MM-dd 23:59:59")."'")
            ->where("`EVT`.`channel` = ".$channel_id)
            ->group("BC.alias")
        ;
        
        if (Zend_Registry::get('adult')!==true){
            $select->where("`CH`.`adult` = FALSE");
        }
        
        $result = $this->db->fetchAll($select);
        return $result;
        
    }
 
}

?>