<?php

class Xmltv_Parser_Programs_Movies extends Xmltv_Parser_ProgramInfoParser
{
	
	private $_cat_id=3; // Художественный фильм
	private $_channels=array();
	private $_classCategories=array(3, 14, 15, 17);
	
	
	public function process(Zend_Date $start, Zend_Date $end){
		
		$this->_loadPrograms( $start, $end );
		
		$logfile = APPLICATION_PATH.'/../log/'.__CLASS__.'.log';
		if (is_file($logfile))
		unlink($logfile);
		
		var_dump(count($this->chunks, COUNT_RECURSIVE));
		//die(__FILE__.': '.__LINE__);
		
		$site_config = Xmltv_Config::getConfig('site')->site;
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
				
    			try {
    				
    				$this->setTitle();
					$this->setAlias();
					$this->setSubTitle();
					
					if ( !$this->_desc->intro )
					$this->setDescription($this->_program->desc_intro, $this->_program->desc_body);
					
					if ((int)$this->_program->category == 0)
					$this->setCategory(18);
					
    			} catch (Exception $e) {
    				echo '<b>'.$e->getMessage().'</b>';
					Zend_Debug::dump($e->getTrace());
					die(__FILE__.': '.__LINE__);
    			}
    			
				$this->setProgramProps();
				$matches[] = $this->_program;
				$cc++;
				
    		}
    		
