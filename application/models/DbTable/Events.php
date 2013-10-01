<?php
class Xmltv_Model_DbTable_Events extends Xmltv_Db_Table_Abstract
{
    protected $_name = 'bc_events';
	protected $_primary = 'id';
    protected $_rowClass = 'Rtvg_Event';
}
?>
