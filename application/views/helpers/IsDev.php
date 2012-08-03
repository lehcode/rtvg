<?php
class Zend_View_Helper_IsDev extends Zend_View_Helper_Abstract 
{
	function isDev(){
		
		if (strstr($_SERVER['HTTP_HOST'], '.lan'))
			return true;
		
		return false;
	}
}