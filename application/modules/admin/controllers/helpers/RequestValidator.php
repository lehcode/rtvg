<?php
class Admin_Controller_Helper_RequestValidator extends Zend_Controller_Action_Helper_Abstract
{
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
    
    public function isValidRequest($action=array())
    {
    	
    	if (!$action)
		return false;
		
		//var_dump($this->getRequest()->getParams());
		//die();
		
		$filters = array( '*'=>'StringTrim', '*'=>'StringToLower' );
		$validators = array(
	    	'module'=>array(
	    		new Zend_Validate_Regex('/^[a-z]+$/')),
	    	'controller'=>array(
	    		new Zend_Validate_Regex('/^[a-z]+$/')),
	    	'action'=>array(
	    		new Zend_Validate_Regex('/^[a-z-]+$/')),
	    	'format'=>array(
	    		new Zend_Validate_Regex('/^html|json$/')));
		switch ($action) {
			case 'delete-programs':
				$validators['cleanprograms'] = array( new Zend_Validate_Digits());
				$validators['deleteinfo']    = array( new Zend_Validate_Digits() );
				$validators['search_start']  = array( new Zend_Validate_Date(array('format'=>'dd.MM.yyyy', 'locale'=>'ru')) );
				$validators['search_end']    = array( new Zend_Validate_Date(array('format'=>'dd.MM.yyyy', 'locale'=>'ru')) );
				break;
			case 'parse-saved-programs':
				$validators['do_parse_premieres'] = array( new Zend_Validate_Digits());
				$validators['do_parse_series'] = array( new Zend_Validate_Digits() );
				$validators['do_parse_movies'] = array( new Zend_Validate_Digits() );
				$validators['do_parse_sports'] = array( new Zend_Validate_Digits() );
				$validators['do_parse_breaks'] = array( new Zend_Validate_Digits() );
				$validators['do_parse_documentary'] = array( new Zend_Validate_Digits() );
				$validators['save_updates'] = array( new Zend_Validate_Digits() );
				$validators['start_date'] = array( new Zend_Validate_Date(array('format'=>'dd.MM.yyyy', 'locale'=>'ru')) );
				$validators['end_date'] = array( new Zend_Validate_Date(array('format'=>'dd.MM.yyyy', 'locale'=>'ru')) );
				break;
			default:
		}
		
		$input = new Zend_Filter_Input($filters, $validators, $this->getRequest()->getParams());
    	if ($input->isValid())
    	return true;
    	
    	return false;
    	
    	return;
    }
    
	/**
     * Strategy pattern: call helper as broker method
     */
    public function direct($params=array())
    {
    	
    	if (isset($params['method']) && !empty($params['method'])) {
    		switch (strtolower($params['method'])) {
    			
    			case 'isvalidrequest':
    				if (isset($params['action']) && !empty($params['action']))
    				return $this->isValidRequest($params['action']);
    				else
    				return;
    				
    			default:
    		}
    	}
    }
    
}