<?php
/**
 * Database table for storing torrents info
 *
 * @uses Zend_Db_Table_Abstract
 * @version $Id: Torrents.php,v 1.2 2013-03-03 23:34:13 developer Exp $
 */

class Xmltv_Model_DbTable_Torrents extends Xmltv_Db_Table_Abstract
{

    protected $_name = 'torrents';
    //protected $_primary = 'id';
	
    /**
     * (non-PHPdoc)
     * @see Zend_Db_Table_Abstract::init()
     */
	public function init() {
	    parent::init();
    }

	
}

