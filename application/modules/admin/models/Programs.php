<?php
/**
 * 
 * Programs model for Admin module
 * 
 * @author     Antony Repin <egeshisolutions@gmail.com>
 * @subpackage backend
 * @version    $Id: Programs.php,v 1.31 2013-04-11 05:19:16 developer Exp $
 */

class Admin_Model_Programs extends Xmltv_Model_Programs
{
	
	protected $countriesList = array(
		'Австралия'=>'au',
		'Аргентина'=>'ar',
		'Бельгия'=>'be',
		'Великобритания'=>'gb',
		'Германия'=>'de',
		'Дания'=>'dk',
		'Индия'=>'in',
		'Индонезия'=>'id',
		'Испания'=>'es',
		'Ирландия'=>'ie',
		'Италия'=>'it',
		'Канада'=>'ca',
		'Китай'=>'cn',
		'Мексика'=>'mx',
		'Нидерланды'=>'nl',
		'Россия'=>'ru',
		'США'=>'us',
		'Украина'=>'ua',
		'Финляндия'=>'fi',
		'Франция'=>'fr',
		'Южная Корея'=>'kp',
		'Япония'=>'jp',
	);
	
	protected $categoriesMap = array(
			'анимационный фильм'=>'Анимационные фильмы',
			'биографический фильм'=>'Биографические фильмы',
			'боевик'=>'Боевики',
			'детективный сериал'=>'Детективные сериалы',
			'детям'=>'Детские передачи',
			'документальный фильм'=>'Документальные фильмы',
			'информационные'=>'Информационные передачи',
			'историческая мелодрама'=>'Исторические мелодрамы',
			'киновестерн'=>'Киновестерны',
			'кинодрама'=>'Кинодрамы',
			'комедия'=>'Комедии',
			'криминальный сериал'=>'Криминальные сериалы',
			'многосерийный фильм'=>'Многосерийные фильмы',
			'музыкальная передача'=>'Музыкальные передачи',
			'мультсериал'=>'Мультсериалы',
			'мультфильм'=>'Мультфильмы',
			'музыка'=>'Музыкальные передачи',
			'остросюжетный детектив'=>'Остросюжетные детективы',
			'остросюжетный сериал'=>'Остросюжетные сериалы',
			'остросюжетный фильм'=>'Остросюжетные фильмы',
			'познавательные'=>'Познавательные передачи',
			'политическая аналитика'=>'Передачи о политике',
			'приключенческая комедия'=>'Приключенческие комедии',
			'приключенческий фильм'=>'Приключенческие фильмы',
			'развлекательные'=>'Развлекательные передачи',
			'религия'=>'Религиозные передачи',
			'сериал'=>'Сериалы',
			'спорт'=>'Спортивные передачи',
			'телеспектакль'=>'Телеспектакли',
			'триллер'=>'Триллеры',
			'фантастико-приключенческий фильм'=>'Фантастико-приключенческие фильмы',
			'фантастическая комедия'=>'Фантастические комедии',
			'фантастический боевик'=>'Фантастические боевики',
			'фантастический сериал'=>'Фантастические сериалы',
			'фантастика'=>'Фантастические фильмы',
			'фильм-фэнтези'=>'Фильмы-фэнтези',
			'художественный фильм'=>'Художественные фильмы',
			'шпионский боевик'=>'Шпионские боевики',
			'шпионские боевики'=>'Шпионские боевики',
	);
	
	public static $namesRegex = array(
			'/^(\p{Lu}\p{Ll}+)\s(\p{Lu}\p{Ll}+|фон|Фон|де|Де|ди|Ди|дас|ла|ван|Ван|ле|дю|ЛаРю)\s(\p{Lu}\p{Ll}+)/u',
			'/^(\p{Lu}\p{Ll}+)\s((Ле|Ла|Ди|Дю)\p{Lu}\p{Ll}+)/u',
			'/^(\p{Lu}\p{Ll}+)\s((О\s|Мак|О`|О’|Делл)?\p{Lu}\p{Ll}+)/u',
			'/^(\p{Lu}\p{Ll}+)\s(\p{Lu}\p{Ll}+)\s(мл\.|ст\.|Мл\.|Ст\.|мл|ст)/u',
			'/^(\p{Lu}\p{Ll}+)-(\p{Lu}\p{Ll}+)\s(\p{Lu}\p{Ll}+)\s(\p{Lu}\p{Ll}+)/u',
			'/^(\p{Lu}\p{Ll})\.(\p{Lu})\.\s(\p{Lu}\p{Ll}+)/u',
			'/^(\p{Lu}\p{Ll}+)\s(\p{Lu}\p{Ll}+)\s(\p{Lu}`(\p{Lu}|\p{Ll})\p{Ll}+)/u',
			'/^(\p{Lu}\p{Ll}+)\s(\p{Lu}`\p{Lu}\p{Ll}+)\s(\p{Lu}\p{Ll}+)/u',
			'/^(\p{Lu}\p{Ll}+)-((\p{Lu}|\p{Ll})\p{Ll}+)\s(\p{Lu}\p{Ll}+)/u',
			'/^(\p{Lu}\p{Ll}+)\s(\p{Lu}\p{Ll})\.\s(\p{Lu}\p{Ll}+)$/u',
			'/^(\p{Lu}\p{Ll}+)-((\p{Lu}|\p{Ll})\p{Ll}+)\s(\p{Lu}\p{Ll}+)/u',
			'/^(\p{Lu}\p{Ll}+)\.\s(\p{Lu}\p{Ll}+)\s(\p{Lu}\p{Ll}+)/u',
			'/^(\p{Lu}\p{Ll}+)\s\p{Lu}\.\s(\p{Lu}\p{Ll}+)/u',
			'/^(\p{Lu}\p{Ll}+)\s(\p{Lu}\p{Ll}+)-(\p{Lu}\p{Ll}+)/u',
			'/^(\p{Lu}\p{Ll}+)\s(\p{Lu}{1,2}\p{Ll}+)/u',
			'/^(\p{Lu}\p{Ll}+)\s(\p{Lu}{1,2}\p{Ll}+)/u',
			'/^(\p{Lu})\.(\p{Lu})\.\s(\p{Lu}\p{Ll}+)/u',
	);
	
	private $_trim_options=array('charlist'=>' -');
	private $_tolower_options=array('encoding'=>'UTF-8');
	private $_regex_list='/[:;_,\.\[\]\(\)\\`\{\}"\!\+\?]+/';
	private $_logger;
	private $_nameRegex = '/^[\w]{2,}-?\s*[\w]{2,}-?\s*([\w]{2,})?\s*(мл\.|ст\.|jr|sr)?$/ui';
	protected $ageRatingRegex = array(
		'/\s([\d]{1,2})\+$/ui',
		'/\s\(([\d]{1,2})\+\)$/ui',
		'/^([\d]{1,2})\+\s/ui',
	) ;
	
	protected $actorsTable;
	protected $directorsTable;
	
	

	/**
	 * Model onstructor
	 */
	public function __construct(){
		
	    parent::__construct();
	    $this->actorsTable    = new Admin_Model_DbTable_Actors();
	    $this->directorsTable = new Admin_Model_DbTable_Directors();
	    $this->programsTable  = new Admin_Model_DbTable_Programs();
		
	}
	
	/**
	 * 
	 * Archive routines
	 * @param  Zend_Date $start
	 * @param  Zend_Date $end
	 * @throws Zend_Exception
	 */
	public function archivePrograms(Zend_Date $start, Zend_Date $end){
		
		if (!is_a($start, 'Zend_Date') || !isset($start))
			throw new Zend_Exception(__METHOD__." - Wrong start date!");
			
		if (!is_a($end, 'Zend_Date') || !isset($end))
			throw new Zend_Exception(__METHOD__." - Wrong end date!");
		
		
		$start = $start->addDay(1);
		
		$where = "`start`<'".$start->toString("YYYY-MM-dd 00:00:00")."'";
		if ($end) {
			$where .= " AND `start`>='".$end->toString("YYYY-MM-dd 00:00:00")."'";
		}
		
		ini_set('max_execution_time', 0);
		
		$batch_size = 500;
		$programsAcrhive	 = new Admin_Model_DbTable_ProgramsArchive();
		$descriptionsAcrhive = new Admin_Model_DbTable_ProgramsDescriptionsArchive();
		$propsAcrhive		= new Admin_Model_DbTable_ProgramsPropsArchive();
		$select = $this->programsTable->select(false)
			->from(array('prog'=>$this->programsTable->getName()))
			->where($where)
			->order('prog.start ASC');
		
		//var_dump($select->assemble());
		$list = $this->programsTable->fetchAll($select);
		
		var_dump(count($list));
		
		do {
			if (count($list)>0){
					
				foreach ($list as $i) {
					
					//var_dump($i->toArray());
					//die(__FILE__.': '.__LINE__);
					
					$programData = $i->toArray();
					$descData = $this->programsDescTable->fetchRow("`hash`='".$programData['hash']."'");
					if ($descData)
						$descData = $descData->toArray();
					else 
						$descData = array();
					
					$propsData = $this->programsPropsTable->fetchRow("`hash`='".$programData['hash']."'");
					if ($propsData)
						$propsData = $propsData->toArray();
					else 
						$propsData = array();
					
					//var_dump($programData);
					//var_dump($descData);
					//var_dump($propsData);
					//die(__FILE__.': '.__LINE__);
					
					try {
						
						$programsAcrhive->insert($programData);
						
						if ($descData) {
							try {
								$descriptionsAcrhive->insert($descData);
							} catch (Exception $e) {
								if ($e->getCode()==1062){
									$descriptionsAcrhive->update($descData, "`hash`='".$programData['hash']."'");
								} else {
									throw new Zend_Exception($e->getMessage(), $e->getCode());
								}
							}
						}
						
						
						if ($propsData){
							try {
								$propsAcrhive->insert($propsData);
							} catch (Exception $e) {
								if ($e->getCode()==1062){
									$propsAcrhive->update($propsData, "`hash`='".$programData['hash']."'");
								} else {
									throw new Zend_Exception($e->getMessage(), $e->getCode());
								}
							}
						}
						
					} catch (Exception $e) {
						if($e->getCode()==1062){
							$programsAcrhive->update($programData, "`hash`='".$programData['hash']."'");
						} else {
							throw new Zend_Exception($e->getMessage(), $e->getCode());
						}
					}
					
					$this->programsTable->delete("`hash`='".$programData['hash']."'");
					
					if (!empty($descData)) {
						$this->programsDescTable->delete("`hash`='".$programData['hash']."'");
					}
					if (!empty($propsData)) {
						$this->programsPropsTable->delete("`hash`='".$programData['hash']."'");
					}
				}
			} else {
				echo "За этот период программ не найдено";
				exit();
			}
			
		} while(!(count($list)>0));
		
		return true;
		
		
	}
	
	/**
	 * Parse string for age rating
	 * 
	 * @param  string $input
	 * @return int
	 */
	public function extractAgeRating($string){
	    
	    //var_dump($string);
	    $r=0;
	    foreach ($this->ageRatingRegex as $regex){
	        //var_dump($regex);
	        //var_dump(preg_match($regex, $string));
	        if (preg_match($regex, $string, $m)) {
	            $r = (int)$m[1];
	        }
	    }
	    
	    if (APPLICATION_ENV=='development'){
	        //var_dump($r);
	        //die(__FILE__.': '.__LINE__);
	    }
	    
		return $r;
				
	}

	
	
	public function stripAgeRating($string){
		
		foreach ($this->ageRatingRegex as $regex){
	    	if (preg_match($regex, $string, $m)) {
	    	    $t = trim($m[0]);
	    		return trim( Xmltv_String::str_ireplace( $t, '', $string));
	    	}
	    }
	    return $string;
		
	}

	
	
