<?php
/**
 * Parser for m.tv.yandex.ru
 */
//require_once("Xmltv/Parser/Curl.php");
class Xmltv_Parser_Listings_Tvyandexru extends Xmltv_Parser_Curl
{
    
    
    /**
     * Yandex-specific title
     * @var string
     */
    private static $yaTitle;
    
    /**
     * @var DOMDocument
     */
    public $document;

    /**
     * @var array
     */
    private static $activeChannels=array();
    
    /**
     * Key is Yandex title
     * Value is Rtvg title
     * 
     * @var array
     */
    private $channelMap = array(
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
    );
    
    private $startUrl='';
    
    public function __construct( $url='' )
    {
        parent::__construct( $this->startUrl );
    }
    
    public function __destruct()
    {
        parent::__destruct();
    }
    
    public function getStartUrl(){
    	return $this->startUrl;
    }
    
    public function setUrl($url='')
    {
    	parent::setUrl($url);
        
    }
    
    public function extractPersons($namesRegex=array(), $value=null){
    	
        if (APPLICATION_ENV=='development'){
        	//var_dump(func_get_args());
        	//die(__FILE__.': '.__LINE__);
        }
        
        $result=array();
        if (Xmltv_String::stristr($value, ',')){
        	$names = explode(',', $value);
        } else {
        	$names[] = $value;
        }
        foreach ($names as $n){
        	$name = trim($n);
        	foreach ($namesRegex as $pattern){
        		if (preg_match($pattern, $name, $m)){
        			$newName = trim($m[0]);
        			if (!in_array($newName, $result)){
        				$result[] = $newName;
        			}
        		}
        	}
        }
        
        if (APPLICATION_ENV=='development'){
        	//var_dump($result);
        	//die(__FILE__.': '.__LINE__);
        }
        
        return $result;
        
    }
    
    
    
    
    
