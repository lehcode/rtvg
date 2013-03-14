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
    
    
    /**
     * Programs descriptions
     * @var Xmltv_Model_DbTable_ProgramsDescriptions
     */
    protected $descriptionsTable;
    /**
     *
     * @var Xmltv_Model_DbTable_ProgramsCategories
     */
    protected $categoriesTable;
    /**
     *
     * @var Xmltv_Model_DbTable_ChannelsCategories
     */
    protected $channelsCategoriesTable;
    /**
     *
     * @var Xmltv_Model_DbTable_ChannelsRatings
     */
    protected $channelsRatingsTable;
    /**
     * Channels
     * @var Xmltv_Model_DbTable_Channels
     */
    protected $channelsTable;
    
    /**
     * Actors
     * @var Xmltv_Model_DbTable_Actors
     */
    protected $actorsTable;

    /**
     * Programs
     * @var Xmltv_Model_DbTable_Programs
     */
    protected $programsTable;
    
    /**
     * Directors
     * @var Xmltv_Model_DbTable_Directors
     */
    protected $directorsTable;

    /**
     * Video cache for sidebar
     * @var Xmltv_Model_DbTable_VcacheSidebar
     */
    protected $vcacheSidebarTable;

    /**
     * Video cache for main listing videos
     * @var Xmltv_Model_DbTable_VcacheListings
     */
    protected $vcacheListingsTable;

    /**
     * Video cache for videos related to listing
     * @var Xmltv_Model_DbTable_VcacheRelated
     */
    protected $vcacheRelatedTable;

    /**
     * Video cache for videos related to listing
     * @var Xmltv_Model_DbTable_VcacheMain
     */
    protected $vcacheMainTable;

    /**
     * Video cache for videos related to listing
     * @var Xmltv_Model_DbTable_VcacheRelated
     */
    protected $channelsCommentsTable;
    
    /**
     * Video cache for videos related to listing
     * @var Xmltv_Model_DbTable_ProgramsRatings
     */
    protected $programsRatingsTable;
    
    
	/**
     * Model constructor
     * 
     * @param array $config
     */
	public function __construct($config=array()){

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
		
		$this->initTables();
		
	}
	
	
	/**
	 * Set row value
	 * 
	 * @param  string $name
	 * @param  string $value
	 * @throws Exception
	 */
	public function __set($name, $value)
	{
		$method = 'set' . $name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception('Invalid guestbook property');
		}
		$this->$method($value);
	}
	
	/**
	 * Get row value
	 * 
	 * @param  string $name
	 * @throws Exception
	 */
	public function __get($name)
	{
		$method = 'get' . $name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception('Invalid guestbook property');
		}
		return $this->$method();
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
	
	protected function initTables(){
		
	    $this->descriptionsTable       = new Xmltv_Model_DbTable_ProgramsDescriptions();
	    $this->categoriesTable         = new Xmltv_Model_DbTable_ProgramsCategories();
	    $this->channelsTable           = new Xmltv_Model_DbTable_Channels();
	    $this->channelsCategoriesTable = new Xmltv_Model_DbTable_ChannelsCategories();
	    $this->channelsRatingsTable    = new Xmltv_Model_DbTable_ChannelsRatings();
	    
	    $this->actorsTable    = new Xmltv_Model_DbTable_Actors();
	    $this->directorsTable = new Xmltv_Model_DbTable_Directors();
	    $this->programsTable  = new Xmltv_Model_DbTable_Programs();
	    
	    $this->vcacheListingsTable = new Xmltv_Model_DbTable_VcacheListings();
	    $this->vcacheSidebarTable  = new Xmltv_Model_DbTable_VcacheSidebar();
	    $this->vcacheRelatedTable  = new Xmltv_Model_DbTable_VcacheRelated();
	    $this->vcacheMainTable     = new Xmltv_Model_DbTable_VcacheMain();
	    
	    $this->channelsCommentsTable = new Xmltv_Model_DbTable_ChannelsComments();
	    
	    $this->programsRatingsTable = new Xmltv_Model_DbTable_ProgramsRatings();
	}
	
	/**
	 * Debug select statement
	 * 
	 * @param Zend_Db_Select $select
	 * @param string $method
	 */
	protected static function debugSelect( Zend_Db_Select $select, $method=__METHOD__){
	    
	    echo '<b>'.$method.'</b><br />';
		Zend_Debug::dump($select->assemble());
        
	}

	public function setOptions(array $options) {
	    
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
?>