	/**
	 * Process program title and detect properties
	 * 
	 * @param string $string //Program title
	 * @return array // broadcast data
	 */
	public function parseTitle ($input=null) {
		
		if(!$input)
			throw new Zend_Exception("No input provided!");
		
		$result['title'] = trim($input);
		
		$result = $this->detectAgeRating( $result );
		if (APPLICATION_ENV=='development'){
			//var_dump($result);
			//die(__FILE__.': '.__LINE__);
		}
		
		// Премьера
		$result = $this->detectPremiere( $result );
		if (APPLICATION_ENV=='development'){
			//var_dump($result);
			//die(__FILE__.': '.__LINE__);
		}

		// Перерыв, профилактика
		$result = $this->parseBreak( $result );
		if (APPLICATION_ENV=='development'){
			//var_dump($result);
			//die(__FILE__.': '.__LINE__);
		}
		
		// Live program
		$result = $this->detectLive( $result );		
		if (APPLICATION_ENV=='development'){
			//var_dump($result);
			//die(__FILE__.': '.__LINE__);
		}
		
		// Новости
		$result = $this->parseNewsTitle( $result );
		if (APPLICATION_ENV=='development'){
			//var_dump($result);
			//die(__FILE__.': '.__LINE__);
		}
		
		// Сериалы
		$result = $this->parseSeriesTitle( $result );		
		if (APPLICATION_ENV=='development'){
			//var_dump($result);
			//die(__FILE__.': '.__LINE__);
		}
		
		// Спортивные программы
		$result = $this->parseSportsTitle( $result );
		if (APPLICATION_ENV=='development'){
			//var_dump($result);
			//die(__FILE__.': '.__LINE__);
		}
		
		// Музыкальные программы
		$result = $this->parseMusicTitle( $result );
		if (APPLICATION_ENV=='development'){
			//var_dump($result);
			//die(__FILE__.': '.__LINE__);
		}
		
		//Название с восклицательным знаком
		if (strstr($result['title'], '!')){
			$trim = new Zend_Filter_StringTrim( array('charlist'=>'" ') );
			$r = explode( '!', $result['title'] );
			$result['title']	 = $trim->filter( $r[0] ).'!';
			$result['sub_title'] = Xmltv_String::ucfirst( $trim->filter($r[1]) );
		}
		
		// Смесь кирилицы и латинницы (бывает)
		/*
		if (preg_match('/^([\p{Cyrillic}\s]+)\s+and\s+([\p{Cyrillic}\s]+)$/ui', $result['title'], $m)){
			$result['title'] = $m[1];
			$result['sub_title'] = Xmltv_String::ucfirst(trim($m[2]));
		
		}
		*/
		
		//"Кто хочет стать миллионером?" с Дмитрием Дибровым
		/*
		if (preg_match('/^([\p{Common}\p{Cyrillic}\p{Latin}]+) ((с|со)\s\w+\s\w+[\.-]*)$/u', $result['title'], $m)){
			
			$trim = new Zend_Filter_StringTrim( array('charlist'=>'" ') );
			$result['title']	 = $trim->filter( $m[1]);
			$result['sub_title'] = $trim->filter( Xmltv_String::ucfirst( $trim->filter($m[2])), '" ');
		}
		*/
		
		if (Xmltv_String::stristr($result['title'], '+')){
			$result['title'] = Xmltv_String::str_ireplace('+', 'плюс', $result['title']);
		}
		
		if (preg_match('/^"([\p{Common}\p{Cyrillic}\p{Latin}]+)"\.\s+(.+)$/ui', $result['title'], $m)){
		    if (APPLICATION_ENV=='development'){
		    	//var_dump($m);
		    	//die(__FILE__.': '.__LINE__);
		    }
		    $result['title']     = $m[1];
		    $result['sub_title'] = $m[2];
		}
		
		$result['title'] = str_replace('...', '…', $result['title']);
		$trim = new Zend_Filter_StringTrim(array('charlist'=>',!?:;- \/\''));
		$result['title'] = $trim->filter($result['title']);
		$result['sub_title'] = trim($result['sub_title']);
		
		if (APPLICATION_ENV=='development'){
			//var_dump($result);
			//die(__FILE__.': '.__LINE__);
		}
		
		return $result;
		
	}
	
	
	/**
	 * Get category ID from it's title
	 * @param unknown_type $title
	 * @return number
	 */
	public function catIdFromTitle($title){
		
	    if (APPLICATION_ENV=='development'){
	    	//var_dump($title);
	    	//die(__FILE__.': '.__LINE__);
	    }
	    
	    $catLower = Xmltv_String::strtolower( trim($title));
	    if (array_key_exists($catLower, $this->categoriesMap)){
	    	$title = $this->categoriesMap[$catLower];
	    }
	    
	    foreach ($this->programsCategoriesList as $cat) {
			if( Xmltv_String::strtolower($title) == Xmltv_String::strtolower($cat['title'])) {
				$catId = (int)$cat['id'];
			}
		}
		
		if (APPLICATION_ENV=='development'){
			//var_dump($catId);
			//die(__FILE__.': '.__LINE__);
		}
		
		return $catId;
		
	}


