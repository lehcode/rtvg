<?php
/**
 *
 * Resizes an image and converts it to PNG returning the PNG data as a string
 *
 * @author  Acuminate http://stackoverflow.com/users/2482/acuminate
 * @package rutvgid
 * @filesource $Source: /home/developer/cvs/rutvgid.ru/application/controllers/helpers/ImageToPng.php,v $
 * @version $Id: ImageToPng.php,v 1.1 2013-01-02 05:11:45 developer Exp $
 */
class Xmltv_Controller_Action_Helper_ImageToPng extends Zend_Controller_Action_Helper_Abstract
{
    
    /**
     * @var Zend_Loader_PluginLoader
     */
    public $pluginLoader;
    
    protected $tempFolder;
    protected $maxSize;
    
    
    /**
     * Constructor: initialize plugin loader
     *
     * @return void
     */
    public function __construct()
    {
    	$this->pluginLoader = new Zend_Loader_PluginLoader();
    }
    
    /**
     * Strategy pattern: call helper as broker method
     *
     * @param  string $method
     * @param  array  $params
     * @return boolean|mixed
     */
    public function direct($srcFile, $maxSize = 100) {
    
    	return $this->imageToPng($srcFile, $maxSize);
    
    }
    
    /**
     * 
     * @param unknown_type $srcFile
     * @param unknown_type $maxSize
     * @throws Exception
     * @throws Zend_Exception
     * @return string
     */
	function imageToPng($srcFile, $params=array()) {
	    
	    if (!empty($params))
	        $this->setParams($params);
	    
		list($width_orig, $height_orig, $type) = getimagesize($srcFile);
	
		// Get the aspect ratio
		$ratio_orig = $width_orig / $height_orig;
	
		$width  = $this->maxSize;
		$height = $this->maxSize;
	
		// resize to height (orig is portrait)
		if ($ratio_orig < 1) {
			$width = $height * $ratio_orig;
		}
		// resize to width (orig is landscape)
		else {
			$height = $width / $ratio_orig;
		}
	
		// Temporarily increase the memory limit to allow for larger images
		ini_set('memory_limit', '32M');
	
		switch ($type)
		{
			case IMAGETYPE_GIF:
				$image = imagecreatefromgif($srcFile);
				break;
			case IMAGETYPE_JPEG:
				$image = imagecreatefromjpeg($srcFile);
				break;
			case IMAGETYPE_PNG:
				$image = imagecreatefrompng($srcFile);
				break;
			default:
				throw new Exception('Unrecognized image type ' . $type);
		}
	
		// create a new blank image
		$newImage = imagecreatetruecolor($width, $height);
	
		// Copy the old image to the new image
		imagecopyresampled($newImage, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
	
		// Output to a temp file
		$destFile = tempnam($this->tempFolder, '__');
		
		imagepng($newImage, $destFile);
	
		// Free memory
		imagedestroy($newImage);
	
		if ( is_file($destFile) ) {
			$f = fopen($destFile, 'rb');
			$data = fread($f, filesize($destFile));
			fclose($f);
	
			// Remove the tempfile
			unlink($destFile);
			return $data;
		}
	
		throw new Zend_Exception('Image conversion failed.');
	}
	
	/**
	 * Set converter parameters
	 * 
	 * @param array $params
	 */
	protected function setParams($params=array()){
		
	    foreach ($params as $p){
	        
	        if (isset($params['temp_folder']) && !empty($params['temp_folder'])){
	            $this->tempFolder = $params['temp_folder'];
	        }
	        
	        if (isset($params['max_size']) && !empty($params['max_size'])){
	            $this->maxSize = $params['max_size'];
	        }
	    }
	    
	}
}