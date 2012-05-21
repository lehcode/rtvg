<?php

class Xmltv_Parser_Programs_Series extends Xmltv_Parser_ProgramInfoParser
{
	
	private $_cat_id=5; // Сериал
	private $_channels=array();
	private $_classCategories=array(5, 14, 16, 19);
	
	/* (non-PHPdoc)
	 * @see library/Xmltv/Parser/Xmltv_Parser_ProgramInfoParser#process()
	 */
	public function process(Zend_Date $start, Zend_Date $end){
		
		$this->_loadPrograms( $start, $end );
		
		$logfile = ROOT_PATH.'/log/'.__CLASS__.'.log';
		if (is_file($logfile))
		unlink($logfile);
		
		//var_dump(count($this->chunks));
		//die(__FILE__.': '.__LINE__);
		
		$matches = array();
		$cc=0;
		foreach ($this->chunks as $ck=>$part) {
    		foreach ( $part as $pk=>$current ) {
    			
    			$this->_program = new stdClass();
    			
    			if (!$current->hash) {
					var_dump($current);
    				die( "Идентификатор программы не может быть NULL " . __METHOD__ . ': ' . __LINE__ );
    			}
    			
    			$current->start = new Zend_Date($current->start, 'yyyy-MM-dd HH:mm:ss');
    			$current->end   = new Zend_Date($current->end, 'yyyy-MM-dd HH:mm:ss');
    			$this->_program = $current;
				/*
    			if ($current->start->toString('yyyy-MM-dd HH:mm:ss') == '2012-05-14 19:20:00'){
    				try {
    					$this->setTitle();
						$this->setAlias();
						$this->setSubTitle();
    				} catch (Exception $e) {
						printf( '<b>%s: %s</b>', __METHOD__, $e->getMessage() );
						die(__FILE__.': '.__LINE__);
					}
					die(__FILE__.': '.__LINE__);
    			}
    			*/
    			try {
    				
    				$this->setTitle();
					/*
    				if ($current->hash == '6e41e89ccdfe985e111bb4ffb6332920'){
						var_dump($current);
						die(__FILE__.': '.__LINE__);
					}
    				*/
    				$this->setAlias();
					$this->setSubTitle();
					
					if (!empty($this->_program->desc_intro))
					$this->setDescription();
					else {
						$this->_desc->intro='';
						$this->_desc->body='';
					}
					/*
					if ($current->hash == '6e41e89ccdfe985e111bb4ffb6332920'){
						var_dump($this);
						die(__FILE__.': '.__LINE__);
					}
					*/
				} catch (Exception $e) {
					printf( '<b>%s: %s</b>', __METHOD__, $e->getMessage() );
					die(__FILE__.': '.__LINE__);
				}
				
				$this->setProgramProps();
				$matches[] = $this->_program;
				$cc++;
				var_dump($cc);
				var_dump($this->_program->hash);
    			
    		}
    		//var_dump($matches);
			//die(__FILE__.': '.__LINE__);
		}
		
		var_dump(count($matches));
		die(__FILE__.': '.__LINE__);
		
		return $matches;
	}
	
	/* (non-PHPdoc)
	 * @see library/Xmltv/Parser/Xmltv_Parser_ProgramInfoParser#matches()
	 */
	/*
	protected function matches(){
		
		if( !$this->_program->title)
		throw new Exception( __METHOD__ . ": Ошибка! Отсутствует название программы. " . __LINE__, 500 );
		
		if( Xmltv_String::stristr( $this->_program->title, 'сериал' )
		 || Xmltv_String::stristr( $this->_program->title, 'телесериал' ) 
		 || preg_match( '/[0-9]+-я сери[яи]/iu', $this->_program->title ) 
		 || preg_match( '/(сезон-[0-9]+-й\.?)|([0-9]+-й-сезон\.?)/iu', $this->_program->title) ) {
			return true;
		}
	}
	*/
	protected function setTitle(){
		
		$r = $this->_program->title;
		
		$r = preg_replace(array(
			'/\s+[0-9]+-я\s+сери[яали]+\.?/ius',
			'/\s+сери[яали]+\s*[0-9]+\.?/ius',
			'/\s+сезон-[0-9]+-й\.?/ius',
			'/\s+[0-9]+-?й?\s+сезон\.?/ius',
			'/\s+часть\s+[0-9]+/ius',
			'/\s+[0-9]-я\s+часть/ius',
			'/\(\s+и\s*\)$/ius'
		), '', $r);
		$r = preg_replace(array('/"(.+)"\.\\s*.*"(.+)"/iu', '/(.+)\.? "(.+)"/iu'), '\1, «\2»', $r);
		$r = str_replace(array('.   -', ',,'), ',', $r);
		$r = str_replace(' (  -', '. ', $r);
		$r = str_replace('. ,', '.', $r);
		$r = Xmltv_String::str_ireplace('телесериал', '', $r);
		$r = preg_replace(array(
			'/\.\s*Часть\s*\)?/ius',
			'/\.\s*и\s*$/ius',
			'/,\s*и\s*$/ius',
			'/\.\s*Новый сезон/ius',
			'/"\s*,\s*$/ius',
		), '', $r);
		
		/*
		if ($this->_program->hash== '6e41e89ccdfe985e111bb4ffb6332920') {
			var_dump($this->_program->title);
			var_dump($r);
			die(__FILE__.': '.__LINE__);
		}
		*/
		
		if (Xmltv_String::strtolower($r)=='сериал') {
			$this->_title = $r;
		} elseif (trim(Xmltv_String::strtolower($r))=='') { 
			$this->_title = "Неизвестная программа";
		} else
		$this->_title = parent::cleanTitle($r);
		
	}
	
