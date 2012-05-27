<?php

/**
 * Bootstrap
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: Cache.php,v 1.3 2012-05-27 20:05:50 dev Exp $
 *
 */
class Xmltv_Cache {
	
	private $_caching;
	private $_cache;
	private $_location;
	private $_lifetime;
	
	public function __construct($config=array()){
		
		$this->_lifetime = isset($config['cache_lifetime']) && !empty($config['cache_lifetime']) ? (int)$config['cache_lifetime'] : Xmltv_Config::getCacheLifetime();
		$this->_location = isset($config['location']) && !empty($config['location']) ? (string)$config['location'] : Xmltv_Config::getCacheLocation();
		$this->_caching = Xmltv_Config::getCaching()===true ? true : false ;
				
		$this->_cache  = Zend_Cache::factory( 'Core', 'File', array(  
			'lifetime' => $this->_lifetime,
			'automatic_serialization' => true ), array( 'cache_dir' => ROOT_PATH . $this->_location ) );
		
	}
	
	public function isOn(){
		
		if (Xmltv_Config::getCaching()===true)
		return true;
		
		return false;
	}
	
	public function getHash($input=null){
		
		if (!$input)
		throw new Exception("Не указан кэш-идентификатор", 500);
		
		$regex = new Zend_Filter_PregReplace(array('match'=>'/[^a-zA-Z0-9_]/', 'replace'=>'_'));
		
		if (Xmltv_Config::getDebug())
		$result = $regex->filter($input);
		else
		$result = md5($input);
		
		return $result;
				
	}
	
	public function load($hash=null, $frontend='Core', $sub_folder=null){
		
		if (!$hash)
		throw new Exception("Не указан кэш-идентификатор", 500);
		
		if (!$sub_folder) {
			$this->_location = ROOT_PATH . Xmltv_Config::getCacheLocation();
		} else {
			$this->_location = ROOT_PATH . Xmltv_Config::getCacheLocation() . '/' . $sub_folder;
		}
		
		$frontend = ucfirst(strtolower($frontend));
		
		if ($frontend!='Core') {
			
			$this->_location .= '/'.$frontend;
			
		}
		
		//var_dump($this->_location);
		//die(__FILE__.': '.__LINE__);
		
		if (!is_dir($this->_location))
		mkdir($this->_location);
		
		$this->_cache = Zend_Cache::factory(
		$frontend,
		'File', 
		array( 
			'lifetime' => $this->_lifetime,
			'automatic_serialization' => true ),
		array( 'cache_dir' => $this->_location ) );
		
		return $this->_cache->load($hash);
	}
	
	public function save($contents=null, $hash=null, $frontend='Core', $sub_folder=null){
		
		if (!$hash)
		throw new Exception("Не указан кэш-идентификатор", 500);
		
		if (!$sub_folder) {
			$this->_location = ROOT_PATH . Xmltv_Config::getCacheLocation();
		} else {
			$this->_location = ROOT_PATH . Xmltv_Config::getCacheLocation() . '/' . $sub_folder;
		}
		
		if ($frontend!='Core') {
			
			$frontend = ucfirst(strtolower($frontend));
			$this->_location .= '/' . $frontend;
			
			//var_dump($this->_location);
			
			if (!is_dir($this->_location))
			mkdir($this->_location);
			
			try {
				$this->_cache = Zend_Cache::factory( $frontend, 'File',  array( 
				'lifetime' => $this->_lifetime,
				'automatic_serialization' => true ),
				array( 'cache_dir' => $this->_location ) );
			} catch (Exception $e) {
				echo $e->getMessage();
				die(__FILE__.': '.__LINE__);
			}
			
			
		} 
		
		try {
			$this->_cache->save($contents, $hash);
		} catch (Exception $e) {
			echo "Не могу сохранить запись в кэш";
		}
		
	}
	
	
	/**
	 * @param string $folder
	 */
	public function setLocation ($folder=null) {
		
		if (!$folder)
		throw new Exception("Не указан параметр для ".__METHOD__, 500);
		
		$this->_location = $folder;
		
	}

	
}