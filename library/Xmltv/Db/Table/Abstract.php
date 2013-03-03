<?php 
/**
 * Abstract class for Rutvgid database tables
 *
 * @uses Zend_Db_Table_Abstract
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @version $Id: Abstract.php,v 1.7 2013-03-03 23:30:36 developer Exp $
 */
class Xmltv_Db_Table_Abstract extends Zend_Db_Table_Abstract {
    
    /**
     * Table name
     * @var string
     */
    protected $_name = '';
    
    /**
     * Table prefix
     * @var string
     */
    protected $_pfx = '';

    /**
     * Table prefix
     * @var string
     */
    protected $_primary = 'id';
    
    const FETCH_MODE = Zend_Db::FETCH_ASSOC;
    const ERR_PARAMETER_MISSING = "Пропущен параметр для ";
    const ERR_WRONG_DATE_FORMAT = "Неверный формат даты! ";
    const ERR_WRONG_DB_PREFIX = "Неверный префикс базы данных!";
    
    /**
     * Constructor
     * 
     * @param array $config
     */
    public function init(){
        
        $this->_pfx = (string)Zend_Registry::get('app_config')->resources->multidb->local->get('tbl_prefix');
        
        if (!$this->_pfx){
            throw new Zend_Exception(self::ERR_WRONG_DB_PREFIX);
        }
        
        $this->setName($this->_pfx.$this->_name);
        
    }
    
    /**
     * @return string
     */
    public function getName() {
    	return $this->_name;
    }
    
    /**
     * @param string $string
     */
    protected function setName($string=null) {
    	$this->_name = $string;
    }
    
    /**
     * Debug select statement
     * @param Zend_Db_Select $select
     */
    protected function debugSelect( Zend_Db_Select $select, $method=__METHOD__){
        
        echo '<b>'.$method.'</b><br />';
        try {
           echo '<pre>'.$select->assemble().'</pre>';
        } catch (Zend_Db_Table_Select_Exception $e) {
            throw new Zend_Exception($e->getMessage(), $e->getCode(), $e);
        }
        
    }
    
    /**
     * (non-PHPdoc)
     * @see Zend_Db_Table_Abstract::_setup()
     */
    protected function _setup(){
    	parent::_setup();
    }
    
}