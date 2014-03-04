<?php

/**
 * Programs database table class
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @version $Id: Programs.php,v 1.23 2013-04-11 05:21:11 developer Exp $
 *
 */
class Xmltv_Model_DbTable_Programs extends Xmltv_Db_Table_Abstract
{

    protected $_name = 'bc';
    protected $_primary = 'hash';
    protected $_rowClass = 'Rtvg_Broadcast';

    /**
     * @var Xmltv_Model_DbTable_Events 
     */
    private $_eventsTable;

    /**
     * @var Xmltv_Model_DbTable_Channels 
     */
    private $_channelsTable;

    /**
     * @var Xmltv_Model_DbTable_ProgramsCategories 
     */
    private $_bcCategoriesTable;

    public function init()
    {
        parent::init();
        $this->_bcCategoriesTable = new Xmltv_Model_DbTable_ProgramsCategories();
        $this->_channelsTable = new Xmltv_Model_DbTable_Channels();
        $this->_eventsTable = new Xmltv_Model_DbTable_Events();
    }

    /**
     * Required because parent class is abstract
     */
    public function getBroadcasts($channel_id = null, $date = null, $count = null)
    {
        return $this->fetchDayItems($channel_id, $date, $count);
    }

    /**
     * 
     * Load premieres list
     * @param  Zend_Date $start
     * @param  Zend_Date $end
     * @return array
     * @deprecated since version 5.4
     */
    public function getPremieres(Zend_Date $start, Zend_Date $end)
    {

        $select = $this->_db->select()->from(array('p' => $this->_name), '*')
            ->joinLeft('rtvg_programs_props', "p.`hash`=rtvg_programs_props.`hash` ", array('actors', 'directors', 'premiere', 'premiere_date', 'rating'))
            ->joinLeft('rtvg_programs_descriptions', "p.`hash`=rtvg_programs_descriptions.`hash`", array('desc_intro' => 'intro', 'desc_body' => 'body'))
            ->joinLeft(array('ch' => 'rtvg_channels'), "p.`ch_id`=ch.`ch_id`", array('channel_title' => 'ch.title', 'channel_alias' => 'LOWER(`ch`.`alias`)'))
            ->joinLeft(array('cat' => 'rtvg_programs_categories'), "p.`category`=cat.`id`", array('category_title' => 'cat.title'))
            ->where("`p`.`start` >= '" . $start->toString('yyyy-MM-dd 00:00:00') . "'")
            ->where("`p`.`end` <= '" . $end->toString('yyyy-MM-dd 00:00:00') . "'")
            ->where("`p`.`new` = '1'")
            ->where("`ch`.`published` = '1'")
            ->group("p.alias")
            ->order("p.start ASC");
        $result = $this->_db->query($select)->fetchAll(self::FETCH_MODE);

        foreach ($result as $k => $row) {
            $result[$k]->start = new Zend_Date($row->start);
            $result[$k]->end = new Zend_Date($row->end);
        }

        return $result;
    }

