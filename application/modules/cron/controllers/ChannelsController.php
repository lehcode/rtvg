<?php
/*
 * Channels CRON tasks class
 */

/**
 * Channels CRON actions
 *
 * @author Hiragana
 */
class Xmltv_Cron_ChannelsControler extends Zend_Controller_Action
{
    
    protected $channelsModel;
    
    public function init(){
        $this->channelsModel = new Xmltv_Model_DbTable_Channels();
    }
    
    public function cleanEmptyChannelsAction(){
        
        die(__FILE__.': '.__LINE__);
        
    }
    
}

?>
