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
	    	->joinLeft( array('CC'=>$this->contentCategoriesTable->getName()), "`A`.`content_cat`=`CC`.`id`", array(
	    		'content_cat_id'=>'id',
	    		'content_cat_title'=>'title',
	    		'content_cat_alias'=>'alias',
	    	))
	    	->joinLeft( array('CHC'=>$this->channelsCategoriesTable->getName()), "`A`.`channel_cat`=`CHC`.`id`", array(
	    		'channel_cat_id'=>'id',
	    		'channel_cat_title'=>'title',
	    		'channel_cat_alias'=>'alias',
	    	))
	    	->joinLeft( array('PC'=>$this->channelsCategoriesTable->getName()), "`A`.`prog_cat`=`PC`.`id`", array(
	    		'program_cat_id'=>'id',
	    		'program_cat_title'=>'title',
	    		'program_cat_alias'=>'alias',
	    	))
	    	->where( "`A`.`active` = TRUE AND `A`.`publish_down`<'".Zend_Date::now()->toString('YYYY-MM-dd')."'")
	    	->order( "A.publish_up DESC" )
	    	->limit( $amt );
	    
	    $result = $this->db->fetchAll($select);
	    
        if (!count($result)){
	        return array();
	    }
	    
	    foreach ($result as $k=>$item){
            if (isset($item['date_added']) && !empty($item['date_added'])){
                $result[$k]['date_added']   = new Zend_Date( $item['date_added'], 'YYYY-MM-dd' );
            }
            if (isset($item['publish_up']) && !empty($item['publish_up'])){
                $result[$k]['publish_up']   = new Zend_Date( $item['publish_up'], 'YYYY-MM-dd' );
            }
            if (isset($item['publish_down']) && !empty($item['publish_down'])){
                $result[$k]['publish_down'] = new Zend_Date( $item['publish_down'], 'YYYY-MM-dd' );
            }
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
				//'content_cat',
				//'channel_cat',
				//'prog_cat',
				'video_cat',
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

		if (APPLICATION_ENV=='development'){
		    Zend_Registry::get('fireLog')->log($select->assemble(), Zend_Log::INFO);
		    //die(__FILE__.': '.__LINE__);
	    }
	    
	    $result = $this->db->fetchAll($select);
	     
	    if (APPLICATION_ENV=='development'){
	    	//var_dump($result);
	    	//die(__FILE__.': '.__LINE__);
	    }
	     
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
	public function dayListingArticles( $currentProgram=array(), $channel=array(), $amt=10 ){
		
	    return $this->articlesTable->fetchAll(array(
			"`a`.`prog_cat`='".$currentProgram['category_id']."' OR `a`.`channel_cat`='".$channel['id']."' "
		), "a.publish_up DESC", $amt );
	    
	}
	
}