	protected function setSubTitle(){
		
		$sub_title=$this->_program->title;
		
		if (preg_match( '/^.+ Часть ([0-9]+)-я - ".+" \(([0-9]+)-я - ([0-9]+)-я серии\)/iu', $sub_title, $m )) {
			//var_dump($m);
			//die(__FILE__.': '.__LINE__);
			/**
			 * @example МУР. Часть 1-я - "1941" (1-я - 2-я серии)
			 */
			$sub_title = "Часть ".$m[1].", серии ".$m[2]." - ".$m[3];
		} elseif (preg_match( '/^.+ \(".+"\. ([0-9]+)-я и ([0-9]+)-я серии\)/iu', $sub_title, $m )) {
			/**
			 * @example Ментовские войны-2 ("За неделю до весны". 3-я и 4-я серии)
			 */
			$sub_title = "Серии ".$m[1]." и ".$m[2];
		} elseif (preg_match('/^.+ \(([0-9]+)-я и ([0-9]+)-я серии\)/iu', $sub_title, $m)) {
			//var_dump($m);
			//die(__FILE__.': '.__LINE__);
			/**
			 * @example Братаны-2 (1-я и 2-я серии)
			 */
			$sub_title = "Серии ".$m[1]." и ".$m[2];
		} elseif ( preg_match( '/^.+ \(([0-9]+)-я серия - "Крот". Часть ([0-9]+)-я\)/iu', $sub_title, $m ) ) {
			/**
			 * @example Лектор (2-я серия - "Крот". Часть 2-я)
			 */
			$sub_title = "Часть ".$m[2]." серия ".$m[1];
		} elseif ( preg_match( '/^.+ \(?([0-9]+)-я\s*(-|и)\s*([0-9]+)-я\)?/ius', $sub_title, $m ) ){
			//var_dump($m);
			//die(__FILE__.': '.__LINE__);
			/**
			 * @example Обручальное кольцо (XXX-я -XXX-я серии)
			 */
			$sub_title = "Серии c ".$m[1]." по ".$m[3];
		} elseif ( preg_match( '/^.+\(([0-9]+)-я серия - "(.+)"(\.) ([0-9]+)-я серия - "(.*)"\)/iu', $sub_title, $m ) ){
			/**
			 * @example Шаповалов (1-я серия - "Куратор". 2-я серия - "Любовь и смерть")
			 */
			$sub_title = "Серии ".$m[1]." и ".$m[4];
		} elseif ( preg_match( '/([0-9]+)-я и ([0-9]+)-я сери[я|и]/iu', $sub_title, $m)) {
			/**
			 * @example 1-я и 2-я серии
			 */
			$sub_title = "Серии ".$m[1]." и ".$m[3];
		} elseif ( preg_match( '/([0-9]+)-я серия/iu', $sub_title, $m)) {
			$sub_title = "Серия ".$m[1];
		} elseif ( preg_match( '/([0-9]+)-я часть/iu', $sub_title, $m)) {
			$sub_title = "Часть ".$m[1];
		} elseif ( preg_match( '/([0-9]+)-?й?\s+сезон\.\s+([0-9]+)-?-я?\s+серия/iu', $sub_title, $m)) {
			/**
			 * @example Раз, два, взяли! 1 сезон. 5-я серия
			 */
			$sub_title = "Сезон ".$m[1].', серия '.$m[2];
		} elseif ( preg_match( '/сезон-([0-9]+)-й/iu', $sub_title, $m)) {
			$sub_title = "Сезон ".$m[1];
		} elseif ( preg_match( '/([0-9]+)-й сезон/iu', $sub_title, $m)) {
			$sub_title = "Сезон ".$m[1];
		}
		/*
		if (Xmltv_String::stristr($input, 'ментовские войны-3')) {
			var_dump($this->_program->title);
			var_dump($sub_title);
			die(__FILE__.': '.__LINE__);
		}
		*/
		$this->_sub_title = parent::cleanTitle($sub_title, true);
	}
	
