<?php 
/**
 * Abstract class for Rutvgid database tables
 *
 * @uses Zend_Db_Table_Abstract
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @version $Id: Abstract.php,v 1.11 2013-04-11 05:21:13 developer Exp $
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
    protected $_prefix = '';

    /**
     * Primary column
     * @var string
     */
    protected $_primary = array('id');
    
    /**
     * Container for default values to be used
     * in newly created row
     * @var array
     */
    protected $_defaultValues = array();
    
    const FETCH_MODE = Zend_Db::FETCH_OBJ;
    const ERR_PARAMETER_MISSING = "Пропущен параметр для ";
    const ERR_WRONG_DATE_FORMAT = "Неверный формат даты! ";
    const ERR_WRONG_DB_PREFIX = "Неверный префикс базы данных!";
    
    /**
     * Constructor
     * 
     * @param array $config
     */
    public function init(){
        
        $this->_prefix = (string)Zend_Registry::get('app_config')->resources->multidb->local->get('tbl_prefix');
        
        if (!$this->_prefix){
            throw new Zend_Exception(self::ERR_WRONG_DB_PREFIX);
        }
        
        $this->setName($this->_prefix.$this->_name);
        
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
     * Get table prefix string
     * 
     * @return string
     */
    protected function getPrefix(){
    	
        return $this->_prefix;
        
    }
    
}