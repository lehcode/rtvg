<?php

class Xmltv_Parser_Programs_Series extends Xmltv_Parser_ProgramInfoParser
{
	
	private $_cat_id=5; // Сериал
	private $_channels=array();
	private $_classCategories=array(0, 5, 14, 16, 19);
	
	/* (non-PHPdoc)
	 * @see library/Xmltv/Parser/Xmltv_Parser_ProgramInfoParser#process()
	 */
	public function process(Zend_Date $start, Zend_Date $end){
		
		$this->_loadPrograms( $start, $end );
		
		$logfile = ROOT_PATH.'/log/'.__CLASS__.'.log';
		if (is_file($logfile))
		unlink($logfile);
		
		$matches = array();
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
    			
    			if ($this->matches()) {
    				$this->setTitle();
					$this->setAlias();
					$this->setSubTitle();
					
					if (!empty($this->_program->desc_intro))
					$this->setDescription();
					
					$this->setProgramProps();
					
					$p = $this->_program;
					$this->updateProgramInfo( $p, __CLASS__ );
					$this->updateProgramProps( $p, __CLASS__ );
					$matches[] = $this->_program;
    			}
    		}
		}
		return $matches;
	}
	
	/* (non-PHPdoc)
	 * @see library/Xmltv/Parser/Xmltv_Parser_ProgramInfoParser#matches()
	 */
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
	
	protected function setTitle(){
		
		$r = $this->_program->title;
		
		$r = preg_replace(array(
			'/[0-9]+-[яи]\.?/iu',
			'/сери[яиал]\.?/iu',
			'/сезон-[0-9]+-й\.?/iu',
			'/[0-9]+-й-сезон\.?/iu',
			'/часть\s+[0-9]+/ius',
			'/\(\s+и\s*\)$/ius'
		), '', $r);
		$r = preg_replace(array('/"(.+)"\.\\s*.*"(.+)"/iu', '/(.+)\.? "(.+)"/iu'), '\1, «\2»', $r);
		$r = str_replace(array('.   -', ',,'), ',', $r);
		$r = str_replace(' (  -', '. ', $r);
		$r = str_replace('. ,', '.', $r);
		$r = preg_replace(array(
			'/\.\s*Часть\s*\)?/ius',
			'/\.\s*и\s*$/ius',
			'/,\s*и\s*$/ius',
			'/\.\s*Новый сезон/ius',
			'/"\s*,\s*$/ius',
		), '', $r);
		/*
		if (Xmltv_String::stristr($result, 'Ментовские войны-3')) {
			var_dump($this->_program->title);
			var_dump($result);
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
		$result = preg_replace('/[0-9]+-я серия\.? ?/', '', $result);
		$result = Xmltv_String::str_ireplace('...', '…', $result);
		$result = $this->_removeDirectorsFromDesc( $result );
		$result = $this->_removeActorsFromDesc( $result );
		
		//var_dump($this->_program);
		//var_dump($result);
		//die(__FILE__.': '.__LINE__);
		
		$this->_desc->intro = $result;
		
	}
	
	private function _removeActorsFromDesc($text=null){
		
		if (!$text)
		throw new Exception("Не указан параметр для ".__METHOD__, 500);
		
		if (preg_match('/(.+)режиссер[ы]?\s*:?\s*(.+)\.?(.*)/muis', $text, $m)) {
			var_dump($this->_program);
			var_dump($m);
			die(__FILE__.': '.__LINE__);
		}
		
		if (preg_match('/(.+)в ролях\s*:?\s*(.+)\.(.*)/muis', $text, $m)) {
			
			//var_dump($m);
			
			$intro  = trim($m[1]);
			$body   = trim($m[3]);
			$actors = explode(',',$m[2]);
			
			//var_dump($this->_program);
			//var_dump($pos);
			//var_dump($intro);
			//var_dump($body);
			//var_dump($actors);
			
			$this->updateActors($actors);
			$text = $intro.' '.$body;
			
			//die(__FILE__.': '.__LINE__);
			
		} else {
			return $text;
		}
		
		return $text;
		
	}
	
	private function _removeDirectorsFromDesc($text=null){
		
		if (!$text)
		throw new Exception("Не указан параметр для ".__METHOD__, 500);
		
		if (Xmltv_String::stristr($text, 'режиссер')) {
			$parts = explode('режиссер', $text);
			var_dump($text);
			var_dump($parts);
			die(__FILE__.': '.__LINE__);
		} else {
			return $text;
		}
		
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
		
		/*foreach ($this->_classCategories as $cat){
			if ( !in_array((string)$cat, $this->_channels) )
			$this->_channels[]=$cat;
		}*/
		
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
	}
	
	
	
}