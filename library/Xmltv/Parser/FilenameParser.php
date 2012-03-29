<?php
class Xmltv_Parser_FilenameParser extends Zend_Controller_Action_Helper_Abstract
{	
	public static function stripExt($file=null) {
		if (!$file) return;
		return preg_replace('#\.[^.]*$#', '', $file);
	}
	
	
	public static function getExt($file=null) {
		if (!$file) return;
		$chunks = explode('.', $file);
		$chunksCount = count($chunks) - 1;
		
		if($chunksCount > 0)
		return $chunks[$chunksCount];
		
		return false;
	}
	
	public static function getXmlFileName($file=null){
		if (!$file) return;
		return preg_replace('/(\..+)$/', '', self::stripExt(self::getFileName($file)));
	}
	
	public static function getFileName($file=null) {
		$slash = strrpos($file, '/');
		if ($slash !== false)
		return substr($file, $slash + 1);
		else
		return $file;
	}
	
}