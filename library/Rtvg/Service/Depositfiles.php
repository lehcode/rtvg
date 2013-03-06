<?php
class Rtvg_Depositfiles extends Zend_Service_Abstract
{
    public function __construct(){
    	
        
        $this->setHttpClient($httpClient);
        
    }
}