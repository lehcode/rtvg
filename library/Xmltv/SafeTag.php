<?php
class Xmltv_SafeTag {
	
	public static function convertTitle($input=''){
		
		if (!$input)
			return '';
			
		$trim       = new Zend_Filter_StringTrim(' -');
		$separator  = new Zend_Filter_Word_SeparatorToDash(' ');
		$tolower    = new Zend_Filter_StringToLower();
		$doubledash = new Zend_Filter_PregReplace(array("match"=>'/[-]{2,}/', 'replace'=>'-'));
		
		$result = preg_replace('/[^0-9\p{Cyrillic}\p{Latin}]+/u', ' ', $input);
		$result = $tolower->filter( $trim->filter( $doubledash->filter( $separator->filter( $result ))));
		
		return $result;
		
	}
	
}