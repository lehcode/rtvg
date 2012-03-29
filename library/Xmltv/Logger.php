<?php
class Xmltv_Logger 
{
	public static function write($message=null, $priority=null){
		if (!$message) return;
		if (!$priority) $priority=Zend_Log::INFO;
		
		$logger = new Zend_Log();
		$logger->registerErrorHandler();
    	$writer = new Zend_Log_Writer_Stream(ROOT_PATH.'/log/rtvg.log', 'a');
     	$logger->addWriter($writer);
     	$logger->log($message, $priority);
	}
}