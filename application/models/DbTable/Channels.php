<?php
/**
 * Database table for channels info
 *
 * @uses Zend_Db_Table_Abstract
 * @version $Id: Channels.php,v 1.15 2013-03-01 19:37:58 developer Exp $
 */

class Xmltv_Model_DbTable_Channels extends Xmltv_Db_Table_Abstract
{

    protected $_name = 'channels';
    protected $_primary = 'id';
	
	const FETCH_MODE = Zend_Db::FETCH_OBJ;
	
	/**
	 * Constructor
	 * 
	 * @param array $config
	 */
    public function __construct($config=array()) {
    	
    	parent::__construct(array(
    		'name'=>$this->getName(),
    		'primary'=>$this->_primary,
    	));
    }
    
    /**
     * Load featired channels list
     * 
     * @param  string $order
     * @param  int    $total
     * @param  bool   $by_hits
     * @throws Zend_Exception
     * @return mixed
     */
    public function featuredChannels($total=20, $order='id'){
    	
    	return $this->fetchAll("`featured`='1'", $order, $total);
    	
    }
    
    /**
     * 
     * @param  string $alias
     * @throws Zend_Exception
     * @return Zend_Db_Table_Rowset
     */
    public function fetchCategory($alias=null){
    	
    	if (!$alias)
			throw new Zend_Exception("Не указан один или более параметров для ".__FUNCTION__, 500);
		
    	$select = $this->select()
    		->from(array('ch'=>$this->getName()), '*')
    		->join(array('cat'=>$this->channelsCategoriesTable->getName()), "`ch`.`category`=`cat`.`id`", array())
    		->where("`cat`.`alias` LIKE '$alias'")
    		->where("`ch`.`published`='1'")
    		->order("ch.title ASC");
    	
    	$result = $this->fetchAll($select);
    	return $result;
    	
    }
	
	/**
	 * 
	 * @param unknown_type $amt
	 */
	public function topChannels($amt=20, $offset=0){
		
	    die(__FILE__.': '.__LINE__);
	    
	    $ratings = new Xmltv_Model_DbTable_ChannelsRatings();
	    $select = $this->select(true)
	    	->from(array('ch'=>$this->getName()))
	    	->join(array('r'=>$ratings->getName()))
	    	->limit($amt);
	    	
	    if (APPLICATION_ENV=='development'){
	        Zend_Debug::dump($select->assemble());
	        die(__FILE__.': '.__LINE__);
	    }
	    
	}

	
	
}

