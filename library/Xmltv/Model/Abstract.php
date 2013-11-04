<?php
class Xmltv_Model_Abstract
{
    /**
     * Main model for table
     * @var Xmltv_Model_DbTable
     */
    protected $table;
    /**
     *
     * @var Zend_Db_Adapter_Pdo_Mysql
     */
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
    protected $bcCategoriesTable;
    
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
     * Broadcasts table
     * @var Xmltv_Model_DbTable_Programs
     */
    protected $bcTable;
    
    /**
     * Video cache for sidebar
     * @var Xmltv_Model_DbTable_VcacheSidebar
     */
    protected $vcacheSidebarTable;

    /**
     * Video cache for sidebar
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
    protected $bcRatingsTable;
    
    /**
     * Events
     * @var Xmltv_Model_DbTable_Events
     */
    protected $eventsTable;
    
    /**
     * @var Xmltv_Model_DbTable_ContentCategories 
     */
    protected $contentCategoriesTable;
    
    /**
     * @var Xmltv_Model_DbTable_YtCategories
     */
    protected $ytCategoriesTable;

	/**
     * @var Xmltv_Model_DbTable_ArticlesRating
     */
    protected $articlesRatingTable;
    
    
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
		$this->db = new Zend_Db_Adapter_Pdo_Mysql( $this->dbConf);
		
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
		$method = 'set' . ucfirst( $name );
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Zend_Exception('Invalid model method: '.$method);
		}
		$this->$method($value);
	}
    
    /**
     * 
     * @return Rtvg_Cache
     */
    protected function getCache(){
        $cache = new Rtvg_Cache();
        return $cache;
    }
	
	/**
	 * Get row value
	 *
	 * @param  string $name
	 * @throws Exception
	 */
	public function __get($name) {
		$method = 'get' . ucfirst( $name );
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Zend_Exception('Invalid model method: '.$method);
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
		
	    $this->bcCategoriesTable = $this->getBcCategoriesTable();
	    $this->channelsTable = new Xmltv_Model_DbTable_Channels();
	    $this->channelsCategoriesTable = new Xmltv_Model_DbTable_ChannelsCategories();
	    $this->channelsRatingsTable = new Xmltv_Model_DbTable_ChannelsRatings();
	    $this->bcTable  = new Xmltv_Model_DbTable_Programs();
	    $this->vcacheListingsTable = new Xmltv_Model_DbTable_VcacheListings();
	    $this->vcacheSidebarTable = new Xmltv_Model_DbTable_VcacheSidebar();
	    $this->vcacheRelatedTable = new Xmltv_Model_DbTable_VcacheRelated();
	    $this->vcacheMainTable = new Xmltv_Model_DbTable_VcacheMain();
        $this->bcRatingsTable = new Xmltv_Model_DbTable_ProgramsRatings();
        $this->eventsTable = new Xmltv_Model_DbTable_Events();
	    $this->channelsCommentsTable = new Xmltv_Model_DbTable_ChannelsComments();
        $this->contentCategoriesTable = new Xmltv_Model_DbTable_ContentCategories();
        $this->ytCategoriesTable = new Xmltv_Model_DbTable_YtCategories();
        $this->articlesRatingTable = new Xmltv_Model_DbTable_ArticlesRating();
	    
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
    
    /**
     * 
     * @return Xmltv_Model_DbTable_ProgramsCategories
     */
    protected function getBcCategoriesTable(){
        return new Xmltv_Model_DbTable_ProgramsCategories();
    }
    
}
?>