    		//var_dump($matches);
    		//var_dump(count($matches));
    		//var_dump($i);
		}
		
		//var_dump(count($matches));
		//die(__FILE__.': '.__LINE__);
		
		return $matches;
		
	}
	
	/* (non-PHPdoc)
	 * @see library/Xmltv/Parser/Xmltv_Parser_ProgramInfoParser#matches()
	 */
	protected function matches(){
		
		if(!$this->_program->title)
		throw new Exception( __METHOD__ . ": Отсутствует название программы. " . __LINE__, 500 );
		
		
		
		//var_dump($this->_program);
		//die(__FILE__.': '.__LINE__);
		//var_dump($this->_channels);
		
		if (!empty($this->_program->desc_intro)) {
			if( Xmltv_String::stristr($this->_program->desc_intro, 'телеспектакль') 
			 || Xmltv_String::stristr($this->_program->desc_intro, 'в детективе') 
			 || Xmltv_String::stristr($this->_program->desc_intro, 'многосерийный фильм')
			 || Xmltv_String::stristr($this->_program->desc_intro, 'остросюжетный детектив')
			 || preg_match('/\s+Фильм\s+(\p{Cyrillic}+\s*\p{Cyrillic}+)\s+".+"/ius', $this->_program->title) ) {
				return true;
			}
		} 
		if( Xmltv_String::stristr($this->_program->title, 'многосерийный фильм')
			 || Xmltv_String::stristr($this->_program->title, 'остросюжетный детектив') ){
			return true;
		} 
		
		//var_dump($this->_program);
		//die(__FILE__.': '.__LINE__);
		
		return;
		
		//if (!in_array($this->_program->ch_id, $channels)) {
			
		//} else {
		//	return false;
		//}
		
		
	}
	
	/**
	 * Overloaded Xmltv_Parser_ProgramInfoParser#setTitle()
	 */
	protected function setTitle(){
		
		$r = $this->_program->title;
		/*
		if (!in_array((string)$this->_program->ch_id, $this->_channels)) {
			var_dump($this->_program);
			die(__FILE__.': '.__LINE__);
		}
		*/
		//var_dump($this->_program);
		//die(__FILE__.': '.__LINE__);
		
		if (Xmltv_String::stristr($r, 'многосерийный фильм')) {
			$r = Xmltv_String::str_ireplace('многосерийный фильм', '', $r);
			$this->_cat_id = 15;
		} 
		
		//$result = str_replace(array('.   -', ',,'), ',', $result);
		//$result = str_replace(' (  -', '. ', $result);
		/*
		if (Xmltv_String::stristr($this->_program->title, 'фильм')) {
			var_dump($this->_program);
			var_dump($result);
			die(__FILE__.': '.__LINE__);
		}
		*/
		$this->_title = parent::cleanTitle($r);
		
	}
	
	/**
	 * Overloaded Xmltv_Parser_ProgramInfoParser#setSubTitle()
	 */
	protected function setSubTitle($input=null){
		
		//if(  !$input ) throw new Exception( 
		//"Не указан параметр для " . __METHOD__, 500 );
		
		$sub_title=$this->_program->title;
		
		//var_dump($sub_title);
		//var_dump($this->_program);
		//die(__FILE__.': '.__LINE__);
		
		if (preg_match('/".+".*\s+.*многосерийный фильм/ius', $sub_title )) {
			/**
			 * @example "Лето волков". Многосерийный фильм
			 */
			$sub_title = 'Многосерийный фильм';
		} 
		$this->_sub_title = parent::cleanTitle($sub_title, true);
	}
	
	/**
	 * Overloaded Xmltv_Parser_ProgramInfoParser#setDescription()
	 */
	protected function setDescription($intro=null, $body=''){
		
		if (!$intro && empty($this->_program->desc_intro))
		throw new Exception("Не указан параметр для ".__METHOD__, 500);
		
		//var_dump($this->_program);
		//die(__FILE__.': '.__LINE__);
		
		if (!$intro && (empty($this->_desc->intro) && !empty($this->_program->desc_intro)))
		$intro = $this->_program->desc_intro;
		if (!$body && empty($this->_desc->body) && !empty($this->_program->desc_body))
		$body = $this->_program->desc_body;
		
		if (Xmltv_String::stristr($intro, 'телеспектакль')) {
			$this->_cat_id = 18;
		}
		
		$result = $intro." ".$body;
		$result = Xmltv_String::str_ireplace('...', '…', $result);
		$result = $this->removeDirectorsFromDesc( $result );
		$result = $this->removeActorsFromDesc( $result );
		
		$this->_desc->intro = $result;
		
	}
	/*
	private function removeActorsFromDesc($text=null){
		
		if (!$text)
		throw new Exception("Не указан параметр для ".__METHOD__, 500);
		
		if (Xmltv_String::stristr($text, 'в ролях')) {
			$pos = Xmltv_String::strpos($text, 'В ролях');
			//$parts = explode('в ролях', $text);
			$names = Xmltv_String::substr( $text, $pos, Xmltv_String::strlen($text)-1 );
			$desc = trim( Xmltv_String::str_ireplace( $names, '', $text) );
			//var_dump($this->_program);
			//var_dump($pos);
			//$names = trim( Xmltv_String::str_ireplace('в ролях:', '', $names), '. ' );
			//$names = explode( ',', $names );
			//var_dump($names);
			//var_dump($desc);
			//die(__FILE__.': '.__LINE__);
			return $desc;
		} else {
			return $text;
		}
		
	}
	*/
	
	
	protected function removeDirectorsFromDesc($text=null){
		
		if (!$text)
		throw new Exception("Не указан параметр для ".__METHOD__, 500);
		
		if (Xmltv_String::stristr($text, 'Художественный фильм')) {
			return $text;
		} elseif (Xmltv_String::stristr($text, 'режиссер')) {
			$parts = explode('режиссер', $text);
			var_dump($text);
			var_dump($parts);
			die(__FILE__.': '.__LINE__);
		} else {
			return $text;
		}
		
	}
	
	
	private function _loadPrograms(Zend_Date $start, Zend_Date $end){
		
		$site_config = Xmltv_Config::getConfig('site');
		
		if (empty($site_config->channels->movies))
		throw new Exception( __METHOD__ . ': Не указана настройка "channels.movies" в configs/site.ini. ' . __LINE__, 500 );
		
		if (stristr($site_config->channels->movies, ','))
		$this->_channels = explode(',', $site_config->channels->movies);
		else 
		$this->_channels = array($site_config->channels->movies);
		
		$broadcasts = new Admin_Model_DbTable_Programs();
		$result = $broadcasts->fetchMovies( $start, $end, $this->_classCategories, $this->_channels );
		
		//var_dump(count($result));
		//die(__FILE__.': '.__LINE__);
		
		$this->chunks = array_chunk($result, 500);
		
	}
	
	protected function setProgramProps () {

		$this->_program->title      = $this->_title;
		$this->_program->alias      = $this->_alias;
		$this->_program->sub_title  = $this->_sub_title;
		$this->_program->desc_intro = $this->_desc->intro;
		$this->_program->desc_body  = $this->_desc->body;
		$this->_program->category   = $this->_cat_id;
		
	}
	/*
	public function loadPrograms(Zend_Date $start, Zend_Date $end){
		
		if (!$this->_cat_id)
		throw new Exception( __METHOD__ . ': Отсутствует категория программы. ' . __LINE__, 500 );
		
		$broadcasts = new Admin_Model_DbTable_Programs();
		$programs = $broadcasts->fetchProgramsForPeriod($start, $end, $this->_classCategories);
		$this->chunks = array_chunk($programs, 500);
		
	}
	*/
	
	protected function setCategory($id=null){
		
		$this->_cat_id = (int)$id;
		
	}
	
	protected function setAlias(){
		$a = $this->_title;
		$this->_alias = Xmltv_String::strtolower( parent::cleanAlias( $a ) );
	}
	
}