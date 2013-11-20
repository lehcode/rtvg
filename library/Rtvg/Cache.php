<?php
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
	private $_subfolder;
	
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
	    
	    $loc = isset($config['location']) && !empty($config['location']) ? 
            realpath( APPLICATION_PATH.'/../cache/'.$this->_subfolder) : 
            realpath( APPLICATION_PATH.'/../cache/' ) ;
        $this->setLocation($loc);
        
        $frontendOptions = array(  
			'lifetime' => $this->_lifetime,
			'automatic_serialization' => true );
	    $backendOptions = array( 
			'cache_dir' => $loc,
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
		
		if (!$input){
			throw new Zend_Cache_Exception("Не указан кэш-идентификатор", 500);
        }
		return md5($input );
				
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
		
		if (!$hash) {
			throw new Zend_Cache_Exception("Не указан кэш-идентификатор");
        }
        
        $dir = realpath(APPLICATION_PATH.'/../cache/');
        if ($sub_folder) {
            $dir = rtrim($dir, '/').'/'.ltrim($sub_folder, '/');
			$this->setLocation( $dir );
		}
        
        if ($frontend!='Core') {
		    $perm = APPLICATION_ENV=='development' ? 0777 : 0555 ;
			if ( !is_dir($dir) && !mkdir( $dir, $perm, true )){
				throw new Zend_Exception( Rtvg_Message::ERR_CANNOT_CREATE_DIR, 500 );
			}
		}
		
		$this->_cache = Zend_Cache::factory(
			$frontend,
			'File', 
			array( 
				'lifetime' => $this->_lifetime,
				'automatic_serialization' => true ),
			array( 'cache_dir' => $dir )
		);
		
		$load = $this->_cache->load($hash);
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
		
		if (!$hash){
			throw new Zend_Cache_Exception("Не указан кэш-идентификатор", 500);
        }
        if (!$sub_folder){
            throw new Zend_Exception("Не указана папка кэша");
        }
		
        if ($frontend!='Core') {
			
		    try {
				$this->_cache = Zend_Cache::factory( $frontend, 'File',  array( 
				'lifetime' => $this->_lifetime,
				'automatic_serialization' => true ),
				array( 'cache_dir' => $this->_subfolder ) );
			} catch (Zend_Cache_Exception $e) {
				throw new Zend_Exception($e->getMessage(), $e->getCode(), $e);
			}
			
			$this->_cache->setOption('file_name_prefix', 'zzz');
			
		}
		
		try {
			$this->_cache->save($contents, $hash);
		} catch (Exception $e) {
            if(get_class($e)=='Zend_Cache_Exception'){
                throw new Zend_Exception("Не могу сохранить запись в папку '".$sub_folder."'!");
            }
		}
		
		return true;
		
	}
	
	
	/**
	 * @param string $folder
	 */
	public function setLocation ($folder=null) {
		
		if (!$folder){
            $folder = APPLICATION_PATH . '/../cache/';
        }
		
		$this->_subfolder = $folder;
		
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