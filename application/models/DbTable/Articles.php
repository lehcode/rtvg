<?php
/**
 * Frontend articles model
 * 
 * @author     Antony Repin <egeshisolutions@gmail.com>
 * @subpackage frontend
 * @version    $Id: Articles.php,v 1.2 2013-04-03 04:08:16 developer Exp $
 *
 */
class Xmltv_Model_DbTable_Articles extends Xmltv_Db_Table_Abstract
{
	
    protected $_name    = 'articles';
    protected $_primary = array('id');
    
    /**
     * @var Xmltv_Model_DbTable_ContentCategories
     */
    private $categoriesTable;
    
	public function init()
	{
		parent::init();
		$this->categoriesTable = new Xmltv_Model_DbTable_ContentCategories();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Zend_Db_Table_Abstract::fetchAll()
	 */
	public function fetchAll($where = null, $order = null, $count = null, $offset = null){
		
	    $select = $this->_db->select()
	    	->from( array('a'=>$this->getName()), array(
	    		'id',
	    		'title',
	    		'alias',
	    		'intro',
	    		'body',
	    		'tags',
	    		'metadesc',
	    		'added',
	    		'publish_up',
	    		'publish_down',
	    		'hits',
	    		'is_ref',
	    		'is_paid',
	    		'is_cpa',
	    ))
	    ->joinLeft( array('cc'=>$this->categoriesTable->getName()), "`a`.`content_cat`=`cc`.`id`",array(
	    	'content_cat_id'=>'cc.id',
	    	'content_cat_title'=>'cc.title',
	    	'content_cat_alias'=>'cc.alias',
	    ) );
	    
	    if (is_array($where) && !empty($where)){
	        foreach ($where as $string){
	            $select->where( $string );
	        }
	    } elseif (is_string($where)){
	        $select->where( $where );
	    }
	    
	    $select->limit( $count, $offset );
	     
	    if (APPLICATION_ENV=='development'){
	        //var_dump($select->assemble());
	    	Zend_Registry::get('console_log')->log( $select->assemble(), Zend_Log::INFO );
	    	//die(__FILE__.': '.__LINE__);
	    }
	     
	    $result = $this->_db->fetchAll( $select );
	     
	    if (APPLICATION_ENV=='development'){
	        //var_dump($result);
	    	Zend_Registry::get('console_log')->log( $result, Zend_Log::INFO );
	    	//die(__FILE__.': '.__LINE__);
	    }
	     
	    if (!count($result)){
	    	return false;
	    }
	    
	    foreach ($result as $k=>$item){
	    	$item->added        = new Zend_Date( $item->added, 'YYYY-MM-dd' );
	    	$item->publish_up   = new Zend_Date( $item->publish_up, 'YYYY-MM-dd' );
	    	$item->publish_down = new Zend_Date( $item->publish_down, 'YYYY-MM-dd' );
	    }
	     
	    return $result;
	    
	}
	
}