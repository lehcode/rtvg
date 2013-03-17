<?php
/**
 * Content categories database class
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @version $Id: ContentCategories.php,v 1.1 2013-03-17 06:33:12 developer Exp $
 *
 */
class Xmltv_Model_DbTable_ContentCategories extends Xmltv_Db_Table_Abstract
{

	protected $_name    = 'content_categories';
	protected $_primary = array('id');

	/**
	 * Constructor
	 * 
	 * @param array $config
	 */
	public function init() {
		parent::init();
	}
	
}