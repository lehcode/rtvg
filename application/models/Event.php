<?php
class Xmltv_Model_Event extends Xmltv_Model_Abstract
{
    protected $eventsTable;
    
    public function __construct($config = array())
    {
        parent::__construct($config);
        $this->eventsTable = new Xmltv_Model_DbTable_Events();
    }
    
    /**
     * Add broadcast event
     * @param array $data
     * @return Zend_Db_Table_Row
     */
    public function create(array $data=array()){
        return $this->eventsTable->createRow( $data );
    }
    
    public function get($hash){
        die(__FILE__.': '.__LINE__);
    }
}