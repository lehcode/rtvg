<?php

/**
 * Bootstrap
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: Cache.php,v 1.4 2012-12-27 17:00:43 developer Exp $
 *
 */
class Xmltv_Cache {
	
    /**
     * Caching state
     * @var boolean
     */
	public $enabled;
	/**
	 * Caching object
	 * @var Zend_Cache_Frontend_File
	 */
	private $_cache;
	/**
	 * Sub-folder for data, relative to /cache
	 * @var string
	 */
	private $_location='/cache';
	/**
	 * Cache lifetime
	 * @var int
	 */
	private $_lifetime;
	
	/**
	 * Constructor
	 * @param array $config
	 */
	public function __construct($config=array()){
		
	    $this->_lifetime = isset($config['lifetime']) && !empty($config['lifetime']) ? 
			(int)$config['lifetime'] : 86400;
		$this->_location = isset($config['location']) && !empty($config['location']) ? 
			ROOT_PATH.'/'.$this->_location.(string)$config['location'] : ROOT_PATH.'/cache' ;
		$this->enabled  = (bool)Zend_Registry::get('site_config')->cache->system->get('enabled', false);
		$this->_cache  = Zend_Cache::factory( 'Core', 'File', array(  
			'lifetime' => $this->_lifetime,
			'automatic_serialization' => true ), array( 'cache_dir' => $this->_location ) );
		
	}
	
	/**
	 * Generate unique hash
	 * @param  string $input
	 * @throws Zend_Cache_Exception
	 * @return string
	 */
	public static function getHash($input=null){
		
		if (!$input)
			throw new Zend_Cache_Exception("Не указан кэш-идентификатор", 500);
		
		$regex  = new Zend_Filter_PregReplace(array('match'=>'/[^a-zA-Z0-9_]/', 'replace'=>'_'));
		return md5($regex->filter( $input ));
				
	}
	
	/**
	 * Load item from cache
	 * 
	 * @param string $hash
	 * @param string $frontend //Zend_Cache_Frontend
	 * @param string $sub_folder //sub-folder relative to /cache
	 * @throws Zend_Cache_Exception
	 * @return mixed
	 */
	public function load($hash=null, $frontend='Core', $sub_folder=null){
		
		if (!$hash)
			throw new Zend_Cache_Exception("Не указан кэш-идентификатор");
		
		$loc = Zend_Registry::get('site_config')->cache->system->get('location', '/cache');
		if (!$sub_folder) {
			$this->setLocation( $loc );
		} else {
			$this->setLocation( $loc.$sub_folder );
		}
		
		$frontend = ucfirst(strtolower($frontend));
		if ($frontend!='Core') {
		    if (mkdir('/cache/'.$frontend, 0755, true)){
				$this->setLocation( '/cache/'.$frontend );
		    }
		}
		
		//var_dump($this->_location);
		//die(__FILE__.': '.__LINE__);
		
		if (!is_dir($this->_location))
			return false;
		
		$this->_cache = Zend_Cache::factory(
			$frontend,
			'File', 
			array( 
				'lifetime' => $this->_lifetime,
				'automatic_serialization' => true ),
			array( 'cache_dir' => $this->_location ) 
		);
		
		return $this->_cache->load($hash);
	}
	
	/**
	 * Store item to cache
	 * 
	 * @param mixed  $contents
	 * @param string $hash
	 * @param string $frontend //Zend_Cache_Frontend
	 * @param string $sub_folder //sub-folder relative to /cache
	 * @throws Zend_Cache_Exception
	 */
	public function save($contents=null, $hash=null, $frontend='Core', $sub_folder=null){
		
		if (!$hash)
			throw new Zend_Cache_Exception("Не указан кэш-идентификатор", 500);
		
		if (!$sub_folder) 
			throw new Zend_Cache_Exception("Не указана папка кэша", 500);
		
		if ($frontend!='Core') {
			
			$frontend = ucfirst(strtolower($frontend));
			$this->_location .= '/' . $frontend;
			
			//var_dump($this->_location);
			
			if (!is_dir($this->_location))
				mkdir($this->_location, 0755, true);
			
			try {
				$this->_cache = Zend_Cache::factory( $frontend, 'File',  array( 
				'lifetime' => $this->_lifetime,
				'automatic_serialization' => true ),
				array( 'cache_dir' => $this->_location ) );
			} catch (Exception $e) {
				throw new Zend_Cache_Exception($e->getMessage(), $e->getCode(), $e);
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
			throw new Zend_Cache_Exception("Не указана папка кэша", 500);
		
		$this->_location = $folder;
		
	}
	/**
	 * @return the $_lifetime
	 */
	public function getLifetime() {
		return $this->_lifetime;
	}

	/**
	 * @param field_type $_lifetime
	 */
	public function setLifetime($sec=null) {
		if (!$sec)
			return false;
			
		$this->_lifetime = $sec;
	}


	
}