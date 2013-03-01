<?php 
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
    
    const FETCH_MODE = Zend_Db::FETCH_ASSOC;
    const ERR_PARAMETER_MISSING = "Пропущен параметр для ";
    const ERR_WRONG_DATE_FORMAT = "Неверный формат даты! ";
    
    /**
     * Constructor
     * 
     * @param array $config
     */
    public function __construct($config=array()){
        
        if (isset($config['name']) && !empty($config['name'])){
            $conf['name'] = $config['name'];
        }
        
        if (isset($config['primary']) && !empty($config['primary'])){
            $conf['primary'] = $config['primary'];
        }
        
        parent::__construct($conf);
        
        if (isset($config['tbl_prefix'])) {
        	$this->_pfx = (string)$config['tbl_prefix'];
        } else {
        	$this->_pfx = (string)Zend_Registry::get('app_config')->resources->multidb->local->get('tbl_prefix');
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
    public function setName($string=null) {
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
    
}