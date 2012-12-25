<?php
/**
 * Channels model
 *
 * @version $Id: Channels.php,v 1.8 2012-12-25 01:57:53 developer Exp $
 */
class Xmltv_Model_Channels
{
	
	private $_table;
	private $_db;
	private $_tbl_pfx;
	private $_siteConfig;
	private $_appConfig;
	const ERR_WRONG_PARAMS = "Неверные параметры!";
	/**
	 * 
	 * Cache storage
	 * @var Zend_Cache_Core
	 */
	protected $cache;
	
	/**
	 * 
	 * Constructor
	 * @param array $config
	 */
	public function __construct($config=array()){
		
		// Init site config
		if (isset($config['site_config']) && !empty($config['site_config']))
			$this->_siteConfig = $config['site_config'];
		else 
			$this->_siteConfig = Zend_Registry::get('site_config');
		
		// Init application config
		if (isset($config['app_config']) && !empty($config['app_config']))
			$this->_appConfig = $config['app_config'];
		else 
			$this->_appConfig = Zend_Registry::get('app_config');

		// Init cache	
		if (isset($config['cache']) && !empty($config['cache']))
			$this->cache = $config['cache'];
		else 
			$this->cache = new Xmltv_Cache(array('location'=>'/Listings'));
		
			
		$dbConf = $this->_appConfig->resources->multidb->get('local');
		$this->_tbl_pfx = $dbConf->get('tbl_prefix');
		$this->_table   = new Xmltv_Model_DbTable_Channels( array('tbl_prefix'=>$this->_tbl_pfx) );
		$this->_db      = new Zend_Db_Adapter_Mysqli( $dbConf );		
		
	}

	/**
	 * 
	 * Load all published channels
	 */
	public function getPublished(){
		
		$rows = $this->_table->fetchAll("`published`='1'", 'title ASC');
		$view = new Zend_View();
		foreach ($rows as $row) {
			$row->icon = $view->baseUrl('images/channel_logo/'.$row->icon);
		}
		return $rows->toArray();
		
	}
	
	/**
	 * 
	 * Add base path to channel logo image
	 * @param object $channel
	 * @param string $baseUrl
	 */
	/*
	public function fixChannelLogo($channel=null){
		
		if (!$channel)
			return;
		
		
		$view = new Zend_View();
		$channel->icon = $view->baseUrl(images/channel_logo/'.$channel->icon);
		
		return $channel;
	}
	*/
	
	public function createUrl($channel=null, $baseUrl=null){
		
		if (!$channel || $baseUrl===null)
		return;
		
		$channel['icon'] = $baseUrl.'/images/channel_logo/'.$channel['icon'];
		return $channel;
	}
	
	public function getByAlias($alias=null){
		
		try {
			
			$select = $this->_db->select()
			->from(array('ch'=>$this->_table->getName()), array('ch'=>'*', 'ch_alias'=>'LOWER(`ch`.`alias`)'))
			->where("`ch`.`alias` LIKE '$alias'")
			->where("`ch`.`published`='1'")
			->join(array('ch_cat'=>$this->_tbl_pfx.'channels_categories'), '`ch`.`category`=`ch_cat`.`id`', array(
				'category_title'=>'ch_cat.title',
				'category_alias'=>'LOWER(`ch_cat`.`alias`)',
				'category_image'=>'ch_cat.image')
			);
			//var_dump($select->assemble());
			//die(__FILE__.': '.__LINE__);
			$result = $this->_db->fetchRow( $select, null, Zend_Db::FETCH_OBJ );
			
		} catch (Zend_Db_Exception $e) {
			throw new Zend_Exception($e->getMessage(), $e->getCode(), $e);
		}
		
		$result->featured  = (bool)$result->featured;
		$result->published = (bool)$result->published;
		$result->parse     = (bool)$result->parse;
		$result->adult     = (bool)$result->adult;
		
		//var_dump($result);
		//die(__FILE__.': '.__LINE__);	

		return $result;
		
	}

	public function getByTitle($alias=null){
		
		if (!$alias)
		throw new Zend_Exception("Не указан один или более параметров для ".__FUNCTION__, 500);
		
		$alias = Xmltv_String::strtolower($alias);
		$result = $this->_table->fetchRow(" `title` LIKE '$alias'");
		$result->alias = Xmltv_String::strtolower($result->alias);
		return $result;
		
	}
	
	public function getTypeaheadItems(){
		
		try {
			$result = $this->_table->getTypeaheadItems();
		} catch (Exception $e) {
			echo $e->getMessage();
		}
		return $result;
		
		
	}
	
	public function addHit($id=null){
		
		if (!$id || !is_int($id))
			throw new Zend_Exception(self::ERR_WRONG_PARAMS, 500);
		
		$this->_hits_table = new Xmltv_Model_DbTable_ChannelsRatings();
		$this->_hits_table->addHit($id);
			
	}
	
	public function getDescription($id=null){
		
		if (!$id)
		throw new Zend_Exception(self::ERR_WRONG_PARAMS, 500);
		
		$props = $this->_table->find($id)->current();
		$desc['intro'] = $props->desc_intro;
		$desc['body']  = $props->desc_body;
		return $desc;
		
	}
	
	public function categoryChannels($cat_alias=null){
	
		if (!$cat_alias)
			throw new Zend_Exception(self::ERR_WRONG_PARAMS, 500);
		
		$result = $this->_table->fetchCategory($cat_alias);
		$view = new Zend_View();
		foreach ($result as $row) {
			$row->icon = $view->baseUrl('images/channel_logo/'.$row->icon);
		}	
		return $result;
		
	}
	
	public function getWeekSchedule($channel=null, Zend_Date $start, Zend_Date $end){
	
		if (!$channel)
			throw new Zend_Exception(self::ERR_WRONG_PARAMS, 500);
		
		if (!is_a($start, 'Zend_Date') || !is_a($end, 'Zend_Date'))
			throw new Zend_Exception(self::ERR_WRONG_PARAMS, 500);
		
		$hash = md5(__FUNCTION__.'_'.$channel->ch_id.'_week_'.$start->toString("yyyyMMdd"));
		try {
			if (Xmltv_Config::getCaching()){
				if (!$schedule = $this->cache->load($hash, 'Core', 'Listings')) {
					$schedule = $this->_table->fetchWeekItems($channel->ch_id, $start, $end);
					$this->cache->save($schedule, $hash, 'Core', 'Listings');
				}
			} else {
				$schedule = $this->_table->fetchWeekItems($channel->ch_id, $start, $end);
			}
		} catch (Exception $e) {
			echo $e->getMessage();
		}
		
		return $schedule;
		
	}
	
	/**
	 * Channels categories list
	 */
	public function channelsCategories(){
	    
		$table = new Xmltv_Model_DbTable_ChannelsCategories();
		$result = $table->fetchAll(null, "title ASC")->toArray();
		$allChannels = array(
				'id'=>'',
				'title'=>'Все каналы',
				'alias'=>'',
				'image'=>'all-channels.gif',
		);
		array_push( $result, $allChannels );
		return $result;
		
	}
	
	/**
	 * 
	 * @param string $alias
	 */
	public function category($alias=null){
		
		if ($alias){
		    $table = new Xmltv_Model_DbTable_ChannelsCategories();
		    return $table->fetchRow("`alias` LIKE '".$alias."'");
		}
	}
	
}

