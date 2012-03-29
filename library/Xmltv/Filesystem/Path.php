<?php
class Xmltv_Filesystem_Path {
	
	public static function clean($path)
	{
		$path = trim($path);
		if (empty($path)) {
			$path = ROOT_PATH.'/public';
		} else {
			// Remove double slashes and backslahses and convert all slashes and backslashes to DS
			$path = preg_replace('#[/\\\\]+#', '/', $path);
		}

		return $path;
	}
}