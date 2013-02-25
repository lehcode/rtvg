<?php
/**
 * 
 * Request validation action helper
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @package rutvgid
 * @filesource $Source: /home/developer/cvs/rutvgid.ru/application/controllers/helpers/RequestValidator.php,v $
 * @version $Id: RequestValidator.php,v 1.14 2013-02-25 11:40:40 developer Exp $
 */
class Xmltv_Controller_Action_Helper_RequestValidator extends Zend_Controller_Action_Helper_Abstract
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
    
    const ALIAS_REGEX='/^[\p{Cyrillic}\p{Latin}\d_-]+$/ui';
    
    /**
     * 
     * Validate and filter
     * @param string $module
     * @param string $controller
     * @param string $action
     */
    public function isValidRequest($params=null, $options=null) {
    	
        
    	if (APPLICATION_ENV=='development'){
    	    var_dump( $this->getRequest()->getParams() );
    	    //die(__FILE__.': '.__LINE__);
    	}
		
		$filters = array( 
			'*'=>'StringTrim',
			'module'=>'StringToLower',
			'controller'=>'StringToLower', 
			'action'=>'StringToLower', 
			'format'=>array('StringToLower'),);
		$validators = array(
	    	'module'=>array(
	    		new Zend_Validate_Regex('/^[a-z]+$/'),
	    		'presence'=>'required'
	    	),
	    	'controller'=>array(
	    		new Zend_Validate_Regex('/^[a-z]+$/'),
	    		'presence'=>'required'
	    	),
	    	'action'=>array(
	    		new Zend_Validate_Regex('/^[a-z-]+$/'),
	    		'presence'=>'required'
	    	)
	    );
		
		$profile = (bool)Zend_Registry::get('site_config')->site->get('profile');
		if (isset($_GET['RTVG_PROFILE'])){
			$validators['RTVG_PROFILE'] = array(new Zend_Validate_Regex( '/^(0|1)$/u' ));
		}
		if (isset($_GET['XDEBUG_PROFILE'])){
			$validators['XDEBUG_PROFILE'] = array(new Zend_Validate_Regex( '/^(0|1)$/u' ));
		}
	    $module     = $params['module'];
	    $controller = $params['controller'];
	    $action     = $params['action'];
	    
	    switch ($module){
	    	/*
	    	 * default module
	    	 */
	    	case 'default':
	    	default:
	    		
	    		switch ($controller){
	    			
	    			case 'search':
	    			    //die(__FILE__.': '.__LINE__);
	    			    $validators['searchinput'] = array( new Zend_Validate_Regex( '/^[\s\p{Cyrillic}\p{Latin}\d-]+$/u' ));
	    			    $validators['submit']      = array( new Zend_Validate_Regex( '/^>$/'));
	    			    $validators['type']        = array( new Zend_Validate_Regex( '/^(channel)$/u' ));
	    			    break;
	    		    
	    			/**
	    			 * Channels controler actions
	    			 */
	    		    case 'channels':
	    				switch ($action) {
							case 'list':
								
								break;
								
							case 'typeahead':
								$validators['format'] = array( new Zend_Validate_Regex('/^html|json$/'));
								if ($this->getRequest()->getParam('c')) {
									$validators['c'] = array( new Zend_Validate_Regex('/^.+$/'));
								}
								break;
								
							case 'category':
								$validators['category'] = array( new Zend_Validate_Regex( '/^[\p{Cyrillic}-]+/ui' ));
								break;
								
							case 'channel-week':
								$validators['channel'] = array( new Zend_Validate_Regex( '/^[\p{Cyrillic}\p{Latin}\d-]+$/u' ));
								/* $validators['week_start'] = array( 
									new Zend_Validate_Date( array(
										'format'=>'dd.MM.yyyy',
										'locale'=>'ru' ))
								); */
								break;
								
							case 'search':
								die(__FILE__.': '.__LINE__);
								$validators['id']    = array( new Zend_Validate_Regex( '/^[\w\d]+/u' ));
								$validators['alias'] = array( new Zend_Validate_Regex( self::ALIAS_REGEX ));
								$filters['alias']    = 'StringToLower';
								break;
								
							case 'new-comments':
							    $validators['format']  = array( new Zend_Validate_Regex('/^html|json$/'));
							    $validators['channel'] = array( new Zend_Validate_Regex( '/^[\p{Cyrillic}\p{Latin}\d-]+$/u' ));
							    break;
								
							default:
								return false;
						}
	    				
	    				break;
	    			
	    			case 'listings':
	    			    
	    			    $validators['channel'] = array(new Zend_Validate_Regex( '/^[\p{Cyrillic}\p{Latin}\d-]+$/u' ));
	    			    
	    			    switch ($action) {
	    			        
	    			    	case 'category':
	    			    	    foreach ($options['vars']['programsCategories'] as $c){
	    			    	        $cats[]=$c->alias;
	    			    	    }
	    			    	    $validators['category'] = array( new Zend_Validate_Regex( '/^('.implode('|', $cats).')$/u' ));
	    			    	    $validators['timespan'] = array( new Zend_Validate_Regex( '/^(сегодня|неделя)$/u' ));
	    			    	    break;
	    			        
	    			        case 'program-day':
	    			            
	    			            $validators['alias'] = array( new Zend_Validate_Regex( self::ALIAS_REGEX ));
	    			            if ($this->getRequest()->getParam('date')) {
	    			            	$d = $this->getRequest()->getParam('date');
		    			            if (preg_match('/^[\d]{2}-[\d]{2}-[\d]{4}$/', $d)) {
		    			            	$validators['date'] = array( new Zend_Validate_Date( array('format'=>'dd-MM-yyyy', 'locale'=>'ru')), 'presence'=>'required');
		    			            } elseif (preg_match('/^[\d]{4}-[\d]{2}-[\d]{2}$/', $d)) {
		    			            	$validators['date'] = array( new Zend_Validate_Date( array('format'=>'yyyy-MM-dd', 'locale'=>'ru')), 'presence'=>'required');
		    			            } else{
		    			                $validators['date'] = array( new Zend_Validate_Regex( '/^(сегодня|неделя)$/u' ));
		    			            }
	    			            }
	    			            break;
	    			            
	    			    	case 'program-week':
	    			    	    $validators['alias'] = array( new Zend_Validate_Regex( self::ALIAS_REGEX ));
	    			    	    if ($this->getRequest()->getParam('date')) {
		    			    	    $d = $this->getRequest()->getParam('date');
		    			    	    if (preg_match('/^[\d]{2}-[\d]{2}-[\d]{4}$/', $d)) {
		    			    	    	$validators['date'] = array( new Zend_Validate_Date( array('format'=>'dd-MM-yyyy', 'locale'=>'ru')), 'presence'=>'required');
		    			    	    } elseif (preg_match('/^[\d]{4}-[\d]{2}-[\d]{2}$/', $d)) {
		    			    	    	$validators['date'] = array( new Zend_Validate_Date( array('format'=>'yyyy-MM-dd', 'locale'=>'ru')), 'presence'=>'required');
		    			    	    } else {
		    			    	        if ($d=='сегодня' || $d=='неделя') {
		    			    	            $validators['date'] = array( new Zend_Validate_Alpha(false) );
		    			    	        } else return false;
		    			    	    }
	    			    	    }
	    			    	    break;
	    			    	    
	    			    	case 'day-listing':
							case 'day-date':
							    $validators['ts'] = array( new Zend_Validate_Digits());
							    if ($this->getRequest()->getParam('date')) {
							    	$d = $this->getRequest()->getParam('date');
							    	if (preg_match('/^[\d]{2}-[\d]{2}-[\d]{4}$/', $d))
							    		$validators['date'] = array( new Zend_Validate_Date( array('format'=>'dd-MM-yyyy', 'locale'=>'ru')), 'presence'=>'required');
							    	if (preg_match('/^[\d]{4}-[\d]{2}-[\d]{2}$/', $d))
							    		$validators['date'] = array( new Zend_Validate_Date( array('format'=>'yyyy-MM-dd', 'locale'=>'ru')), 'presence'=>'required');

							    }
							    
							    if ($this->getRequest()->getParam('tz')){
							        $validators['tz'] = array( new Zend_Validate_Regex( '/^-?[0-9]{1,2}$/u' ));
							    }
							    
								break;
								
							default:
							    return false;
	    			    }
	    			    break;
	    			case 'videos':
	    			    $validators['alias'] = array( new Zend_Validate_Regex( self::ALIAS_REGEX ));
	    			    $validators['id']    = array( new Zend_Validate_Regex( '/^[a-z\d]+$/i' ));
	    			    break;
	    			/*
	    			 * default controller
	    			 */
	    			case'frontpage':
	    			    	
	    			    	switch ($action){
	    			    		case 'single-channel':
	    			    		    $validators['format'] = array( new Zend_Validate_Alpha());
	    			    		    $validators['id']     = array( new Zend_Validate_Digits());
	    			    		    //die(__FILE__.': '.__LINE__);
	    			    		default: 
	    			    		    break;
	    			    	}
	    			    
	    			    	break;
	    			    	
	    			default:
	    			    return false;
	    			
	    		}
	    		
	    		break;
	    	
	    	/*
	    	 * Administrator interface
	    	 */
	    	case 'admin':
	    	    //var_dump( $this->getRequest()->getParams() );
	    	    switch ($controller){
	    			case 'archive':
	    				switch ($action){
	    					case 'store';
	    						$validators['start_date'] = array( new Zend_Validate_Date( array('format'=>'dd.MM.yyyy', 'locale'=>'ru' )), 'presence'=>'required');
	    						$validators['end_date']   = array( new Zend_Validate_Date( array('format'=>'dd.MM.yyyy', 'locale'=>'ru' )), 'presence'=>'required');
	    						$validators['format']     = array( new Zend_Validate_Regex('/^html|json$/'));
	    						//die(__FILE__.': '.__LINE__);
	    						break;
	    						
	    					default:
	    						return false;
	    				}
	    				break;
	    				
	    			case 'import':
	    			    
	    			    //var_dump($action);
	    			    //die(__FILE__.': '.__LINE__);
	    			    
						switch ($action){
							case 'remote':
							    if ($this->getRequest()->getParam('site')) {
							    	$validators['site'] = array( new Zend_Validate_Alnum());
							    }
							    if ($this->getRequest()->getParam('format')) {
							    	$validators['format'] = array( new Zend_Validate_Regex('/^(html|json)$/'));
							    }
							    
							    //var_dump($validators);
							    //die(__FILE__.': '.__LINE__);
							    	
							    break;
							case 'xml-parse-channels':
							case 'xml-parse-programs':
							    if ($this->getRequest()->getParam('xml_file')) {
							    	$validators['xml_file'] = array( new Zend_Validate_File_Exists( $this->getRequest()->getParam('xml_file')));
							    }
								break;
						}
						
	    				break;

	    			case 'programs':
	    			    switch ($action) {
	    			        
	    			        case 'delete-programs':
	    			            $validators['delete_start']   = array( new Zend_Validate_Regex( '/^[\d]{2}\.[\d]{2}\.[\d]{4}$/'));
	    			            $validators['delete_end']     = array( new Zend_Validate_Regex( '/^[\d]{2}\.[\d]{2}\.[\d]{4}$/'));
	    			            $validators['deleteprograms'] = array( new Zend_Validate_Regex( '/^(0|1)$/'));
	    			            $validators['deleteinfo']     = array( new Zend_Validate_Regex( '/^(0|1)$/'));
	    			            $validators['format']         = array( new Zend_Validate_Regex( '/^(html|json)$/'));
	    			            $validators['submit']         = array( new Zend_Validate_Regex( '/^Старт$/u'));
	    			            //var_dump($validators);
	    			            //die(__FILE__.': '.__LINE__);
	    			        	break;
	    			        
	    			        case 'processing':
	    			            $input = new Zend_Filter_Input($filters, $validators, $this->getRequest()->getParams());
	    			    		return $input;
	    			    		break;
	    			    		
	    			        default: 
	    			            return false;
	    			    }
	    			
	    		}
	    		
	    		break;
	    }
	    		
		
		
		$input = new Zend_Filter_Input($filters, $validators, $this->getRequest()->getParams());
    	
		//var_dump($validators);
		//var_dump($input->isValid('delete_start'));
		//die(__FILE__.': '.__LINE__);
		
		return $input;
    	
    }
    
	/**
     * Strategy pattern: call helper as broker method
     */
    public function direct($params=array()) {
    
        //var_dump($params);
        //var_dump( strtolower($params[0])=='isvalidrequest');
        //var_dump( isset($params['vars']) && !empty($params['vars']) && is_array($params['vars']));
        //var_dump(strtolower($params[0])=='isvalidrequest');
        //die(__FILE__.': '.__LINE__);
        
    	if (strtolower($params[0])=='isvalidrequest') {
    		if (isset($params['vars']) && !empty($params['vars']) && is_array($params['vars'])){
    		    $o = array();
    		    $p  = array();
    		    $p['module']     = $params['vars']['module'];
    		    $p['controller'] = $params['vars']['controller'];
    		    $p['action']     = $params['vars']['action'];
    		    unset($params['module']);
    		    unset($params['controller']);
    		    unset($params['action']);
    		    $options = $params;
    		    $params = $p;
    		    return $this->isValidRequest($params, $options);
    		}
    	}
    	return false;
    }
    
}