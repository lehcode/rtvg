<?php

defined( 'ROOT_PATH' ) || define( 'ROOT_PATH', str_replace( '/application', '', APPLICATION_PATH ) );

/**
 * Cache
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @version $Id: Cache.php,v 1.3 2013-03-14 11:43:11 developer Exp $
 *
 */
class Rtvg_Cache {
	
    /**
     * Caching state
     * @var boolean
     */
	public $enabled=false;
	
	/**
	 * Caching object
	 * @var Zend_Cache_Frontend_File
	 */
	private $_cache;
	
	/**
	 * Sub-folder for data, relative to /cache
	 * @var string
	 */
	private $_location='cache';
	
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
			ROOT_PATH.'/'.$this->_location.(string)$config['location'] : ROOT_PATH.'/'.$this->_location ;
		
	    $frontendOptions = array(  
			'lifetime' => $this->_lifetime,
			'automatic_serialization' => true );
	    $backendOptions = array( 
			'cache_dir' => $this->_location,
			'hashed_directory_level' =>1 );
	    $this->_cache  = Zend_Cache::factory( 'Core', 'File', $frontendOptions,  $backendOptions);
		
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
		
		if (APPLICATION_ENV=='development'){
		    //var_dump(func_get_args());
			//var_dump($this->_location);
			//die(__FILE__.': '.__LINE__);
		}
		
		if ($sub_folder) {
			$this->setLocation( ROOT_PATH.'/cache'.$sub_folder );
		}
		
		if (APPLICATION_ENV=='development'){
			//var_dump($frontend);
			//die(__FILE__.': '.__LINE__);
		}
		
		if ($frontend!='Core') {
		    $dir = ROOT_PATH . '/cache/' . ucfirst( strtolower( $frontend ));
		    $perm = APPLICATION_ENV=='development' ? 0777 : 0555 ;
			if ( !is_dir($dir) && !mkdir( $dir, $perm, true )){
				throw new Zend_Exception( Rtvg_Message::ERR_CANNOT_CREATE_DIR, 500 );
			}
			$this->setLocation( ROOT_PATH . '/cache/' . $frontend );
		}
		
		if (!is_dir($this->_location)) {
		    $msg = sprintf( Rtvg_Message::ERR_LOCATION_UNREACHABLE, $this->_location);
		    throw new Zend_Exception( $msg, 500 );
		}
		
		$this->_cache = Zend_Cache::factory(
			$frontend,
			'File', 
			array( 
				'lifetime' => $this->_lifetime,
				'automatic_serialization' => true ),
			array( 'cache_dir' => $this->_location ) 
		);
		
		if (APPLICATION_ENV=='development'){
			//var_dump($this->_cache);
			//die(__FILE__.': '.__LINE__);
		}
		
		$load = $this->_cache->load($hash);
		
		if (APPLICATION_ENV=='development'){
		    //var_dump($load);
		    //die(__FILE__.': '.__LINE__);
		}
		
		return $load;
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
		
		
		
		if ($frontend!='Core') {
			
		    try {
				$this->_cache = Zend_Cache::factory( $frontend, 'File',  array( 
				'lifetime' => $this->_lifetime,
				'automatic_serialization' => true ),
				array( 'cache_dir' => $this->_location ) );
			} catch (Zend_Cache_Exception $e) {
				throw new Zend_Exception($e->getMessage(), $e->getCode(), $e);
			}
			
			$this->_cache->setOption('file_name_prefix', 'zzz');
			
		}
		
		try {
			$this->_cache->save($contents, $hash);
		} catch (Exception $e) {
			echo "Не могу сохранить запись в кэш";
		}
		
		return true;
		
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