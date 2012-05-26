<?php
class Zend_View_Helper_TruncateProgramTitle extends Zend_View_Helper_Abstract
{
	public function truncateProgramTitle($title=null, $max_len=40){
		
		if (!$title)
		throw new Zend_Exception("Не указан один или более параметров для ".__FUNCTION__, 500);
		//var_dump($title);
		//var_dump(Xmltv_String::strlen($title));
		if (Xmltv_String::strlen($title)>$max_len) {
			$parts = explode(' ', $title);
			$new_title = '';
			foreach ($parts as $word){
				if ((Xmltv_String::strlen(trim($new_title))+Xmltv_String::strlen(trim($word))) <= $max_len)
				$new_title.=" $word";
				else
				return trim($new_title, '. ').'&hellip;';
			}
		} else {
			return trim($title, '. ');
		}
		
	}
}