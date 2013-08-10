<?php
class Rtvg_Listing_Broadcast extends Zend_Db_Table_Row_Abstract
{
    /**
	 * Saves the properties to the database.
	 *
	 * This performs an intelligent insert/update, and reloads the
	 * properties with fresh data from the table on success.
	 *
	 * @return mixed The primary key value(s), as an associative array if the
	 * key is compound, or a scalar if the key is single-column.
	 */
	public function save()
	{
		/**
		 * If the _cleanData array is empty,
		 * this is an INSERT of a new row.
		 * Otherwise it is an UPDATE.
		 */
		if (empty($this->_cleanData)) {
			return $this->_doInsert();
		} else {
			return $this->_doUpdate();
		}
	}
}