    /*
     * Fetch single program page
     */
    public function fetchBroadcastPage( $url=null, $referer=null ){
        
        $this->setUrl( $url );
        $this->setUserAgent( 'Mozilla/5.0 (Windows; U; Windows CE 5.1; rv:1.8.1a3) Gecko/20060610 Minimo/0.016' );
        (null !== $referer) ? $this->setReferrer( $referer ) : null ;
        while(!($broadcastPage = $this->fetch())){
        	usleep(250000);
        }
        return $broadcastPage->getDocument();
		
    }
    
    
    public function parseBroadcastPage( $html, Zend_Date $date ){
    	
        $pageDom = new DOMDocument();
        $pageDom->loadHTML( $html );
        $pageDom->preserveWhiteSpace = false;
        $divs = $pageDom->getElementsByTagName('div');
        $bcData = array();
        foreach ($divs as $div){
            
        	if ( $div->getAttribute('class')=='b-content' ){
        	    
        		$bcData['title'] = $div->getElementsByTagName('h1')->item(0)->nodeValue;
        		
        		foreach ($div->childNodes as $infoNode){
        		    if ($infoNode->getAttribute('class')=='b-broadcast'){
        		        
        		        if ($infoNode->childNodes->item(0)->getAttribute('src')) {
        		        	$bcData['image'] = $infoNode->childNodes->item(0)->getAttribute('src');
        		        }
        		        
        		        if ($infoNode->childNodes->item(1)->getAttribute('class')=='b-broadcast__time') {
        		        	$value = trim( $infoNode->childNodes->item(1)->nodeValue );
        		        	preg_match('/,\s(\d{1,2}:\d{2})—(\d{1,2}:\d{2})$/u', $value, $m);
        		        	$bcData['start'] = !empty($m[1]) ? new Zend_Date( $date->toString( "YYYY-MM-dd" ).' '.trim( $m[1] ), 'YYYY-MM-dd H:mm') : null ;
        		        	$bcData['end']   = !empty($m[2]) ? new Zend_Date( $date->toString( "YYYY-MM-dd" ).' '.trim( $m[2] ), 'YYYY-MM-dd H:mm') : null ;
        		        	$bcData['start'] = $bcData['start']->toString("YYYY-MM-dd HH:mm").':00';
        		        	$bcData['end']   = $bcData['end']->toString("YYYY-MM-dd HH:mm").':00';
        		        }
        		        
        		        $descNode = $infoNode->childNodes->item(2);
        		        if ($descNode && $descNode->getAttribute('class')=='b-broadcast__info'){
        		        	if (is_a($descNode, 'DOMElement')){
                        		$bcData['desc'] = trim($descNode->nodeValue);
                        		
                        		for ($c=1; $c < $descNode->childNodes->length; $c++){
                        			$value = $descNode->childNodes->item($c)->nodeValue;
                        			
                        			if (Xmltv_String::stristr($value, 'Ведущий')) {
                        				if (preg_match('/^Ведущий:\s+([\p{Lu}\p{Ll}\s,]+)+\.?$/ui', $value, $m)){
                        					$bcData['presenters'] = $this->extractPersons( Admin_Model_Programs::$namesRegex, trim($m[1], ' .') );
                        				}
                        			}
                        		
                        			if (Xmltv_String::stristr($value, 'Режиссер:')) {
                        				if (preg_match('/^Режиссер:\s+([\p{Lu}\p{Ll}\s,]+)+\.?$/ui', $value, $m)){
                        					$bcData['directors'] = $this->extractPersons( Admin_Model_Programs::$namesRegex, trim($m[1], ' .') );
                        				}
                        			}
                        		
                        			if (Xmltv_String::stristr($value, 'В ролях:')) {
                        				if (preg_match('/^В ролях:\s+([\p{Lu}\p{Ll}\s,]+)+\.?$/ui', $value, $m)){
                        					$bcData['actors'] = $this->extractPersons( Admin_Model_Programs::$namesRegex, trim($m[1], ' .') );
                        				}
                        			}
                        		
                        			if (Xmltv_String::stristr($value, 'Автор сценария')) {
                        				if (preg_match('/^Автор сценария:\s+([\p{Lu}\p{Ll}\s,-]+)+\.?$/ui', $value, $m)){
                        					$bcData['writers'] = $this->extractPersons( Admin_Model_Programs::$namesRegex, trim($m[1], ' .') );
                        				}
                        			}
                        		
                        			if (Xmltv_String::stristr($value, 'Оператор')) {
                        				if (preg_match('/^Оператор:\s+([\p{Lu}\p{Ll}\s,-]+)+\.?$/ui', $value, $m)){
                        					$bcData['operators'] = $this->extractPersons( Admin_Model_Programs::$namesRegex, trim($m[1], ' .') );
                        				}
                        			}
                        		
                        			if (Xmltv_String::stristr($value, 'Композитор')) {
                        				if (preg_match('/^Композитор:\s+([\p{Lu}\p{Ll}\s,-]+)+\.?$/ui', $value, $m)){
                        					$bcData['composers'] = $this->extractPersons( Admin_Model_Programs::$namesRegex, trim($m[1], ' .') );
                        				}
                        			}
                        		
                        			if (Xmltv_String::stristr($value, 'Продюсер')) {
                        				if (preg_match('/^Продюсер:\s+([\p{Lu}\p{Ll}\s,-]+)+\.?$/ui', $value, $m)){
                        					$bcData['producers'] = $this->extractPersons( Admin_Model_Programs::$namesRegex, trim($m[1], ' .') );
                        				}
                        			}
                        		
                        			if (Xmltv_String::stristr($value, 'Возрастные ограничения')) {
                        				if (preg_match('/^Возрастные ограничения:\s+([\p{Lu}\p{Ll}\s,]+)+\.?$/ui', $value, $m)){
                        					$bcData['rating'] = $this->extractAgeRating( trim($m[1], ' .') );
                        				}
                        			}
                        		
                        			if (Xmltv_String::stristr($value, 'Производство')) {
                        				if (preg_match('/^Производство:\s+(.+)\.?$/ui', $value, $m)){
                        					$bcData['country'] = Rtvg_Converter_CountryRuToIso::convert( trim($m[1], ' .') );
                        				} elseif (preg_match('/^Производство:\s+(.+),\s+\d{4}\s+г\.$/ui', $value, $m)){
                        					$bcData['country'] = Rtvg_Converter_CountryRuToIso::convert( trim($m[1], ' .') );
                        					$bcData['year'] = (int)trim($m[2], ' .');
                        				}
                        			}
                        		
                        		}
                        		
                        	}
        		            
        		        }
        		        
        		    }
        		}
        	} else {
        	    die(__FILE__.': '.__LINE__);
        	}
        	
        	return $bcData;
        	
        }
    }
    