	protected function setDescription(){
		
		$result = $this->_program->desc_intro." ".$this->_program->desc_body;
		
		if ( preg_match('/^(.+)\s*\.\.\."?\s+В\s+ролях:\s+(.+)\s*$/muis', $result, $m) 
				|| preg_match('/^(.+)\s*\.\s+В\s+ролях:\s+(.+)\s*$/muis', $result, $m)
				|| preg_match('/^(.+)\s+Зв[её]зды кино:\s*(.*)\s*$/muis', $result, $m) ) {
			/**
			 * @example …немолод и к тому же, говорят, колдун... В ролях: Джулиано Джемма, Ноэль Роквер, Жан Рошфор, Мишель Мерсье, Робер Оссейн
			 * @example …немолод и к тому же, говорят, колдун. В ролях: Джулиано Джемма, Ноэль Роквер, Жан Рошфор, Мишель Мерсье, Робер Оссейн
			 * @example …Звезды кино: Александр Балуев, Александр Домогаров, Даниил Белых, Марина Зудина, Тэмо Эсадзе
			 * @example …красоту своенравной Дианы..." В ролях: Олег Табаков, Александра Яковлева, Андрей Миронов, Леонид Ярмольник, Михаил Светин, Николай Караченцов
			 */
			//var_dump($m);
			//var_dump($this->_program);
			//die(__FILE__.': '.__LINE__);
			
			if (!isset($m[1]) && empty($m[1]))
			throw new Exception( __METHOD__." - Ошибка обработки описания для ".$this->_program->hash, 500 );
			else
			$this->_desc->intro = $m[1];
			
			if (isset($m[2]) && !empty($m[2])) {
				$actors = array();
				if (strstr($m[2], ',')) {
					$actors = explode(',', $m[2]);
					foreach ($actors as $k=>$a) {
						$actors[$k] = trim($a, ' .');
					}
				} else
				$actors[]=trim($m[2]);
			} else
			throw new Exception( __METHOD__." - Ошибка обработки списка актеров для ".$this->_program->hash, 500 );
			
			//var_dump($actors);
			//die(__FILE__.': '.__LINE__);
			
		} elseif (preg_match('/^художественный\s+фильм\.\s+(.+),\s+([0-9]{4})г\.\s+режиссер:\s+(.+).\s+в\s+ролях:\s+(.+)$/muis', $result, $m)) {
			/**
			 * @example Художественный фильм. Украина, 2007г. Режиссер: Александр Пархоменко. В ролях: Евгения Дмитриева, Александр Песков, Анатолий Лобоцкий, Владимир Горянский, Дмитрий Лаленков. Анна - обыкновенная женщина 35 лет.
			 */
			//var_dump($m);
			$this->_cat_id = 3;
			
			var_dump($m);
			var_dump($this->_program);
			die(__FILE__.': '.__LINE__);
			
			if (isset($m[1]) && !empty($m[1]))
			$this->_program->country = trim($m[1], ' ,.');
			else
			throw new Exception( __METHOD__." - Не определена страна для ".$this->_program->hash, 500 );
			
			if (isset($m[2]) && !empty($m[2])) {
				$d = new Zend_Date($m[2], 'yyyy');
				$this->_program->date = $d->toString('yyyy');
			} else
			throw new Exception( __METHOD__." - Не определен год выпуска фильма для ".$this->_program->hash, 500 );
			
			if (isset($m[3]) && !empty($m[3])) {
				$this->_program->directors = array();
				if (strstr($m[3], ','))
				$directors = explode($m[3]);
				else
				$directors[]=trim($m[3]);
				
			} else
			throw new Exception( __METHOD__." - Ошибка обработки списка актеров для ".$this->_program->hash, 500 );
			
			var_dump($m);
			var_dump($this->_program);
			var_dump($directors);
			die(__FILE__.': '.__LINE__);
			
			if (isset($m[4]) && !empty($m[4])) {
				$actors = array();
				if (strstr($m[4], ','))
				$actors = explode($m[4]);
				else
				$actors[]=trim($m[4]);
			} else
			throw new Exception( __METHOD__." - Ошибка обработки списка актеров для ".$this->_program->hash, 500 );
			
			var_dump($m);
			var_dump($this->_program);
			var_dump($directors);
			die(__FILE__.': '.__LINE__);
			
			if (!isset($m[5]) && empty($m[5]))
			throw new Exception( __METHOD__." - Ошибка обработки описания для ".$this->_program->hash, 500 );
			else
			$this->_desc->intro = $m[5];
			
			var_dump($this->_program);
			die(__FILE__.': '.__LINE__);
			
		} elseif (preg_match('/^-\s+(.+)\.\s+комедия\.\s+в\s+ролях:\s+(.+)\.\s([0-9]{4})г\./muis', $result, $m)) {
			/**
			 * @example - эпизод с самым плохим шафером. комедия. в ролях: дженнифер энистон, кортни кокс, лиза кудроу, мэтт леблан, мэттью перри, дэвид швиммер. 1997г.
			 */
			var_dump($m);
			$this->_cat_id = 20;
			die(__FILE__.': '.__LINE__);
		} elseif (preg_match('/^комедия\.\s+(.+)[\.,]\s+([0-9]{4})г\.режиссер[ы]?\s*:?\s*(.+)\.?\s+в\s+ролях:\s+(.+)\.\s+(.+)/muis', $result, $m)) {
			/**
			 * @example Комедия. Франция. 2004г. Режиссер: Габриэль Агийон. В ролях: Жерар Дармон, Мишель Ларок, Дани Боон. Лоик и Себ - гламурная гей - пара, которой жизнь сдала счастливую карту
			 */
			var_dump($m);
			$this->_cat_id = 20;
			die(__FILE__.': '.__LINE__);
		} elseif (preg_match('/^художественный фильм\.\s+(.+)[\.,]\s+([0-9]{4})г\.режиссер[ы]?\s*:?\s*(.+)\.?\s+в\s+ролях:\s+(.+)\.\s+(.+)/muis', $result, $m)) {
			/**
			 * @example Художественный фильм. Украина, 2007г. Режиссер: Александр Пархоменко. В ролях: Евгения Дмитриева, Александр Песков, Анатолий Лобоцкий, Владимир Горянский, Дмитрий Лаленков. Анна - обыкновенная женщина 35 лет....
			 */
			
			//var_dump($m);
			$this->_cat_id = 20;
			die(__FILE__.': '.__LINE__);
			
			$intro  = trim($m[5]);
			
			$actors = array();
			if (strstr($m[4], ','))
			$actors = explode($m[4]);
			else
			$actors[]=trim($m[4]);
			
			if (isset($m[3]) && !empty($m[3])) {
				
				$directors = array();
				if (strstr($m[3], ','))
				$directors = explode($m[3]);
				else
				$directors[] = trim($m[3]);
				
			}
			
			$this->_cat_id = 3;
			$d = new Zend_Date($m[2], 'yyyy');
			$this->_program->date    = $d->toString(DATE_MYSQL);
			$this->_program->country = trim($m[1]);
			$result  = trim($m[5]);
			
		} elseif (preg_match('/(.+)в ролях\s*:?\s*(.+)\.(.*)/muis', $result, $m)) {
			
			$intro  = trim($m[1]);
			$body   = trim($m[3]);
			$actors = explode(',',$m[2]);
			$result = $intro.' '.$body;
			
		} elseif (preg_match('/(.+)\s+режиссеры?\s*:?\s*(.+)\.(.*)/muis', $result, $m)) {
			
			$intro  = trim($m[1]);
			$body   = trim($m[3]);
			$actors = explode(',',$m[2]);
			$result = $intro.' '.$body;
			
		} else {
			$info = sprintf( "Программа:<br />%s", print_r( $result, true) );
			Xmltv_Logger::write( $info, Zend_Log::INFO, __CLASS__.'.log' );
			//Zend_Debug::dump($result);
			//die(__FILE__.': '.__LINE__);
		}
		
		if (isset($directors) && !empty($directors)) {
			try {
				$this->updateDirectors($directors);
			} catch (Exception $e) {
				printf( '<b>%s: %s</b>', __METHOD__, $e->getMessage());
				die(__FILE__.': '.__LINE__);
			}
		}
		
		if (isset($actors) && !empty($actors)) {
			try {
				$this->updateActors($actors);
			} catch (Exception $e) {
				printf( '<b>%s: %s</b>', __METHOD__, $e->getMessage());
				die(__FILE__.': '.__LINE__);
			}
		}

		$result = Xmltv_String::str_ireplace('...', '…', $result);
		$this->_desc->intro = $result;
		
	}
	
	
	
