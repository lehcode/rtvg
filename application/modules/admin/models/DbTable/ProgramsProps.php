<?php

class Admin_Model_DbTable_ProgramsProps extends Xmltv_Model_DbTable_ProgramsProps
{

    protected $_name = 'programs_props';
    
    public function getByHash($hash=null){
    	
    	if( empty( $hash ) ) 
		throw new Exception("Пропущен один или более параметров для ".__METHOD__, 500);
		
		return $this->find($hash);
		
	}
    
}

