<?php
class Xmltv_Filesystem_File {
	
	/**
	 * Makes file name safe to use
	 * 
	 * @param string $file  The name of the file [not full path]
	 * @return string  The sanitised string 
	 */
	public static function makeSafe($file = null) {
		return preg_replace ( array ('#(\.){2,}#', '#[^A-Za-z0-9\.\_\- ]#', '#^\.#' ), '', $file );
	}
	
	
	/**
	 * Copies a file
	 * 
	 * @param string $src Source file name
	 * @param string $dest Target file name
	 * @param string $path Optional: Base path
	 * @return string
	 */
	public static function copy($src, $dest, $basePath = null) {
		// Prepend a base path if it exists
		if ($basePath) {
			$src  = Xmltv_Filesystem_Path::clean ( $basePath . '/' . $src );
			$dest = Xmltv_Filesystem_Path::clean ( $basePath . '/' . $dest );
		}
		
		// Check src path
		if (!is_readable ($src))
		throw new Exception(printf(__METHOD__.': Не удается найти или прочитать файл: $%s', $src));
		
		if (!@copy($src,$dest))
		throw new Exception('Копирование не удалось');
		
		$ret = true;
		
		return $ret;
	}
	
	/**
	 * Delete a file or array of files
	 * 
	 * @param string|array $file The file name or an array of file names
	 * @return True on success
	 */
	public static function delete($file) {
		
		if (is_array ( $file )) {
			$files = $file;
		} else {
			$files [] = $file;
		}
		foreach ( $files as $file ) {
			$file = JPath::clean ( $file );
			
			// Try making the file writable first.
			@chmod ( $file, 0777 );
			
			// In case of restricted permissions we zap it one way or the other
			// as long as the owner is either the webserver or the ftp
			if (!@unlink ( $file )) {
				$filename = basename ( $file );
				throw new Exception ( printf (__METHOD__. ': Не удалось удалить %s', $filename ) );
			} 
		}
		
		return true;
	}
	
	/**
	 * Moves a file
	 * 
	 * @param string $src
	 * @param string $dest
	 * @param string $path
	 * @return bool
	 */
	public static function move($src, $dest, $rootPath = '') {
		
		if ($rootPath) {
			$src  = Xmltv_Filesystem_Path::clean ( $rootPath . '/' . $src );
			$dest = Xmltv_Filesystem_Path::clean ( $rootPath . '/' . $dest );
		}
		
		// Check src path
		if (! is_readable ( $src )) 
		throw new Exception('Не удается найти исходный файл');
		
		if (! @ rename ( $src, $dest ))
		throw new Exception('Переименовать не удалось');
		
		return true;
	}
	
	/**
	 * @param string $filename   The full file path
	 * @param bool   $incpath    Use include path
	 * @param int    $amount     Amount of file to read
	 * @param int    $chunksize  Size of chunks to read
	 * @param int    $offset     Offset of the file
	 * @return string Returns file contents or throws exception if failed
	 */
	public static function read($filename, $incpath = false, $amount = 0, $chunksize = 8192, $offset = 0) {
		// Initialise variables.
		$data = null;
		if ($amount && $chunksize > $amount) {
			$chunksize = $amount;
		}
		
		if (false === $fh = fopen ( $filename, 'rb', $incpath ))
		throw new Exception(printf (__METHOD__. ': Не удается открыть файл: %s', $filename ) );
		
		clearstatcache ();
		
		if ($offset)
		fseek ( $fh, $offset );
		
		if ($fsize = @ filesize ( $filename )) {
			if ($amount && $fsize > $amount) {
				$data = fread ( $fh, $amount );
			} else {
				$data = fread ( $fh, $fsize );
			}
		} else {
			$data = '';
			// While it's:
			// 1: Not the end of the file AND
			// 2a: No Max Amount set OR
			// 2b: The length of the data is less than the max amount we want
			while ( ! feof ( $fh ) && (! $amount || strlen ( $data ) < $amount) ) {
				$data .= fread ( $fh, $chunksize );
			}
		}
		fclose ( $fh );
		
		return $data;
	}
	
	/**
	 * @param string $file         The full file path
	 * @param string $buffer       The buffer to write
	 * @return bool True on success
	 */
	public static function write($file, &$buffer) {
		
		@set_time_limit ( ini_get ( 'max_execution_time' ) );
		
		// If the destination directory doesn't exist we need to create it
		if (! file_exists ( dirname ( $file ) ))
		Xmltv_Filesystem_Folder::create ( dirname ( $file ) );
		
		$file = Xmltv_Filesystem_Path::clean ( $file );
		return is_int(file_put_contents($file, $buffer)) ? true : false;
	}
	
	/**
	 * @param string $file
	 * @return boolean
	 */
	public static function exists($file) {
		return is_file ( Xmltv_Filesystem_Path::clean ( $file ) );
	}
	
	/**
	 * Returns the name, without any path
	 * 
	 * @param string $file  File path
	 * @return string filename
	 */
	public static function getName($file) {
		
		$file  = str_replace ( '\\', '/', $file );
		$slash = strrpos ( $file, '/' );
		
		if ($slash !== false)
			return substr ( $file, $slash + 1 );
		else
			return $file;
		
	}
	
	/**
	 * Returns the path
	 * 
	 * @param string $file  File path
	 * @return string FilterIteratorile path
	 */
	public static function getPath($file) {
		
		$file  = str_replace ( '\\', '/', $file );
		$slash = strrpos ( $file, '/' );
		
		if ($slash)
			return substr ( $file, 0, $slash + 1 );
		
		return '';
	}
	
}