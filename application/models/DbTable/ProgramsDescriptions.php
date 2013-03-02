<?php
/**
 * Database table class
 * serving programs descriptions
 *
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @uses Xmltv_Db_Table_Abstract
 * @version $Id: ProgramsDescriptions.php,v 1.3 2013-03-02 15:29:45 developer Exp $
 *
 */
class Xmltv_Model_DbTable_ProgramsDescriptions extends Xmltv_Db_Table_Abstract
{

    protected $_name = 'programs_descs';
    protected $_primary = 'id';

    /**
     * Constructor
     * @param unknown_type $config
     */
    public function init () {
    	parent::init();
    }
    
    /**
     * (non-PHPdoc)
     * @see Zend_Db_Table_Abstract::_setup()
     */
    protected function _setup(){
    
    	parent::_setup();
    	$now = Zend_Date::now();
    	$this->_defaultValues = array(
    			'id'=>null,
    			'alias'=>null,
    			'channel'=>null,
    			'added'=>$now->toString('YYYY-MM-dd HH:mm:ss'),
    			'published'=>1,
    			'intro'=>'',
    			'text'=>'',
    	);
    		
    }

}

