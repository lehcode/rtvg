<?php
class Xmltv_Filesystem_Folder 
{
	public static function exists($path=null) {
		if (!$path) return;
		return is_dir(Xmltv_Filesystem_Path::clean($path));
	}
	
	public static function files($path, $filter = '.', $recurse = false, $fullpath = false, $exclude = array('.svn', 'CVS'))
	{
		// Initialize variables
		$arr = array();

		// Check to make sure the path valid and clean
		$path = Xmltv_Filesystem_Path::clean($path);

		// Is the path a folder?
		if (!is_dir($path))
		throw new Exception(__METHOD__.': Path is not a folder');
		
		// read the source directory
		$handle = opendir($path);
		while (($file = readdir($handle)) !== false)
		{
			if (($file != '.') && ($file != '..') && (!in_array($file, $exclude))) {
				$dir = $path . DS . $file;
				$isDir = is_dir($dir);
				if ($isDir) {
					if ($recurse) {
						if (is_integer($recurse)) {
							$arr2 = self::files($dir, $filter, $recurse - 1, $fullpath);
						} else {
							$arr2 = self::files($dir, $filter, $recurse, $fullpath);
						}
						
						$arr = array_merge($arr, $arr2);
					}
				} else {
					if (preg_match("/$filter/", $file)) {
						if ($fullpath) {
							$arr[] = $path . DS . $file;
						} else {
							$arr[] = $file;
						}
					}
				}
			}
		}
		closedir($handle);

		asort($arr);
		return $arr;
	}
	
	public static function create($path = '', $mode = 0755) {
		// Initialise variables.
		//$FTPOptions = JClientHelper::getCredentials ( 'ftp' );
		static $nested = 0;
		
		// Check to make sure the path valid and clean
		$path = Xmltv_Filesystem_Path::clean ( $path );
		
		// Check if parent dir exists
		$parent = dirname ( $path );
		if (! self::exists ( $parent )) {
			
			// Prevent infinite loops!
			$nested ++;
			if (($nested > 20) || ($parent == $path))
				throw new Exception ( 'Обнаружен бесконечный цикл' );
				
			// Create the parent directory
			if (self::create ( $parent, $mode ) !== true) {
				// JFolder::create throws an error
				$nested --;
				return false;
			}
			
			// OK, parent directory has been created
			$nested --;
		}
		
		// Check if dir already exists
		if (self::exists ( $path )) {
			return true;
		}
		
		// We need to get and explode the open_basedir paths
		$obd = ini_get ( 'open_basedir' );
		
		// If open_basedir is set we need to get the open_basedir that the path is in
		if ($obd != null) {
			// Create the array of open_basedir paths
			$obdArray = explode ( ":", $obd );
			$inBaseDir = false;
			// Iterate through open_basedir paths looking for a match
			foreach ( $obdArray as $test ) {
				$test = Xmltv_Filesystem_Path::clean ( $test );
				if (strpos ( $path, $test ) === 0) {
					$inBaseDir = true;
					break;
				}
			}
			if ($inBaseDir == false)
				throw new Exception ( 'Путь не в пределах значения переменной open_basedir' );
		
		}
		
		// First set umask
		$origmask = @umask ( 0 );
		
		// Create the path
		if (! $ret = @mkdir ( $path, $mode )) {
			@umask ( $origmask );
			throw new Exception ( 'Не удалось создать каталог' );
		}
		
		// Reset umask
		@umask ( $origmask );
		
		return $ret;
	}
	
}