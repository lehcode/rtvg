<?php
class Zend_View_helper_VideoId extends Zend_View_Helper_Abstract
{
	public function videoId($yt_id=null){
		
		if (!$yt_id)
		throw new Zend_Exception("Не указан один или более параметров для ".__FUNCTION__, 500);
		
		return strrev( str_replace( "%3D", "", urlencode( base64_encode( (string)$yt_id))));
		
	}
}