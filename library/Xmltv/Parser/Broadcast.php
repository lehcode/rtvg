<?php
/**
 * 
 * Generic broadcast parser
 * 
 * @author     Antony Repin <egeshisolutions@gmail.com>
 * @subpackage backend
 * @version    $Id: Programs.php,v 1.31 2013/04/11 05:19:16 developer Exp $
 */
class Xmltv_Parser_Broadcast extends Xmltv_Parser_Curl 
{
    
    public static $categoriesMap = array(
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
    
    /**
     * Process program title and detect properties
     *
     * @param  string $string //Program title
     * @return array  // broadcast data
     */
    public function parseTitle ($title=null) {
    
    	if(!$title) {
    		return;
    	}
    
    	$result['title'] = trim($title);

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
     * Detect premiere in broadcast title
     *
     * @param  array $data
     * @return array
     */
    protected function detectPremiere($data)
    {
        $result = $data;
    	$result['premiere'] = 0;
    	 
    	if (APPLICATION_ENV=='development'){
    		//var_dump($result);
    		//die(__FILE__.': '.__LINE__);
    	}
    	 
    	$regex = array(
    		'/\s+Нов(ые|ая)\s+сери(и|я)/ui',
    		'/\s+премьера/ui',
    		'/^Премьера\.?\s/ui',
    		'/^Премьера сезона\.?\s*/ui',
    	);
    	foreach ($regex as $r) {
    		if ( preg_match($r, $data['title'], $m) ){
    			$result['title']    = Xmltv_String::str_ireplace($m[0], '', $data['title']);
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
     * Detect live program
     *
     * @param  array $data
     * @return array
     */
    protected function detectLive($data){
    
    	$result = $data;
    	$result['live'] = 0;
    	 
    	$search = array(
    			'Прямая трансляция',
    			'Трансляция из',
    			'Доброе утро',
    			'Утро на канале',
    	);
    	foreach ($search as $string){
    		if ( Xmltv_String::stristr( $data['title'], $string )) {
    			$result['live'] = 1;
    		}
    	}
    	 
    	if ( preg_match( '/^(Прямая трансляция:\s)(.+)$/ui', $data['title'], $m)){
    		$result['title'] = Xmltv_String::str_ireplace( $m[1], '', $data['title']);
    		$result['live'] = 1;
    	} elseif( preg_match('/^(.+)\s(Прямая трансляция).*$/ui', $data['title'], $m)){
    		$result['title'] = Xmltv_String::str_ireplace( $m[2], '', $data['title']);
    		$result['live'] = 1;
    	}
    	 
    	if (Xmltv_String::stristr($data['title'], 'Live')){
    		$result['title'] = str_replace( 'Live. ', '', $data['title'] );
    		$result['title'] = str_replace( 'Live ', '', $data['title'] );
    		$result['live']  = 1;
    	}
    	 
    	return $result;
    	 
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
     * @deprecated
     */
    protected function detectAgeRating($data){
    
        $result = $data;
    	//Проверка возрастного рейтинга в названии
    	if (($rating = $this->extractAgeRating($data['title']))>0) {
    		$result['title']  = Xmltv_String::ucfirst($this->stripAgeRating($data['title']));
    		$result['rating'] = $rating;
    	}
    	 
    	if ($result) {
    		return $result;
    	}
    	 
    	$input['rating'] = null;
    	return $input;
    	 
    }
    
    /**
     * Parse string for age rating
     *
     * @param  string $input
     * @return int
     * @deprecated
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
     * Parse series title
     * 
     * @param array $data
     * @param array $map
     */
    protected function parseSeriesTitle(array $data, array $map){
    
        if (APPLICATION_ENV=='development'){
        	//var_dump(var_dump(func_get_args()));
        	//die(__FILE__.': '.__LINE__);
        }
        
        $result = $data;
        preg_match($map['regex'], $data['title'], $m);
        //var_dump($map['regex']);
        //var_dump($m);
        //var_dump($m[(int)$map['matches']['title']]);
        $result['title']       = isset($map['matches']['title']) ? $m[(int)$map['matches']['title']] : $data['title'] ; 
        $result['sub_title']   = isset($map['matches']['sub_title']) ? $m[(int)$map['matches']['sub_title']] : null ;
        
        if (is_array($m[(int)$map['matches']['episode']])){
            $result['episode_num'] = implode(',', $m[(int)$map['matches']['episode']]);
        } else {
            $result['episode_num'] = (int)$m[(int)$map['matches']['episode']];
        }
        
        if (APPLICATION_ENV=='development'){
    		//var_dump($result);
    		//die(__FILE__.': '.__LINE__);
    	}
        
        return $result;
        
        /*
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
    		$result['category']  = $this->catIdFromTitle('Сериал');
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
    	*/
    	 
    }
    
    
    /**
     * Parse sports broadcast title
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
	 * Get category ID from it's title
	 * @param unknown_type $title
	 * @return number
	 */
	protected function catIdFromTitle($title){
		
	    if (APPLICATION_ENV=='development'){
	    	//var_dump($title);
	    	//die(__FILE__.': '.__LINE__);
	    }
	    
	    $catLower = Xmltv_String::strtolower( trim($title));
	    if (array_key_exists($catLower, self::$categoriesMap)){
	    	$title = self::$categoriesMap[$catLower];
	    }
	    
	    $table = new Xmltv_Model_DbTable_ProgramsCategories();
	    $categories = $table->fetchAll()->toArray();
	    foreach ($categories as $cat) {
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
		 
		$table = new Xmltv_Model_DbTable_ProgramsCategories();
		$broadcastCategories = $table->fetchAll();
			
		if ($cat_title) {
	
			$exists   = false;
			$catLower = Xmltv_String::strtolower($cat_title);
			if (array_key_exists($catLower, self::$categoriesMap)){
				$cat_title = self::$categoriesMap[$catLower];
			}
				
			if (APPLICATION_ENV=='development'){
				//var_dump($cat_title);
				//die(__FILE__.': '.__LINE__);
			}
			
			
			foreach ($broadcastCategories as $c) {
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
					foreach ($broadcastCategories as $c){
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
    
}