<?php
/**
 * Database table for channels categories info
 *
 * @uses Xmltv_Db_Table_Abstract
 * @version $Id: ChannelsCategories.php,v 1.6 2013-03-03 23:34:13 developer Exp $
 */
class Xmltv_Model_DbTable_ChannelsCategories extends Xmltv_Db_Table_Abstract
{
	
	protected $_name    = 'channels_categories';
	protected $_primary = 'id';
	
		
	/**
	 * (non-PHPdoc)
	 * @see Zend_Db_Table_Abstract::init()
	 */
	public function init() {
		parent::init();
	}
	
	public function fetchId($alias = null){
		return $this->fetchRow("`alias`='$alias'");
	}
	
}

