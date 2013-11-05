<?php
/**
 * Articles model
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @version $Id: Articles.php,v 1.5 2013-04-03 04:08:16 developer Exp $
 *
 */
class Xmltv_Model_Articles extends Xmltv_Model_Abstract
{
	/**
	 * Table prefix
	 * @var string
	 */
	protected static $tblPfx='';
	
	/**
	 * @var Xmltv_Model_DbTable_Articles
	 */
	protected $articlesTable;
	
	/**
	 * @var Xmltv_Model_DbTable_ContentCategories
	 */
	protected $contentCategoriesTable;

	/**
	 * @var Xmltv_Model_DbTable_ProgramsCategories
	 */
	protected $programsCategoriesTable;

	/**
	 * @var Xmltv_Model_DbTable_ChannelsCategories
	 */
	protected $channelsCategoriesTable;
	
	public function __construct(array $config=null)
	{
		parent::__construct($config);
		$this->articlesTable           = new Xmltv_Model_DbTable_Articles();
		$this->contentCategoriesTable  = new Xmltv_Model_DbTable_ContentCategories();
		$this->programsCategoriesTable = new Xmltv_Model_DbTable_ProgramsCategories();
		$this->channelsCategoriesTable = new Xmltv_Model_DbTable_ChannelsCategories();
		
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
	
	/**
	 * Articles list for frontpage
	 * 
	 * @param int $amt
	 */
	public function frontpageItems($amt=10){
		
	    $select = $this->db->select()
	    	->from( array('ART'=>$this->articlesTable->getName()), array( 
	    		'id',
	    		'title',
	    		'alias',
	    		'image',
	    		'metadesc',
	    		'added',
	    	))
	    	->joinLeft( array('CC'=>$this->contentCategoriesTable->getName()), "`ART`.`content_cat_id`=`CC`.`id`", array(
	    		'content_cat_id'=>'id',
	    		'content_cat_title'=>'title',
	    		'content_cat_alias'=>'alias',
	    	))
	    	->joinLeft( array('CHC'=>$this->channelsCategoriesTable->getName()), "`ART`.`content_cat_id`=`CHC`.`id`", array(
	    		'channel_cat_id'=>'id',
	    		'channel_cat_title'=>'title',
	    		'channel_cat_alias'=>'alias',
	    	))
	    	->joinLeft( array('PC'=>$this->channelsCategoriesTable->getName()), "`ART`.`bc_cat_id`=`PC`.`id`", array(
	    		'bc_cat_id'=>'id',
	    		'bc_cat_title'=>'title',
	    		'bc_cat_alias'=>'alias',
	    	))
	    	->where("`ART`.`active` = TRUE")
            ->where("`ART`.`publish_down` IS NULL OR `ART`.`publish_down` < '".Zend_Date::now()->toString('YYYY-MM-dd')."'")
	    	->order("ART.publish_up DESC" )
	    	->limit( $amt );
	    
        $result = $this->db->fetchAll($select);
	    
        if (!count($result)){
	        return array();
	    }
	    
	    foreach ($result as $k=>$item){
            $result[$k]['id'] = (int)$item['id'];
            $result[$k]['content_cat_id'] = (int)$item['content_cat_id'];
            $result[$k]['bc_cat_id'] = (int)$item['bc_cat_id'];
            $result[$k]['channel_cat_id'] = (int)$item['channel_cat_id'];
            $result[$k]['added']   = new Zend_Date( $item['added'], 'YYYY-MM-dd' );
	    }
	    
	    return $result;
	    
	}
	
	/**
	 * @param string $articleAlias
	 */
	public function singleItem( $articleAlias ){
		
	    $select = $this->db->select()
	    	->from( array('ART'=>$this->articlesTable->getName()), array(
	    		'id',
	    		'title',
	    		'alias',
	    		'intro',
	    		'body',
	    		'tags',
	    		'metadesc',
	    	))
	    	->join(array('CONTCAT'=>$this->contentCategoriesTable->getName()), "`ART`.`content_cat_id`=`CONTCAT`.`id`", array(
	    		'content_cat_id'=>'id',
	    		'content_cat_title'=>'title',
	    		'content_cat_alias'=>'alias',
	    	))
	    	->join(array('CHCAT'=>$this->channelsCategoriesTable->getName()), "`ART`.`channel_cat_id`=`CHCAT`.`id`", array(
	    		'channel_cat_id'=>'id',
	    		'channel_cat_title'=>'title',
	    		'channel_cat_alias'=>'alias',
	    	))
	    	->join(array('BCCAT'=>$this->programsCategoriesTable->getName()), "`ART`.`bc_cat_id`=`BCCAT`.`id`", array(
	    		'bc_cat_id'=>'id',
	    		'bc_cat_title'=>'title',
	    		'bc_cat_alias'=>'alias',
	    	))
            ->joinLeft(array('RATING'=>$this->articlesRatingTable->getName()), "`ART`.`id` = `RATING`.`article`", array(
                'hits',
                'rating'
            ))
	    	->where("`ART`.`active` = TRUE")
            ->where("`ART`.`publish_down` > '".Zend_Date::now()->toString('YYYY-MM-dd')."' OR `ART`.`publish_down` IS NULL")
            ->where("`ART`.`alias` = '$articleAlias'")
	    	->limit(1)
        ;
        
        $result = $this->db->fetchAll($select);
	     
	    if (!count($result)){
	    	return array();
	    }
	    
	    foreach ($result as $k=>$item){
	    	$result[$k]['id'] = (int)$item['id'];
	    	$result[$k]['hits'] = (int)$item['hits'];
	    	$result[$k]['is_ref'] = (bool)$item['is_ref'];
	    	$result[$k]['is_paid'] = (bool)$item['is_paid'];
	    	$result[$k]['is_cpa'] = (bool)$item['is_cpa'];
	    	$result[$k]['content_cat_id'] = (int)$item['content_cat_id'];
	    	$result[$k]['channel_cat_id'] = (int)$item['channel_cat_id'];
	    	$result[$k]['bc_cat_id'] = (int)$item['bc_cat_id'];
	    	$result[$k]['date_added']   = new Zend_Date( $item['date_added'], 'YYYY-MM-dd' );
	    	$result[$k]['publish_up']   = new Zend_Date( $item['publish_up'], 'YYYY-MM-dd' );
	    	$result[$k]['publish_down'] = new Zend_Date( $item['publish_down'], 'YYYY-MM-dd' );
	    }
	     
	    return $result;
	    
	}
	
	/**
	 * get realted items by source type
	 * 
	 * @param array  $item // source object
	 * @param string $type // article|channel|program|video
	 */
	public function relatedItems( $item=null, $type='article' ){
		
	    if (!$item || empty($item) || !is_array($item)){
	        throw new Zend_Exception( "Parent article for related items is missing" );
	    }
	    
	    switch ($type){
	    	default:
	    	    throw new Zend_Exception( "Type was not defined" );
	    	break;
	    	case 'article':
	    	    return $this->articleRelatedItems( $item );
	    	break;
	    	case 'channel':
	    	    return $this->channelRelatedItems( $item );
	    	break;
	    	case 'program':
	    	    return $this->programRelatedItems( $item );
	    	break;
	    	case 'video':
	    	    return $this->videoRelatedItems( $item );
	    	break;
	    }
	    
	}
	
	/**
	 * Fetch items related to article
	 * by content category
	 * 
	 * @param array $article
	 * @param int   $amt
	 */
	private function articleRelatedItems( $article, $amt=4 ){
		
        $select = $this->db->select()
            ->from(array('ART'=>$this->articlesTable->getName()))
            ->join(array('CONTCAT'=>$this->contentCategoriesTable->getName()), "`ART`.`content_cat_id` = `CONTCAT`.`id`", array(
                'content_cat_alias'=>'alias'
            ))
            ->where("`ART`.`content_cat_id`= ".(int)$article['content_cat_id'])
            ->where("`ART`.`id` != ".(int)$article['id'])
            ->order("publish_up DESC")
            ->limit((int)$amt);
        
        return $this->db->fetchAll($select);
	}
	
	/**
	 * Complete list of categories of articles
	 */
	public function getCategories(){
		
	    $result = $this->contentCategoriesTable->fetchAll()->toArray();
        foreach ($result as $k=>$val){
            $result[$k]['id'] = $val['id'];
        }
        return $result;
        
	}
    
    public function randomCategory(){
        
        $cats = $this->getCategories();
        $idx = array_rand($cats, 1);
        return $cats[$idx];
        
    }
	
	/**
	 * Fetch articles for ListingsController::day-listing()
	 * 
	 * @param array $currentProgram
	 * @param array $channel
	 * @param int   $amt
	 */
	public function dayListingArticles( $broadcast=null, $amt=5 ){
		
        $select = $this->db->select();
        $select->from(array('A'=>$this->articlesTable->getName()), "*")
            ->join(array('CC'=>$this->contentCategoriesTable->getName()), "A.bc_cat_id = CC.id", array(
                'content_cat_id'=>'id',
                'content_cat_title'=>'title',
                'content_cat_alias'=>'alias',
            ))
            ->where("A.bc_cat_id = ".(int)$broadcast['category'])
            ->order("A.publish_up DESC")
            ->limit($amt)
        ;
        
        $result = $this->db->fetchAll($select);
        
        if (count($result)==0){
            $select->from(array('ART'=>$this->articlesTable->getName()), "*")
                ->join(array('CCC'=>$this->contentCategoriesTable->getName()), "A.bc_cat_id = CCC.id", array(
                    'content_cat_id'=>'id',
                    'content_cat_title'=>'title',
                    'content_cat_alias'=>'alias',
                ))
                ->order("ART.publish_up DESC")
                ->where("A.bc_cat_id = ".(int)$broadcast['category'])
                ->limit($amt)
            ;
        }
        
        return $result;
	}
    
    /**
     * Fetch list of articles belonging to particular category
     * 
     * @param int $cat_id
     * @param int $amt
     */
    public function categoryItems($cat_id=null, $amt=5){
        
        if (!$cat_id || !is_int($cat_id)){
            
        }
        
        $select = $this->db->select()
            ->from(array('ART'=>$this->articlesTable->getName()), array(
                'id',
                'title',
                'alias',
                'intro',
                'body',
                'metadesc',
                'tags'=>'LOWER(ART.tags)',
                'added',
                'author',
            ))
            ->join(array('CONTCAT'=>$this->contentCategoriesTable->getName()), "`ART`.`content_cat_id` = `CONTCAT`.`id`", array(
                'content_cat_id'=>'id',
                'content_cat_title'=>'title',
                'content_cat_alias'=>'alias',
            ))
            ->where("ART.active = TRUE")
            ->where("ART.publish_up <= '".Zend_Date::now()->toString("YYYY-MM-dd")."'")
            ->where("ART.publish_down > '".Zend_Date::now()->toString("YYYY-MM-dd")."' OR ART.publish_down IS NULL")
            ->where("ART.content_cat_id = " . (int)$cat_id)
            ->limit((int)$amt);
        ;
        
        $result = $this->db->fetchAll($select);
        
        foreach ($result as $k=>$item){
            $result[$k]['id'] = (int)$item['id'];
            $result[$k]['content_cat_id'] = (int)$item['content_cat_id'];
            $result[$k]['added'] = new Zend_Date($item['added'], "YYYY-MM-dd");
        }
        
        return $result;
        
    }
    
    
	
}