	private function _loadPrograms(Zend_Date $start, Zend_Date $end){
		
		//die(__FILE__.': '.__LINE__);
		
		$site_config = Xmltv_Config::getConfig('site');
		
		if (empty($site_config->channels->series))
		throw new Exception( __METHOD__ . ': Не указана настройка "channels.series" в configs/site.ini. ' . __LINE__, 500 );
		
		if (stristr($site_config->channels->series, ','))
		$this->_channels = explode(',', $site_config->channels->series);
		else 
		$this->_channels = array($site_config->channels->series);
		
		$programsTable = new Admin_Model_DbTable_Programs();
		$result = $programsTable->fetchSeries($start, $end, $this->_classCategories, $this->_channels);
		
		//var_dump(count($result));
		//die(__FILE__.': '.__LINE__);
		
		$this->chunks = array_chunk($result, 500);
		
	}
	
	/* (non-PHPdoc)
	 * @see library/Xmltv/Parser/Xmltv_Parser_ProgramInfoParser#getProgram()
	 */
	public function getProgram(){
		return $this->_program;
	}
	
	/**
	 * Update program object properties
	 */
	protected function setProgramProps(){
		
		$this->_program->title          = (string)$this->_title;
		$this->_program->alias          = (string)$this->_alias;
		$this->_program->sub_title      = (string)$this->_sub_title;
		$this->_program->desc_intro     = (string)$this->_desc->intro;
		$this->_program->desc_body      = (string)$this->_desc->body;
		$this->_program->category       = (int)$this->_cat_id;
		
	}
	
