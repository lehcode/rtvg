<?php
/**
 * Database table for channels comments
 *
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @uses Xmltv_Db_Table_Abstract
 * @version $Id: ChannelsComments.php,v 1.4 2013-03-05 06:53:19 developer Exp $
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
    	
        $this->_defaultValues = array(
        		'id'=>null,
        		'author'=>'',
        		'intro'=>'',
        		'fulltext'=>'',
        		'date_created'=>null,
        		'date_added'=>null,
        		'published'=>1,
        		'src_url'=>'',
        		'feed_url'=>'',
        		'parent_id'=>''
        );
        
        parent::init();
        
    }

}

