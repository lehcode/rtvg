<?php
/**
 * Articles model
 * 
 * @author     Antony Repin <egeshisolutions@gmail.com>
 * @subpackage backend
 * @version    $Id: Articles.php,v 1.2 2013-03-17 18:34:58 developer Exp $
 *
 */
class Admin_Model_DbTable_Articles extends Xmltv_Db_Table_Abstract
{
	
	protected $_name    = 'content';
	protected $_primary = array('id');
	
	public function init()
	{
		parent::init();
	}
}