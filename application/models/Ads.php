<?php
class Xmltv_Model_Ads extends Xmltv_Model_Abstract
{
    
    /**
     * Get a random banner code from database
     * by size and optional tags
     * 
     * @param int   $width
     * @param int   $height
     * @param array $tags // optional
     */
    public function pickRandom($width=null, $height=null, array $tags=null){
    	
        if (APPLICATION_ENV=='development'){
        	die(__FILE__.': '.__LINE__);
        }
        
    }
    
}