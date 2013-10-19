<?php
class Rtvg_Counters {
    
    
    private $_data   = array();
    private $_folder = '/public/js/ss/counters';
    
    const IDX_LIVEINTERNET='li';
    const IDX_GOOGLE='gg';
    
    /**
     * Counters management
     * 
     * @param string $folder // from which folder to load scripts
     */
    public function __construct($folder=null){
    	
        $f = APPLICATION_PATH.'/../public/js/ss/counters';
        if (false !== ($h = opendir($f))) {
        	while (false !== ($e = readdir($h))) {
        		if ($e != "." && $e != "..") {
        		    var_dump($e);
        			//$this->_setData(file_get_contents($f.'/'.$e));
        		}
        	}
        	closedir($h);
        }
        
    }
    
    
    public function getHeadJs($idx=null){
    	
        
    }

    public function getInlineJs($idx=null){
    	
        
    }
    
    public function getCounterImage($idx=null){
    	
        
    }
    
}