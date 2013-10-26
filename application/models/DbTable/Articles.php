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
	    	->from( array('A'=>$this->getName()), '*')
            ->joinLeft( array('CONTCAT'=>$this->categoriesTable->getName()), "`A`.`content_cat`=`CONTCAT`.`id`",array(
                'content_cat_id'=>'id',
                'content_cat_title'=>'title',
                'content_cat_alias'=>'alias',
            ))
        ;
	    
	    if (is_array($where) && !empty($where)){
	        foreach ($where as $string){
	            $select->where( $string );
	        }
	    } elseif (is_string($where)){
	        $select->where( $where );
	    }
	    
	    $select->limit( $count, $offset );
	     
	    $result = $this->_db->fetchAll( $select );
	     
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