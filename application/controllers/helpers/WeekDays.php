<?php
class Zend_Controller_Helper_WeekDays extends Zend_Controller_Action_Helper_Abstract {
	
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
		
		if ($date->toString(Zend_Date::WEEKDAY_DIGIT, 'ru')!=1) {
			do{
				$date->subDay(1);
				//var_dump($date->toString(Zend_Date::WEEKDAY_DIGIT));			
			} while ($date->toString(Zend_Date::WEEKDAY_DIGIT, 'ru')!=1);
		}
		
		return $date;
    	
    }
    
    
    public function getEnd($date=null){
    
    	if (!$date)
		$date = new Zend_Date();
		
		if ($date->toString(Zend_Date::WEEKDAY_DIGIT, 'ru')!=0) {
			do{
				$date->addDay(1);
			} while ($date->toString(Zend_Date::WEEKDAY_DIGIT, 'ru')!=0);
   		}
		$date->sub(1, Zend_Date::MINUTE);
		
		return $date;
    	
    }
    
    public function direct($params=array()){
    	
    	//var_dump( func_get_args() );
    	//die(__FILE__.': '.__LINE__);
    	
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