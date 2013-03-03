<?php
/**
 *
 * Routing plugin
 *
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @version $Id: Top.php,v 1.5 2013-03-03 23:34:13 developer Exp $
 */

class Xmltv_Controller_Action_Helper_Top extends Zend_Controller_Action_Helper_Abstract
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
	
	/**
	 * Strategy pattern: call helper as broker method
	 * 
	 * @param  string $method
	 * @param  array  $params
	 * @return boolean|mixed
	 */
	public function direct($method=null, $params=array()) {
	
	    if (APPLICATION_ENV=='development'){
	    	//var_dump(func_get_args());
	    }
	    
	    if ($method) {
			switch (strtolower($method)){
				case 'topprograms':
					return $this->topPrograms( $params['amt'] );
					break;
				case 'topchannels':
				    return $this->topChannels( $params['amt'] );
					break;
				default:
				    return false;
			}
			return false;
		}
		return false;
		
	}
	
	/**
	 * Fetch top programs list
	 * 
	 * @param int $amt
	 * @return Zend_Db_Table_Rowset
	 */
	public function topPrograms($amt=20){
		
	    $t = new Xmltv_Model_DbTable_ProgramsRatings();
		return $t->fetchTopPrograms( $amt );
		
	}

	/**
	 * Fetch top channels list
	 * 
	 * @param  int $amt
	 * @return Zend_Db_Table_Rowset
	 */
	public function topChannels($amt=20){
		
		$m = new Xmltv_Model_Channels();
		return $m->topChannels( $amt );
		
	}
}