<?php
class Zend_View_helper_VideoAlias extends Zend_View_Helper_Abstract
{
	public function videoAlias($title=null){
		
		if (!$title)
		throw new Zend_Exception("Не указан один или более параметров для ".__METHOD__, 500);
		
		$trim       = new Zend_Filter_StringTrim(' -');
		$separator  = new Zend_Filter_Word_SeparatorToDash(' ');
		//$regex      = new Zend_Filter_PregReplace(array("match"=>'/["\'.,:;-\?\{\}\[\]\!`\/\(\)]+/', 'replace'=>' '));
		//$regex      = new Zend_Filter_PregReplace(array("match"=>'/[^\p{Latin}\p{Cyrillic}\p{N} -]+/', 'replace'=>' '));
		$tolower    = new Zend_Filter_StringToLower();
		$doubledash = new Zend_Filter_PregReplace(array("match"=>'/[-]+/', 'replace'=>'-'));
		//$cyrlatin   = new Zend_Filter_PregReplace(array("match"=>'/[^\p{Latin}\p{Cyrillic}\p{N} -]+/ui', 'replace'=>''));
		
		$title = preg_replace('/[^\p{Latin}\p{Cyrillic}\p{N} -]+/ui', ' ', $title);
		
		$result = $tolower->filter( $trim->filter( $doubledash->filter( $separator->filter( $title ))));
		
		//if (preg_match('/[^\p{Latin}\p{Cyrillic}\p{N} -]+/ui', $result))
		//$result = $cyrlatin->filter($result);
		//$result = preg_replace($pattern, $replacement, $subject)
		
		return $result;
		
	}
}