	/**
	 * 
	 * Detect program category
	 * @param  array  $info
	 * @param  string $xml_title
	 * @return int
	 */
	public function getProgramCategory ($cat_title=null, $prog_desc=null) {
		
	    if (APPLICATION_ENV=='development'){
	    	//var_dump(func_get_args());
	    	//die(__FILE__.': '.__LINE__);
	    }
	    
	    if ($cat_title) {
		    
			$exists   = false;
			$catLower = Xmltv_String::strtolower($cat_title);
			if (array_key_exists($catLower, $this->categoriesMap)){
				$cat_title = $this->categoriesMap[$catLower];
			}
			
			if (APPLICATION_ENV=='development'){
				//var_dump($cat_title);
				//die(__FILE__.': '.__LINE__);
			}
			
			foreach ($this->programsCategoriesList as $c) {
			    if( Xmltv_String::strtolower( $c->title ) == Xmltv_String::strtolower($cat_title)) {
					$catId  = (int)$c->id;
					$exists = true;
				}
			}
			
			if (APPLICATION_ENV=='development'){
				//var_dump($catId);
				//die(__FILE__.': '.__LINE__);
			}
			
			// If not found
			if (!$exists){
			    if (APPLICATION_ENV=='development'){
			        //var_dump( func_get_args());
			        //var_dump($c->title);
			        //die(__FILE__.': '.__LINE__);
			    }
				
			}
		} elseif ($prog_desc) {
		    
			$categoriesRegex = array(
				'/-?\s*сериал\.?/'
			);
			foreach ($categoriesRegex as $regex){
				if (preg_match($regex, $prog_desc, $m)){
					foreach ($this->programsCategoriesList as $c){
						if (Xmltv_String::strtolower($c->title)=='сериал'){
							$catId = (int)$c->id;
						}
					}
				}
			}
			
		} else {
			return false;
		}
		
		if (APPLICATION_ENV=='development'){
			//var_dump($catId);
			//die(__FILE__.': '.__LINE__);
		}
		
		return $catId;
	
	}

	
	/**
	 * Parse XML description
	 * 
	 * @param string $desc
	 * @param string $hash
	 * @return void
	 */
	public function parseDescription (array $data) {
		
	    if (!$data['desc']){
	        $data['desc'] = '';
	        return $data;
	    }
	    
	    $desc = $data['desc'];
	    $result = array();
		
		//Проверка возрастного рейтинга в названии
		if (($r = $this->extractAgeRating($desc))>0) {
			$result['rating'] = $r;
			$desc = $this->stripAgeRating($desc);
		}
		
		if (Xmltv_String::stristr($desc, "ЗАКАЖИ и СМОТРИ +7(495)775-5620")) {
			$desc = Xmltv_String::str_ireplace( ' ЗАКАЖИ и СМОТРИ +7(495)775-5620', '', $desc);
		}
		
		
		//'([-\w+\s\w+,^A-Z]+,?)'
		$regex= array(
			'/^([\w\s]+)\s-\s(комедия)\.?\sВ главной роли\s([-\w+\s\w+,^A-Z]+)\.(\s)([\w\s-]+),\s([\d]{4}).*$/ui', //ПОМЕСТЬЕ - комедия. В главной роли Гэбриэл Диани. США, 2011г. 16+
			'/^([\w\s,]+)\s-\s(фильм-фэнтези)(\s)([-\w+\s\w+,^A-Z]+)\.\s([\w\s-]+),\s([\d]{4}).*$/ui', //ЛЕВ, КОЛДУНЬЯ И ВОЛШЕБНЫЙ ШКАФ - фильм-фэнтези Эндрю Эдамсона. США-Новая Зеландия, 2005г. 0+
			'/^([\w\s,]+)\s-\s(фильм-фэнтези)\.\sВ ролях:\s([-\w+\s\w+,^A-Z]+ и [-\w+\s\w+,^A-Z]+)\.(\s)([\w\s-]+),\s([\d]{4}).*$/ui', //ПРИНЦ КАСПИАН - фильм-фэнтези. В ролях: Бен Барнс, Джорджи Хенли, Уильям Мозли, Скандер Кейнс и Анна Попплуэлл. Великобритания-США, 2008г. 0+
			'/^-(\s)фантастический\s(анимационный фильм)(\.)(\s)([\w\s-]+),\s([\d]{4}).*$/ui', //- фантастический анимационный фильм. США, 2011г. 6+
		);
		foreach ($regex as $r) {
			if (preg_match($r, $desc, $m)) {
				 
				// Breakpoint
				if (APPLICATION_ENV=='development'){
				    //var_dump($desc);
					//var_dump($m);
				}
				 
				$result['category'] = $this->catIdFromTitle(trim($m[2]));
				$result['actors'] = implode(",", $this->_parsePersonsNames(trim($m[3]), 'actor') );
				$desc = $this->_removePersonsNames($desc, $result['actors'], 'actor');
				$result['directors'] = implode(",", $this->_parsePersonsNames(trim($m[4]), 'director') );
				$desc = $this->_removePersonsNames($desc, $result['directors'], 'director');
				$result['country'] = self::countryRuToIso(trim($m[5]));
				$result['year'] = (int)trim($m[6]);
				$result['title'] = Xmltv_String::ucfirst( Xmltv_String::strtolower(trim($m[1])));
				$desc = preg_replace('/^.+$/ui', '', $desc);
				
				if (APPLICATION_ENV=='development'){
					//var_dump($result);
					//die(__FILE__.': '.__LINE__);
				}
				
				return $result;
					  
			}
		}
		
		
		$regex = array(
			//Детективный сериал. Великобритания, 2001 - 2004гг. Режиссер Мартин Хатчингс. В ролях Тревор Ив, Сью Джонстон, Холи Эйрд, Клэр Гуз. Самые страшные слова для любого следователя “ "убийство раскрыть нельзя". Однако новые технологии позволяют решать самые непростые задачи, в том числе раскрывать преступления, совершенные много лет назад. Именно с этой целью был создан специальный "убойный" отдел под руководством старшего детектива Бойда. Бойду и его команде предстоит расследовать загадочную смерть фотожурналиста в автокатастрофе, двойное убийство, за которое женщина провела 25 лет в тюрьме, убийство полицейского и многие другие страшные преступления.
			'/^(Детективный сериал)\.(\s)(\w+),\s+[\d]{4}\s+-\s+([\d]{4})\w+\.\sРежиссер:?\s+([\w\s-]+)\.\sВ ролях\s([-\w+\s\w+,^A-Z]+,?)\.\s(.+)/ui',
			//Триллер, детектив. Studio Canal, Франция, 2005г. Режиссер: Жером Саль.Интерпол преследует неуловимого мошенника Энтони Циммера, специализирующегося на отмывании денег для русской мафии. Недавно Циммер сделал пластическую операцию, которая целиком изменила его внешность. Теперь единственная ниточка - любовница Энтони, обворожительная красотка Кьяра. Но Кьяра это тоже понимает, и заводит интрижку напоказ с ничего не подозревающим простаком Франсуа. Он того же роста и того же возраста, что и Циммер. И для спецслужб, и для мафии это достаточное основание, чтобы попытаться убить Франсуа.
			'/^(Триллер|детектив).+\.\s([\w\s]+),\s(\w+),\s([\d]{4})г\.\sРежиссер:\s([\w\s-]+)\.\sВ ролях:\s([-\w+\s\w+,^A-Z]+,?)\.\s(.+)$/ui',
			//Комедия. Великобритания, 2004г. Режиссер: Джон МакКэй. В ролях: Сэм Рокуэлл, Кассандра Белл, Том Уилкинсон. Джим Крокер, дебошир и постоянный персонаж скандальной хроники лондонских газет, впервые в жизни влюбляется. Но дело в том, что избранница - американка и заочно терпеть Крокера не может. Джимми прикидывается сыном своего дворецкого, садится на атлантический лайнер и отправляется за девушкой своей мечты. Положение сильно осложняется тем, что и в Нью - Йорке немало людей, которые близко знакомы с Крокером.
			'/^(Комедия)\.\(s+\)(\w+),\s+([\d]+).+\s+Режиссер:\s+(.+)\.\s+В ролях:\s+([-\w+\s\w+,^A-Z]+,?)\.(.+)$/ui',
			//Приключения. Таллинфильм, 1969г. Режиссер: Григорий Кроманов. В ролях: Александр Голобородько, Ингрид Андринь, Эльзе Радзиня, Ролан Быков, Ээве Киви, Улдис Ваздикс ХVI век. Лифляндия. В одном из аристократических домов умирает старый рыцарь Рисбитер. Он завещал сыну шкатулку с семейной реликвией. Духовные пастыри ближайшего монастыря хотят завладеть шкатулкой, чтобы приумножить славу обители. Молодой наследник согласен уступить церкви реликвию, но с одним условием: ему должны отдать в жены прекрасную Агнес, племянницу аббатисы женского монастыря. А сердце юной красавицы принадлежит свободолюбивому рыцарю в Габриэлю, другу всех обманутых и беззащитных.... Фильм снят по роману эстонского писателя Э. Борнхeэ "Последний день монастыря святой Бригитты".
			'/^([\w\s,]+)\.(\s+)([\w\s-?\.?]+),\s+([\d]{4})г\.?\s+Режиссер:\s+(\w+\s\w+)\.\s+В ролях:\s+([-\w+\s\w+,^A-Z]+,?)\.?\s+(.+)/ui',
		);
		foreach ($regex as $r){
			if (preg_match($r, $desc, $m)){
				
				// Breakpoint
				if (APPLICATION_ENV=='development'){
					//var_dump($m);
					//die(__FILE__.': '.__LINE__);
				}
				
				$result['category'] = $this->catIdFromTitle(trim($m[1]));
				$result['studio']   = trim($m[2]);
				$result['country'] = self::countryRuToIso(trim($m[3]));
				$result['year'] = (int)trim($m[4]);
				$desc = trim($m[7]);
				$result['actors'] = implode(",", $this->_parsePersonsNames( trim($m[6]), 'actor') );
				$desc = $this->_removePersonsNames($desc, $result['actors'], 'actor');
				$result['directors'] = implode(",", $this->_parsePersonsNames(trim($m[5]), 'director') );
				$desc = $this->_removePersonsNames($desc, $result['directors'], 'director');
		    	
				// Breakpoint
				if (APPLICATION_ENV=='development'){
					//var_dump($result);
					//die(__FILE__.': '.__LINE__);
				}
				
			}
		}
		
		//ЗАТЕРЯННЫЙ МИР - фантастико-приключенческий фильм Стивена Спилберга. В ролях: Джефф Голдблюм, Джулианна Мур и Ричард Аттенборо. США, 1997г. 12+
		if (preg_match('/^([\w\s,]+)\s-\s(фантастико-приключенческий фильм)\s([\w\s-]+)\.\sВ ролях:\s([-\w+\s\w+,^A-Z]+ и [-\w+\s\w+,^A-Z]+)\.\s([\w\s-]+),\s([\d]{4}).*$/ui', $desc, $m)){
			
		    $result['title']     = Xmltv_String::ucfirst( Xmltv_String::strtolower(trim($m[1])));
		    $result['category']  = $this->catIdFromTitle(trim($m[2]));
		    $result['directors'] = implode(",", $this->_parsePersonsNames(trim($m[3]), 'director') );
		    $desc = $this->_removePersonsNames($desc, $result['directors'], 'director');
		    $result['actors'] = implode(",", $this->_parsePersonsNames( trim($m[4]), 'actor') );
		    $desc = $this->_removePersonsNames($desc, $result['actors'], 'actor');
		    
			// Breakpoint
			if (APPLICATION_ENV=='development'){
				//var_dump($m);
				//die(__FILE__.': '.__LINE__);
			}
			
			$result['country'] = self::countryRuToIso(trim($m[5]));
			$result['year']    = (int)trim($m[6]);
			
			$desc = preg_replace('/^.+$/ui', '', $desc);
			
			// Breakpoint
			if (APPLICATION_ENV=='development'){
				//var_dump($result);
				//die(__FILE__.': '.__LINE__);
			}
			
		} elseif (preg_match('/^([\w\s,]+)\.\s+(.+)\,\s+(\w+),\s+([\d]{4})г\.\s+Режиссер:\s+([-\w+\s\w+,^A-Z]+)\.\sСценарий:\s+([-\w+\s\w+,^A-Z]+)\.\s+В ролях:\s+([-\w+\s\w+,^A-Z]+,?)\.\s(.+)/ui', $desc, $m)){
			//Фантастика, приключения, мелодрама. Warner Bros. - DreamWorks SKG, США, 2001г. Режиссер: Стивен Спилберг. Сценарий: Стивен Спилберг. В ролях: Стивен Спилберг, Бонни Кертис, Хэйли Джоэл Осмент, Джуд Ло, Фрэнсис О"Коннор, Брендан Глисон, Сэм Робардс, Уильям Херт, Джейк Томас, Кен Леунг. Середина 21 века. Из - за глобального потепления климат на планете становится непредсказуемым. Люди создают новое поколение роботов, способное помочь им в борьбе за выживание. И, хотя природные ресурсы скудеют, высокие технологии развиваются со стремительной скоростью. Киборги живут бок о бок с людьми и выручают их во всех сферах деятельности. И тут наука преподносит человечеству очередной сюрприз - создается чудо - робот совершенно иного порядка: с разумом, нервной системой, способный испытывать все человеческие эмоции и главное - любить. Это настоящий подарок для супружеских пар, не имеющих детей. Творение нарекают Дэвидом. Кибернетический мальчик, по виду ничем не отличающийся от живого ребенка, попадает в семью ученых Генри и Моники, участвовавших в работе над проектом, и становится их сыном. Но готовы ли его новые родители ко всем последствиям такого рискованного эксперимента?
			
			// Breakpoint
			if (APPLICATION_ENV=='development'){
				//var_dump($m);
				//die(__FILE__.': '.__LINE__);
			}
			
			$genres = explode(', ', $m[1]);
			$result['category']  = mt_rand( 0, count($genres)-1);
			$result['producer']  = trim($m[2]);
			$result['country']   = self::countryRuToIso(trim($m[3]));
			$result['year']      = (int)trim($m[4]);
			$result['writer']    = trim($m[6]);
			$result['directors'] = implode( ",", $this->_parsePersonsNames( trim($m[5]), 'director') );
			$desc = $this->_removePersonsNames( $desc, $result['directors'], 'director');
			
			$result['actors'] = implode(",", $this->_parsePersonsNames( trim($m[7]), 'actor') );
			$desc = $this->_removePersonsNames($desc, $result['actors'], 'actor');
			$result['desc'] = $desc = trim($m[8]);

			// Breakpoint
			if (APPLICATION_ENV=='development'){
				//var_dump($result);
				//die(__FILE__.': '.__LINE__);
			}
			
		}
		
		$movies = array(
				'фантастико-приключенческий фильм',
				'приключенческая комедия',
				'историческая мелодрама',
				'фантастический боевик',
				'триллер',
				'фильм-фэнтези',
				'биографический фильм',
				'драма',
				'анимационный фильм',
				'комедийный боевик',
				'романтическая комедия',
		);
		$regex= array(
				'/^-?\s*('.implode('|', $movies).')(\.)(\s)([\w\s-]+),\s([\d]{4}).+$/ui', //- фильм-фэнтези. Индия, 2011г. 12+
				'/^-?\s*('.implode('|', $movies).')\.?\s(\w+\s\w+)\.\sВ ролях:\s([\w\s,-]+ и [\w\s-]+)\.\s([\w\s-]+),\s([\d]{4}).+$/ui',
				'/^-?\s*('.implode('|', $movies).')\.(\s)В главной роли\s([\w\s-]+)\.\s([\w\s-]+),\s([\d]{4}).+$$/ui',
				'/^-?\s*(боевик)\.(\s)В ролях:\s([\w\s,-]+ и [\w\s-]+)\.\s([\w\s-]+),\s([\d]{4}).+$/ui',
				'/^-?\s*(боевик)\s([\w\s-]+)\.\sВ ролях:\s([\w\s,-]+ и [\w\s-]+)\.\s([\w\s-]+),\s([\d]{4}).+$/ui',
				'/^-?\s*('.implode('|', $movies).')\.(\s)В ролях:\s([\w\s,-]+ и [\w\s-]+)\.\s([\w\s-]+),\s([\d]{4}).+$/ui', //- приключенческая комедия. В ролях: Зэвьер Сэмюэл и Кевин Бишоп. Австралия-Великобритания, 2011г. 18+
				'/^-?\s*(комедия)\s([\w\s-]+)\.(\s)([\w\s-]+),\s([\d]{4}).+$/ui',
				'/^-?\s*(фильм)\s([\w\s-]+)\.\sВ ролях:\s([\w\s,-]+ и [\w\s-]+)\.\s([\w\s-]+),\s([\d]{4}).+$/ui', //- фильм Джеймса Кэмерона. В ролях: Леонардо Ди Каприо, Кейт Уинслет, Билли Зейн и Кэти Бэйтс. США, 1997г. 12+
				'/^-?\s*(фильм)\s([\w\s-]+)\.\sВ главной роли:?\s([\w\s-]+)\.\s([\w\s-]+),\s([\d]{4}).+$/ui', //- фильм братьев Коэн. В главной роли Майкл Стулбарг. США-Великобритания-Франция, 2009. 16+
				'/^-?\s*(комедия)\.(\s)В ролях:\s([\w\s,-]+ и [\w\s-]+)\.\s([\w\s-]+),\s([\d]{4}).+$/ui', //- комедия. В ролях: Гэри Энтин, Линдси Шоу и Роберт Адамсон. США, 2010г. 18+
				'/^-?\s*(вестерн)\.(\s)В ролях:\s([\w\s,-]+,?)\.\s([\w\s-]+),\s([\d]{4}).*$/ui', //- вестерн. В ролях: Кристиан Бейл, Расселл Кроу, Чад Брамметт, Крис Браунинг, Кевин Дьюранд. США, 2007. 12+
		);
		foreach ($regex as $r){
			 
			if (preg_match($r, $desc, $m)){
					
				// Breakpoint
				if (APPLICATION_ENV=='development'){
					//var_dump($desc);
					//var_dump($m);
				}
				
				/*
				 * Категория
				 */
				$f = array(
					'фильм'=>'художественные фильмы',
					'драма'=>'кинодрамы',
				);
				$c = trim($m[1]);
				if (array_key_exists($c, $f)) {
					$result['category']  = $this->catIdFromTitle( $f[$c] );
				} else {
					$result['category']  = $this->catIdFromTitle(trim($m[1]));
				}
					
				$result['actors'] = implode(",", $this->_parsePersonsNames(trim($m[3]), 'actor') );
				$desc = $this->_removePersonsNames($desc, $result['actors'], 'actor');
				$result['country'] = self::countryRuToIso(trim($m[4]));
				$result['year'] = (int)trim($m[5]);
				$desc = preg_replace('/^.+$/ui', '', $desc);
					
				if (APPLICATION_ENV=='development'){
					//var_dump($result);
					//die(__FILE__.': '.__LINE__);
				}
				
				return $result;
					
			}
		}
		
		
		if (Xmltv_String::stristr($desc, 'В ролях') || Xmltv_String::stristr($desc, 'Звезды кино')){
			if (preg_match('/^(.*)\s(В ролях|Звезды кино):\s([-\w+\s\w+,^A-Z]+,?)\.?\s*(.*)$/ui', $desc, $m)){
			    
			    if (APPLICATION_ENV=='development'){
			        //var_dump($desc);
			    	//var_dump($m);
			    }
			    
				$result['actors'] = implode(",", $this->_parsePersonsNames( $desc, 'actor'));
				$desc = $this->_removePersonsNames( $desc, $result['actors'], 'actor');
				$result['desc'] = trim( trim($m[1]).' '.trim($m[4]));
				
				// Breakpoint
				if (APPLICATION_ENV=='development'){
					//var_dump($result);
					//die(__FILE__.': '.__LINE__);
				}
				
			}
			
			
		}
		
		if (Xmltv_String::stristr($desc, 'Детективный сериал') || 
		Xmltv_String::stristr($desc, 'сериала "Детективы"')){
			$result['category'] = $this->catIdFromTitle('детективный сериал');
		} elseif(Xmltv_String::stristr($desc, 'Информационная программа')){
			$result['category'] = $this->catIdFromTitle('информационные');
		} elseif (Xmltv_String::stristr($desc, 'Новости спорта')){
			$result['category'] = $this->catIdFromTitle('спортивные новости');
		}
		
		if (($rating=$this->extractAgeRating($desc))>0){
		    $result['rating'] = $rating;
		    $desc = $this->stripAgeRating($desc);
		}
		
		$trim = new Zend_Filter_StringTrim(array('charlist'=>' -,'));
		$result['text'] = $trim->filter( Xmltv_String::str_ireplace('...', '…', $desc) );
		
		if (preg_match('/([\d]{2}-плюс)$/u', $result['alias'], $m)) {
		    $result['alias'] = Xmltv_String::str_ireplace($m[1], '', $result['alias']);
		    var_dump($result);
		    die(__FILE__.': '.__LINE__);
		}
		
		$result['actors'] = $this->_parsePersonsNames( $desc, 'actor');
		if (!empty($result['actors'])) {
			$desc = $this->_removePersonsNames( $desc, $result['actors'], 'actor');
		}
		
		return $result;
		
		
	}
	
