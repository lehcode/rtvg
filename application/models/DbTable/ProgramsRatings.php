<?php

class Xmltv_Model_DbTable_ProgramsRatings extends Xmltv_Db_Table_Abstract
{

    protected $_name = 'bc_ratings';
    protected $_primary = 'id';
	protected $pfx='';
    const FETCH_MODE = Zend_Db::FETCH_OBJ;
    private $_broadcasts;
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
    	
    	$this->_broadcasts = new Xmltv_Model_DbTable_Programs();
    	$this->_channelsTable = new Xmltv_Model_DbTable_Channels();
    	
    }
    
    /**
     * (non-PHPdoc)
     * @see Zend_Db_Table_Abstract::_setup()
     */
    protected function _setup(){
    
    	parent::_setup();
    	$now = Zend_Date::now();
    	$this->_defaultValues = array(
    			'id'=>0,
    			'alias'=>'',
    			'channel'=>null,
    			'hits'=>0,
    			'star_rating'=>null,
    	);
    		
    }
    
    /**
     * 
     * Add hit to program rating
     * @param string $alias
     * @param int $channel_id
     */
	public function addHit($alias=null, $channel_id=null){
    			
		if (!$alias || !$channel_id)
			throw new Exception("Не указан один или более параметров для ".__METHOD__, 500);
    			
		
		try {
			if (!$row = $this->find($alias)->current()) {
				$row = $this->createRow( array('alias'=>$alias, 'channel'=>$channel_id), true);
			}
			
			$row->channel = $channel_id;		
			$row->hits+=1;
			$row->save();
			
		} catch (Exception $e) {
			if ($e->getCode()!=1062){
				throw new Zend_Exception($e->getMessage(), $e->getCode());
			}
		}
		
		
    }

}

