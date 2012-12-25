<?php

class Xmltv_Model_DbTable_ProgramsRatings extends Zend_Db_Table_Abstract
{

    protected $_name = 'programs_ratings';
	
    const FETCH_MODE = Zend_Db::FETCH_OBJ;
	
    /**
     * 
     * Constructor
     * @param array $config
     */
    public function __construct($config=array()){
    	
    	parent::__construct(array('name'=>$this->_name));
		
    	if (isset($config['tbl_prefix'])) {
    		$pfx = (string)$config['tbl_prefix'];
    	} else {
    		$pfx = Zend_Registry::get('app_config')->resources->multidb->local->get('tbl_prefix', 'rtvg_');
    	}
    	$this->setName($pfx.$this->_name);
    	
    }
    
    /**
     * 
     * Add hit to program rating
     * @param string $alias
     * @param int $channel_id
     */
	public function addHit($hash=null, $channel_id=null){
    			
		if (!$hash || !$channel_id)
			throw new Exception("Не указан один или более параметров для ".__METHOD__, 500);
    			
		
		try {
			if (!$row = $this->find($hash)->current())
				$row = $this->createRow( array('hash'=>$hash, 'channel'=>$channel_id), true);
		
			$row->channel = $channel_id;		
			$row->hits+=1;
			
			$row->save();
		} catch (Exception $e) {
			if ($e->getCode()!=1062){
				throw new Zend_Exception($e->getMessage(), $e->getCode());
			}
		}
		
		
    }
    
    /**
     * 
     * Get top programs list
     * @param int $amt
     */
    public function fetchTopPrograms($amt=10){
    	
    	$channels = new Xmltv_Model_DbTable_Channels();
		$programs = new Xmltv_Model_DbTable_Programs();
		$select = $this->_db->select()
			->from(array('r'=>$this->getName()))
			->where("`channel`!='0'")
			->join(array('ch'=>$channels->getName()), "`ch`.`ch_id`=`r`.`channel`", array(
				'channel_title'=>'ch.title',
				'channel_alias'=>'LOWER(`ch`.`alias`)',
				'channel_icon'=>'ch.icon')
			)
			->join(array('prog'=>$programs->getName()), "`r`.`hash`=`prog`.`hash`", array(
				'prog_title'=>'prog.title',
				'prog_sub_title'=>'prog.sub_title',
				'prog_alias'=>'LOWER(`prog`.`alias`)')
			)
			->order("hits DESC")
			->limit((int)$amt);

		//var_dump($select->assemble());
		//die(__FILE__.': '.__LINE__);
		
		$result = $this->_db->fetchAssoc($select);
		$view = new Zend_View();
		foreach ($result as $k=>$r){
		    $result[$k]['channel_icon']=$view->baseUrl('images/channel_logo/'.$result[$k]['channel_icon']);
		}
		
		//var_dump($result);
		//die(__FILE__.': '.__LINE__);
		
    	return $result;
    	
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

