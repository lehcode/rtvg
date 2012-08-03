<?php
class Zend_View_helper_SafeTag extends Zend_View_Helper_Abstract
{
	public function safeTag($tag=null){
		
		if (!$tag)
			throw new Zend_Exception("Не указан один или более параметров для ".__METHOD__, 500);
			
		//var_dump(func_get_args());
		//die(__FILE__.': '.__LINE__);
		
		$trim       = new Zend_Filter_StringTrim(' -');
		//$regex      = new Zend_Filter_PregReplace(array("match"=>'/[^\w]+/u', 'replace'=>' '));
		$separator  = new Zend_Filter_Word_SeparatorToDash(' ');
		$tolower    = new Zend_Filter_StringToLower();
		$doubledash = new Zend_Filter_PregReplace(array("match"=>'/[-]{2,}/', 'replace'=>'-'));
		
		//var_dump($tag);
		//var_dump($regex->filter($tag));
		//die(__FILE__.': '.__LINE__);
		
		$result = preg_replace('/[^0-9\p{Cyrillic}\p{Latin}]+/u', ' ', $tag);
		
		if (@$_GET[d]==1) {
			//var_dump($tag);
			//var_dump($result);
			//die(__FILE__.': '.__LINE__);
		}
		
		$result = $tolower->filter( $trim->filter( $doubledash->filter( $separator->filter( $result ))));
		
		return $result;
		
	}
}