<?php
class Admin_Model_Articles {
	
	/**
	 * Prefix for table name
	 * @var string
	 */
	protected static $tblPfx;
	
	/**
	 * Database adapter
	 * @var Zend_Db_Adapter_Mysqli
	 */
	protected $db;
	
	/**
	 * Articles database table class
	 * @var Admin_Model_DbTable_Articles
	 */
	protected $articlesTable;

	/**
	 * Articles database table class
	 * @var Admin_Model_DbTable_Articles
	 */
	protected $articlesTable;
	
	public function __construct(array $config=null){
		
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
		
		$this->articlesTable = new Admin_Model_DbTable_Articles();
		$this->contentCategoriesTable = new Admin_Model_DbTable_ContentCategories();
		
	}
	
	/**
	 * Set model config
	 * 
	 * @param array $options
	 */
	protected function setOptions(array $options=null) {
	    
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
	 * Fetch all articles list with properties
	 * @param int $amt
	 * @param int $offset
	 */
	public function getList( $amt=null, $offset=0 ){
		
		$select = $this->db->select()
			->from(array('a'=>$this->articlesTable->getName()))
			-join(array(), $pieces);
			
		if (APPLICATION_ENV=='development'){
			self::debugSelect($select, __METHOD__);
			die(__FILE__.': '.__LINE__);
		}
		
		/**
		 * @var Zend_Paginator
		 */
		$paginator = $this->getPaginator($select);
		var_dump(Zend_Paginator);
		die(__FILE__.': '.__LINE__);
		
	}
	
	/**
	 * Enter description here ...
	 * @param Zend_Db_Select $select
	 */
	public function getPaginator( $select=null){
		
		if (!$select){
			$select = $this->db->select()
				->from(array('a'=>$this->articlesTable->getName()));
		}
		return new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));
		
	}
	
	/**
     * Debug select statement
     * @param Zend_Db_Select $select
     */
    protected function debugSelect( Zend_Db_Select $select, $method=__METHOD__){
        
        echo '<b>'.$method.'</b><br />';
        try {
           echo '<pre>'.$select->assemble().'</pre>';
        } catch (Zend_Db_Table_Select_Exception $e) {
            throw new Zend_Exception($e->getMessage(), $e->getCode(), $e);
        }
        
    }
	
}