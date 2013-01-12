<?php 
abstract class Xmltv_Model_Abstract
{
    /**
     * Main model for table
     * @var Xmltv_Model_DbTable
     */
    protected $table;
    protected $db;
    protected $dbConf;
    protected static $tblPfx='';
    
    const ERR_WRONG_PARAMS = "Неверные параметры для ";
    
	/**
	 * 
	 * Constructor
	 * @param array $config
	 */
	public function __construct($config=array()){

	    if (!isset($config['db']) || empty($config['db']) || !is_a($config['db'], 'Zend_Config'))
	        throw new Zend_Exception( self::ERR_WRONG_PARAMS.__METHOD__, 500);
	    
		// Init cache	
		$this->cache = new Xmltv_Cache(array('location'=>'/Listings'));
		
		// Init database
		$this->dbConf = $config['db'];
		$this->db = new Zend_Db_Adapter_Mysqli( $this->dbConf);
		
		// Set table prefix
		$pfx = $this->dbConf->get('tbl_prefix');
		if( !empty($pfx)) {
		    $this->tblPfx = $this->dbConf->get('tbl_prefix'); 
		}
				
		
	}
	/**
	 * @return the $tbl_pfx
	 */
	public function getTblPfx() {

		return self::$tblPfx;
	}
	
	/**
	 * @return the $db
	 */
	public function getDb() {

		return $this->db;
	}

 
    
}
?>