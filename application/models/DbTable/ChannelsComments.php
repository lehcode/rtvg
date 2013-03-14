<?php
/**
 * Database table for the channels comments
 *
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @uses Xmltv_Db_Table_Abstract
 * @version $Id: ChannelsComments.php,v 1.6 2013-03-14 06:47:08 developer Exp $
 */
class Xmltv_Model_DbTable_ChannelsComments extends Xmltv_Db_Table_Abstract
{

	protected $_name = 'channels_comments';
    protected $_primary = 'id';
    protected $_defaultValues = array(
        'id'=>0,
        'author'=>'',
        'intro'=>'',
        'created'=>null,
        'added'=>null,
        'published'=>1,
        'src_url'=>'',
        'parent_id'=>'',
        'url_crc'=>'' );
    
    /**
     * (non-PHPdoc)
     * @see Xmltv_Db_Table_Abstract::init()
     */
    public function init() {
    	
        $this->_pfx = Zend_Registry::get('app_config')->resources->multidb->local->get('tbl_prefix');
        if (!$this->_pfx){
            throw new Zend_Exception(self::ERR_WRONG_DB_PREFIX);
        }
        $this->setName($this->_pfx.$this->_name);
        
        $this->setRowClass( 'Rtvg_Comment_Item' );
        $this->setRowsetClass( 'Rtvg_Comment_Collection' );
        $this->_defaultValues['added'] = Zend_Date::now()->toString("YYYY-MM-dd HH:mm:ss");
    	
    }
    
    
    
    /**
     * (non-PHPdoc)
     * @see Zend_Db_Table_Abstract::createRow()
     */
    public function createRow(array $data = array(), $defaultSource = null){
    	
        $rowData = parent::createRow($data, $defaultSource);
        
        foreach ($this->_defaultValues as $dK=>$dV){
            if (!$rowData->$dK) {
            	$rowData->$dK = $dV;
            }
        }
        
        $rowData->published = $this->_defaultValues['published'];
        $rowData->url_crc = base64_encode( hash('crc32', $rowData->src_url, true));
        
        if (APPLICATION_ENV=='development'){
            //var_dump($rowData);
            //die(__FILE__.': '.__LINE__);
        }
        
        return $rowData;
        
    }

}

