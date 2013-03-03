<?php
/**
 * Database table for channels comments
 *
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @uses Xmltv_Db_Table_Abstract
 * @version $Id: ChannelsComments.php,v 1.3 2013-03-03 23:34:13 developer Exp $
 */
class Xmltv_Model_DbTable_ChannelsComments extends Xmltv_Db_Table_Abstract
{

	protected $_name = 'channels_comments';
    protected $_primary = 'id';
    
    /**
     * (non-PHPdoc)
     * @see Xmltv_Db_Table_Abstract::init()
     */
    public function init() {
    	parent::init();
    }
    
    
    protected function _setup(){
        
    	parent::_setup();
    	$date = new Zend_Date();
    	$this->_defaultValues = array(
    		'id'=>null,
    		'author'=>'',
    		'intro'=>'',
    		'fulltext'=>'',
    		'date_created'=>$date->toString('YYYY-MM-dd HH:mm:ss'),
    		'date_added'=>$date->toString('YYYY-MM-dd HH:mm:ss'),
    		'published'=>1,
    		'src_url'=>'',
    		'feed_url'=>'',
    		'parent_id'=>''
    	);
    	
    }

}

