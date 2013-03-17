<?php
/**
 * Content articles backend model
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @subpackage backend
 * @version $Id: Articles.php,v 1.2 2013-03-17 18:34:58 developer Exp $
 *
 */
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
	 * Content articles database table
	 * @var Admin_Model_DbTable_Articles
	 */
	protected $articlesTable;

	/**
	 * Content categories database table
	 * @var Admin_Model_DbTable_ContentCategories
	 */
	protected $contentCategoriesTable;
	
	/**
	 * Content categories database table
	 * @var Admin_Model_DbTable_ChannelsCategories
	 */
	protected $channelsCategoriesTable;
	
	/**
	 * Content categories database table
	 * @var Admin_Model_DbTable_ProgramsCategories
	 */
	protected $programsCategoriesTable;
	
	/**
	 * 
	 * Enter description here ...
	 * @param array $config
	 */
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
		
		$this->articlesTable           = new Admin_Model_DbTable_Articles();
		$this->contentCategoriesTable  = new Xmltv_Model_DbTable_ContentCategories();
		$this->channelsCategoriesTable = new Xmltv_Model_DbTable_ChannelsCategories();
		$this->programsCategoriesTable = new Xmltv_Model_DbTable_ProgramsCategories();
		
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
	 */
	public function getList(){
		
		$select = $this->db->select()
			->from(array('a'=>$this->articlesTable->getName()), array(
				'id',
				'title',
				'alias',
				'intro',
				'body',
				'tags',
				'metadesc',
				'metakeys',
			))
			->join( array('content_cat'=>$this->contentCategoriesTable->getName()), "`a`.`content_cat`=`content_cat`.`id`", array(
				'content_cat_id'=>'id',
				'content_cat_title'=>'title',
				'content_cat_alias'=>'alias',
			))
			->join( array('channel_cat'=>$this->channelsCategoriesTable->getName()), "`a`.`channel_cat`=`channel_cat`.`id`", array(
				'channel_cat_id'=>'id',
				'channel_cat_title'=>'title',
				'channel_cat_alias'=>'alias',
			))
			->join( array('prog_cat'=>$this->programsCategoriesTable->getName()), "`a`.`channel_cat`=`prog_cat`.`id`", array(
				'prog_cat_id'=>'id',
				'prog_cat_title'=>'title',
				'prog_cat_alias'=>'alias',
			))
			->where("`a`.`published`=1");
			
		if (APPLICATION_ENV=='development'){
			//self::debugSelect($select, __METHOD__);
			//die(__FILE__.': '.__LINE__);
		}
		
		/**
		 * @var Zend_Paginator
		 */
		$result = $this->getPaginator( $select );
		
		return $result;
		
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