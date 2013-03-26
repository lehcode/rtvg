<?php
/**
 * Articles model
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @version $Id: Articles.php,v 1.4 2013-03-26 20:03:26 developer Exp $
 *
 */
class Xmltv_Model_Articles
{
	/**
	 * @var Zend_Db_Adapater_Mysqli
	 */
	protected $db;
	
	/**
	 * Table prefix
	 * @var string
	 */
	protected static $tblPfx='';
	
	public function __construct(array $config=null)
	{
		if (!isset($config['db']) || empty($config['db']) || !is_a($config['db'], 'Zend_Config')) {
	        $config['db'] = Zend_Registry::get('app_config')->resources->multidb->local;
	    }
	    
	    if (is_array($config)) {
	    	$this->setOptions($config);
	    }
	    
		// Init database
		$this->dbConf = $config['db'];
		$this->db = new Zend_Db_Adapter_Mysqli( $this->dbConf);
		
		// Set table prefix
		$pfx = $this->dbConf->get('tbl_prefix');
		if(false !== (bool)$pfx) {
		    self::$tblPfx = $pfx; 
		}
	}
	
	/**
	 * Set model options
	 * @param array $options
	 */
	public function setOptions(array $options=null) {
	    
		$methods = get_class_methods($this);
		foreach ($options as $key => $value) {
			$method = 'set' . ucfirst($key);
			if (in_array($method, $methods)) {
				$this->$method($value);
			}
		}
		return $this;
	}
	
}