<?php
class Xmltv_Controller_Helper_WeekDays extends Zend_Controller_Action_Helper_Abstract {
	
	/**
     * @var Zend_Loader_PluginLoader
     */
    public $pluginLoader;
    
	/**
     * Constructor: initialize plugin loader
     *
     * @return void
     */
    public function __construct()
    {
        $this->pluginLoader = new Zend_Loader_PluginLoader();
    }
    
    public function getStart($date=null){
    
    	if (!$date)
		$date = new Zend_Date(null, null, 'ru');
		
		do{
			if ($date->toString(Zend_Date::WEEKDAY_DIGIT, 'ru')!=1)
			$date->subDay(1);			
		} while ($date->toString(Zend_Date::WEEKDAY_DIGIT, 'ru')!=1);
		
		return $date;
    	
    }
    
    
    public function getEnd($date=null){
    
    	if (!$date)
		$date = new Zend_Date();
		
		do{
			$date->addDay(1);
		} while ($date->toString(Zend_Date::WEEKDAY_DIGIT, 'ru')!=1);
		$date->sub(1, Zend_Date::MINUTE);
		
		return $date;
    	
    }
    
    public function direct($params=array()){
    	
    	if (empty($params))
    	throw new Zend_Exception("ERROR: Пропущен один или более параметров для".__METHOD__, 500);
    	
    	if (isset($params['method']) && !empty($params['method'])) {
    		switch (strtolower($params['method'])) {
    			
    			case 'getstart':
    				if (isset($params['data']) && !empty($params['data']))
    				return $this->getStart($params['data']['date']);
    				else
    				return;

    			case 'getend':
    				if (isset($params['data']) && !empty($params['data']))
    				return $this->getEnd($params['data']['date']);
    				else
    				return;
    				
    			default:
    		}
    	}
    	
    }
	
}