    /**
     * 
     * @param  array $bc // broadcast data
     * @return array
     */
    public function fixTitle($bc=array()){
    	
        //var_dump($bc['title']);
        //die(__FILE__.': '.__LINE__);
        
        if ('Телеканал "Доброе утро"' == $bc['title']) {
            $bc['title'] = 'Доброе утро';
            $bc['sub_title'] = 'Телеканал';
        }
        
        $comms = array(
        	'с Геннадием Малаховым',
        	'с Дмитрием Дибровым',
        	'с Таней Беккетт',
        );
        foreach ($comms as $p){
            if (preg_match('/^(.+)('.$p.'.*)$/ui', $bc['title'], $m)){
                $bc['title'] = trim($m[1]);
                $bc['sub_title'] = trim($m[2]);
            }
        }
        
        return $bc;
        
    }
    
    /**
     * 
     * @param unknown_type $url
     */
    public function fetchCollectionPage($url=null){
        
        $this->setUrl( $url );
        $this->setUserAgent( 'Mozilla/5.0 (Windows; U; Windows CE 5.1; rv:1.8.1a3) Gecko/20060610 Minimo/0.016' );
        $this->setProxy('localhost', 8118);
        $this->setReferrer('http://m.tv.yandex.ru');
        $this->setOption( CURLOPT_TIMEOUT, 20 );
        $this->setOption( CURLOPT_FOLLOWLOCATION, 1 );
        $this->setOption( CURLOPT_MAXREDIRS, 5 );
        
        while(!($result = $this->fetch())){
        	usleep(250000);
        }
        
        return $result;
        
    }
    
    
    public function mapChannel($siteChannels=array()){
        
        if (!$this->document || empty($this->document) || !is_a($this->document, 'DOMDocument')){
            throw new Zend_Exception( "Collection DOM not defined!" );
        }
        
        if (array_key_exists(self::$yaTitle, $this->channelMap)){
        	return $this->channelMap[self::$yaTitle];
        } else {
        	foreach ($siteChannels as $ch){
        		if (Xmltv_String::strtolower( $ch['title'] ) == Xmltv_String::strtolower( self::$yaTitle ) ){
        			return $ch['title'];
        		}
        	}
        }
        return false;
    }
    
    /**
     * @return string
     */
    public function yaTitle($nodeValue=null)
    {
        if ($nodeValue) {
            self::$yaTitle = $nodeValue;
        }
        
        return self::$yaTitle;
        
    }
    
    /**
     * 
     * @param DOMElement $table
     * @return multitype:string
     */
    public function extractBroadcastUrls(DOMElement $table){
    	
        $bcRows = $table->getElementsByTagName('tr');
        $result = array();
        $trI=0;
        foreach ($bcRows as $row){
            $tds = $row->childNodes;
            $tdI=0;
            foreach ($tds as $td){
                if ($tdI==0 && $trI>0){
                    if (is_a($td->childNodes, 'DOMNodeList')) {
                    	$href = $td->childNodes->item(0)->getAttribute('href');
                    	if (APPLICATION_ENV=='development'){
                    		//var_dump($href);
                    		//die(__FILE__.': '.__LINE__);
                    	}
                    	$result[] = 'http://m.tv.yandex.ru/'.ltrim( $href, '/' );
                    }
                }
                $tdI++;
            }
            $trI++;
        }
        if (APPLICATION_ENV=='development'){
        	//var_dump($result);
        	//die(__FILE__.': '.__LINE__);
        }
        
        return $result;
        
    }
    
    
    
}