	/**
	 * Convert russian country name to ISO format
	 * 
	 * @deprecated
	 * @param string $string
	 */
	private function _parseCountry($string=null){
		
	    
	    if( stristr($string, '-')){
	    	$country = preg_replace('/(\s-\s)/ui', '-', $string);
	    	$ex = explode( '-', $country);
	    	$c=array();
	    	foreach ($ex as $e){
	    		$key = trim($e);
	    		if ( array_key_exists( $key, $this->countriesList)){
	    			$result['country'][] = $this->countriesList[$key];
	    		} else {
	    		    $profile = (bool)Zend_Registry::get('site_config')->profile;
			    	if ($profile){
	    				var_dump($country);
	    				die(__FILE__.': '.__LINE__);
	    			}
	    		}
	    	}
	    	if ($result && is_array($result)){
	    		$result = implode(',', $result['country']);
	    		return $result;
	    	} 
	    	return null;
	    } else {
	    	$country = $string;
	    	if ( array_key_exists( $country, $this->countriesList)){
	    		return $this->countriesList[$country];
	    	} else {
	    	    $profile = (bool)Zend_Registry::get('site_config')->profile;
			    if ($profile){
	    			var_dump($country);
	    			die(__FILE__.': '.__LINE__);
	    		}
	    	}
	    }
	    
	    
	    
	}
	
	/**
	 * 
	 * Удаление имени режисера/ов из описания передачи
	 * с использованием регулярных выражений
	 * 
	 * @param  string $desc
	 * @param  array $actors
	 * @return string
	 * @deprecated
	 */
	private function _removeDirectorsNames($desc, $actors){
	    
	    return $this->_removeActorsNames($desc, $actors);
	    
	}
	
	/**
	 * 
	 * @deprecated
	 * @param unknown_type $desc
	 * @param unknown_type $actors
	 * @return Ambigous <string, unknown, mixed>
	 */
	private function _removeActorsNames($desc, $actors){
	    foreach ($actors as $p){
	    	$desc = Xmltv_String::str_ireplace($p.',', '', $desc);
	    }
	    return $desc;
	}
	
	/**
	 * 
	 * @param  string $text
	 * @param  array $persons
	 * @return string
	 */
	private function _removePersonsNames($text=null, $persons=array(), $type='actor'){
		
	    //var_dump(func_get_args());
	    //die(__FILE__.': '.__LINE__);
	    
	    switch ($type){
	    	case 'director':
	    	    $table = new Xmltv_Model_DbTable_Directors();
	    	    break;
	    	case 'actor':
	    	    $table = new Xmltv_Model_DbTable_Actors();
	    	    break;
	    	    
	    }
	    if (!empty($persons)){
	        
	        if (is_array($persons)){
	            $persons = implode(',', $persons);
	        }
	        
	        $names = $table->fetchAll("`id` IN (".$persons.")")->toArray();
		    foreach ($names as $p){
		        
		        //var_dump($names);
		        //var_dump($p['complete_name']);
		        //die(__FILE__.': '.__LINE__);
				
		        if ( Xmltv_String::stristr( $text, ' В ролях: '.$p['complete_name'])) {
		            $text = Xmltv_String::str_ireplace( ' и '.$p['complete_name'], '', $text);
		        }
		        if ( Xmltv_String::stristr( $text, ' и '.$p['complete_name'])) {
		            $text = Xmltv_String::str_ireplace( ' и '.$p['complete_name'], '', $text);
		        }
		        if ( Xmltv_String::stristr( $text, $p['complete_name'].',')) {
		            $text = Xmltv_String::str_ireplace( $p['complete_name'].',', '', $text);
		        }
		        if ( Xmltv_String::stristr( $text, $p['complete_name'].'.')){
		            $text = Xmltv_String::str_ireplace( $p['complete_name'].'.', '', $text);
		        }
		        if ( Xmltv_String::stristr( $text, $p['complete_name'])){
		            $text = Xmltv_String::str_ireplace( $p['complete_name'], '', $text);
		        }
		        $text = trim( preg_replace('/\s+/ui', ' ', $text), ' ,.');
		        //var_dump(Xmltv_String::stristr( $text, $p['complete_name']));
		        //var_dump($text);
		    	
		    }
		    
		    $replace = new Zend_Filter_PregReplace(array('match'=>'/( \(\)(\.|\s)?)/ui', 'replace'=>' '));
		    $text = $replace->filter( $text);
	    }
	    
	    $text = trim($text);
	    //var_dump($text);
	    //die(__FILE__.': '.__LINE__);
	    
	    return $text;
	    
	}
	
	/**
	 * Парсинг имен с использованием регулярных выражений
	 * 
	 * @param  string $string
	 * @param  string $type
	 * @return array
	 */
	private function _parsePersonsNames($string='', $type='actor'){
		
	    if (APPLICATION_ENV=='development'){
	        //var_dump($string);
	    }
	    
	    $result  = array();
		$trim    = new Zend_Filter_StringTrim( array('charlist'=>' .,'));
		$string  = $trim->filter( $string);
		$string  = $trim->filter( $string);
		$e = explode('. ', $string);
		//var_dump($e);
		$names = array();
		$fm=null;
		foreach ($e as $k=>$s){
		    if (!preg_match('/\.\.$/ui', $s)){
		    	if (preg_match('/(\s\p{Lu}|\s\p{Lu}\.\p{Lu})$/u', $s)){
		        	//var_dump($s);
		        	$names[]=$e[$k];
		        }
	        }
	        
		}
		//var_dump($names);
		$names[]=$e[count($e)-1];
		$string = implode('. ', $names);
		
		//die(__FILE__.': '.__LINE__);
		
		if (Xmltv_String::stristr( $string, ' и ')) {
	    	$string = Xmltv_String::str_ireplace( " и ", ", ", $string);
	    }
	    if (Xmltv_String::stristr( $string, ' , ')) {
	    	$string = Xmltv_String::str_ireplace( " , ", ", ", $string);
	    }
	    if (Xmltv_String::stristr( $string, ' и,')){
	    	$string = Xmltv_String::str_ireplace( " и,", " , ", $string);
	    }
	    if (Xmltv_String::stristr( $string, ' и др')){
	    	$string = Xmltv_String::str_ireplace( " и др", "", $string);
	    }
	    //var_dump($string);
	    $names = explode(', ', trim($string, ' .,'));
	    //var_dump($names);
	    foreach ( $names as $part) {
	        $name = trim($part);
	    	foreach ( self::$namesRegex as $r){
	    	    if (Xmltv_String::stristr($name, 'ё')){
	    	        $name = Xmltv_String::str_ireplace('ё', 'е', $name);
	    	    }
	    		if ( preg_match($r, $name, $m)){
	    			if ( ($id = $this->personId( $trim->filter($m[0]), $type))!==null){
	    				$result[] = $id;
	    				break;
	    			}
	    		}
	    	}
	    }
		//var_dump($result);
		//die(__FILE__.': '.__LINE__);
		
		return $result;
		
	}
	
	/**
	 * Check if director exists in database and save if new.
	 * 
	 * @param  string $name
	 * @throws Zend_Exception
	 * @return int
	 */
	public function personId($name=null, $position='actor', $new_format=false){
		
		if ($name){
		    
			switch ($position){
				case 'actor':
					$table = $this->actorsTable;
					break;
				case 'director':
					$table = $this->directorsTable;
					break;
			}
				
			if (!$row = $table->fetchRow("`complete_name` LIKE '$name'")){
				try {
					$id = $table->insert(array('complete_name'=>$name));
				} catch (Zend_Db_Table_Exception $e) {
					throw new Zend_Exception($e->getMessage(), $e->getCode(), $e);
				}
			} else {
				$id = $row->id;
			}
			return (int)$id;
			
		}
	}
	
