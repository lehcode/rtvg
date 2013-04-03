<?php
/**
 * 
 * Week date calculations functions
 * 
 * @author  Antony Repin
 * @version $Id: UserDevice.php,v 1.1 2013-04-03 04:04:35 developer Exp $
 *
 */
class Xmltv_Controller_Action_Helper_UserDevice extends Zend_Controller_Action_Helper_Abstract {
	
	/**
	 * @var Zend_Loader_PluginLoader
	 */
	public $pluginLoader;
	
	/**
	 * @var Zend_Http_UserAgent
	 */
	private $userAgent;
	
	/**
	 * @var Zend_Http_UserAgent_AbstractDevice
	 */
	private $userDevice;
	
	/**
	 * Constructor: initialize plugin loader
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->pluginLoader = new Zend_Loader_PluginLoader();
	}
	
	/**
	 * 
	 * Calculate week start date
	 * @param Zend_Date $date
	 */
	public function getDevice($date=null){
	
		try {
        	$this->userAgent = $bootstrap->getResource('useragent');
        	$this->userDevice = $this->userAgent->getDevice();
        } catch (Exception $e) {
        	$this->_helper->layout()->setLayout('access-denied');
        	    return;
        }
        $this->view->assign( 'user_device', $this->userDevice );
		
	}
	
	/**
	 * 
	 * @param  string	 $method
	 * @param  Zend_Date  $date
	 * @throws Zend_Exception
	 * @return null|Zend_Date
	 */
	public function direct($method=null, Zend_Date $date){
		
		//var_dump(func_get_args());
		die(__METHOD__);
		
		if (!$method)
			return false;
		
		switch (strtolower($method)) {
			case 'getstart':
				return $this->getStart( $date );
				break;
			case 'getend':
				return $this->getEnd( $date );
				break;
			default:
				break;
		}
		
	}
	
}