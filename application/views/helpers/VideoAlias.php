<?php
class Zend_View_helper_VideoAlias extends Zend_View_Helper_Abstract
{
	public function videoAlias($title=null){
		
		if (!$title)
		throw new Zend_Exception("Не указан один или более параметров для ".__METHOD__, 500);
		
		$trim       = new Zend_Filter_StringTrim(' "\'.,:-?!(){}[]`');
		$separator  = new Zend_Filter_Word_SeparatorToDash(' ');
		$regex      = new Zend_Filter_PregReplace(array("match"=>'/["\'.,:-\?\{\}\[\]\!`]+/', 'replace'=>' '));
		$tolower    = new Zend_Filter_StringToLower();
		$doubledash = new Zend_Filter_PregReplace(array("match"=>'/[-]+/', 'replace'=>'-'));
		
		$result = $tolower->filter( $doubledash->filter( $trim->filter( $separator->filter( $regex->filter($title)))));
		
		return $result;
		
	}
}