	/**
	 * @param array $credits
	 * @param array $info
	 */
	public function saveCredits ($credits=array(), $info=array()) {
		
		if( empty( $credits ) || empty( $info ) ) 
		throw new Exception("Пропущен один или все параметры для ".__METHOD__, 500);;
		
		$table   = new Admin_Model_DbTable_Actors();
		$tolower = new Zend_Filter_StringToLower( $this->_tolower_options );
		$props   = new Admin_Model_DbTable_ProgramsProps();
		//$cache   = new Xmltv_Cache();
		
		//var_dump($credits);
		//die(__FILE__.': '.__LINE__);
		
		foreach ($credits['actors'] as $k => $p) {
			
			$exists=false;
			$parts=explode( ' ', $p );
			
			if( count( $parts ) == 2 ) {
				
				$snames=$table->fetchAll( 
				"`f_name` LIKE '" . $tolower->filter( $parts[0] ) . "'
				AND `s_name` LIKE '" . $tolower->filter( $parts[1] ) . "'" );
				
				if(count($snames)) {
					foreach ($snames as $sn) {
						
						$existingName = $tolower->filter( sprintf( "%s %s", $sn->f_name, $sn->s_name ) );
						
						//var_dump($sn);
						//var_dump($existingName);
						//die(__FILE__.': '.__LINE__);
						
						try {
							if( $existingName == $tolower->filter( implode( ' ', $parts ) ) ) {
								$this->_updateProgramActors( $sn->toArray(), $info );
							}
						} catch (Exception $e) {
							echo __FUNCTION__.' Error# '.$e->getCode().': '. $e->getMessage();
							die(__FILE__.': '.__LINE__);
						}
						
					}
				} else {
					
					//die(__FILE__.': '.__LINE__);
					
					$new = $this->_addCreditsName( $parts, 'actor', $info );
					$this->_updateProgramActors( $new, $info );
				}
			} elseif( count( $parts ) == 3 ) {
				
				//die(__FILE__.': '.__LINE__);
				
				$snames = $table->fetchAll( 
				"`f_name` LIKE '" . $tolower->filter( $parts[0] ) . "' 
				AND `m_name` LIKE '" . $tolower->filter( $parts[1] ) . "' 
				AND `s_name` LIKE '" . $tolower->filter( $parts[2] ) . "'" );
			
				if( count( $snames ) ) {
					foreach ($snames as $sn) {
						$existingName=$tolower->filter( sprintf( "%s %s %s", $sn->f_name, $sn->m_name, $sn->s_name ) );
						if( $existingName == $tolower->filter( implode( ' ', $parts ) ) ) {
							try {
								$this->_updateProgramActors( $sn->toArray(), $info );
							} catch (Exception $e) {
								echo __FUNCTION__.' Error# '.$e->getCode().': '. $e->getMessage();
								die(__FILE__.': '.__LINE__);
							}
						}
					}
				} else {
					
					//die(__FILE__.': '.__LINE__);
					
					$new = $this->_addCreditsName( $parts, 'actor', $info );
					$this->_updateProgramActors( $new, $info );
				}
				
			}  elseif( count( $parts ) > 3 ) {
				/* ошибка в данных */
				unset( $credits['actors'][$k] );
			}
			
		}
		
		$table=new Admin_Model_DbTable_Directors();
		foreach ($credits['directors'] as $k => $p) {
			
			$parts=explode( ' ', $p );
			
			if( count( $parts ) == 2 ) {
				
				$snames=$table->fetchAll( 
				"`f_name` LIKE '" . $tolower->filter( $parts[0] ) . "' AND `s_name` LIKE '" . $tolower->filter( $parts[1] ) . "'" );
				
				if( count( $snames ) ) {
					foreach ($snames as $sn) {
						$existingName=$tolower->filter( sprintf( "%s %s", $sn->f_name, $sn->s_name ) );
						if( $existingName == $tolower->filter( implode( ' ', $parts ) ) ) {
							try {
								$this->_updateProgramDirectors( $existingName, $info );
							} catch (Exception $e) {
								echo __FUNCTION__.' Error# '.$e->getCode().': '. $e->getMessage();
								die(__FILE__.': '.__LINE__);
							}
						}
					}
				} else {
					$new = $this->_addCreditsName( $parts, 'director', $info );
					$this->_updateProgramDirectors( $new, $info );
					
				}
				
				
			} elseif( count( $parts ) == 3 ) {
				$snames=$table->fetchAll( 
				"`f_name` LIKE '" . $tolower->filter( $parts[0] ) . "' 
					AND `m_name` LIKE '" . $tolower->filter( $parts[1] ) . "' 
					AND `s_name` LIKE '" . $tolower->filter( $parts[2] ) . "'" );
				
				if( count( $snames ) ) {
					foreach ($snames as $sn) {
						$existingName=$tolower->filter( sprintf( "%s %s %s", $sn->f_name, $sn->m_name, $sn->s_name ) );
						if( $existingName == $tolower->filter( implode( ' ', $parts ) ) ) {
							try {
								$this->_updateProgramDirectors( $existingName, $info );
							} catch (Exception $e) {
								echo __FUNCTION__.' Error# '.$e->getCode().': '. $e->getMessage();
								die(__FILE__.': '.__LINE__);
							}
						}
					}
				} else {
					$new = $this->_addCreditsName( $parts, 'director', $info );
					$this->_updateProgramDirectors( $new, $info );
				}
				
			} elseif( count( $parts ) > 3 ) {
				/* ошибка в данных */
				unset( $credits['directors'][$k] );
			} else {
				continue;
			}
		}
		
	}

	
	private function _addCreditsName ($parts=array(), $type='actor', $info=array()) {
		
		$serializer=new Zend_Serializer_Adapter_Json();
		$props=new Admin_Model_DbTable_ProgramsProps();
		
		if( $type == 'actor' )
		$table=new Admin_Model_DbTable_Actors();
		if( $type == 'director' )
		$table=new Admin_Model_DbTable_Directors();
		
		$found=false;
		try {
			
			if( count( $parts ) == 2 ) {
				$found=$table->fetchRow( "`f_name`='%" . $parts[0] . "%' AND `s_name`='%" . $parts[1] . "%'" );
				if( !$found )
				$new=$table->createRow( array('f_name'=>$parts[0], 's_name'=>$parts[1]) );
			}
			
			if( count( $parts ) == 3 ) {
				$found=$table->fetchRow( 
				"`f_name`='%" . $parts[0] . "%' AND `m_name`='%" . $parts[1] . "%' AND `s_name`='%" . $parts[2] . "%'" );
				if(  !$found )
				$new=$table->createRow( array('f_name'=>$parts[0], 'm_name'=>$parts[1], 's_name'=>$parts[2]) );
			}
			
			$id=$new->save();
			$new->id=(int)$id;
			return $new->toArray();
		
		} catch (Exception $e) {
			echo __METHOD__.": Не могу сохранить запись";
			die( __FILE__ . ': ' . __LINE__ );
		}
		//die(__FILE__.': '.__LINE__);
	}


	/**
	 * @param array $existing
	 * @param array $info
	 * @return void
	 */
	private function _updateProgramActors ($existing = array(), $info = array()) {
		
		if( empty( $existing ) || empty( $info ) ) 
		throw new Exception("Пропущен один или все параметры для ".__METHOD__, 500);
		
		if (!is_array($existing))
		throw new Exception("Неверный тип данных для ".__METHOD__, 500);
		
		$props = new Admin_Model_DbTable_ProgramsProps();
		$serializer = new Zend_Serializer_Adapter_Json();
		
		$p_props = $props->fetchRow("`hash`='".$info['hash']."'" );
		if( count( $p_props ) == 0 ) {
			try {
				$p_props = $props->createRow();
				$p_props->hash = $info['hash'];
				$p_props->actors = $serializer->serialize( array($existing['id']) );
				try {
					$p_props->save();
				} catch (Exception $e) {
					if ($e->getCode()!=1062){
						var_dump($e->getCode());
						echo "Не могу добавить актера: " . $e->getMessage();
						var_dump($e->getTrace());
						die( __FILE__ . ': ' . __LINE__ );
					}
				}
			} catch (Exception $e) {
				if ($e->getCode()!=1062){
					var_dump($e->getCode());
					echo "Не могу добавить актера: " . $e->getMessage();
					var_dump($e->getTrace());
					die( __FILE__ . ': ' . __LINE__ );
				}
			}
		} else {
			try {
				$p_props = $p_props->toArray();
				$p_props['hash'] = $info['hash'];
				$persons = !empty($p_props['actors']) ? $p_props['actors'] : '[]' ;
				$persons = $serializer->unserialize( $persons );
				if(  !( in_array( $existing['id'], $persons ) ) ) {
					$persons[] = $existing['id'];
				}
				$p_props['actors'] = $serializer->serialize( $persons );
				$props->update( $p_props, "`hash`='" . $info['hash'] . "'" );
			} catch (Exception $e) {
				echo "Не могу обновить актера: ";
				echo __FUNCTION__.' Error# '.$e->getCode().': '. $e->getMessage();
				die(__FILE__.': '.__LINE__);
				/*
				if( $e->getCode() == 0 ) {
					
					$p_props['actors'] = $serializer->serialize( array($existing['id']) );
					try {
						$props->update( $p_props, "`hash`='" . $info['hash'] . "'" );
					} catch (Exception $e) {
						echo "Не могу обновить актера: " . $e->getMessage();
						die( __FILE__ . ': ' . __LINE__ );
					}
				}
				*/
			}
		}
	}


	/**
	 * @param array $existing
	 * @param array $info
	 * @return void
	 */
	private function _updateProgramDirectors ($existing = array(), $info = array()) {
		
		if( empty($existing) || empty( $info ) ) 
		throw new Exception("Пропущен один или все параметры для ".__METHOD__, 500);
		
		//var_dump(func_get_args());
		//die(__FILE__.': '.__LINE__);
		
		$props = new Admin_Model_DbTable_ProgramsProps();
		$serializer = new Zend_Serializer_Adapter_Json();
		$p_props = $props->fetchRow("`hash`='".$info['hash']."'" );
		
		if( count( $p_props ) == 0 ) {
			$p_props = $props->createRow();
			$p_props->hash = $info['hash'];
			try {
				$p_props->directors = $serializer->serialize( array($existing['id']) );
				$p_props->save();
			} catch (Exception $e) {
				if ($e->getCode()!=1062) {
					echo "Не могу добавить режиссера: ";
					echo __FUNCTION__.' Error# '.$e->getCode().': '. $e->getMessage();
					die(__FILE__.': '.__LINE__);
				}
			}
		} else {
			try {
				$p_props = $p_props->toArray();
				$p_props['hash'] = $info['hash'];
				$persons = !empty($p_props['directors']) ? $p_props['directors'] : '[]' ;
				$persons = $serializer->unserialize( $persons );
				if(  !( in_array( $existing['id'], $persons ) ) ) {
					$persons[] = $existing['id'];
				}
				$p_props['directors'] = $serializer->serialize( $persons );
				$props->update( $p_props, "`hash`='" . $info['hash'] . "'" );
			} catch (Exception $e) {
				echo "Не могу обновить режиссера: " . $e->getMessage();
				echo __FUNCTION__.' Error# '.$e->getCode().': '. $e->getMessage();
				die(__FILE__.': '.__LINE__);
				/*
				if( $e->getCode() == 0 ) {
					$p_props['directors'] = $serializer->serialize( array($existing['id']) );
					try {
						$props->update( $p_props, "`hash`='" . $info['hash'] . "'" );
					} catch (Exception $e) {
						echo "Не могу обновить режиссера: " . $e->getMessage();
						die( __FILE__ . ': ' . __LINE__ );
					}
				}
				*/
			}
		}
	}


	
	public function getPremieresCurrentWeek(){
		
		$d = new Zend_Date(null, null, 'ru');
		do{
			$d->subDay(1);
		} while ($d->toString(Zend_Date::WEEKDAY_DIGIT, 'ru')>1);
		$weekStart = $d;
		
		$d = new Zend_Date(null, null, 'ru');
		do{
			$d->addDay(1);
		} while ($d->toString(Zend_Date::WEEKDAY_DIGIT, 'ru')>0);
		$weekEnd = $d;
		
		$result = $this->programsTable->fetchAll(array(
			"`start`>='".$weekStart->toString('yyyy-MM-dd')." 00:00:00'",
			"`end`<='".$weekEnd->toString('yyyy-MM-dd')." 23:59:59'",
			"`title` LIKE '%премьера%'"
		), "start ASC");
		for ($i=0; $i<count($result); $i++) {
			//var_dump($result[$i]);
			//var_dump($result[$i]->title);
			/*
			if (Xmltv_String::stristr($current->title, 'премьера'){
				
			}
			*/
		}
		
		//var_dump($weekStart->toString(Zend_Date::DATE_MEDIUM));
		//var_dump($weekEnd->toString(Zend_Date::DATE_MEDIUM));
	}
	
	/**
	 * 
	 * @param  string $input
	 * @throws Exception
	 * @return string
	 */
	private function _getDateString($input=null){
		
		if(!$input)
			throw new Exception("Пропущен один или все параметры для ".__METHOD__, 500);
		
		$date['year']	  = substr($input, 0, 4);
		$date['month']	 = substr($input, 4,2);
		$date['day']	   = substr($input, 6,2);
		$date['hours']	 = substr($input, 8,2);
		$date['minutes']   = substr($input, 10,2);
		$date['seconds']   = substr($input, 12,2);
		$date['gmt_diff']  = substr($input, 16,4);
		return $date['year'].'-'.$date['month'].'-'.$date['day'].' '.$date['hours'].':'.$date['minutes'].':'.$date['seconds'].' '.$date['gmt_diff'];
		
	}
	
	
	public function saveActor($data=array()){
		
		if (empty($data)) {
			throw new Zend_Exception("Пропущен один или все параметры для ".__METHOD__, 500);
		}
		
		$table = new Admin_Model_DbTable_Actors();
		
		die(__FILE__.': '.__LINE__);
		
		if (!$actor_found = @$table->fetchRow(array( "`f_name`='".$data['f_name']."'", "`s_name`='".$data['s_name']."'" ))->toArray()) {
			try {
				$actor_id = $table->insert($data);
			} catch(Zend_Db_Table_Exception $e) {
				echo __METHOD__.' error#'.$e->getCode().': '.$e->getMessage();
				die(__FILE__.': '.__LINE__);
			}
		} else {
			$actor_id = $actor_found['id'];
		}
		return $actor_id;
	}
	
	/**
	 * Save new program to DB
	 * 
	 * @param  array $info
	 * @throws Exception
	 * @return unknown|Ambigous <mixed, multitype:>
	 * @deprecated
	 */
	public function saveProgram( $data=array()){
		
	    if (empty($data)) {
	        throw new Zend_Exception( Rtvg_Message::ERR_MISSING_PARAM, 500);
	    } elseif(!is_array($data)){
	        throw new Zend_Exception( Rtvg_Message::ERR_WRONG_TYPE_FOR.__METHOD__, 500);
	    } else {
	        $data['actors']       = !isset($data['actors'])       || empty($data['actors'])       ? '' : $data['actors'] ;
	        $data['directors']    = !isset($data['directors'])    || empty($data['directors'])    ? '' : $data['directors'] ;
	        $data['writers']      = !isset($data['writers'])      || empty($data['writers'])      ? '' : $data['writers'] ;
	        $data['adapters']     = !isset($data['adapters'])     || empty($data['adapters'])     ? '' : $data['adapters'] ;
	        $data['producers']    = !isset($data['producers'])    || empty($data['producers'])    ? '' : $data['producers'] ;
	        $data['composers']    = !isset($data['composers'])    || empty($data['composers'])    ? '' : $data['composers'] ;
	        $data['editors']      = !isset($data['editors'])      || empty($data['editors'])      ? '' : $data['editors'] ;
	        $data['presenters']   = !isset($data['presenters'])   || empty($data['presenters'])   ? '' : $data['presenters'] ;
	        $data['commentators'] = !isset($data['commentators']) || empty($data['commentators']) ? '' : $data['commentators'] ;
	        $data['guests']       = !isset($data['guests'])       || empty($data['guests'])       ? '' : $data['guests'] ;
	        $hash = $this->programsTable->insert($data);
	        return $hash;
	    }
	    
	    return false;
	    
	}
	
	/**
	 * Search for particular program by it's cache
	 * 
	 * @param  string $hash
	 * @throws Exception
	 * @throws Zend_Exception
	 * @return Zend_Db_Table_Row
	 */
	public function findProgram($hash=null){
	
		if ($hash) {
			return $this->programsTable->find($hash)->current();
		}
		
	}
	
	
	public function getProgramsCountForWeek(Zend_Date $weekStart, Zend_Date $weekEnd){
		return $this->programsTable->getProgramsCountForWeek($weekStart, $weekEnd);
	}
	
	
	/**
	 * 
	 * Convert date string to Zend_Date object
	 * @param string $string
	 * @return Zend_Date
	 */
	public function startDateFromAttr($string=null){
		
		$f = Zend_Date::YEAR."-".Zend_Date::MONTH."-".Zend_Date::DAY." ".Zend_Date::HOUR.':'.Zend_Date::MINUTE.':'.Zend_Date::SECOND.' '.Zend_Date::GMT_DIFF;
		$date_str = $this->_getDateString($string);
		return new Zend_Date($date_str, $f);
		
	}
	
	/**
	 *
	 * Convert date string to Zend_Date object
	 * @param string $string
	 * @return Zend_Date
	 */
	public function endDateFromAttr($string=null){
		
		$f = Zend_Date::YEAR."-".Zend_Date::MONTH."-".Zend_Date::DAY." ".Zend_Date::HOUR.':'.Zend_Date::MINUTE.':'.Zend_Date::SECOND.' '.Zend_Date::GMT_DIFF;
		$date_str = $this->_getDateString($string);
		return new Zend_Date($date_str, $f);
		
	}
	

	public function deletePrograms($start=null, $end=null, $linked=false){
		
	    //var_dump(func_get_args());
	    //die(__FILE__.': '.__LINE__);
	    
	    if ($start && $end){
	        
			if ($linked) {
			    $this->programsTable->deleteProgramsLinked($start, $end);
			} else {
				$this->programsTable->delete(array(
					"`start` >= '$start'",
					"`start` < '$end'"
				));
			}
			
	    }
		
	}
	
	/**
	 * Generate alias
	 * 
	 * @param  string $input
	 * @param  bool   $replace_plus //Заменять + на слово
	 * @return string|null
	 */
	public static function makeAlias($input, $replace_plus=false){
		
	    if (APPLICATION_ENV=='development'){
	        //var_dump($input);
	        //die(__FILE__.': '.__LINE__);
	    }
	    
		$result = $input;
		
		$symbols = array('"', '.', ',', '...', '…', '!', '?', ':', ';', '-', '  ');
		foreach ($symbols as $s){
		    if (stristr( $result, $s)){
		        $result = str_replace($s, ' ', $result );
		    }
		}
		
		$cyrillic = array('ё'=>'е', 'Ё'=>'Е');
		foreach ($cyrillic as $s=>$r){
		    if (Xmltv_String::stristr( $result, $s)){
		    	$result = str_replace($s, $r, $result );
		    }
		}
		
		if ($replace_plus){
		    if (Xmltv_String::stristr($result, '+'))
		    	$result = Xmltv_String::str_replace( '+', '-плюс-', $result );
		}
		 
		$result = preg_replace('/[^0-9\p{Cyrillic}\p{Latin}]+/ui', ' ', $result);
		
		//$f = new Zend_Filter_StringTrim(array('charlist'=>'".,…!?:;- \/\''));
		//$result = $f->filter($result);
		
		$f = new Zend_Filter_Word_SeparatorToDash(' ');
		$result = Xmltv_String::strtolower( $f->filter( $result ));
		
		$result = trim( $result, '-' );
		
		$result = Xmltv_String::strtolower( $result );
		
		if (APPLICATION_ENV=='development'){
			//var_dump($result);
			//die(__FILE__.': '.__LINE__);
		}
		
		return $result;
		
	}
	
	/**
	 * Parse spor program title
	 * 
	 * @param  array $input
	 * @return array
	 */
	protected function parseSportsTitle($input){
		
		if (APPLICATION_ENV=='development'){
			//var_dump(func_get_args());
			//die(__FILE__.': '.__LINE__);
		}
		
		$result = $input;
	    
		$sports = array(
	    		'Street Workout',
	    		'Автоспорт',
	    		'Альпинизм',
	    		'Австралийский футбол',
	    		'Американский футбол',
	    		'Баскетбол',
	    		'Бальные танцы',
	    		'Боевые искусства',
	    		'Биатлон',
	    		'Профессиональный бокс',
	    		'Бокс',
	    		'Боулинг',
	    		'Волейбол',
	    		'виндсерфинг',
	    		'Гандбол',
	    		'Гонки',
	    		'Горнолыжный спорт',
	    		'Горные лыжи',
	    		'Гребля',
	    		'Клиффдайвинг',
	    		'Конный спорт',
	    		'Конькобежный спорт',
	    		'Лыжные гонки',
	    		'Мотоспорт',
	    		'Мотофристайл',
	    		'Парусный спорт',
	    		'Плавание',
	    		'Прыжки на лыжах с трамплина',
	    		'Прыжки на лыжах',
	    		'Прыжки с трамплина',
	    		'Регби',
	    		'Родео',
	    		'Санный спорт',
	    		'Сквош',
	    		'Смешанные единоборства',
	    		'Скейтбординг',
	    		'Сноуборд',
	    		'Снукер',
	    		'Спортивные танцы',
	    		'Теннис',
	    		'Тимберспорт',
	    		'Триатлон',
	    		'Фигурное катание',
	    		'Фрирайд',
	    		'Фристайл',
	    		'Футбол',
	    		'Хоккей',
	    		'Шары',
	    		'Шахматы',
	    		'Экстремальные виды спорта',
	    );
	    $champs = array(
	    		'Чемпионат Италии',
	    		'Чемпионат Испании',
	    		'Чемпионат Германии',
	    		'Кубок',
	    );
	    
		$regex= array(
	    		'/^('.implode('|', $sports).')\.\s+([\p{Common}\p{Cyrillic}\p{Latin}]+)\.\s*([\p{Common}\p{Cyrillic}\p{Latin}]*)$/ui',
	    		'/^('.implode('|', $champs).').*\.?(\s*)([\p{Common}\p{Cyrillic}\p{Latin}]+)$/ui',
	    );
	    foreach ($regex as $r){
	    	if (preg_match($r, $input['title'], $m)){
	    		$result['title'] = trim(trim($m[1]).'. '.trim($m[2]), ' .');
	    		$result['sub_title'] = isset($m[3]) ? trim($m[3]) : '' ;
	    		$result['category'] = $this->catIdFromTitle('спорт');
	    	}
	    }
	    
		if (APPLICATION_ENV=='development'){
			//var_dump($result);
			//die(__FILE__.': '.__LINE__);
		}
	    
		$regex = '/^(Мировой Кубок)\.\s+([\p{Common}\p{Cyrillic}\p{Latin}]+)$/ui';
	    if (preg_match($regex, $input['title'], $m)){
	    	//var_dump($string);
	    	$result['title']	 = trim($m[1]);
	    	$result['sub_title'] = isset($m[2]) ? trim($m[2]) : '' ;
	    	$result['category'] = $this->catIdFromTitle('спорт');
	    }
	    
	    
	    $regex = array(
	    		'/^((Жеребьевка)\s+([\p{Common}\p{Cyrillic}\p{Latin}]+))\.\s+(\p{Common}\p{Cyrillic}\p{Latin})$/ui',	  				//Жеребьевка 1/8 финала Лиги чемпионов. Прямая трансляция
	    		'/^("([\p{Common}\p{Cyrillic}]+)"\.\s+([\p{Common}\p{Cyrillic}\p{Latin}]+))\.\s+([\p{Common}\p{Cyrillic}\p{Latin}]+)$/ui',			//"В дни теннисных каникул". Уимблдон-2012. Сабина Лисицки - Божана Йовановски
	    		'/^("([\p{Common}\p{Cyrillic}]+)"\.\s+([\p{Common}\p{Cyrillic}\p{Latin}]+\.\s+[\p{Common}\p{Cyrillic}\p{Latin}]+))\.\s+([\p{Common}\p{Cyrillic}\p{Latin}]+)$/ui',	//"В дни теннисных каникул". Уимблдон-2012. Финал. Д. Маррей/Фр. Нильсен - Р. Линдстедт/Х. Текау
	    		'/^(.+(Чемпионат России\s[\p{Common}\p{Cyrillic}\d\s]+))\sсезона\s([\d]{4}-[\d]{4})\sгода\.?(.+)/ui',	//СОГАЗ - Чемпионат России по футболу сезона 2012-2013 года. "Терек" - "Динамо"
	    );
	    foreach ($regex as $r){
	    	if (preg_match($r, $input['title'], $m)){
	    		$result['title']	 = trim($m[2]).' '.trim($m[3]);
	    		$result['sub_title'] = isset($m[4]) ? trim($m[4]) : '' ;
	    		$result['category']  = $this->catIdFromTitle('спорт');
	    	}
	    }
	    
	    if (Xmltv_String::stristr($input['title'], ' Этап Кубка ')){
	    	$e = explode('. ', $input['title']);
	    	$result['title'] = trim($e[0]).'. '.trim($e[1]);
	    	unset($e[0]); unset($e[1]);
	    	$result['sub_title'] = implode(', ', $e);
	    	$result['category']  = $this->catIdFromTitle('спорт');
	    }
	    
	    $regex = array(
	    		'/^(.+),\s+(раунд\s+[\d]+)$/ui', //Чемпионат мира по смешанным единоборствам Mix Fight M1 Сhallenge, раунд 7
	    		'/^(.+),\s+(Этап\s+[\d]+,\s+\w+)$/ui',  //Мировая серия по мотофристайлу "X-Fighters" 2012 года, Этап 5, Мюнхен
	    );
	    foreach ($regex as $r){
	    	if (preg_match($r, $input['title'], $m)){
				$result['title'] = trim($m[1]);
	    		$result['sub_title'] = Xmltv_String::ucfirst(trim($m[2]));
	    		
	    	}
	    }
	    
	    if (APPLICATION_ENV=='development'){
	    	//var_dump($result);
	    	//die(__FILE__.': '.__LINE__);
	    }
	    
	    if ($result) {
	    	return $result;
	    }
	    
	    return $input;
	    
	}
	
	/**
	 * Parse news program title
	 * 
	 * @param  array $input
	 * @return array
	 */
	protected function parseNewsTitle($input){
		
		$result = $input;
		
	    if ( $input['title']=='Вечерние новости с субтитрами') {
	        
	    	$result['title'] = $input['title'];
	    	$result['sub_title'] = '';
	    	$result['category'] = $this->catIdFromTitle('Новости');
	    	$result['live'] = 1;
	    	
	    } elseif (Xmltv_String::stristr($input['title'], 'Новости, ')){
	        $result = $input;
	    	$comma = Xmltv_String::strpos($input['title'], ',');
	    	$title = trim( Xmltv_String::substr($input['title'], 0, $comma));
	    	$result['title'] = $title;
	    	$result['sub_title'] = Xmltv_String::ucfirst( trim( Xmltv_String::substr( $input['title'], $comma+1, Xmltv_String::strlen( $input['title']))) );
	    	$result['category'] = $this->catIdFromTitle('Новости');
	    	$result['live'] = 1;
	    }
	    
	    $regex = array(
	    		'/^Новости$/ui',
	    		'/^Время$/ui',
	    		'/^Вести$/ui',
	    		'/^Вести\sнедели$/ui',
	    		'/^Новости.+субтитрами.*$/ui',
	    		'/^(вечерние)\sновости$/ui',
	    		'/^местное время\.?.*$/iu',
	    		'/^события\.?.*$/iu',
	    		'/ news /iu',
	    		'/Евроньюс/iu',
	    		'/Euronews/iu',
	    );
	    foreach ($regex as $r){
	    	if (preg_match($r, $input['title'], $m)){
	    	    $result = $input;
	    		$result['category'] = $this->catIdFromTitle('Новости');
	    		$result['live'] = 1;
	    	}
	    		
	    }
	    
	    return $result;
	    
	}
	
	/**
	 * Detect live program
	 * 
	 * @param  array $input
	 * @return array
	 */
	protected function detectLive($input){
		
	    $result = $input;
	    $result['live'] = 0;
	    
	    $search = array(
	    	'Прямая трансляция',
	    	'Трансляция из',
	    	'Доброе утро',
	    	'Утро на канале',
	    );
	    foreach ($search as $string){
	        if ( Xmltv_String::stristr( $input['title'], $string )) {
	            $result['live'] = 1;
	        }
	    }
	    
	    if ( preg_match( '/^(Прямая трансляция:\s)(.+)$/ui', $input['title'], $m)){
	    	$result['title'] = Xmltv_String::str_ireplace( $m[1], '', $input['title']);
	    	$result['live'] = 1;
	    } elseif( preg_match('/^(.+)\s(Прямая трансляция).*$/ui', $input['title'], $m)){
	    	$result['title'] = Xmltv_String::str_ireplace( $m[2], '', $input['title']);
	    	$result['live'] = 1;
	    }
	    
	    if (Xmltv_String::stristr($input['title'], 'Live')){
	    	$result['title'] = str_replace( 'Live. ', '', $input['title'] );
	    	$result['title'] = str_replace( 'Live ', '', $input['title'] );
	    	$result['live']  = 1;
	    }
	    
	    return $result;
	    
	}
	
	/**
	 * Detect premiere
	 * 
	 * @param  array $input
	 * @return int
	 */
	protected function detectPremiere($input){
	    
	    $result = $input;
	    $result['premiere'] = 0;
	    
	    if (APPLICATION_ENV=='development'){
	        //var_dump($result);
	    }
	    
	    $regex = array(
	    		'/\s+Нов(ые|ая)\s+сери(и|я)/ui',
	    		'/\s+премьера/ui',
	    		'/^Премьера\.?\s/ui',
	    		'/^Премьера сезона\.?\s*/ui',
	    );
	    foreach ($regex as $r) {
	    	if ( preg_match($r, $input['title'], $m) ){
	    	    $result['title'] = Xmltv_String::str_ireplace($m[0], '', $result['title']);
	    		$result['premiere'] = 1;
	    		
	    		if (APPLICATION_ENV=='development'){
	    			//var_dump($result);
	    			//die(__FILE__.': '.__LINE__);
	    		}
	    	}
	    }
	    
	    return $result;
	}
	
	
	/**
	 * Parse music program title
	 * 
	 * @param  array $input
	 * @return array
	 */
	protected function parseMusicTitle($input){
		
	    /*
	     * Джазовые концерты и фестивали
	    */
	    $trim = new Zend_Filter_StringTrim(array('charlist'=>' .,'));
	    $music = array(
	    		'Фестиваль в .+',
	    		'Концерт на .+',
	    		'Исполняет .+',
	    		'Хореография.+',
	    		'на \w+ джазовом фестивале.+',
	    		'на фестивале .+',
	    		'на джазовом фестивале в .+',
	    		'в театре .+',
	    		'в клубе .+',
	    );
	    $regex = array(
	    		//'/^("[\w\s-]+")(\s)\(.+\).+$/ui', //"Speсtrum Road" (Дж. Брюс, С. Блэкмен, Дж. Медески и В. Рейд) в клубе "Порги и Бесс"
	    		'/^(Произведения [\w\s,-]+)\.(\s)(.+ под управлением .+)$/ui', //Произведения Брамса и Шимановского. Лондонский симфонический оркестр под управлением Валерия Гергиева
	    		'/^(Произведения [\w\s,-]+)(\s)(в исполнении .+)$/ui', //Произведения Адамса и Малера в исполнении Лос-Анджелесского филармонического оркестра под управлением Г. Дудамеля
	    		'/^(Оркестр [\w\s,"-]+)\.(\s)(.+)$/u', //Оркестр Чарли Хейдена "Liberation Music Orchestra". Концерт на фестивале джаза в Монреале
	    		'/^([\w\s\.-]+: Симфония №[\d]{1,})\.(\s)(.+)$/ui', //Бетховен: Симфония №1. Ансамбль "Les Dissonanсes"
	    		'/^([\w\s\.-]+: Квартет №[\d]{1,})\.(\s)(.+)$/ui', //Бетховен: Квартет №9. Ансамбль "Les Dissonanсes"
	    		'/^([\w\s\.-]+\.? Концерт для [\w\s,]+)\.(\s)(.+)$/ui', //Бетховен. Концерт для скрипки. Давид Грималь и ансамбль "Les Dissonanсes". Дижон
	    		//"Так поступают все женщины". Фестиваль в Зальцбурге
	    		//Баллаке Сиссоко и Венсан Сегаль. Концерт на джазовом фестивале "Rhino Jazz"
	    		//Произведения Мусоргского, Яначека и Прокофьева. Исполняет Фазиль Сай. Гренобль
	    		//"Кантата". Хореография: М. Бежар. Балет Мориса Бежара
	    		//Музыка "Antilles Mizik" на Мартиниканском джазовом фестивале-2010
	    		//Юссу Ндур и "Super Etoile de Dakar" на фестивале в Фесе-2011
	    		'/^(.+).?(\s)('.implode('|', $music).')$/u',
	    );
	    foreach ($regex as $r){
	    	if (preg_match($r, $input['title'], $m)){
	    		$result['title']	 = $trim->filter(trim($m[1]).'. '.trim($m[2]), ' .');
	    		$result['sub_title'] = isset($m[3]) ? str_replace(array('(',')'), '', Xmltv_String::ucfirst(trim($m[3]))) : '' ;
	    		$result['category'] = $this->catIdFromTitle('Музыка');
	    	}
	    }
	    
	    /*
	     * Просто концерты
	    */
	    $regex = array(
	    		'/^(.+)\.\s(Концерт\s.+)$/ui' //"Не только о любви". Концерт Николая Баскова
	    );
	    foreach ($regex as $r){
	    	if (preg_match($r, $input['title'], $m)){
	    		if(APPLICATION_ENV=='development'){
	    			$result['title']	 = trim($m[1]);
	    			$result['sub_title'] = trim($m[2]);
	    		}
	    	}
	    }
	    
	    
	    /*
	     * классическая музыка
	    */
	    $regex = array(
	    		'/^(Симфония №[\d]{1,})\s\(([\w\s]+)\)\s(под управлением\s[\w\s\.-]+)$/ui', //Симфония №7 (Брамс) под управлением Карлоса Клайбера
	    		'/^(Симфония №[\d]{1,})\s\(([\w\s]+)\)\.?\s(Дирижер:\s[\w\s\.-]+)\.?$/ui', //Симфония №33 (Моцарт). Дирижер: Карлос Клайбер
	    		'/^(Увертюра .+)(\s)(под управлением\s[\w\s\.-]+)$/ui', //Увертюра "Кориолан" (Бетховен) под управлением Карлоса Клайбера
	    		'/^(Музыка [\w\s\,"-]+)\.?(\s)(Дирижер:\s[\w\s\.-]+)\.?$/u', //Музыка Дебюсси, Равеля и Бетховена. Дирижер: Эса-Пекка Салонен
	    );
	    foreach ($regex as $r){
	    	if (preg_match($r, $input['title'], $m)){
	    		$result['title']	 = trim($m[2]).', '.trim($m[1]);
	    		$result['sub_title'] = isset($m[3]) ? Xmltv_String::ucfirst(trim($m[3])) : '' ;
	    		$result['category'] = $this->catIdFromTitle('классическая музыка');
	    	}
	    }
	    
	    if ($result) {
	    	return $result;
	    }
	    
	    return $input;
	    
	}
	
	/**
	 * Parse series title
	 * 
	 * @param  array $input
	 * @return array
	 */
	protected function parseSeriesTitle($input){
		
	    // Обработка названий сериалов
	    $regex = '/(.+)\s*\("(.+)",\s+([\d]+)-я серия\)/ui';
	    if (preg_match($regex, $input['title'], $m)){
	        
	        $result = $input;
	        
	        if (APPLICATION_ENV=='development'){
	        	//var_dump($input['title']);
	        	//var_dump($m);
	        }
	        
	        $result['title']     = trim($m[1]);
	    	$result['sub_title'] = trim($m[2]);
	    	$result['episode']   = (int)$m[3];
	    	$result['category']  = $this->catIdFromTitle('Сериал');
	    	
	    	if (APPLICATION_ENV=='development'){
	    		//var_dump($result);
	    		//die(__FILE__.': '.__LINE__);
	    	}
	    	
	    	return $result;
	    	
	    }
	    
	    $regex = '/^(.+),\s(ч|часть)\.?\s?([\d]+)\.?$/ui'; //"Горница", ч. 1.
	    if (preg_match($regex, $input['title'], $m)){
	        
	        if (APPLICATION_ENV=='development'){
	        	var_dump($input['title']);
	        	var_dump($m);
	        }
	        
	        $result = $input;
	    	$result['title'] = trim($m[1]);
	    	$result['episode'] = (int)$m[3];
	    	$result['category'] = $this->catIdFromTitle('Сериал');
	    	
	    	if (APPLICATION_ENV=='development'){
	    		var_dump($result);
	    		die(__FILE__.': '.__LINE__);
	    	}
	    	
	    }
	    
	    
	    $regex = '/\s*\(\s*"(.+)",\s+([\d]+)-[\w]\s+и\s+([\d]+)-[\w]\s+серии\)/ui'; //Опергруппа-2 ( "Деньги - это бумага", 1-я и 2-я серии)
	    if (preg_match($regex, $input['title'], $m)){
	        
	        if (APPLICATION_ENV=='development'){
	        	//var_dump($input['title']);
	        	//var_dump($m);
	        }
	        
	        $result = $input;
	    	$result['title'] = Xmltv_String::str_ireplace($m[0], '', $input['title']);
	    	$result['sub_title'] = trim($m[1]);
	    	$result['episode'] = (int)$m[2].','.(int)$m[3];
	    	$result['category'] = $this->catIdFromTitle('Сериал');
	    	
	    	if (APPLICATION_ENV=='development'){
	    	    //var_dump($result);
	    		//die(__FILE__.': '.__LINE__);
	    	}
	    }
	    
	    
	    if (preg_match_all('/(([\d]+)-я серия - "([\w\d\s]+)"\.?)/ui', $input['title'], $m)){
	        
	        if (APPLICATION_ENV=='development'){
	        	//var_dump($m);
	        	//die(__FILE__.': '.__LINE__);
	        }
	        
	        $result = $input;
	        $trim = new Zend_Filter_StringTrim(array('charlist'=>' ()'));
	        $subTitle = (bool)$input['sub_title']===false ? '' : $input['sub_title'] ;
	        if (count($m[1])>1){
		        foreach ($m[1] as $k=>$s){
		            $input['title'] =  Xmltv_String::str_ireplace($s, '', $input['title']);
		            $subTitle .= "$s ";
		        }
		        $result['title']     = $trim->filter( $input['title'] );
		        $result['sub_title'] = $trim->filter( $subTitle );
	        } else {
	            $result['title']     = $trim->filter( Xmltv_String::str_ireplace($m[1][0], '', $input['title']));
	            $result['sub_title'] = $m[3][0];
	            $result['episode']   = (int)$m[2][0];
	        }
	        
	        $result['category'] = $this->catIdFromTitle('Сериал');
	        
	        if (APPLICATION_ENV=='development'){
	        	//var_dump($result);
	        	//die(__FILE__.': '.__LINE__);
	        }
	        
	        return $result;
	        
	    }
	    
	    
	    $regex = array(
	    		'/^(.+)\s*\(Фильм\s+([\d]+)-[\w]\.?\s+-?\s*"(.+)"\)/ui', //Потаенное судно (Фильм 1-й. "Потаенное судно. 1710-1900 гг.")
	    		'/^(.+)\s*\(Часть\s+([\d]+)-[\w]\.?\s+-?\s*"(.+)"\)/ui', //Летний дворец и тайные сады последних императоров Китая (Часть 1-я - "Цяньлун и рассвет Поднебесной")
	    		//'/^(.+)\s*\(([\d]+)-[\w]\s+серия\s+-\s+"(.+)"\)/ui',  //Сквозь кротовую нору с Морганом Фрименом (1-я серия - «С чего началась Вселенная?»)
	    );
	    foreach ($regex as $r){
	    	if (preg_match($r, $input['title'], $m)){
	    	    
	    	    $result = $input;
	    	    
	    		if (APPLICATION_ENV=='development'){
	    		    //var_dump($m);
	    		}
	    	    
	    		$result['title']     = trim($m[1]);
	    		$result['sub_title'] = trim($m[3]);
	    		$result['episode']   = (int)$m[2];
	    		$result['category']  = $this->catIdFromTitle('Познавательные');
	    		
	    		if (APPLICATION_ENV=='development'){
	    			//var_dump($result);
	    			//die(__FILE__.': '.__LINE__);
	    		}
	    		
	    		return $result;
	    	}
	    }
	    
	    
	    $regex = array(
	    		'/\s*\(([\d]+)-[\w]+\s+серия\)/ui',
	    		'/\s\(Серия\s([\d]+)\)$/ui',
	    		'/\s\(Эпизод ([\d]+)\.[\d]{1}\)$/ui',
	    		'/, (\d+)-я серия$/ui',
	    );
	    foreach ($regex as $r) {
	    	if (preg_match($r, $input['title'], $m)) {
	    	    
	    	    $result = $input;
	    	    
	    		if (APPLICATION_ENV=='development'){
	    			//var_dump($m);
	    		    //die(__FILE__.': '.__LINE__);
	    		}
	    		
	    		$result['title']    = trim( Xmltv_String::str_ireplace($m[0], '', $input['title']), '" ,.' );
	    		$result['episode']  = (int)$m[1];
	    		$result['category'] = $this->catIdFromTitle('Сериал');
	    		
	    		if (APPLICATION_ENV=='development'){
	    			//var_dump($result);
	    			//die(__FILE__.': '.__LINE__);
	    		}
	    		
	    		return $result;
	    	}
	    }
	    
	    $regex = array(
	    		'/\s*\(([\d]+)-[\w]\s*-\s*([\d]+)\s*-[\w]\s+серии\)/ui',
	    		'/\s*\(([\d]+)-[\w]\s+и\s+([\d]+)-[\w]\s+серии\)/ui',
	    		'/\s*Новые\s+серии\s+\(([\d]+)-[\w]+\s+серия\)/ui',
	    );
	    foreach ($regex as $r) {
	    	if (preg_match($r, $input['title'], $m)) {
	    	    
	    	    $result = $input;
	    	    
	    	    if (APPLICATION_ENV=='development'){
	    	        //var_dump($m);
	    	    }
	    	    
	    	    $result['title'] = Xmltv_String::str_ireplace($m[0], '', $input['title']);
	    		unset($m[0]);
	    		$result['episode']  = implode(',', $m);
	    		$result['category'] = $this->catIdFromTitle('Сериал');
	    		
	    		if (APPLICATION_ENV=='development'){
	    			//var_dump($result);
	    			//die(__FILE__.': '.__LINE__);
	    		}
	    		
	    		return $result;
	    	}
	    }
	    
	    $regex = array(
	    		'/^(.+)\.?\s+\("(.+)"\.?\s+([\d]+)-[\w]\s+серия\s+-\s+"(.+)"\)/ui', //Исторические путешествия Ивана Толстого ("Писательская любовь. Сергей Есенин". 1-я серия - "Дунькин платок")
	    		'/^(.+)\.?\s*\((.+)\s+([\d]+)-я\s+лекция\)/ui', //Aсademia (Галина Китайгородская. "Уникальность иностранного языка как учебного предмета". 1-я лекция)
	    );
	    foreach ($regex as $r){
	    	if (preg_match($r, $input['title'], $m)){
	    	    
	    	    $result = $input;
	    	    
	    	    if (APPLICATION_ENV=='development'){
	    	    	//var_dump($m);
	    	    }
	    	    
	    		$result['title'] = trim($m[1]);
	    		$result['sub_title'] = trim($m[2]).'. '.trim($m[4]);
	    		$result['episode'] = (int)$m[3];
	    		$result['category'] = $this->catIdFromTitle('Познавательные');
	    		
	    		if (APPLICATION_ENV=='development'){
	    			//var_dump($result);
	    			//die(__FILE__.': '.__LINE__);
	    		}
	    		
	    		return $result;
	    		
	    	}
	    }
	    
	    $regex = '/^(.+)\s\((Фильм\s+[\d]+)-[\w]\s+-\s+"(.+)",\s+([\d]+)-[\w]\s+и\s+([\d]+)-[\w]\s+серии\)/ui'; //Шериф (Фильм 5-й - "Сто тысяч для сына", 1-я и 2-я серии)
	    if (preg_match($regex, $input['title'], $m)){
	        die(__FILE__.': '.__LINE__);
	        $result = $input;
	    	$result['title'] = trim($m[1]);
	    	$result['sub_title'] = trim($m[2]).'. '.trim($m[3]);
	    	$result['episode'] = (int)trim($m[4]).','.(int)trim($m[5]);
	    	$result['category'] = $this->catIdFromTitle('Сериал');
	    }
	    
	    
	    $regex = '/^(.+)\s+\(Фильм\s+([\w]+):?\s+"(.+)"\)/ui'; //Предлагаемые обстоятельства (Фильм третий: "Богатый наследник")
	    if (preg_match($regex, $result['title'], $m)){
	        die(__FILE__.': '.__LINE__);
	        $result = $input;
	    	$result['title'] = trim($m[1]);
	    	$result['sub_title'] = trim($m[3]);
	    	switch(trim($m[2])){
	    		default:
	    		case 'первый':
	    			$result['episode'] = 1;
	    			break;
	    		case 'второй':
	    			$result['episode'] = 2;
	    			break;
	    		case 'третий':
	    			$result['episode'] = 3;
	    			break;
	    		case 'четвертый':
	    			$result['episode'] = 4;
	    			break;
	    	}
	    	$result['category'] = $this->catIdFromTitle('Сериал');
	    }
	    
	    
	    
	    if ( preg_match('/^(.+)\s?\(([\d]+)-?я?\s+серия\s-\s"([\w\d\s]+)"\.?\)/ui', $result['title'], $m)) { //(25-я серия - "Рейдерский захват".
	    	die(__FILE__.': '.__LINE__);
	    	$result = $input;
	    	$result['title']     = trim($m[1]).'. «'.trim($m[3]).'»';
	    	$result['sub_title'] = '';
	    	$result['episode']   = (int)$m[2];
	    	$result['category'] = $this->catIdFromTitle('Сериал');
	    }
	    
	    
	    // Обработка названий сериалов. проход 2, поиск в подзаголовке
	    $regex = array(
	    		'/([\d]+)-?[\w]?\s+серия\s?-\s?/ui', //10 серия
	    		'/серия\s+([\d]+)/ui', //Серия 7
	    );
	    foreach ($regex as $r){
	    	if (preg_match($r, $input['title'], $m)){
	    	    
	    	    $result = $input;
	    	    
	    	    if (APPLICATION_ENV=='development'){
	    	        //var_dump($m);
	    	    }
	    	    
	    	    $result['title'] = Xmltv_String::str_ireplace($m[0], '', $input['title']);
	    	    $result['sub_title'] = '';
	    		$result['episode'] = (int)$m[1];
	    		$result['category'] = $this->catIdFromTitle('Сериал');
	    		
	    		if (APPLICATION_ENV=='development'){
	    			//var_dump($result);
	    			//die(__FILE__.': '.__LINE__);
	    		}
	    		
	    		return $result;
	    	}
	    }
	    
	    if(APPLICATION_ENV=='development'){
	    	//var_dump($result);
	    	//die(__FILE__.': '.__LINE__);
	    }
	    
	    if ($result) {
	        $result['category'] = $this->catIdFromTitle('Сериал');
	        return $result;
	    }
	    
	    $input['episode'] = null;
	    return $input;
	    
	}
	
	/**
	 * Parse break
	 * 
	 * @param  array $input
	 * @return array
	 */
	protected function parseBreak(array $data){
		
	    $result = $data;
	    if (Xmltv_String::stristr($data['title'], 'внимание! ') || Xmltv_String::stristr($data['title'], "канал заканчивает вещание")
	    	 || Xmltv_String::stristr($data['title'], "Перерыв") ){
	        $result['title'] = 'Перерыв';
	    	$result['sub_title'] = '';
	    }
	    
	    if (Xmltv_String::stristr($data['title'], 'профилактика на канале! ') || (Xmltv_String::strtolower($data['title'])=="Профилактические работы")){
	        $result['title'] = 'Профилактика';
	        $result['sub_title'] = '';
	    }
	    
	    return $result;
	    
	}
	
	/**
	 * Detect and strip age rating
	 * 
	 * @param  array $input
	 * @return array
	 */
	protected function detectAgeRating($input){
		
	    //Проверка возрастного рейтинга в названии
	    if (($rating = $this->extractAgeRating($input['title']))>0) {
	        $result['title']  = Xmltv_String::ucfirst($this->stripAgeRating($input['title']));
	    	$result['rating'] = $rating;
	    }
	    
	    if ($result) {
	    	return $result;
	    }
	    
	    $input['rating'] = null;
	    return $input;
	    
	}
	
	/**
	 * @param array $input
	 */
	public function newBroadcast(array $input=array()){
		
	    return $this->programsTable->createRow( $input );
		
	}
	
	/**
	 * 
	 * @param  string $input
	 * @return string
	 */
	public function cleanTitle($input=null){
		
	    if (!$input){
	        return false;
	    }
	    
	    $result = $input;
	    $result = preg_replace('/\s+/u', ' ', $result);
	    return $result;
	    
	}
	
	/**
	 * 
	 * @return array
	 */
	public function getNamesRegex(){
		return $this->namesRegex;
	}
	
	public function getProgramHash($prog){
		
	    //Calculate hash
	    return md5($prog->alias.$prog->channel.$prog->start.$prog->end);
	    
	}
	
	
	
}

