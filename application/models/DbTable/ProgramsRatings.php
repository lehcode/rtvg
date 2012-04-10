<?php

class Xmltv_Model_DbTable_ProgramsRatings extends Zend_Db_Table_Abstract
{

    protected $_name = 'rtvg_programs_ratings';

	public function addHit($alias){
    	
    	if (!$alias)
		throw new Zend_Exception("Не указан один или более параметров для ".__METHOD__, 500);
    	
		$logger = new Zend_Log( new Zend_Log_Writer_Stream( ROOT_PATH . '/log/exceptions.log' ) );
		
		if (!$row = $this->find($alias)->current())
		$row = $this->createRow(array('title_alias'=>$alias), true);
		
		$row->hits+=1;
		try {
			$row->save();
		} catch (Exception $e) {
			if ($e->getCode()!=1062){
				$logger->debug( $e->getMessage() . PHP_EOL . $e->getTraceAsString() );
			}
		}
		
		
    }

}

