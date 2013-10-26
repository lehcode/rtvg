<?php
/**
 * Broadcasts ratings table class
 */
class Xmltv_Model_DbTable_ProgramsRatings extends Xmltv_Db_Table_Abstract
{

    protected $_name = 'bc_ratings';
    protected $_primary = 'hash';
	protected $pfx='';
    private $_bcs;
    private $_channelsTable;
	
    /**
     * 
     * Constructor
     * @param array $config
     */
    public function __construct($config=array()){
    	
    	parent::__construct(array(
    		'name'=>$this->_name,
    		'primary'=>$this->_primary
    	));
    	
    	$this->_bcs = new Xmltv_Model_DbTable_Programs();
    	$this->_channelsTable = new Xmltv_Model_DbTable_Channels();
    	
    }
    
    /**
     * (non-PHPdoc)
     * @see Zend_Db_Table_Abstract::_setup()
     */
    protected function _setup(){
    	parent::_setup();
    	$this->_defaultValues = array(
            'hash'=>0,
            'hits'=>0,
            'star_rating'=>0,
    	);
    		
    }
    
    /**
     * 
     * Add hit to program rating
     * @param string $alias
     * @param int $channel_id
     */
	public function addHit($hash=null){
    			
		if (!$hash || strlen($hash)!=32){
			throw new Exception("Не указан один или более параметров для ".__METHOD__, 500);
        }
		
        $alnum = new Zend_Filter_Alnum();
        $hash = $alnum->filter($hash);
		
        if (!$row = $this->find($hash)->current()) {
            $vals = $this->_defaultValues;
            $vals['hash'] = $hash;
            $row = $this->createRow($vals);
        }

        $row->hits+=1;
        $row->save();
		
    }
    
}

