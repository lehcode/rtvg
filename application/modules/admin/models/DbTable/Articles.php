<?php
/**
 * Articles model
 * 
 * @author     Antony Repin <egeshisolutions@gmail.com>
 * @package backend
 * @version    $Id: Articles.php,v 1.4 2013-04-03 04:08:16 developer Exp $
 *
 */
class Admin_Model_DbTable_Articles extends Xmltv_Db_Table_Abstract
{
	
	protected $_name    = 'articles';
	protected $_primary = array('id');
	protected $_defaultValues = array(
		'id'=>0,
		'title'=>'',
		'alias'=>'',
		'intro'=>'',
		'body'=>'',
		'tags'=>'',
		'image'=>'',
		'metadesc'=>'',
		'content_cat'=>null,
		'channel_cat'=>null,
		'prog_cat'=>null,
		'video_cat'=>'"NULL"',
		'hits'=>0,
		'published'=>0,
		'publish_up'=>null,
		'publish_down'=>null,
		'added'=>null,
		'author'=>null,
		'is_ref'=>0,
		'is_paid'=>0,
		'is_cpa'=>1,
	);
	
	/**
	 * (non-PHPdoc)
	 * @see Xmltv_Db_Table_Abstract::init()
	 */
	public function init()
	{
		parent::init();
		$this->setRowClass('Rtvg_Content_Article');
		
		$now = Zend_Date::now();
		$this->_defaultValues['id']           = $this->_db->quote(null);
		$this->_defaultValues['video_cat']    = $this->_db->quote(null);
		$this->_defaultValues['publish_up']   = $now->toString('YYYY-MM-dd');
		$this->_defaultValues['added']        = $now->toString('YYYY-MM-dd');
		$this->_defaultValues['publish_down'] = $now->subDay(1)->toString('YYYY-MM-dd');
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Zend_Db_Table_Abstract::createRow()
	 */
	public function createRow(array $data=null, $defautSource=null){
	
		$rowData = parent::createRow($data, $defautSource);
	
		foreach ($this->_defaultValues as $dK=>$dV){
			if (!$rowData->$dK && $dK!=$this->_primary[0]) {
				$rowData->$dK = $dV;
			}
		}
	
		return $rowData;
	
	}
	
}