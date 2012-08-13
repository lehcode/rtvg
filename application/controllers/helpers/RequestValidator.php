<?php
class Zend_Controller_Action_Helper_RequestValidator extends Zend_Controller_Action_Helper_Abstract
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
    
    public function isValidRequest($action='') {
    	
    	//die(__FILE__.': '.__LINE__);
    	
    	if (empty($action))
			return false;
		
		//var_dump($this->getRequest()->getParams());
		//var_dump($action);
		//die(__FILE__.': '.__LINE__);
		
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
			case 'channel-week':
				$validators['channel']    = array( new Zend_Validate_Regex( '/^[\p{L}\p{N}-]+/ui' ));
				$validators['week_start'] = array( new Zend_Validate_Date( array('format'=>'dd.MM.yyyy', 'locale'=>'ru' )));
				break;
			case 'show-video':
				$validators['id']    = array( new Zend_Validate_Regex( '/^[\p{L}\p{N}]+/iu' ));
				$validators['alias'] = array( new Zend_Validate_Regex( '/^[\p{L}\p{N}-]+/iu' ));
				break;
			case 'search':
				die(__FILE__.': '.__LINE__);
				$validators['id']    = array( new Zend_Validate_Regex( '/^[\p{L}\p{N}]+/iu' ));
				$validators['alias'] = array( new Zend_Validate_Regex( '/^[\p{L}\p{N}-]+/iu' ));
				break;
			default:
				return false;
		}
		
		$input = new Zend_Filter_Input($filters, $validators, $this->getRequest()->getParams());
    	
		//var_dump($input->isValid());
		
		if ($input->isValid())
    	return true;
    	else
    	return false;
    	
    }
    
	/**
     * Strategy pattern: call helper as broker method
     */
    
    public function direct($params=array())
    {
    	
    	//var_dump($params);
    	//die(__FILE__.': '.__LINE__);
    	
    	if (isset($params['method']) && !empty($params['method'])) {
    		switch (strtolower($params['method'])) {
    			case 'isvalidrequest':
    				if (isset($params['action']) && !empty($params['action']))
    				return $this->isValidRequest($params['action']);
    				else
    				return false;
    			default:
    				return;
    		}
    	} else {
    		return false;
    	}
    }
    
}