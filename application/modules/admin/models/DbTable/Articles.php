<?php
/**
 * Articles model
 * 
 * @author     Antony Repin <egeshisolutions@gmail.com>
 * @subpackage backend
 * @version    $Id: Articles.php,v 1.3 2013-03-24 03:02:28 developer Exp $
 *
 */
class Admin_Model_DbTable_Articles extends Xmltv_Db_Table_Abstract
{
	
	protected $_name    = 'content';
	
	public function init()
	{
		parent::init();
	}
	
}