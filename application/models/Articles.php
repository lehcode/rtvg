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
	    	->from( array('A'=>$this->articlesTable->getName()), array( 
	    		'id',
	    		'title',
	    		'alias',
	    		'image',
	    		'metadesc',
	    	))
	    	->joinLeft( array('CC'=>$this->contentCategoriesTable->getName()), "`A`.`content_cat_id`=`CC`.`id`", array(
	    		'content_cat_id'=>'id',
	    		'content_cat_title'=>'title',
	    		'content_cat_alias'=>'alias',
	    	))
	    	->joinLeft( array('CHC'=>$this->channelsCategoriesTable->getName()), "`A`.`content_cat_id`=`CHC`.`id`", array(
	    		'channel_cat_id'=>'id',
	    		'channel_cat_title'=>'title',
	    		'channel_cat_alias'=>'alias',
	    	))
	    	->joinLeft( array('PC'=>$this->channelsCategoriesTable->getName()), "`A`.`bc_cat_id`=`PC`.`id`", array(
	    		'bc_cat_id'=>'id',
	    		'bc_cat_title'=>'title',
	    		'bc_cat_alias'=>'alias',
	    	))
	    	->where( "`A`.`active` = TRUE AND `A`.`publish_down`<'".Zend_Date::now()->toString('YYYY-MM-dd')."'")
	    	->order( "A.publish_up DESC" )
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
            $result[$k]['date_added']   = new Zend_Date( $item['date_added'], 'YYYY-MM-dd' );
            $result[$k]['publish_up']   = new Zend_Date( $item['publish_up'], 'YYYY-MM-dd' );
            $result[$k]['publish_down'] = new Zend_Date( $item['publish_down'], 'YYYY-MM-dd' );
	    }
	    
	    return $result;
	    
	}
	
	/**
	 * @param string $articleAlias
	 */
	public function singleItem( $articleAlias ){
		
	    $select = $this->db->select()
	    	->from( array('a'=>$this->articlesTable->getName()), array(
	    		'id',
	    		'title',
	    		'alias',
	    		'intro',
	    		'body',
	    		'tags',
	    		'metadesc',
				'hits',
				'is_ref',
				'is_paid',
				'is_cpa',
	    	))
	    	->joinLeft(array('cc'=>$this->contentCategoriesTable->getName()), "`a`.`content_cat`=`cc`.`id`", array(
	    		'content_cat_id'=>'id',
	    		'content_cat_title'=>'title',
	    		'content_cat_alias'=>'alias',
	    	))
	    	->joinLeft(array('chc'=>$this->channelsCategoriesTable->getName()), "`a`.`channel_cat`=`chc`.`id`", array(
	    		'channel_cat_id'=>'id',
	    		'channel_cat_title'=>'title',
	    		'channel_cat_alias'=>'alias',
	    	))
	    	->joinLeft(array('pc'=>$this->programsCategoriesTable->getName()), "`a`.`prog_cat`=`pc`.`id`", array(
	    		'program_cat_id'=>'id',
	    		'program_cat_title'=>'title',
	    		'program_cat_alias'=>'alias',
	    	))
	    	->where("`a`.`published`='1' AND `a`.`publish_down`<'".Zend_Date::now()->toString('YYYY-MM-dd')."' AND `a`.`alias`='$articleAlias' ")
	    	->limit( 1 );

		$result = $this->db->fetchAll($select);
	     
	    if (!count($result)){
	    	return false;
	    }
	    
	    foreach ($result as $k=>$item){
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
	        throw new Zend_Exception( Rtvg_Message::ERR_WRONG_PARAM );
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
		
		return $this->articlesTable->fetchAll(array(
			"`a`.`content_cat`='".$article['content_cat_id']."'",
			"`a`.`id` != '".(int)$article['id']."'"
		), "a.publish_up DESC", $amt );
	    
	}
	
	/**
	 * All articles categories list
	 */
	public function getCategories(){
		
	    return $this->contentCategoriesTable->fetchAll()->toArray();
	    
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
	
}