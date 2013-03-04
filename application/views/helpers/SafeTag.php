<?php
/**
 * 
 * @author Antony Repin <egeshisolutions@gmail.com>
 * @version $Id: SafeTag.php,v 1.4 2013-03-04 17:57:51 developer Exp $
 *
 */
class Zend_View_helper_SafeTag extends Zend_View_Helper_Abstract
{
	public function safeTag($tag=null){
		
		if (!$tag)
			throw new Zend_Exception("Не указан один или более параметров для ".__METHOD__, 500);
			
		$trim       = new Zend_Filter_StringTrim(' -');
		$separator  = new Zend_Filter_Word_SeparatorToDash(' ');
		$doubledash = new Zend_Filter_PregReplace(array("match"=>'/[-]{2,}/', 'replace'=>'-'));
		
		$result = preg_replace('/[^\p{Cyrillic}\p{Latin}0-9 -]+/u', ' ', $tag);
		$result = Xmltv_String::strtolower( $trim->filter( $doubledash->filter( $separator->filter( $result ))));
		$escape = new Zend_Filter_HtmlEntities();
		return $escape->filter($result);
		
	}
}