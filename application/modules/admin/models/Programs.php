<?php
/**
 * 
 * Programs model for Admin module
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @package rutvgid
 * @filesource $Source: /home/developer/cvs/rutvgid.ru/application/modules/admin/models/Programs.php,v $
 * @version $Id: Programs.php,v 1.17 2012-12-25 01:57:53 developer Exp $
 */

class Admin_Model_Programs 
	{
	
	//protected $_tableName = 'rtvg_programs';
	/**
	 * 
	 * Programs table
	 * @var Admin_Model_DbTable_Programs
	 */
	protected $programsTable;
	/**
	 * 
	 * Table with properties on programs
	 * @var Admin_Model_DbTable_ProgramsProps
	 */
	protected $programsPropsTable;
	/**
	 * 
	 * Table with programs descriptions
	 * @var Admin_Model_DbTable_ProgramsDescriptions
	 */
	protected $programsDescTable;
	/**
	 * 
	 * Table with programs ratings
	 * @var Admin_Model_DbTable_ProgramsRatings
	 */
	protected $programsRatingsTable;
	/**
	 * 
	 * Table with programs categories
	 * @var Admin_Model_DbTable_ProgramsCategories
	 */
	protected $programsCategoriesTable;
	/**
	 * 
	 * Table with actors info
	 * @var Admin_Model_DbTable_Actors
	 */
	protected $actorsTable;
	/**
	 * 
	 * Table with directors info
	 * @var Admin_Model_DbTable_Directors
	 */
	protected $directorsTable;
	
	protected $catList;
	
	private $_trim_options=array('charlist'=>' -');
	private $_tolower_options=array('encoding'=>'UTF-8');
	private $_regex_list='/[:;_,\.\[\]\(\)\\`\{\}"\!\+\?]+/';
	private $_logger;
	private $_nameRegex = '/^[\w]{2,}-?\s*[\w]{2,}-?\s*([\w]{2,})?\s*(мл\.|ст\.|jr|sr)?$/ui';
	private $_ageRatingRegex = '/\s\(?([0-9]{1,2})\+\)?$/ui';
	private $_lAgeRatingRegex = '/^([0-9]{1,2})\+\s/ui';
	
	const ERR_MISSING_PARAMS = "Пропущен параметр!";
	const ERR_CANNOT_SAVE = "Не могу сохранить запись!";

	/**
	 * Model onstructor
	 */
	public function __construct(){
		
		$this->programsTable            = new Admin_Model_DbTable_Programs();
		$this->programsPropsTable       = new Admin_Model_DbTable_ProgramsProps();
		$this->programsDescTable        = new Admin_Model_DbTable_ProgramsDescriptions();
		$this->programsCategoriesTable  = new Admin_Model_DbTable_ProgramsCategories();
		$this->actorsTable              = new Admin_Model_DbTable_Actors();
		$this->directorsTable           = new Admin_Model_DbTable_Directors();
		$this->catList = $this->programsCategoriesTable->fetchAll();
		
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
		$programsAcrhive     = new Admin_Model_DbTable_ProgramsArchive();
		$descriptionsAcrhive = new Admin_Model_DbTable_ProgramsDescriptionsArchive();
		$propsAcrhive        = new Admin_Model_DbTable_ProgramsPropsArchive();
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
	    
	    if ($string){
	        
	        //var_dump($string);
	        
	        if (preg_match($this->_ageRatingRegex, $string, $m))
		    	$r = (int)$m[1];
		    elseif (preg_match($this->_lAgeRatingRegex, $string, $m))
		        $r = (int)$m[1];
		    
		    //var_dump($r);
		    
		    if ($r>0)
		        return $r; 
		    
	    }
	    
	}

	
	
	public function stripAgeRating($input){
	    if ($input){
	        //die(__FILE__.': '.__LINE__);
	        if (preg_match($this->_ageRatingRegex, $input, $m)){
	            return trim(Xmltv_String::str_ireplace(trim($m[0]), '', $input));
	        } elseif (preg_match($this->_lAgeRatingRegex, $input, $m)){
	            return Xmltv_String::str_ireplace(trim($m[0]), '', $input);
		    }
	        return $input;
	    }
	}

	
	
	/**
	 * Process program title and detect properties
	 * 
	 * @param string $string //Program title
	 */
	public function makeTitles ($input) {
		
		if(!$input)
			throw new Zend_Exception("No input provided!");
		
		//var_dump($input);
		
		$tolower = new Zend_Filter_StringToLower( $this->_tolower_options );
		
		//Проверка возрастного рейтинга в названии
		//var_dump(preg_match($this->_ageRatingRegex, $input));
		//die();
		if ($rating = $this->extractAgeRating($input)) {
			$result['rating'] = $rating;
			$result['title']  = Xmltv_String::ucfirst($this->stripAgeRating($input));
		} else {
		    $result['title'] = Xmltv_String::ucfirst($input);
		}
		$result['title'] = str_replace('...', '…', $result['title']);
		
		//var_dump($result);
		//die(__FILE__.': '.__LINE__);
		
		// Detect premiere
		$premieresRegex = array(
			'/Нов(ые|ая)\s+сери(и/я)/ui',
			'/премьера/ui',
		);
		foreach ($premieresRegex as $regex) {
			if (Xmltv_String::stristr($result['title'], 'Новые серии'))
				$result['premiere']=1;
		}
		
		if (Xmltv_String::stristr($result['title'], 'Прямая трансляция') || 
		Xmltv_String::stristr($result['title'], 'Трансляция из')) {
			$result['live'] = 1;
			if (preg_match('/^(Прямая трансляция:\s)(.+)$/ui', $result['title'], $m)){
			    $result['title'] = Xmltv_String::str_ireplace($m[1], '', $result['title']);
			} elseif(preg_match('/^(.+)\s(Прямая трансляция).*$/ui', $result['title'], $m)){
			    $result['title'] = Xmltv_String::str_ireplace($m[2], '', $result['title']);
			}
		}
		if (Xmltv_String::stristr($result['title'], 'Live')){
		    $result['title'] = str_replace( 'Live. ', '', $result['title'] );
		    $result['title'] = str_replace( 'Live ', '', $result['title'] );
		    $result['live']  = 1;
		}
		
		// Обработка названий сериалов
		$regex = '/\s*\("(.+)",\s+([\d])+-[\w]\s+серия\)/ui';
		if (preg_match($regex, $result['title'], $m)){
			$result['title'] = Xmltv_String::str_ireplace($m[0], '', $result['title']);
			$result['sub_title'] = trim($m[1]);
			$result['episode'] = (int)$m[2];
			
		}

		$regex = '/^(.+),\s(ч|часть)\.?\s?([\d]+)\.?$/ui'; //"Горница", ч. 1.
		if (preg_match($regex, $result['title'], $m)){
			$result['title'] = trim($m[1]);
			$result['episode'] = (int)$m[3];
			
		}

		$regex = '/\s*\(\s*"(.+)",\s+([\d]+)-[\w]\s+и\s+([\d]+)-[\w]\s+серии\)/ui'; //Опергруппа-2 ( "Деньги - это бумага", 1-я и 2-я серии)
		if (preg_match($regex, $result['title'], $m)){
			$result['title'] = Xmltv_String::str_ireplace($m[0], '', $result['title']);
			$result['sub_title'] = trim($m[1]);
			$result['episode'] = (int)$m[2].','.(int)$m[3];
			
		}
		
		
		$regex = array( 
			'/^(.+)\s*\(Фильм\s+([\d]+)-[\w]\.?\s+-?\s*"(.+)"\)/ui', //Потаенное судно (Фильм 1-й. "Потаенное судно. 1710-1900 гг.")
			'/^(.+)\s*\(Часть\s+([\d]+)-[\w]\.?\s+-?\s*"(.+)"\)/ui', //Летний дворец и тайные сады последних императоров Китая (Часть 1-я - "Цяньлун и рассвет Поднебесной")
			'/^(.+)\s*\(([\d]+)-[\w]\s+серия\s+-\s+"(.+)"\)/ui',  //Сквозь кротовую нору с Морганом Фрименом (1-я серия - «С чего началась Вселенная?»)
		);
		foreach ($regex as $r){
			if (preg_match($r, $result['title'], $m)){
				$result['title'] = trim($m[1]);
				$result['sub_title'] = trim($m[3]);
				$result['episode'] = (int)$m[2];
				$result['episode']  = Zend_Json::encode($result['episode']);
				$result['category'] = $this->catIdFromTitle('познавательные');
			}
		}
		
		//var_dump($result['title']);
		
		$regex = array(
			'/\s*\(([\d]+)-[\w]+\s+серия\)/ui',
			'/\s*\(([\d]+)-[\w]\s*-\s*([\d]+)\s*-[\w]\s+серии\)/ui',
			'/\s*\(([\d]+)-[\w]\s+и\s+([\d]+)-[\w]\s+серии\)/ui',
			'/\s*Новые\s+серии\s+\(([\d]+)-[\w]+\s+серия\)/ui',
			'/\s\(Серия\s([\d]+)\)$/ui',
			'/\s\(Эпизод ([\d]+)\.[\d]{1}\)$/ui',
		);
		foreach ($regex as $r) {
			if (preg_match($r, $result['title'], $m)) {
			    //var_dump($m);
			    $result['title'] = Xmltv_String::str_ireplace($m[0], '', $result['title']);
				unset($m[0]);
				$result['episode'] = implode(',', $m);
				$result['category'] = $this->catIdFromTitle('сериал');
				//var_dump($result);
				//die(__FILE__.': '.__LINE__);
			}
		}
		
		$regex = array( 
			'/^(.+)\.?\s+\("(.+)"\.?\s+([\d]+)-[\w]\s+серия\s+-\s+"(.+)"\)/ui', //Исторические путешествия Ивана Толстого ("Писательская любовь. Сергей Есенин". 1-я серия - "Дунькин платок")
			'/^(.+)\.?\s*\((.+)\s+([\d]+)-я\s+лекция\)/ui', //Aсademia (Галина Китайгородская. "Уникальность иностранного языка как учебного предмета". 1-я лекция)
		);
		foreach ($regex as $r){
			if (preg_match($r, $result['title'], $m)){
				$result['title'] = trim($m[1]);
				$result['sub_title'] = trim($m[2]).'. '.trim($m[4]);
				$result['episode'] = (int)$m[3];
				$result['category'] = $this->catIdFromTitle('познавательные');
			}
		}
		
		
		$regex = '/^(.+)\s+\(Фильм\s+([\w]+):?\s+"(.+)"\)/ui'; //Предлагаемые обстоятельства (Фильм третий: "Богатый наследник")
		if (preg_match($regex, $result['title'], $m)){
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
		}
		
		$regex = '/^(.+)\s\((Фильм\s+[\d]+)-[\w]\s+-\s+"(.+)",\s+([\d]+)-[\w]\s+и\s+([\d]+)-[\w]\s+серии\)/ui'; //Шериф (Фильм 5-й - "Сто тысяч для сына", 1-я и 2-я серии)
		if (preg_match($regex, $result['title'], $m)){
			$result['title'] = trim($m[1]);
			$result['sub_title'] = trim($m[2]).'. '.trim($m[3]);
			$result['episode'] = (int)trim($m[4]).','.trim($m[5]);
		}
		
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
			'/^('.implode('|', $sports).')\.\s*([\w\s\d"-]+)\.\s*(.*)$/ui',
			'/^('.implode('|', $champs).').*\.?(\s*)(.*)$/ui', 
		);
		foreach ($regex as $r){
			if (preg_match($r, $result['title'], $m)){
				$result['title'] = trim(trim($m[1]).'. '.trim($m[2]), ' .');
				$result['sub_title'] = isset($m[3]) ? trim($m[3]) : '' ;
				$result['category'] = $this->catIdFromTitle('спорт');
			}
		}
		
		$regex = '/^(Мировой Кубок)\.\s+([\w\s\d" -\.]+)$/ui';
		if (preg_match($regex, $result['title'], $m)){
		    //var_dump($result['title']);
			$result['title']     = trim($m[1]);
			$result['sub_title'] = isset($m[2]) ? trim($m[2]) : '' ;
			$result['category'] = $this->catIdFromTitle('спорт');
		}
		
		
		$regex = array(
			'/^((Жеребьевка)\s+([\w\d\s\/]+))\.\s+(.+)$/ui',      				//Жеребьевка 1/8 финала Лиги чемпионов. Прямая трансляция
			'/^("([\w\s]+)"\.\s+([\w\s\d-]+))\.\s+([\w\s\.-]+)$/ui',			//"В дни теннисных каникул". Уимблдон-2012. Сабина Лисицки - Божана Йовановски
			'/^("([\w\s]+)"\.\s+([\w\s\d-]+\.\s+\w+))\.\s+([\w\s\.\/-]+)$/ui',	//"В дни теннисных каникул". Уимблдон-2012. Финал. Д. Маррей/Фр. Нильсен - Р. Линдстедт/Х. Текау
			'/^(.+(Чемпионат России\s[\w\s]+))\sсезона\s([\d]{4}-[\d]{4})\sгода\.?(.+)/ui',	//СОГАЗ - Чемпионат России по футболу сезона 2012-2013 года. "Терек" - "Динамо"
		);
		foreach ($regex as $r){
			if (preg_match($r, $result['title'], $m)){
			    //var_dump($m);
			    $result['title']     = trim($m[2]).' '.trim($m[3]);
				$result['sub_title'] = isset($m[4]) ? trim($m[4]) : '' ;
				$result['category']  = $this->catIdFromTitle('спорт');
				//var_dump($result);
				//die();
			}
		}
		
		if (Xmltv_String::stristr($result['title'], ' Этап Кубка ')){
		    $e = explode('. ', $result['title']);
		    $result['title'] = trim($e[0]).'. '.trim($e[1]);
		    unset($e[0]); unset($e[1]);
		    $result['sub_title'] = implode(', ', $e);
		    $result['category']  = $this->catIdFromTitle('спорт');
		}
		
		
		
		
		if (preg_match('/^(.+)\s+\((.+)\)$/ui', $result['title'], $m)){
			$result['title']     = trim($m[1]);
			$result['sub_title'] = trim($m[2]);
		}
		
		if (Xmltv_String::stristr($result['title'], 'внимание! ') ||
		Xmltv_String::stristr($result['title'], "канал заканчивает вещание") || 
		Xmltv_String::stristr($result['title'], "Перерыв") || 
		Xmltv_String::stristr($result['title'], "профилактика на канале")){
			$result['title'] = 'Перерыв';
			$result['sub_title'] = '';
		}
		
		if (Xmltv_String::stristr($result['title'], 'Новости, ')){
		    $comma = Xmltv_String::strpos($result['title'], ',');
		    $title = trim( Xmltv_String::substr($result['title'], 0, $comma));
		    $result['sub_title'] = Xmltv_String::ucfirst( trim( Xmltv_String::substr( $result['title'], $comma+1, Xmltv_String::strlen($result['title']))) );
		    $result['title'] = $title;
		    $result['category'] = $this->catIdFromTitle('новости');
		}
		
		//Название с восклицательным знаком
		if (strstr($result['title'], '!')){
			$r = explode('!', $result['title']);
			$result['title']     = trim($r[0]);
			$result['sub_title'] = trim($r[1]);
		}
		
		
		$regex = array(
			'/^(.+),\s+(раунд\s+[\d]+)$/ui', //Чемпионат мира по смешанным единоборствам Mix Fight M1 Сhallenge, раунд 7
			'/^(.+),\s+(Этап\s+[\d]+,\s+\w+)$/ui',  //Мировая серия по мотофристайлу "X-Fighters" 2012 года, Этап 5, Мюнхен
		);
		foreach ($regex as $r){
		    if (preg_match($r, $result['title'], $m)){
		        $result['title']     = trim($m[1]);
		        $result['sub_title'] = Xmltv_String::ucfirst(trim($m[2]));
		    }
		}
		
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
				'/^("[\w\s-]+")(\s)(.+)$/ui', //"Speсtrum Road" (Дж. Брюс, С. Блэкмен, Дж. Медески и В. Рейд) в клубе "Порги и Бесс"
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
				'/^(.+).?(\s)()$/ui', //Мейнард Фергюсон на джазовом фестивале в Монреале
			
				
		);
		foreach ($regex as $r){
			if (preg_match($r, $result['title'], $m)){
			    //var_dump($m);
				$result['title']     = $trim->filter(trim($m[1]).'. '.trim($m[2]), ' .');
				$result['sub_title'] = isset($m[3]) ? str_replace(array('(',')'), '', Xmltv_String::ucfirst(trim($m[3]))) : '' ;
				$result['category'] = $this->catIdFromTitle('классическая музыка');
				//var_dump($result);
				//die(__FILE__.': '.__LINE__);
			}
		}
		
		$regex = array(
			'/^(Симфония №[\d]{1,})\s\(([\w\s]+)\)\s(под управлением\s[\w\s\.-]+)$/ui', //Симфония №7 (Брамс) под управлением Карлоса Клайбера
			'/^(Симфония №[\d]{1,})\s\(([\w\s]+)\)\.?\s(Дирижер:\s[\w\s\.-]+)\.?$/ui', //Симфония №33 (Моцарт). Дирижер: Карлос Клайбер
			'/^(Увертюра .+)(\s)(под управлением\s[\w\s\.-]+)$/ui', //Увертюра "Кориолан" (Бетховен) под управлением Карлоса Клайбера
			'/^(Музыка [\w\s\,"-]+)\.?(\s)(Дирижер:\s[\w\s\.-]+)\.?$/u', //Музыка Дебюсси, Равеля и Бетховена. Дирижер: Эса-Пекка Салонен
		);
		
		foreach ($regex as $r){
			if (preg_match($r, $result['title'], $m)){
				//var_dump($m);
				$result['title']     = $trim->filter(trim($m[2]).', '.trim($m[1]));
				$result['sub_title'] = isset($m[3]) ? Xmltv_String::ucfirst(trim($m[3])) : '' ;
				$result['category'] = $this->catIdFromTitle('классическая музыка');
				//var_dump($result);
				//die(__FILE__.': '.__LINE__);
			}
		}
		
		// Обработка названий сериалов.
		// проход 2, поиск в подзаголовке
		$regex = array( 
			'/([\d]+)-?[\w]?\s+серия/ui', //10 серия
			'/серия\s+([\d]+)/ui', //Серия 7
		);
		foreach ($regex as $r){
			if (preg_match($r, $result['sub_title'], $m)){
				$result['sub_title'] = '';
				$result['episode'] = (int)$m[1];
			}
		}
		
		$result['title'] = str_replace('...', '…', $result['title']);
		//$result['title'] = str_replace('"', '', $result['title']);
		$f = new Zend_Filter_StringTrim(array('charlist'=>'",!?:;- \/\''));
		$result['title'] = $f->filter($result['title']);
		$result['sub_title'] = trim($result['sub_title']);
		
		if (preg_match('/^([\p{Cyrillic}\s]+)\s+and\s+([\p{Cyrillic}\s]+)$/ui', $result['title'], $m)){
			$result['title'] = trim($m[1]);
			$result['sub_title'] = Xmltv_String::ucfirst(trim($m[2]));
				
		}
		
		
		if (Xmltv_String::stristr($result['title'], '+')){
		    $result['title'] = Xmltv_String::str_ireplace('+', 'плюс', $result['title']);
		}
		
		//var_dump($result);
		//die(__FILE__.': '.__LINE__);
		
		return $result;
	}
	
	/**
	 * Get category ID from it's title
	 * @param unknown_type $title
	 * @return number
	 */
	public function catIdFromTitle($title=null){
		
	    if ($title){
	        foreach ($this->catList as $c) {
	        	if(Xmltv_String::strtolower($c->title) == Xmltv_String::strtolower(trim($title))) {
	        		return (int)$c->id;
	        	}
	        }
	    }
	    
	}


	/**
	 * 
	 * Detect program category
	 * @param  array  $info
	 * @param  string $xml_title
	 * @return int
	 */
	public function getProgramCategory ($cat_title=null, $prog_desc=null) {
		
		if ($cat_title) {
			$exists = false;
			foreach ($this->catList as $c) {
				if(Xmltv_String::strtolower($c->title)==Xmltv_String::strtolower($cat_title)) {
					$catId  = (int)$c->id;
					$exists = true;
				}
			}
			// If not found
			if (!$exists){
				try {
					$catId = $this->programsCategoriesTable->insert( array('title'=>$cat_title) );
				} catch (Zend_Db_Table_Exception $e) {
					throw new Zend_Exception($e->getMessage(), $e->getCode());
				}
			}
		} elseif ($prog_desc) {
			$categoriesRegex = array(
				'/-?\s*сериал\.?/'
			);
			foreach ($categoriesRegex as $regex){
				if (preg_match($regex, $prog_desc, $m)){
					foreach ($this->catList as $c){
						if (Xmltv_String::strtolower($c->title)=='сериал'){
							$catId = (int)$c->id;
						}
					}
				}
			}
		} else {
			return null;
		}
		
		return $catId;
	
	}

	/**
	 * @deprecated
	 */
	/*
	public function savePremiere ($info=array()) {
		
		if( empty( $info ) ) 
		return array();
		
		$programs = new Admin_Model_DbTable_Programs();
		$props    = new Admin_Model_DbTable_ProgramsProps();
		$trim     = new Zend_Filter_StringTrim();
		
		$info['new']=1;
		
		
		$new = $props->createRow();
		$new->hash=$info['hash'];
		$new->premiere=1;
		$new->premiere_date=$info['start'];
		
		//$info['title'] = Xmltv_String::ucfirst( $trim->filter( preg_replace('/премьера[ \.]?/iu', '', $info['title']) ) );
		
		try {
			$new->save();
		} catch (Exception $e){
			if ($e->getCode() == 1062) {
				try {
					$props->update($new->toArray(), "`hash`='" . $info['hash'] . "'");
				} catch (Exception $ee) {
					echo __METHOD__.' error#: '.$ee->getCode().': '. $ee->getMessage();
					//die(__FILE__.': '.__LINE__);
				}
			} else {
				echo __METHOD__.' error#: '.$e->getCode().': '. $e->getMessage();
				//die(__FILE__.': '.__LINE__);
			}
		}
		
		try {
			$programs->update( $info, "`hash` = '".$info['hash']."'" );
		} catch (Exception $e) {
			echo __METHOD__.' error#: '.$e->getCode().': '. $e->getMessage();
			//die(__FILE__.': '.__LINE__);
		}
		
		return $info;

	}
	*/


	/**
	 * 
	 * @param unknown_type $input
	 * @return multitype:multitype:
	 * @deprecated
	 */
	/*
	public function getCredits ($input=null) {
		
		if($input ) {
			
			$result['actors']=array();
			$result['directors']=array();
			
			$tolower=new Zend_Filter_StringToLower( $this->_tolower_options );
			
			if( strstr( $tolower->filter( $input ), 'в ролях' ) ) {
				$d=explode( 'В ролях:', $input );
				//var_dump($d);
				$actors=$d[1];
				$p=explode( 'Режиссер', $actors );
				$result['actors']=explode( ', ', trim( $p[0], '.…: ' ) );
				$result['directors']=explode( ', ', trim( $p[1], '.…: ' ) );
			
			} elseif( strstr( $tolower->filter( $input ), 'режиссер' ) ) {
				
				$p=explode( 'Режиссер', $input );
				$result['actors']=array();
				$result['directors']=explode( ', ', trim( $p[1], '.…: ' ) );
			
			} else {
				return $result;
			}
			
			return $result;
		}
	
	}
	*/

	/**
	 * Parse XML description
	 * 
	 * @param string $desc
	 * @param string $hash
	 * @return void
	 */
	public function parseDescription ($desc=null) {
		
		if ($desc){
		    
		    $result = array();
		    
		    //var_dump($desc);
		    
		    //Проверка возрастного рейтинга в названии
		    if ($r = $this->extractAgeRating($desc)) {
		    	$result['rating'] = $r;
		    	$desc = $this->stripAgeRating($desc);
		    }
		    
		    //var_dump($result);
		    //die(__FILE__.': '.__LINE__);
		    
		    $regex = '/^([\d]+)-я\sсерия$/ui'; //6-я серия (12+)
		    if (preg_match($regex, $result['title'], $m)){
		    	$result['episode'] = (int)$m[1];
		    	$result['text'] = '';
		    	return $result;
		    }
		    
		    
		    //var_dump($result);
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
		    		
		    	    //var_dump($m);
		    	    
		    	    $f = array('фильм'=>'художественный фильм');
		    	    $c = trim($m[1]);
		    	    if (array_key_exists($c, $f)) {
		    	        $result['category']  = $this->catIdFromTitle( $f[$c] );
		    	    } else {
		    	        $result['category']  = $this->catIdFromTitle(trim($m[1]));
		    	    }
		    	    
		    		
		    		$directors = explode(',', $m[2]);
		    		foreach ($directors as $p){
		    			if (($id=$this->personId(trim($p), 'director'))!==null)
		    				$result['directors'][] = $id;
		    		}
		    		
		    		$actors = explode(', ', Xmltv_String::str_ireplace(' и ', ', ', trim($m[3])));
		    		foreach ($actors as $p){
		    			if (($id=$this->personId(trim($p), 'actor'))!==null)
		    				$result['actors'][] = $id;
		    		}
		    		
		    		$country = strtolower(trim($m[4]));
		    		switch ($country){
		    			case 'США':
		    				$result['country']='us';
		    				break;
		    			case 'США-Индия':
		    				$result['country']='us-in';
		    				break;
		    			case 'Австралия-Великобритания':
		    				$result['country']='au-gb';
		    				break;
		    			case 'США-Канада':
		    				$result['country']='us-cn';
		    				break;
		    			case 'США-Австралия-Мексика':
		    				$result['country']='us-au-mx';
		    				break;
		    			case 'Франция-Германия':
		    				$result['country']='fr-de';
		    				break;
		    			case 'Великобритания-Испания':
		    				$result['country']='gb-es';
		    				break;
		    			case 'Индия':
		    				$result['country']='in';
		    				break;
		    			case 'Россия':
		    				$result['country']='ru';
		    				break;
		    			case 'Великобритания-Франция-Германия':
		    				$result['country']='gb-fr-de';
		    				break;
		    			case 'США-Великобритания-Франция':
		    				$result['country']='us-gb-fr';
		    				break;
		    			default:
		    				beak;
		    		}
		    		
		    		$result['year'] = (int)trim($m[5]);
		    		
		    		$desc = preg_replace('/^.+$/ui', '', $desc);
		    		
		    		//var_dump($result);
		    		//var_dump($desc);
		    		//die(__FILE__.': '.__LINE__);
		    	}
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
		        	 
		        	//var_dump($m);
		        	 
		        	$result['category']  = $this->catIdFromTitle(trim($m[2]));
		        	 
		        	$actors = explode(', ', trim($m[3]));
		        	foreach ($actors as $p){
		        		if (($id=$this->personId(trim($p), 'actor'))!==null)
		        			$result['actors'][] = $id;
		        	}
		        	
		        	$directors = explode(', ', trim($m[4]));
		        	foreach ($directors as $p){
		        		if (($id=$this->personId(trim($p), 'director'))!==null)
		        			$result['directors'][] = $id;
		        	}
		        	 
		        	$country = strtolower(trim($m[5]));
		        	switch ($country){
		        		case 'США':
		        			$result['country']='us';
		        			break;
		        		case 'США-Новая Зеландия':
		        			$result['country']='us-nz';
		        			break;
		        		case 'Великобритания-США':
		        			$result['country']='gb-us';
		        			break;
		        	}
		        	 
		        	$result['year']  = (int)trim($m[6]);
		        	$result['title'] = Xmltv_String::ucfirst( Xmltv_String::strtolower(trim($m[1])));
		        	$desc = preg_replace('/^.+$/ui', '', $desc);
		        	
		        	//var_dump($result);
		        	//var_dump($desc);
		        	//die(__FILE__.': '.__LINE__);
		        		  
		        }
		    }
		    
		    
		    
		    $regex = array(
		    	//Детективный сериал. Великобритания, 2001 - 2004гг. Режиссер Мартин Хатчингс. В ролях Тревор Ив, Сью Джонстон, Холи Эйрд, Клэр Гуз. Самые страшные слова для любого следователя “ "убийство раскрыть нельзя". Однако новые технологии позволяют решать самые непростые задачи, в том числе раскрывать преступления, совершенные много лет назад. Именно с этой целью был создан специальный "убойный" отдел под руководством старшего детектива Бойда. Бойду и его команде предстоит расследовать загадочную смерть фотожурналиста в автокатастрофе, двойное убийство, за которое женщина провела 25 лет в тюрьме, убийство полицейского и многие другие страшные преступления.
		    	'/^(Детективный сериал)\.(\s)(\w+),\s+[\d]{4}\s+-\s+([\d]{4})\w+\.\sРежиссер:?\s+([\w\s-]+)\.\sВ ролях\s([-\w+\s\w+,^A-Z]+,?)\.\s(.+)/ui',
		    	//Триллер, детектив. Studio Canal, Франция, 2005г. Режиссер: Жером Саль.Интерпол преследует неуловимого мошенника Энтони Циммера, специализирующегося на отмывании денег для русской мафии. Недавно Циммер сделал пластическую операцию, которая целиком изменила его внешность. Теперь единственная ниточка - любовница Энтони, обворожительная красотка Кьяра. Но Кьяра это тоже понимает, и заводит интрижку напоказ с ничего не подозревающим простаком Франсуа. Он того же роста и того же возраста, что и Циммер. И для спецслужб, и для мафии это достаточное основание, чтобы попытаться убить Франсуа.
		    	'/^(Триллер|детектив).+\.\s([\w\s]+),\s(\w+),\s([\d]{4})г\.\sРежиссер:\s([\w\s-]+)\.\sВ ролях:\s([-\w+\s\w+,^A-Z]+,?)\.\s(.+)$/ui',
			    //Комедия. Великобритания, 2004г. Режиссер: Джон МакКэй. В ролях: Сэм Рокуэлл, Кассандра Белл, Том Уилкинсон. Джим Крокер, дебошир и постоянный персонаж скандальной хроники лондонских газет, впервые в жизни влюбляется. Но дело в том, что избранница - американка и заочно терпеть Крокера не может. Джимми прикидывается сыном своего дворецкого, садится на атлантический лайнер и отправляется за девушкой своей мечты. Положение сильно осложняется тем, что и в Нью - Йорке немало людей, которые близко знакомы с Крокером.
		    	'/^(Комедия)\.\(s+)(\w+),\s+([\d]+).+\s+Режиссер:\s+(.+)\.\s+В ролях:\s+([-\w+\s\w+,^A-Z]+,?)\.(.+)$/ui',
			    //Приключения. Таллинфильм, 1969г. Режиссер: Григорий Кроманов. В ролях: Александр Голобородько, Ингрид Андринь, Эльзе Радзиня, Ролан Быков, Ээве Киви, Улдис Ваздикс ХVI век. Лифляндия. В одном из аристократических домов умирает старый рыцарь Рисбитер. Он завещал сыну шкатулку с семейной реликвией. Духовные пастыри ближайшего монастыря хотят завладеть шкатулкой, чтобы приумножить славу обители. Молодой наследник согласен уступить церкви реликвию, но с одним условием: ему должны отдать в жены прекрасную Агнес, племянницу аббатисы женского монастыря. А сердце юной красавицы принадлежит свободолюбивому рыцарю в Габриэлю, другу всех обманутых и беззащитных.... Фильм снят по роману эстонского писателя Э. Борнхeэ "Последний день монастыря святой Бригитты".
		    	'/^([\w\s,]+)\.(\s+)([\w\s-?\.?]+),\s+([\d]{4})г\.?\s+Режиссер:\s+(\w+\s\w+)\.\s+В ролях:\s+([-\w+\s\w+,^A-Z]+,?)\.?\s+(.+)/ui',
		    );
		    foreach ($regex as $r){
		        if (preg_match($r, $desc, $m)){
		            //var_dump($m);
		            
		            $result['category'] = $this->catIdFromTitle(trim($m[1]));
		            $result['studio']   = trim($m[2]);
		            
		            switch (trim($m[3])){
		            	case 'Франция';
		            		$result['country'] = 'fr';
		            		break;
		            	case 'Великобритания';
		            		$result['country'] = 'gb';
		            		break;
		            }
		            
		            $result['year'] = (int)trim($m[4]);
		            
		            $directors = explode(',', $m[5]);
		            foreach ($directors as $p){
		            	if (($id=$this->personId(trim($p), 'director'))!==null)
		            		$result['directors'][] = $id;
		            }
		            
		            $actors = explode(', ', $m[6]);
		            foreach ($actors as $p){
		            	if (($id=$this->personId(trim($p), 'actor'))!==null)
		            		$result['actors'][] = $id;
		            }
		            
		            $desc = trim($m[7]);
		            
		            //var_dump($result);
		            //var_dump($desc);
		            //die(__FILE__.': '.__LINE__);
		        }
		    }
		    
		    
			//ЗАТЕРЯННЫЙ МИР - фантастико-приключенческий фильм Стивена Спилберга. В ролях: Джефф Голдблюм, Джулианна Мур и Ричард Аттенборо. США, 1997г. 12+
		    if (preg_match('/^([\w\s,]+)\s-\s(фантастико-приключенческий фильм)\s([\w\s-]+)\.\sВ ролях:\s([-\w+\s\w+,^A-Z]+ и [-\w+\s\w+,^A-Z]+)\.\s([\w\s-]+),\s([\d]{4}).*$/ui', $desc, $m)){
		        //var_dump($m);
		        
		        $result['category']  = $this->catIdFromTitle(trim($m[2]));
		        
		        $directors = explode(', ', trim($m[3]));
		        foreach ($directors as $p){
		        	if (($id=$this->personId(trim($p), 'director'))!==null)
		        		$result['directors'][] = $id;
		        }
		        
		        $actors = explode(', ', trim($m[4]));
		        foreach ($actors as $p){
		        	if (($id=$this->personId(trim($p), 'actor'))!==null)
		        		$result['actors'][] = $id;
		        }
		        
		        $country = strtolower(trim($m[5]));
		        switch ($country){
		        	case 'США':
		        		$result['country']='us';
		        		break;
		        }
		        
		        $result['year']  = (int)trim($m[6]);
		        $result['title'] = Xmltv_String::ucfirst( Xmltv_String::strtolower(trim($m[1])));
		        $desc = preg_replace('/^.+$/ui', '', $desc);
		        
		        //var_dump($result);
		        //var_dump($desc);
		        //die(__FILE__.': '.__LINE__);
		    } elseif (preg_match('/^([\w\s,]+)\.\s+(.+)\,\s+(\w+),\s+([\d]{4})г\.\s+Режиссер:\s+([-\w+\s\w+,^A-Z]+)\.\sСценарий:\s+([-\w+\s\w+,^A-Z]+)\.\s+В ролях:\s+([-\w+\s\w+,^A-Z]+,?)\.\s(.+)/ui', $desc, $m)){
		        //Фантастика, приключения, мелодрама. Warner Bros. - DreamWorks SKG, США, 2001г. Режиссер: Стивен Спилберг. Сценарий: Стивен Спилберг. В ролях: Стивен Спилберг, Бонни Кертис, Хэйли Джоэл Осмент, Джуд Ло, Фрэнсис О"Коннор, Брендан Глисон, Сэм Робардс, Уильям Херт, Джейк Томас, Кен Леунг. Середина 21 века. Из - за глобального потепления климат на планете становится непредсказуемым. Люди создают новое поколение роботов, способное помочь им в борьбе за выживание. И, хотя природные ресурсы скудеют, высокие технологии развиваются со стремительной скоростью. Киборги живут бок о бок с людьми и выручают их во всех сферах деятельности. И тут наука преподносит человечеству очередной сюрприз - создается чудо - робот совершенно иного порядка: с разумом, нервной системой, способный испытывать все человеческие эмоции и главное - любить. Это настоящий подарок для супружеских пар, не имеющих детей. Творение нарекают Дэвидом. Кибернетический мальчик, по виду ничем не отличающийся от живого ребенка, попадает в семью ученых Генри и Моники, участвовавших в работе над проектом, и становится их сыном. Но готовы ли его новые родители ко всем последствиям такого рискованного эксперимента?
		    	//var_dump($m);
		        
		        $genres = explode(', ', $m[1]);
		    	foreach ($genres as $g){
		    	    $result['genres'] = Xmltv_String::ucfirst(trim($g));
		    	}
		    	
		        $result['producer'] = trim($m[2]);
		        
		        $country = strtolower(trim($m[3]));
		        switch ($country){
		        	case 'США':
		        	    $result['country']='us';
		        		break;
		        	default:
		        	    beak;
		        }
		        
		        $result['year'] = (int)trim($m[4]);
		        
		        $directors = explode(',', $m[5]);
		    	foreach ($directors as $p){
		    	    if (($id=$this->personId(trim($p), 'director'))!==null)
		        		$result['directors'][] = $id;
		        }
		        
		        $result['writer'] = trim($m[6]);
		        
		        $actors = explode(', ', $m[7]);
		   		foreach ($actors as $p){
		   		    if (($id=$this->personId(trim($p), 'actor'))!==null)
		        		$result['actors'][] = $id;
		        }
		        
		        $result['desc'] = $desc = trim($m[8]);
		    	
		        //var_dump($result);
		    	//die(__FILE__.': '.__LINE__);
		    	
		    } elseif (preg_match('/^(.+)\.?\.?(\.|!|\?)\s+([-\w+\s\w+,^A-Z]+,?)\.?$/ui', $desc, $m)){
		        //var_dump($m);
		        $actors = explode(',', trim($m[3]));
		        //var_dump($actors);
		        foreach ($actors as $p){
		            if (preg_match($this->_nameRegex, trim($p), $mm)){
		                if (($id = $this->personId($mm[0], 'actor'))!==null)
		            		$result['actors'][] = $id;
		            }
		        }
		        //var_dump($result);
		        //die(__FILE__.': '.__LINE__);
		        
		    }
			
		    if (Xmltv_String::stristr($desc, 'В ролях') || Xmltv_String::stristr($desc, 'Звезды кино')){
		        $result['actors']=array();
		        if (preg_match('/^(.*)\s(В\sролях|Звезды\sкино):\s([-\w+\s\w+,^A-Z]+,?)\.?\s*(.*)$/ui', $desc, $m)){
		            //var_dump($m);
			        $result['text'] = trim($m[1]);
			        $actors = explode(', ', $m[3]);
			        foreach ($actors as $p){
			            if (($id = $this->personId(trim($p), 'actor'))!==null)
							$result['actors'][] = $id;
			        }
			        $desc = @trim($m[1]).@trim($m[4]);
		        }
		        //var_dump($result);
		        //var_dump($desc);
		        //die(__FILE__.': '.__LINE__);
		    }
		    
		    if (Xmltv_String::stristr($desc, 'Детективный сериал') || 
		    Xmltv_String::stristr($desc, 'сериала "Детективы"')){
		        $result['category'] = $this->catIdFromTitle('детективный сериал');
		    } elseif(Xmltv_String::stristr($desc, 'Информационная программа')){
		        $result['category'] = $this->catIdFromTitle('информационные');
		    } elseif (Xmltv_String::stristr($desc, 'Новости спорта')){
		        $result['category'] = $this->catIdFromTitle('спортивные новости');
		    }
		    
		    if (preg_match($this->_ageRatingRegex, $desc, $m)) {
		        $result['rating'] = (int)trim($m[1]);
		        $desc = str_replace($m[0], '', $desc);
		    }
		    
		    $trim = new Zend_Filter_StringTrim(array('charlist'=>' -,'));
		    $result['text'] = $trim->filter( Xmltv_String::str_ireplace('...', '…', $desc) );
		    
		    //var_dump($result);
		    //die(__FILE__.': '.__LINE__);
		    
		    return $result;
	    }
	}
	
	/**
	 * Check if director exists in database
	 * and save if new. Return director ID
	 * @param  string $name
	 * @throws Zend_Exception
	 * @return int
	 */
	public function personId($name=null, $position='actor'){
		
	    if ($name){
	        //var_dump($name);
	        //var_dump(preg_match($this->_nameRegex, $name, $m));
	        if (preg_match($this->_nameRegex, $name, $m)){
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
	}
	
	/**
	 * Save description
	 * 
	 * @param  array $desc
	 * @throws Exception
	 * @return boolean
	 */
	public function saveDescription($data=array()){
		
		if( !empty($data) && is_array($data) ) {
			try {
				$this->programsDescTable->insert( $data );
			} catch (Exception $e) {
				if ($e->getCode()==1062) {
				    try {
				        $this->programsDescTable->update($data, "hash = '".$data['hash']."'");
				    } catch (Zend_Db_Table_Exception $e) {}
				    return true;
				} else {
					throw new Zend_Exception($e->getMessage(), $e->getCode(), $e);
				}
			}
		} else {
		    throw new Exception(self::ERR_MISSING_PARAMS, 500);
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
		$cache   = new Xmltv_Cache();
		
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


	public function getActorsNames () {
	
	}


	public function getHash ($channel_id=null, $start=null, $end=null) {
		
		if(  !$channel_id ||  !$start ||  !$end ) return;
		
		return md5( $channel_id . $start . $end );
	
	}


	private function _processNewsTitle ($info=array()) {
		
		if( empty( $info ) ) 
		throw new Exception("Пропущен один или все параметры для ".__METHOD__, 500);
		
		$tolower=new Zend_Filter_StringToLower( $this->_tolower_options );
		
		if( $tolower->filter( $info['title'] ) == $tolower->filter( 'Евроньюс' ) ) {
			$info=$this->setProgramCategory( $info, 'Новости' );
		} elseif( preg_match( '/^новости\.?.*$/iu', $info['title'] ) ) {
			
			if( $tolower->filter( $info['title'] ) == $tolower->filter( 'Новости культуры' ) ) {} elseif( $tolower->filter( 
			$info['title'] ) == $tolower->filter( 'Новости с субтитрами' ) ) {
				$info['title']='Новости';
				$info['sub_title']='C субтитрами';
			} elseif( $tolower->filter( $info['title'] ) == $tolower->filter( 'Вечерние новости с субтитрами' ) ) {
				$info['title']='Вечерние новости';
				$info['sub_title']='C субтитрами';
			} elseif( $tolower->filter( $info['title'] ) == $tolower->filter( 'новости' ) ) {
				$info['title']='Новости';
				$info['sub_title']='';
			}
			$info=$this->setProgramCategory( $info, 'Новости' );
		
		} elseif( preg_match( '/^вести\.?.*$/iu', $info['title'] ) || preg_match( '/^местное время\.?.*$/iu', 
		$info['title'] ) || preg_match( '/^события\.?.*$/iu', $info['title'] ) || preg_match( '/ news /iu', 
		$info['title'] ) ) {
			$info=$this->setProgramCategory( $info, 'Новости' );
		}
		
		return $info;
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
		
		$date['year']      = substr($input, 0, 4);
		$date['month']     = substr($input, 4,2);
		$date['day']       = substr($input, 6,2);
		$date['hours']     = substr($input, 8,2);
		$date['minutes']   = substr($input, 10,2);
		$date['seconds']   = substr($input, 12,2);
		$date['gmt_diff']  = substr($input, 16,4);
		return $date['year'].'-'.$date['month'].'-'.$date['day'].' '.$date['hours'].':'.$date['minutes'].':'.$date['seconds'].' '.$date['gmt_diff'];
		
	}
	
	
	public function saveActor($data=array()){
		
		if (empty($data))
			throw new Exception("Пропущен один или все параметры для ".__METHOD__, 500);
		
		$table = new Admin_Model_DbTable_Actors();
		//$cache = new Xmltv_Cache(array('lifetime', 7200));
		//$hash = $cache->getHash(__METHOD__.'_'.$data['f_name'].'_'.$data['s_name']);
		//if (!$actor_found = $cache->load($hash)) {
			/*$actor_found = @$table->fetchRow(array(
				"`f_name`='".$data['f_name']."'",
				"`s_name`='".$data['s_name']."'"
			))->toArray();*/
			//$cache->save($actor_found, $hash);
		//}
		
		if (!$actor_found = @$table->fetchRow(array( "`f_name`='".$data['f_name']."'", "`s_name`='".$data['s_name']."'" ))->toArray()) {
			try {
				$actor_id = $table->insert($data);
			} catch(Exception $e) {
				echo __METHOD__.' error#'.$e->getCode().': '.$e->getMessage();
				die(__FILE__.': '.__LINE__);
			}
		} else {
			$actor_id = $actor_found['id'];
		}
		return $actor_id;
	}
	
	/*
	private function _addActorToProps($actor_id=null, $info=array()){
		
		if (empty($info) || !$actor_id)
		throw new Exception("Пропущен один или все параметры для ".__METHOD__, 500);
		
		$props_table = new Admin_Model_DbTable_ProgramsProps();
		$serializer  = new Zend_Serializer_Adapter_Json();
		
		$hash = $this->getHash($info['ch_id'], $info['start'], $info['end']);
		
		if (!$props = $props_table->find($hash)->current()){
			$props = $props_table->createRow(array(), true);
			$props->hash = $hash;
			try {
				$actors_info   = $serializer->unserialize($props->actors);
				$actors_info[] = $actor_id;
			} catch (Exception $e) {
				$actors_info = array();
				$actors_info[] = $actor_id;
			}
			$props->actors = $serializer->serialize($actors_info);
		}
		
		$props = $props->toArray();
		//var_dump($props);
		
		
		try {
			$props_table->insert($props, "`hash`='$hash'");
		} catch (Exception $e) {
			if ($e->getCode()==1062) {
				$props_table->update($props, "`hash`='$hash'");
			} else {
				echo __METHOD__.' error#'.$e->getCode().': '.$e->getMessage();
				die(__FILE__.': '.__LINE__);
			}
		}
		
		return $props;
		
	}
	*/
	
	/**
	 * Save new program to DB
	 * 
	 * @param  array $info
	 * @throws Exception
	 * @return unknown|Ambigous <mixed, multitype:>
	 */
	public function saveProgram($data=array()){
		
		if (!empty($data) && is_array($data)) {
		    try {
				$hash = $this->programsTable->insert($data);
			} catch (Exception $e) {
			    //var_dump($e->getCode());
			    //die(__FILE__.': '.__LINE__);
				if ($e->getCode()==1062) {
				    try {
				        $this->programsTable->update($data, "hash = '".$data['hash']."'");
				    } catch (Zend_Db_Table_Exception $e) {}
				    return $data['hash'];
				} else {
					throw new Zend_Exception($e->getMessage(), $e->getCode(), $e);
				}
			}
			
			if (!$hash)
				throw new Exception(self::ERR_CANNOT_SAVE);
			
			return $hash;
		} else {
		    throw new Zend_Exception(self::ERR_MISSING_PARAMS, 500);
		}
	}
	
	/**
	 * 
	 * @param  array $info
	 * @throws Zend_Exception
	 * @return string
	 */
	public function saveProps($data=array()){
		
	    if (!empty($data) && is_array($data)) {
	    	try {
	    		$hash = $this->programsPropsTable->insert($data);
	    	} catch (Exception $e) {
	    		if ($e->getCode()==1062) {
	    		    try {
	    		        $this->programsPropsTable->update($data, "hash = '".$data['hash']."'");
	    		    } catch (Zend_Db_Table_Exception $e) {}
	    		    return $data['hash'];
	    		} else {
	    			throw new Zend_Exception($e->getMessage(), $e->getCode());
	    		}
	    	}
	    		
	    	if (!$hash)
	    		throw new Zend_Exception(self::ERR_CANNOT_SAVE, 500);
	    		
	    	return $hash;
	    } else {
	    	throw new Zend_Exception(self::ERR_MISSING_PARAMS, 500);
	    }
	    
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
	
	/*
	private function _splitTitle($info=null){
		
		if (empty($info) || !is_array($info))
		throw new Exception("Пропущен один или все параметры для ".__METHOD__, 500);
		
		$trim = new Zend_Filter_StringTrim( $this->_trim_options );
		$logger = new Zend_Log( new Zend_Log_Writer_Stream( ROOT_PATH . '/log/splitTitle.log' ) );
		
		if( preg_match( '/^(И другие)\.\.\.(.+)$/iu', $info['title'], $m ) ) {
			
			$info['title']=Xmltv_String::ucfirst( $trim->filter( $m[2] ) ) . ' ' . Xmltv_String::strtolower( 
			$trim->filter( $m[1] ) );
			
		} elseif ( preg_match( '/^(\.\.\.).*(.+).*(\.\.\.)$/iu', $info['title'], $m ) ) {
			
			if (Xmltv_Config::getDebug()) {
				$msg = print_r($info, true);
				$logger->debug( __LINE__.':'. $msg );
			}
			
			$info['title']=Xmltv_String::ucfirst( $trim->filter( $m[2] ) );
			
		} elseif (preg_match('/(.+) ?\((.+)\)/iu', trim($info['title']), $m)) {
			
			if (Xmltv_Config::getDebug()) {
				$msg = print_r($info, true);
				$logger->debug( __LINE__.':'. $msg );
			}
			
			if (empty($m[1]))
			throw new Exception("Ошибка ".__METHOD__, 500);
			else {
				$info['title']     = $trim->filter($m[1]);
				$info['sub_title'] = $trim->filter($m[2]);
			}
			
		} elseif (preg_match('/^(.+)\.\.\.$/iu', trim($info['title']), $m)) {
			
			if (Xmltv_Config::getDebug()) {
				$msg = print_r($info, true);
				$logger->debug( __LINE__.':'. $msg );
			}
			
			if (empty($m[1]))
			throw new Exception("Ошибка ".__METHOD__, 500);
			else {
				$parts = explode( '.', $m[1]);
				if ( count($parts)>2 ) {
					$last = count($parts)-1;
					do {
						if (Xmltv_String::strlen($parts[$last-1])) {
							$info['sub_title'].= $trim->filter( $parts[$last] );
							unset($parts[$last]);
						}
					} while(count($parts)>2);
				}
				$info['title']=implode('.', $parts).'…';
			}
			
		} elseif (preg_match('/^(.+): (.+)$/', $info['title'], $m)) {
			
			if (empty($m[1]))
			throw new Exception("Ошибка ".__METHOD__, 500);
			else {
				$info['title'] = $trim->filter($m[1]);
				$info['sub_title'] = $trim->filter($m[2]);
				if (Xmltv_Config::getDebug()) {
					$msg = print_r($info, true);
					$logger->debug( __LINE__.':'. $msg );
				}
			}
			
		}
		
		$trimmed = trim($info['title'], ' -');
		if (empty($trimmed)) {
			$message = __METHOD__.": Не могу разделить название программы: ".print_r(func_get_args(), true).' '.print_r($info, true);
			//$this->_logger->log(__METHOD__.': '.$message, Zend_Log::ERR);
			throw new Zend_Exception($message);
		}
		
		return $info;
	}
	*/
	
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
	/*
	public function getProgramTitleFromXml(SimpleXMLElement $xml){
		return $this->_makeAlias( (string)$xml->title );
	}
	*/
	
	public function deletePrograms(Zend_Date $start, Zend_Date $end, $linked=false){
		
		if (!$linked) {
			try {
				$this->programsTable->delete(array(
					"`start` >= '".$start->toString('yyyy-MM-dd')." 00:00:00'",
					"`start` < '".$end->toString('yyyy-MM-dd')." 23:59:59'"
				));
				
			} catch (Exception $e) {
				echo $e->getMessage();
				die(__FILE__.': '.__LINE__);
			}
		} else {
			try {
				$this->programsTable->deleteProgramsWithInfo($start, $end);
			} catch (Exception $e) {
				echo $e->getMessage();
				die(__FILE__.': '.__LINE__);
			}
		}
		
	}
	
	/**
	 * @param string $title
	 * @return string
	 * @deprecated
	 */
	/*
	private function _cleanProgramTitle ($title=null) {
		
		if($title){
			$trim    = new Zend_Filter_StringTrim($this->_trim_options);
			$result  = $trim->filter($title);
			$replace = new Zend_Filter_Word_SeparatorToSeparator( '"', '' );
			$result  = $replace->filter($result);
			$replace = new Zend_Filter_Word_SeparatorToSeparator( '  ', ' ' );
			$result  = $replace->filter($result);
			$result  = preg_replace('/(.*)[теле]сериал(.*)/', '\1 \2', $result);
			return $result;
		}
	}
	*/
	
	/**
	 * Generate alias
	 * @param  string $input
	 * @return string|null
	 */
	public function makeAlias($input=null){
		
		if($input) {
		    $result = $input;
		    $result = str_replace(array('"', '.', ',', '...', '…', '!', '?', ':', ';', '-'), ' ', $result );
		    $result = Xmltv_String::str_ireplace( 'ё', 'е', $result );
		    $result = Xmltv_String::str_ireplace( 'Ё', 'Е', $result );
		    $result = Xmltv_String::str_ireplace( '+', '-плюс-', $result );
		    $result = preg_replace('/[^0-9\p{Cyrillic}\p{Latin}]+/ui', ' ', $result);
		    $f = new Zend_Filter_Word_SeparatorToSeparator('  ', ' ');
		    $result = $f->filter($result);
		    $f = new Zend_Filter_StringTrim(array('charlist'=>'".,…!?:;- \/\''));
		    $result = $f->filter($result);
		    $f = new Zend_Filter_Word_SeparatorToDash(' ');
		    $result = Xmltv_String::strtolower($f->filter( $result ));
		    return Xmltv_String::strtolower( $result );
		    
		}
		
	}
	
}

