<?php

class Zend_View_Helper_TruncateString extends Zend_View_Helper_Abstract
{

	public function truncateString ($string, $length=10, $mode='letters') {
		
		//var_dump(func_get_args());
		
		switch ($mode) {
			case 'words':
				$parts = explode(' ', $string);
				$c=count($parts)-1;
				if (count($parts)>$length) {
					do {
						unset($parts[$c]);
						$c--;
					} while (count($parts)>$length);
					return implode(' ', $parts).'…';
				} else {
					return $string;
				}
				//var_dump($parts);
				//die(__FILE__.': '.__LINE__);
				break;
			default:
				return Xmltv_String::substr($this->_input, 0, $length).'…';
		}
		
		
		//return $this->_input;
		//die(__FILE__.': '.__LINE__);
	}
}