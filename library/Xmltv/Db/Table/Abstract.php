<?php 
class Xmltv_Db_Table_Abstract extends Zend_Db_Table_Abstract {
    
    protected $_name = '';
    protected $_pfx = '';
    
    const FETCH_MODE = Zend_Db::FETCH_OBJ;
    const ERR_PARAMETER_MISSING = "Пропущен параметр!";
    
    /**
     * Constructor
     * 
     * @param array $config
     */
    public function __construct($config=array()){
        
        parent::__construct(array('name'=>$config['name']));
        
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
    
}