	protected function setAlias(){
		
		$a = $this->_title;
		$a = preg_replace(array(
			'/[0-9]+-(я|и)\.?/iu',
			'/сери(я|и|ал)\.?/iu',
			'/сезон-[0-9]+-й\.?/iu',
			'/[0-9]+-й-сезон\.?/iu',
			'/часть\s+[0-9]+/ius',
			'/\(\s+и\s+\)$/ius'
		), '', $a);
		$a = preg_replace(array('/^"(.+)". "(.+)"$/iu', '/^(.+)\.? "(.+)"$/iu'), '\1, «\2»', $a);
		$a = str_replace(array('.   -', ',,'), ',', $a);
		$a = str_replace(' (  -', '. ', $a);
		$a = preg_replace(array(
			'/\.\s*Часть\s*\)?/ius',
			'/\.\s*и\s*$/ius',
			'/,\s*и\s*$/ius',
			'/\.\s*Новый сезон/ius',
			'/"\s*,\s*$/ius',
		), '', $a);
		$this->_alias = Xmltv_String::strtolower( parent::cleanAlias( $a ) );
		
		if ($this->_program->hash== '6e41e89ccdfe985e111bb4ffb6332920') {
			//var_dump($this->_program->title);
			var_dump($this);
			die(__FILE__.': '.__LINE__);
		}
		
	}
	
	
	
	protected function removeDirectorsFromDesc($description=null){
		
		if (!$description)
		throw new Exception( __METHOD__ . "Не указан параметр. " . __LINE__, 500 );
		
		if (preg_match('/^художественный\s+фильм\.\s+(.+)/mius', $description, $m) 
		 || preg_match('/^комедия\.\s+(.+)/mius', $description, $m) ) { 
			$newDescription = $description; //обрабатывается в removeActorsFromDesc()
		} elseif (preg_match('/(.+)\.\s+режиссер[ы]?:\s*(.+)\.\s+(.+)/mius', $description, $m)) {
			//var_dump($this->_program);
			//die(__FILE__.': '.__LINE__);
			//return $description;
			$newDescription='';
			if (empty($m[1]) && empty($m[3]))
			throw new Exception( __METHOD__ . "Неверные данные. " . __LINE__, 500 );
			else {
				$newDescription .= trim($m[1]);
				$newDescription .= trim($m[3]);
			}
		} else {
			$newDescription = $description;
		}
		
		/*if (empty($newDescription)) {
			var_dump($this->_program);
			die(__FILE__.': '.__LINE__);
		} else*/
		return $newDescription;
		
	}

}