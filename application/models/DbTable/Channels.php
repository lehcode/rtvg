<?php
/**
 * Database table for channels info
 *
 * @uses Zend_Db_Table_Abstract
 * @version $Id: Channels.php,v 1.14 2013-02-25 11:40:40 developer Exp $
 */

class Xmltv_Model_DbTable_Channels extends Xmltv_Db_Table_Abstract
{

    protected $_name = 'channels';
    protected $channelsRatingsTable;
    protected $channelsCategoriesTable;
	
	const FETCH_MODE = Zend_Db::FETCH_OBJ;
	
	/**
	 * Constructor
	 * 
	 * @param array $config
	 */
    public function __construct($config=array()) {
    	
    	parent::__construct(array('name'=>$this->_name));
		$this->channelsRatingsTable = new Xmltv_Model_DbTable_ChannelsRatings();
		$this->channelsCategoriesTable = new Xmltv_Model_DbTable_ChannelsCategories();
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
    public function featuredChannels($total=20, $order='id', $by_hits=true){
    	
    	$select = $this->_db->select()
    		->from( array( 'ch'=>$this->getName() ), array( 'id', 'title', 'alias'=>'LOWER(`ch`.`alias`)') )
    		->join( array( 'r'=>$this->channelsRatingsTable->getName()), "`ch`.`id`=`r`.`id`", array('hits') )
	    	//->where( "`ch`.`featured`='1'" )
    		->limit( $total );
	    
	    if (!$by_hits){
	    	$select->order("$order ASC");
	    } else {
    		$select->order("r.hits DESC");
    		$select->order("ch.title ASC");
	    }
	    
	    if (APPLICATION_ENV=='development'){
	        echo "<b>".__METHOD__."</b>";
		    Zend_Debug::dump($select->assemble());
		    //die(__FILE__.': '.__LINE__);
	    }
	    
	    $result = $this->_db->query($select)->fetchAll(Zend_Db::FETCH_ASSOC);	    	
    	
	    if (APPLICATION_ENV=='development'){
	    	//Zend_Debug::dump($result);
		    //die(__FILE__.': '.__LINE__);
	    }
	    
	    return $result;
    	
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

