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
     * Programs properties
     * @var Xmltv_Model_DbTable_ProgramsProps
     */
    protected $propertiesTable;
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
    
    
    const ERR_WRONG_PARAMS = "Неверные параметры для ";
    const ERR_NO_DB = "Не указана база данных в ";
    const ERR_MISSING_PARAMS="Пропущен необходимый параметр!";
    const ERR_WRONG_ENTRY="---Wrong entry: ";
    const ERR_PORN_ENTRY="---Порно: ";
    const ERR_NON_CYRILLIC="---Non-cyrillic entry: ";
    const ERR_WRONG_FORMAT="---Неверный формат для ";
    
	/**
     * Model constructor
     * 
     * @param array $config
     */
	public function __construct($config=array()){

	    if (!isset($config['db']) || empty($config['db']) || !is_a($config['db'], 'Zend_Config')) {
	        $config['db'] = Zend_Registry::get('app_config')->resources->multidb->local;
	    }
	    
		// Init cache	
		$this->cache = new Xmltv_Cache(array('location'=>'/Listings'));
		
		// Init database
		$this->dbConf = $config['db'];
		$this->db = new Zend_Db_Adapter_Mysqli( $this->dbConf);
		
		// Set table prefix
		$pfx = $this->dbConf->get('tbl_prefix');
		if( !empty($pfx)) {
		    self::$tblPfx = $this->dbConf->get('tbl_prefix'); 
		}
		
		$this->_initTables();
				
		
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
	
	private function _initTables(){
		
	    $this->descriptionsTable       = new Xmltv_Model_DbTable_ProgramsDescriptions();
	    $this->propertiesTable         = new Xmltv_Model_DbTable_ProgramsProps();
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
		try {
            Zend_Debug::dump($select->assemble());
        } catch (Zend_Db_Select_Exception $e) {
            throw new Zend_Exception($e->getMessage(), $e->getCode(), $e);
        }
	    
	}

 
    
}
?>