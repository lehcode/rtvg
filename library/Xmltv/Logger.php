<?php
class Xmltv_Logger 
{
	public static function write($message=null, $priority=null, $filename=null){
		
		if (!$message) return;
		if (!$priority) $priority=Zend_Log::INFO;
		
		$logger = new Zend_Log();
		$logger->registerErrorHandler();
		$filename = !empty($filename) ? $filename : 'rtvg.log' ;
    	$writer = new Zend_Log_Writer_Stream(APPLICATION_PATH.'/../log/'.$filename, 'a');
     	$logger->addWriter($writer);
     	$logger->log($message, $priority);
     	
	}
}