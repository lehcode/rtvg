<?php
class Rtvg_Comment_Collection extends Zend_Db_Table_Rowset_Abstract {
    
    public function __construct($config=array()){
        parent::__construct($config);
    }
}