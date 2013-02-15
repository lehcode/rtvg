<?php

class Xmltv_Model_DbTable_ProgramsRatings extends Zend_Db_Table_Abstract
{

    protected $_name = 'programs_ratings';
	protected $pfx='';
    const FETCH_MODE = Zend_Db::FETCH_OBJ;
	
    /**
     * 
     * Constructor
     * @param array $config
     */
    public function __construct($config=array()){
    	
    	parent::__construct(array('name'=>$this->_name));
		
    	if (isset($config['tbl_prefix'])) {
    		$this->pfx = (string)$config['tbl_prefix'];
    	} else {
    		$this->pfx = Zend_Registry::get('app_config')->resources->multidb->local->get('tbl_prefix', 'rtvg_');
    	}
    	$this->setName($this->pfx.$this->_name);
    	
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
    
    /**
     * 
     * Get top programs list
     * @param int $amt
     */
    public function fetchTopPrograms($amt=10){
    	
    	$channels = new Xmltv_Model_DbTable_Channels();
		$programs = new Xmltv_Model_DbTable_Programs();
		$select = $this->_db->select()
			->from(array('r'=>$this->getName()), array('prog_alias'=>'alias', 'prog_channel'=>'channel', 'hits'))
			->join(array('ch'=>$channels->getName()), "`r`.`channel`=`ch`.`id`", array(
					'channel_id'=>'ch.id',
					'channel_title'=>'ch.title',
					'channel_alias'=>'LOWER(`ch`.`alias`)',
					'channel_icon'=>'ch.icon'))
			->where("`r`.`channel` IS NOT NULL")
			->where("`ch`.`published`='1'")
			->group("r.channel")
			->order("r.hits DESC")
			->limit((int)$amt);
			

		if (APPLICATION_ENV=='development'){
		    echo "<b>".__METHOD__."</b>";
			Zend_Debug::dump($select->assemble());
			//die(__FILE__.': '.__LINE__);
		}
		
		$result = $this->_db->fetchAssoc($select);
		
		if (APPLICATION_ENV=='development'){
			//Zend_Debug::dump($result);
			//die(__FILE__.': '.__LINE__);
		}
		
		//$view = new Zend_View();
		
		$now = Zend_Date::now();
		if ($now->toString(Zend_Date::WEEKDAY_DIGIT, 'ru')!=0) {
			while ($now->toString(Zend_Date::WEEKDAY_DIGIT, 'ru')!=0) {
				$now->addDay(1);
			}
		}
		$weekEnd = $now;
		
		foreach ($result as $k=>$r){
		    
		    /**
		     * @var Zend_Db_Select
		     */
		    if (APPLICATION_ENV=='development'){
		    	//var_dump($result[$k]);
		    	//die(__FILE__.': '.__LINE__);
		    }
		    $select = $this->_db->select()
		    ->from(array( 'prog'=>'rtvg_programs'), array( 'count'=>'COUNT(*)'))
		    ->where( "`prog`.`alias` LIKE '".$r['prog_alias']."'
		    		AND `prog`.`start` >= '".Zend_Date::now()->toString('yyyy-MM-dd HH:mm')."'
		    		AND `prog`.`start` < '".$weekEnd->toString('yyyy-MM-dd 23:59')."'
		    		AND `prog`.`channel` = '".$r['prog_channel']."'");
		    
		    if (APPLICATION_ENV=='development'){
		    	Zend_Debug::dump($select->assemble());
		    	//die(__FILE__.': '.__LINE__);
		    }
		    
		    $found = $this->_db->fetchRow($select, null, Zend_Db::FETCH_ASSOC);
		    if (count($found)==0){
		    	unset($result[$k]);
		    } else {
		        
		        if ($result[$k]['prog_alias']=='перерыв'){
		            unset($result[$k]);
		            continue;
		        }
		        
		        $select = $this->_db->select()
		        ->from( array('prog'=>$programs->getName()), array(
		        		'prog_title'=>'prog.title',
		        		'prog_sub_title'=>'prog.sub_title',
		        		'prog_alias'=>'LOWER(`prog`.`alias`)',
		        		'prog_start'=>'start',
		        		'prog_end'=>'end',
		        		'prog_channel'=>'channel'
		        ))
		        ->where( "`alias`='".$result[$k]['prog_alias']."'" );
		        
		        if (APPLICATION_ENV=='development'){
		        	echo "<b>".__METHOD__."</b>";
		        	Zend_Debug::dump($select->assemble());
		        	//die(__FILE__.': '.__LINE__);
		        }
		        
		        $prog = $this->_db->fetchRow($select, null, Zend_Db::FETCH_ASSOC);
		        if ($prog && !empty($prog)){
		        	$result[$k] = array_merge($result[$k], $prog);
		        
		        }
		        if (!isset($result[$k]['prog_title'])){
		        	unset($result[$k]);
		        }
		    }
		    
		}
		
		
		if (APPLICATION_ENV=='development'){
			//Zend_Debug::dump($result);
			//die(__FILE__.': '.__LINE__);
		}
		
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
	
	/**
	 * @return string $pfx
	 */
	public function getPfx() {
	
		return $this->pfx;
		
	}

}

