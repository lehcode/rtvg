<?php
/**
 *
 * Channels model for Admin module
 *
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @package rutvgid
 * @filesource $Source: /home/developer/cvs/rutvgid.ru/application/modules/admin/models/Channels.php,v $
 * @version $Id: Channels.php,v 1.2 2013-04-10 01:55:58 developer Exp $
 */
class Admin_Model_Channels extends Xmltv_Model_Channels {
    
    /**
     * Channels map
     * KEY is foreign value, VALUE is local(DB) value
     * @var array
     */
    private $_channelMap = array(
    	'Первый'=>'Первый канал',
    	'Культура'=>'Россия Культура',
    	'5 канал'=>'5 канал (Украина)',
    	'ТВ3'=>'ТВ-3',
    	'MTV'=>'MTV-Russia',
    	'Ю'=>'Ю-ТВ',
    	'Канал Disney'=>'Disney Channel',
    	'ICTV'=>'ICTV (Украина)',
    	'НТН'=>'НТН (Украина)',
    	'Первый национальный Украина'=>'Первый национальный (Украина)',
    	'2+2'=>'2+2 Украина',
    	'М1'=>'М-1',
    	'Добро ТВ (Украина)'=>'Добро ТВ (Украина)',
    	'Москва. Доверие'=>'Доверие',
    	'ИНВА МЕДИА ТВ'=>'Инва-Медиа ТВ',
    	'Первый Интернет Канал'=>'Первый Интернет Канал',
    	'CNN'=>'CNN International',
    	'Discovery Channel'=>'Discovery',
    	'Ocean TV'=>'Ocean-TV',
    	'TV1000 Русское кино'=>'TV1000',
    	'TV XXI'=>'TV XXI (TV21)',
    	'Время'=>'ВРЕМЯ: далекое и близкое',
    	'Индия ТВ'=>'Индия',
    	'Настоящее Страшное Телевидение'=>'Настоящее Страшное ТВ',
    	'Сарафан'=>'Сарафан ТВ',
    	'Тин ТВ'=>'Teen TV',
    	'ТНВ'=>'Татарстан - Новый Век',
    	'Феникс+ Кино'=>'Феникс+Кино',
    	'24 ДОК'=>'24ДОК',
    	'Мир сериала'=>'Мир сериалов',
    	'Психология 21'=>'Психология21',
    	'Zee-TV'=>'Zee TV',
    	'Тонус-ТВ'=>'Тонус ТВ',
    	'TDK'=>'ТДК',
    	'Спорт'=>'Спорт 1',
    	'Zooпарк'=>'Зоопарк',
    	'Драйв'=>'Драйв ТВ',
    	'Здоровое ТВ'=>'Здоровое Телевидение',
    	'КХЛ'=>'КХЛ-ТВ',
    	'Шансон-TB'=>'Шансон ТВ',
    	'НТВ-ПЛЮС Спорт Плюс'=>'Спорт Плюс',
    	'НТВ-ПЛЮС Баскетбол'=>'НТВ Плюс Баскетбол',
    	'НТВ-ПЛЮС Футбол 2'=>'НТВ Плюс Футбол 2',
    	'SONY ТВ'=>'SET (Sony Entertainment Television)',
    	'365'=>'365 Дней',
    	'Детский мир + Телеклуб'=>'Детский мир / Телеклуб',
    	'Ajara TV'=>'AjaraTV',
    	'CNBC'=>'CNBC Europe',
    	'Luxe.TV'=>'Luxe TV',
    	'MusicBox RU'=>'Music Box RU',
    	'MusicBox TV'=>'MUSIC Box TV',
    	'VH1 European'=>'VH1 Europe',
    	'Раз ТВ'=>'РазТВ',
    	'ТРО'=>'Телевидение Ради Общества (ТРО)',
    	'MTV European'=>'MTV European',
    	'Девятый (Орбита)'=>'Девятая орбита',
    	'Наука 2.0'=>'Наука 2.0',
    	'Amazing Life (Удивительная жизнь)'=>'Amazing Life',
    	'ОТВ - Областное телевидение'=>'ОТВ',
    	'ТВ-Центр-Международное'=>'ТВ Центр',
    	'ПЛЮСПЛЮС'=>'Citi',
    	'Теле24'=>'Теле"24"',
    );
    
    public function getChannelMap(){
    	return $this->_channelMap;
    }
    
}