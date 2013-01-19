<?php 
/**
 *
 * Routing plugin
 *
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @package rutvgid
 * @filesource $Source: /home/developer/cvs/rutvgid.ru/application/plugins/Antibot.php,v $
 * @version $Id: Antibot.php,v 1.2 2013-01-19 10:11:14 developer Exp $
 */

class Xmltv_Plugin_Antibot extends Zend_Controller_Plugin_Abstract
{
	protected $_env = 'production';
	protected $_request;
	protected $_router;
	protected $userAgent;
	protected $bots = array(
		'google',
		'yandex',
	);

	/**
	 * Constructor
	 *
	 * @param  string $env Execution environment
	 * @return void
	 */
	public function __construct ($env='production') {
		$this->setEnv( $env );
		$this->userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Mozilla/5.0 (Windows NT 5.1; rv:18.0) Gecko/20100101 Firefox/18.0' ;
	}


	public function setEnv ($env='production') {
		$this->_env = $env;
	}


	/**
	 * Route startup hook
	 *
	 * @param  Zend_Controller_Request_Abstract $request
	 * @return void
	 */
	public function routeStartup (Zend_Controller_Request_Abstract $request) {

		$bot = $this->_detectBot();
		if ($bot===true){
		    
		}
		
	
	}
	
	private function _detectBot(){
		
	    $log = new Zend_Log( new Zend_Log_Writer_Stream( APPLICATION_PATH . '/../log/agents.log' ));
	    $log->log( $this->userAgent, Zend_Log::INFO );
	    /* foreach ($this->bots as $b){
	        if (stristr($this->userAgent, $b)){
	            var_dump( system("whois ".'66.249.76.75'));
	            die(__FILE__.': '.__LINE__);
	        }
	    } */
	    
	    
	}
	
}
?>