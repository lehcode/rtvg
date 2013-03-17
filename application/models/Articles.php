<?php
/**
 * Articles model
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @version $Id: Articles.php,v 1.1 2013-03-17 06:33:12 developer Exp $
 *
 */
class Xmltv_Model_DbTable_Articles extends Xmltv_Db_Table_Abstract
{
	protected $_name = 'content';
	protected $_primary = array('id');
	
	public function init()
	{
		parent::init();
	}
}