    /**
     * @param int $channel_id
     * @param string $date
     * @param int $count // optional
     */
    public function fetchDayItems($channel_id = null, Zend_Date $date = null)
    {

        if (!$channel_id) {
            throw new Zend_Db_Table_Exception(Rtvg_Message::ERR_MISSING_PARAM);
        }

        if (APPLICATION_ENV == 'development') {
            Zend_Registry::get('fireLog')->log(xdebug_time_index(), Zend_Log::DEBUG);
            Zend_Registry::get('fireLog')->log(xdebug_memory_usage(), Zend_Log::DEBUG);
        }

        $select = "SELECT `id` FROM " . $this->_db->quoteIdentifier($this->_channelsTable->getName()) . " WHERE `id`=" . $this->_db->quote($channel_id) . "\n" .
            "AND `published`='1'";
        $result = $this->_db->query($select);

        if (!$result->rowCount()) {
            return false;
        }

        $select = $this->_db->select()
            ->from(array('BC' => $this->getName()), array(
                'title',
                'sub_title',
                'alias',
                'category',
                'image',
                'episode_num',
                'date',
                'desc',
                'hash',
            ))
            ->join(array('EVT' => $this->_eventsTable->getName()), "`BC`.`hash`=`EVT`.`hash`", array(
                'start',
                'end',
                'premiere',
                'new',
                'live'
            ))
            ->join(array('BCCOUNTRY' => 'rtvg_countries'), "`BC`.`country` = `BCCOUNTRY`.`iso`", array(
                'bc_country_name' => 'name',
                'bc_country_iso' => 'iso',
            ))
            ->join(array('BCCAT' => $this->_bcCategoriesTable->getName()), "`BC`.`category`=`BCCAT`.`id`", array(
                'category_id' => 'id',
                'category_title' => 'title',
                'category_title_single' => 'title_single',
                'category_alias' => 'alias',
            ))
        ;

        $select->where("`EVT`.`channel` = $channel_id");

        if (!$date) {
            $select->where("`EVT`.`start` >= " . $this->_db->quote(Zend_Date::now()->toString("YYYY-MM-dd 00:00:00")))
                ->where("`EVT`.`start` < " . $this->_db->quote(Zend_Date::now()->addDay(1)->toString("YYYY-MM-dd 00:00:00")));
        } else {
            $select->where("`EVT`.`start` >= " . $this->_db->quote($date->toString("YYYY-MM-dd HH:mm:00")))
                ->where("`EVT`.`start` < " . $this->_db->quote($date->addDay(1)->toString("YYYY-MM-dd HH:mm:00")));
        }

        $brodcasts = $this->_db->fetchAll($select);

        $select = $this->_db->select()
            ->from(array('CH' => $this->_channelsTable->getName()), array(
                'channel_id' => 'id',
                'channel_title' => 'title',
                'channel_alias' => 'alias',
            ))
            ->join(array('CHCAT' => 'rtvg_countries'), "`CH`.`country` = `CHCAT`.`iso`", array(
                'channel_country_name' => 'name',
                'channel_country_iso' => 'iso',
            ))
            ->where("`CH`.`id`=" . $this->_db->quote($channel_id))
        ;


        $channel = $this->_db->fetchRow($select);

        foreach ($brodcasts as $bc) {
            $bc->channel_id = (int) $channel->channel_id;
            $bc->channel_title = (int) $channel->channel_title;
            $bc->channel_alias = (int) $channel->channel_alias;
            $bc->channel_country_name = (int) $channel->channel_country_name;
            $bc->channel_country_iso = (int) $channel->channel_country_iso;
        }

        $result = array();
        foreach ($brodcasts as $k => $bc) {
            foreach ($bc as $kk => $val) {
                $result[$k][$kk] = $val;
            }
            $result[$k]['start'] = new Zend_Date($bc->start);
            $result[$k]['end'] = new Zend_Date($bc->end);
            $result[$k]['premiere'] = (bool) $bc->premiere;
            $result[$k]['live'] = (bool) $bc->live;
            $result[$k]['new'] = (bool) $bc->new;
            $result[$k]['category_id'] = (int) $bc->category_id;
            $result[$k]['episode_num'] = (int) $bc->episode_num;
            $result[$k]['category'] = (int) $bc->category;
            $result[$k]['date'] = new Zend_Date($row->date);
            ksort($result[$k]);
        }

        if (APPLICATION_ENV == 'development') {
            Zend_Registry::get('fireLog')->log(xdebug_time_index(), Zend_Log::DEBUG);
            Zend_Registry::get('fireLog')->log(xdebug_memory_usage(), Zend_Log::DEBUG);
            Zend_Registry::get('fireLog')->log(xdebug_peak_memory_usage(), Zend_Log::DEBUG);
        }

        return $result;
    }

}
