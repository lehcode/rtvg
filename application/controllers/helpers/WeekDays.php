<?php
/**
 * 
 * Helper class
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: WeekDays.php,v 1.5 2012-12-25 01:59:41 developer Exp $
 *
 */
class Xmltv_Controller_Action_Helper_WeekDays extends Zend_Controller_Action_Helper_Abstract {
	
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
	
	/**
	 * 
	 * Calculate week start date
	 * @param Zend_Date $date
	 */
	public function getStart($date=null){
	
		if (!$date) {
			$result = new Zend_Date();
		} else {
			$result = new Zend_Date( $date->toString('U'), 'U' );
		}
		
		if ($result->toString(Zend_Date::WEEKDAY_DIGIT, 'ru')!=1) {
			while ($result->toString(Zend_Date::WEEKDAY_DIGIT, 'ru')!=1) {
				$result->subDay(1);		
			};
		}
		
		return $result;
		
	}
	
	/**
	 * 
	 * Calculate week end date
	 * @param Zend_Date $date
	 */
	public function getEnd($date=null){
	
		if (!$date){
			$result = new Zend_Date();
		} else {
			$result = new Zend_Date( $date->toString('U'), 'U' );
		}
		
		if ($result->toString(Zend_Date::WEEKDAY_DIGIT, 'ru')!=0) {
		    while ($result->toString(Zend_Date::WEEKDAY_DIGIT, 'ru')!=0) {
		        $result->addDay(1);
		    }
   		}
   		//$result->addDay(1);
   		//$result->subMinute(1);
   		
		
		return $result;
		
	}
	
	/**
	 * 
	 * @param  string	 $method
	 * @param  Zend_Date  $date
	 * @throws Zend_Exception
	 * @return null|Zend_Date
	 */
	public function direct($method=null, Zend_Date $date){
		
		var_dump(func_get_args());
		die(__FILE__.': '.__LINE__);
		
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