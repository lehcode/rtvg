<?php
/**
 * Database table for channels info
 *
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @uses    Zend_Db_Table_Abstract
 * @version $Id: Channels.php,v 1.16 2013-03-03 23:34:13 developer Exp $
 */

class Xmltv_Model_DbTable_Channels extends Xmltv_Db_Table_Abstract
{

    protected $_name = 'channels';
    protected $_primary = 'id';
	
	const FETCH_MODE = Zend_Db::FETCH_OBJ;
	
	/**
	 * (non-PHPdoc)
	 * @see Xmltv_Db_Table_Abstract::init()
	 */
    public function init() {
    	parent::init();
    }
    
    /**
     * (non-PHPdoc)
     * @see Xmltv_Db_Table_Abstract::_setup()
     */
    protected function _setup(){
        parent::_setup();
        $now = Zend_Date::now();
        $this->_defaultValues = array(
        		'id'=>null,
        		'title'=>null,
        		'alias'=>null,
        		'desc_intro'=>'',
        		'desc_body'=>'',
        		'category'=>null,
        		'featured'=>1,
        		'icon'=>'',
        		'format'=>'dvb',
        		'published'=>1,
        		'parse'=>1,
        		'lang'=>'',
        		'url'=>'',
        		'country'=>'',
        		'adult'=>0,
        		'keywords'=>'',
        		'metadesc'=>'',
        		'video_aspect'=>'16:9',
        		'video_quality'=>'720x576',
        		'audio'=>'dolby',
        		'added'=>$now->toString('YYYY-MM-dd'),
        );
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

