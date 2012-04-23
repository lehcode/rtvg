<?php

class Xmltv_Parser_Programs_Movies extends Xmltv_Parser_ProgramInfoParser
{
	
	private $_cat_id=3; // Художественный фильм
	private $_channels=array();
	private $_classCategories=array(0, 3, 14, 15, 17);
	
	/**
	 * Overloaded Xmltv_Parser_ProgramInfoParser#process()
	 * 
	 * @param mixed $return
	 */
	public function process(){
		
		$this->_loadChannels();
		
		$i=0;
		foreach ($this->chunks as $ck=>$part) {
    		foreach ( $part as $pk=>$current ) {
    			
    			/*
    			if ($i==500)
    			die(__FILE__.': '.__LINE__);
    			else
    			var_dump($current);
    			*/
    			$i++;
    			
    			
    			//var_dump(count($this->chunks));
    			//die(__FILE__.': '.__LINE__);
    			
    			if (!$current->hash) {
					var_dump($current);
    				die("Идентификатор программы не может быть NULL ".__METHOD__.': '.__LINE__);
    			}
    			
    			$current->start = new Zend_Date($current->start, 'yyyy-MM-dd HH:mm:ss');
    			$current->end   = new Zend_Date($current->start, 'yyyy-MM-dd HH:mm:ss');
    			$this->setProgram( $current );
    			
    			//var_dump($this->_program);
    			//die(__FILE__.': '.__LINE__);
    			
	    		if ($this->matches()) {
					
	    			$this->setTitle( $this->_program->title );
					
					//var_dump($this);
					//die(__FILE__.': '.__LINE__);
					
					if (!$this->_alias)
					$this->setAlias( $this->_title );
					
					if (!$this->_sub_title)
					$this->setSubTitle( $this->_program->title );
					
					//var_dump($this);
					//die(__FILE__.': '.__LINE__);
					
					if (!isset($this->_desc->intro))
					$this->setDescription($this->_program->desc_intro, $this->_program->desc_body);
					
					//if ((int)$this->_program->category == 0)
					$this->setCategory(18);
					
					//var_dump($this);
					//die(__FILE__.': '.__LINE__);
					
					//if ($return===true)
					//return $this->getProgram();
					//else {
					if ( $this->_title != $this->_program->title )
					$this->updateProgramInfo( $this->getProgram() );
					//}
				}
    			
    		}
		}
		
		
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
		} else {
			if( Xmltv_String::stristr($this->_program->title, 'многосерийный фильм')
			 || Xmltv_String::stristr($this->_program->title, 'остросюжетный детектив') ){
				return true;
			} 
			
			//var_dump($this->_program);
			//die(__FILE__.': '.__LINE__);
			
			return;
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
		
		$result = $this->_program->title;
		/*
		if (!in_array((string)$this->_program->ch_id, $this->_channels)) {
			var_dump($this->_program);
			die(__FILE__.': '.__LINE__);
		}
		*/
		//var_dump($this->_program);
		//die(__FILE__.': '.__LINE__);
		
		if (Xmltv_String::stristr($result, 'Многосерийный фильм')) {
			$result = Xmltv_String::str_ireplace('Многосерийный фильм', '', $result);
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
		parent::cleanTitle($result);
		
	}
	
	/**
	 * Overloaded Xmltv_Parser_ProgramInfoParser#setSubTitle()
	 */
	protected function setSubTitle($input=null){
		
		if(  !$input ) throw new Exception( 
		"Не указан параметр для " . __METHOD__, 500 );
		
		$sub_title=$input;
		
		//var_dump($sub_title);
		//var_dump($this->_program);
		//die(__FILE__.': '.__LINE__);
		
		if (preg_match('/".+".*\s+.*многосерийный фильм/ius', $sub_title )) {
			/**
			 * @example "Лето волков". Многосерийный фильм
			 */
			$sub_title = 'Многосерийный фильм';
		} 
		$this->cleanTitle($sub_title, true);
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
		//$result = preg_replace('/[0-9]+-я серия\.? ?/', '', $result);
		$result = Xmltv_String::str_replace('...', '…', $result);
		$result = $this->removeDirectorsFromDesc( $result );
		$result = $this->removeActorsFromDesc( $result );
		
		$this->_desc['intro'] = $result;
		
	}
	
	private function removeActorsFromDesc($text=null){
		
		if (!$text)
		throw new Exception("Не указан параметр для ".__METHOD__, 500);
		
		if (Xmltv_String::stristr($text, 'в ролях')) {
			$pos = Xmltv_String::strpos($text, 'В ролях');
			//$parts = explode('в ролях', $text);
			$desc = Xmltv_String::substr( $text, $pos, Xmltv_String::strlen($text)-1 );
			$desc = Xmltv_String::substr( $text, $pos, Xmltv_String::strlen($text)-1 );
			var_dump($this->_program);
			var_dump($pos);
			var_dump($desc);
			die(__FILE__.': '.__LINE__);
		} else {
			return $text;
		}
		
	}
	
	private function removeDirectorsFromDesc($text=null){
		
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
	
	private function _loadChannels(){
		
		$site_config = Xmltv_Config::getConfig('site');
		
		if (empty($site_config->channels->movies))
		throw new Exception( __METHOD__ . ': Не указанна настройка "channels.movies" в configs/site.ini. ' . __LINE__, 500 );
		
		if (stristr($site_config->channels->movies, ','))
		$this->_channels = explode(',', $site_config->channels->movies);
		else 
		$this->_channels = array($site_config->channels->movies);
		
	}
	
	public function getProgram () {

		$this->_program->title      = $this->_title;
		$this->_program->alias      = $this->_alias;
		$this->_program->sub_title  = $this->_sub_title;
		$this->_program->desc_intro = $this->_desc->intro;
		$this->_program->desc_body  = $this->_desc->body;
		$this->_program->category   = $this->_cat_id;
		
		//var_dump($this->_program);
		//die(__FILE__.': '.__LINE__);
		
		return $this->_program;
	}
	
	public function loadPrograms(Zend_Date $start, Zend_Date $end){
		
		if (!$this->_cat_id)
		throw new Exception( __METHOD__ . ': Отсутствует категория программы. ' . __LINE__, 500 );
		
		$programsTable = new Admin_Model_DbTable_Programs();
		$programs = $programsTable->fetchProgramsForPeriod($start, $end, $this->_classCategories);
		$this->chunks = array_chunk($programs, 500);
		
	}
	
	protected function setCategory($id=null){
		
		$this->_cat_id = (int)$id;
		
	}
	
}