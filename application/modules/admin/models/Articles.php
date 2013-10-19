<?php
/**
 * Content articles backend model
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @package backend
 * @version $Id: Articles.php,v 1.5 2013-04-03 04:08:16 developer Exp $
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
	 * @var Zend_Db_Adapter_Pdo_Mysql
	 */
	protected $db;
	
	/**
	 * Content articles database table
	 * @var Admin_Model_DbTable_Articles
	 */
	protected $contentTable;

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
		
		if (is_array($config)) {
	    	$this->setOptions($config);
	    }
	    
		// Init database
		$this->db = Zend_Registry::get('db_local');
		
		$this->contentTable            = new Admin_Model_DbTable_Articles();
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
	public function getList( $only_published=false ){
		
	    $usersTable = new Xmltv_Model_DbTable_Users();
		$select = $this->db->select()
			->from(array('a'=>$this->contentTable->getName()), array(
				'id',
				'title',
				'alias',
				'intro',
				'body',
				'tags',
				'metadesc',
				'published',
				'added',
				'publish_up',
				'publish_down',
				'author',
			))
			->joinLeft( array('content_cat'=>$this->contentCategoriesTable->getName()), "`a`.`content_cat`=`content_cat`.`id`", array(
				'content_cat_id'=>'id',
				'content_cat_title'=>'title',
				'content_cat_alias'=>'alias',
			))
			->joinLeft( array('channel_cat'=>$this->channelsCategoriesTable->getName()), "`a`.`channel_cat`=`channel_cat`.`id`", array(
				'channel_cat_id'=>'id',
				'channel_cat_title'=>'title',
				'channel_cat_alias'=>'alias',
			))
			->joinLeft( array('prog_cat'=>$this->programsCategoriesTable->getName()), "`a`.`prog_cat`=`prog_cat`.`id`", array(
				'prog_cat_id'=>'id',
				'prog_cat_title'=>'title',
				'prog_cat_alias'=>'alias',
			))
			->joinLeft( array('u'=>$usersTable->getName()), "`a`.`author`=`u`.`id`", array(
				'author_name'=>'display_name',
				'author_email'=>'email'));
			
			if ($only_published===true){
				$select->where("`a`.`published`=1");
			}
			
		if (APPLICATION_ENV=='development'){
			self::debugSelect($select, __METHOD__);
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
				->from(array('a'=>$this->contentTable->getName()));
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
    
    public function allCategories($as_array=false){
    	
        $r = $this->programsCategoriesTable->fetchAll(null, "title ASC");
        $result['programs'] = $as_array===true ? $r->toArray() : $r ;
        $r = $this->channelsCategoriesTable->fetchAll(null, "title ASC");
    	$result['channel']  = $as_array===true ? $r->toArray() : $r ;
    	$r = $this->contentCategoriesTable->fetchAll(null, "title ASC");
    	$result['content']  = $as_array===true ? $r->toArray() : $r ;
    	return $result;
    }
    
    public function getArticle($id){
    	
    	return $this->contentTable->fetchRow( "`id`=".$this->db->quote($id) );
    	
    }
    
    /**
     * Save article
     * 
     * @param  array $data
     * @throws Zend_Exception
     * @return boolean
     */
    public function saveArticle(array $data=null){
        
        if (isset($data['id']) && (int)$data['id']!=0){
            $this->updateArticle($data);
    	} else {
    	    $row = $this->contentTable->createRow($data);
    	    if (APPLICATION_ENV=='development'){
    	        //var_dump($row->toArray());
    	    	//Zend_Registry::get('fireLog')->log($sql, Zend_Log::INFO);
    	    	//die(__FILE__.': '.__LINE__);
    	    }
    	    $this->contentTable->insert( $row->toArray(), 'id' );
    	}
    	
    	return true;
    	
    }
    
    /**
     * Update row in database
     * 
     * @param  array $data
     * @throws Zend_Exception
     */
    private function updateArticle(array $data=null){
    	
        $update = array();
        foreach ($data as $key=>$value){
        	$update[] = $this->db->quoteIdentifier($key) . '=' . $this->db->quote( $value );
        }
        $sql = "UPDATE `".$this->contentTable->getName()."` SET ".implode( ',', $update )." WHERE `id`=".$data['id'];
        
        if (APPLICATION_ENV=='development'){
        	Zend_Registry::get('fireLog')->log($sql, Zend_Log::INFO);
        	//die(__FILE__.': '.__LINE__);
        }
        
        if(!$this->db->query( $sql )){
        	throw new Zend_Exception( Rtvg_Message::ERR_CANNOT_UPDATE_ROW, 500 );
        }
        
    }
    
    /**
     * 
     * @param  int $id
     * @throws Zend_Exception
     */
    public function deleteArticle($id=null){
        
        if (!$id) 
            throw new Zend_Exception( Rtvg_Message::ERR_WRONG_PARAM, 501 );
        
        try {
            $this->contentTable->delete("`id`='$id'");
        } catch (Exception $e) {
            throw new Zend_Exception( Rtvg_Message::ERR_CANNOT_DELETE_ROW, 501 );
        }
        
        return true;
        
    }
    
    public function toggleArticleState($id=null){
    	
        if (!$id) {
            throw new Zend_Exception( Rtvg_Message::ERR_WRONG_PARAM, 501 );
        }
        
        $row = $this->contentTable->fetchRow( "`id`=".(int)$id );
        //var_dump($row->id);
        var_dump($row->published);
        if (!$row->published){
            $published=1;
        }
        if ($row->published==1){
        	$published=0;
        }
        
        $sql = "UPDATE `".$this->contentTable->getName()."` SET `published`=$published WHERE `id`=".(int)$id;
        $this->db->query($sql);
        
        if (APPLICATION_ENV=='development'){
	        //var_dump($result);
	        //die(__FILE__.': '.__LINE__);
        }
        
    }
    
    public function getContentTags(){
    	
        $select = $this->db->select()
        	->from($this->contentTable->getName(), array('tags'=>'LOWER(`tags`)'));
        $result = $this->db->fetchAll($select);
        
        if (APPLICATION_ENV=='development'){
            //var_dump($result);
        	//die(__FILE__.': '.__LINE__);
        }
        
        $acTags = array();
        foreach ($result as $item){
            $t = explode(',', $item['tags']);
            foreach ($t as $tag){
                $tag = trim($tag);
                if (!in_array($tag, $acTags)){
                    $acTags[]=$tag;
                }
            }
            
        }
        
        if (APPLICATION_ENV=='development'){
        	//var_dump($acTags);
        	//die(__FILE__.': '.__LINE__);
        }
        
        return $acTags;
        
    }
	
}