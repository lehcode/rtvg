<?php
class Xmltv_Cache extends Zend_Cache_Core {
	
	private $_caching;
	private $_cache;
	private $_location;
	private $_lifetime;
	//private $_automaticSerialization=true;
	
	public function __construct($config=array()){
		
		$this->_lifetime = isset($config['cache_lifetime']) && !empty($config['cache_lifetime']) ? (int)$config['cache_lifetime'] : Xmltv_Config::getCacheLifetime();
		$this->_location = isset($config['location']) && !empty($config['location']) ? (int)$config['location'] : Xmltv_Config::getCacheLocation();
		//$this->_automaticSerialization = isset($config['automatic_serialization']) && !empty($config['automatic_serialization']) ? (bool)$config['automatic_serialization'] : true ;
		$this->_caching = Xmltv_Config::getCaching()===true ? true : false ;
		
		$this->_cache  = Zend_Cache::factory(
				'Core',
				'File', 
				array( 
					'lifetime' => $this->_lifetime,
					'automatic_serialization' => true ),
				array( 'cache_dir' => ROOT_PATH . $this->_location ) );
		
	}
	
	public function isOn(){
		
		if (Xmltv_Config::getCaching()===true)
		return true;
		
		return false;
	}
	
	public function getHash($input=null){
		
		if (!$input)
		throw new Exception("Не указан кэш-идентификатор", 500);
		
		$filter = new Zend_Filter_Word_SeparatorToSeparator(':', '_');
		
		if (Xmltv_Config::getDebug()===true)
		return $filter->filter($input);
		else
		return md5($filter->filter($input));
		
	}
	
	public function load($hash=null, $frontend='Core'){
		
		if (!$hash)
		throw new Exception("Не указан кэш-идентификатор", 500);
		
		if ($frontend!='Core') {
			
		}
		
		return $this->_cache->load($hash);
	}
	
}