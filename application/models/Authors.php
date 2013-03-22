<?php
class Xmltv_Model_Authors extends Xmltv_Model_Abstract
{
    
    /**
     *
     * @var Xmltv_Model_DbTable_Users
     */
    protected $table;
    
    public function __construct($config=array()){
    
    	$config['db'] = Zend_Registry::get('app_config')->resources->multidb->local;
    	parent::__construct($config);
    	$this->table = new Xmltv_Model_DbTable_Users();
    
    }
    
    public function allAuthors($as_array=false){
    	
        $result = $this->table->fetchAll("`role`='publisher' OR `role`='editor' OR `role`='god'");
        if ($as_array===true){
            return $result->toArray();
        }
        return $result;
        
    }
}