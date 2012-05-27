<?php

class Zend_View_Helper_TruncateString extends Zend_View_Helper_Abstract
{

	public function truncateString ($string, $length=10, $mode='letters') {
				
		switch ($mode) {
			case 'words':
				$parts = explode(' ', $string);
				$c = count($parts)-1;
				if (count($parts)>$length) {
					do {
						unset($parts[$c]);
						$c--;
					} while (count($parts)>$length);
					return implode(' ', $parts).'…';
				} else {
					return $string;
				}
				break;
			default:
				if (Xmltv_String::strlen($string)<=$length)
				return $string;
				else
				return Xmltv_String::substr($string, 0, $length-1).'…';
		}
	}
}