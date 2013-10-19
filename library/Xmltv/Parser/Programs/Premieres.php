<?php
class Xmltv_Parser_Programs_Premieres extends Xmltv_Parser_ProgramInfoParser 
{
	
	/* (non-PHPdoc)
	 * @see library/Xmltv/Parser/Xmltv_Parser_ProgramInfoParser#process()
	 */
	public function process(Zend_Date $start, Zend_Date $end){
		
		$this->_loadPrograms( $start, $end );
		
		$logfile = APPLICATION_PATH.'/../log/'.__CLASS__.'.log';
		if (is_file($logfile))
		unlink($logfile);
		
		//var_dump($this->chunks);
		//die(__FILE__.': '.__LINE__);
		
		$matches = array();
		$cc=0;
		foreach ($this->chunks as $ck=>$part) {
    		foreach ( $part as $pk=>$current ) {
    			
    			$this->_program = new stdClass();
    			
    			if (!$current->hash) {
					var_dump($current);
    				die("Идентификатор программы не может быть NULL ".__METHOD__.': '.__LINE__);
    			}
    			
    			$current->start = new Zend_Date($current->start, 'yyyy-MM-dd HH:mm:ss');
    			$current->end   = new Zend_Date($current->end, 'yyyy-MM-dd HH:mm:ss');
    			$this->_program = $current;
    			
				try {
					
					if (!$this->setTitle())
					throw new Exception(sprintf("Не могу обработать название для %s", $this->_program->title), 500);
					if (!$this->setAlias())
					throw new Exception(sprintf("Не могу обработать псевдоним для %s", $this->_program->title), 500);
					if (!$this->setSubTitle())
					throw new Exception(sprintf("Не могу обработать подзаголовок для %s", $this->_program->title), 500);
					
				} catch (Exception $e) {
					echo '<b>'.$e->getMessage().'</b>';
					//var_dump($this->_program);
					//var_ump($e->getTrace());
					die(__FILE__.': '.__LINE__);
				}
					
				$this->setProgramProps();
				$matches[] = $this->_program;
				$cc++;
    		}
		}
		
		//var_dump($matches);
		//die(__FILE__.': '.__LINE__);
		
		return $matches;
		
	}
	
	
	/* (non-PHPdoc)
	 * @see library/Xmltv/Parser/Xmltv_Parser_ProgramInfoParser#matches()
	 */
	protected function matches(){
		
		if ( Xmltv_String::stristr( $this->_program->title, 'премьера художественного фильма' ) 
		  || preg_match( '/премьера\.\s+/ius', $this->_program->title ) ) {
			return true;
		}
		
	}
	
	
	
	/**
	 * Normalize Title
	 */
	protected function setTitle(){
		
		$this->_setPremiere();
		
		if ( Xmltv_String::stristr($this->_program->title, 'премьера песни') ) {
			$this->_unsetPremiere();
			$t = $this->_program->title;
		} elseif ( preg_match('/премьера\s+художественного\s+фильма\s*"(.+)"\s*\.\s*После\s+окончания/ius', $this->_program->title, $m) ) {
			/**
			 * @example Премьера художественного фильма "Я ОБЪЯВЛЯЮ ВАМ ВОЙНУ". После окончания смотрите мультфильмы для взрослых
			 */
			//var_dump(Xmltv_String::strlen(preg_replace('/[A-Z]/', '', $m[1])) / Xmltv_String::strlen( $m[1] ) > 0.25);
			//var_dump($this->_program);
			//die(__FILE__.': '.__LINE__);
			$t = Xmltv_String::ucfirst( Xmltv_String::strtolower( $m[1] ) );
		} elseif ( preg_match('/^премьера\.\s+(.*)$/iu', $this->_program->title, $m) ) {
			
			$t = $m[1];
			if (empty($m[1])) {
				$info = print_r($m, true);
				throw new Exception( __METHOD__ . " - Неверные параметры, строка ".__LINE__, 500);
			}
						
		} else {
			$t = $this->_program->title;
			$this->_unsetPremiere();
		}
		
		//var_dump($t);
		//die(__FILE__.': '.__LINE__);
		
		/*
		if ($this->_program->hash == 'cb00ca1a94eafb4447b18dff89a52f1b') {
			var_dump(Xmltv_String::strlen(preg_replace('/[\p{Lu}]+/iu', '', $t)));
			var_dump(Xmltv_String::strlen($t));
			var_dump($t);
			die(__FILE__.': '.__LINE__);
		}
		*/
		
		$this->_title = parent::cleanTitle( $t );
		if ($this->_title && !empty($this->_title))
		return true;
		
	}
	
	/**
	 * 
	 * Clean title
	 * 
	 * @param string $input
	 * @param bool $subtitle // Optional
	 */
	protected function cleanTitle($input=null, $subtitle=false){
		
		if(  !$input && !$subtitle ) 
		throw new Exception(__METHOD__ . ": Пропущен параметр. " . __LINE__, 500);
		
		return parent::cleanTitle( $input, $subtitle );
		/*
		 * $result = $input;
		if (!$subtitle) {
			$regexp = new Zend_Filter_PregReplace(array('match'=>'/^(.*)-?премьера-?(.*)$/iu', 'replace'=>'\1 \2'));
			$result = $regexp->filter($result);
		}
		return parent::cleanTitle( $result );
		*/
		
		
		
	}
	
	/**
	 * Update program properties
	 */
	private function _setPremiere(){
		
		$this->_program->new=1;
		$this->_program->premiere=1;
		$this->_program->premiere_date=$this->_program->start->toString('yyyy-MM-dd HH:mm:ss');
		
	}

	private function _unsetPremiere(){
		
		$this->_program->new=0;
		$this->_program->premiere=0;
		$this->_program->premiere_date='0000-00-00 00:00:00';
		
	}
	
	
	
	/**
	 * Normalize program title alias
	 */
	protected function setAlias(){
		
		$this->_alias = parent::cleanAlias( $this->_title );
		
		if ($this->_alias && !empty($this->_alias))
		return true;
	}
	
	/**
	 * Normalize program sub-title
	 */
	protected function setSubTitle(){
		
		if ( preg_match('/премьера\s+художественного\s+фильма\s*".+"\s*\.\s*(После\s+окончания.+)$/ius', $this->_program->title, $m) ) {
			/**
			 * @example Премьера художественного фильма "Я ОБЪЯВЛЯЮ ВАМ ВОЙНУ". После окончания смотрите мультфильмы для взрослых
			 */
			$sub_title = $m[1];
			
		} elseif ( Xmltv_String::stristr( $this->_program->title, 'премьера' ) ) {
			$sub_title = Xmltv_String::str_ireplace( 'премьера', '', $this->_program->title );
		}
		$this->_sub_title = $this->cleanTitle( $sub_title, true );
		
		if ($this->_sub_title && !empty($this->_sub_title))
		return true;
	}
	
	/**
	 * @return stdClass
	 */
	protected function setProgramProps(){
		
		$this->_program->title          = (string)$this->_title;
		$this->_program->alias          = (string)$this->_alias;
		$this->_program->sub_title      = (string)$this->_sub_title;
		$this->_program->desc_intro     = (string)$this->_desc->intro;
		$this->_program->desc_body      = (string)$this->_desc->body;
		$this->_program->premiere_date  = (string)$this->_program->start->toString(DATE_MYSQL);
		
	}
	
	/* (non-PHPdoc)
	 * @see library/Xmltv/Parser/Xmltv_Parser_ProgramInfoParser#getProgram()
	 */
	public function getProgram(){
		return $this->_program;
	}
	
	
	/* (non-PHPdoc)
	 * @see library/Xmltv/Parser/Xmltv_Parser_ProgramInfoParser#loadPrograms()
	 * 
	 * @todo add avanced search
	 */
	private function _loadPrograms(Zend_Date $start, Zend_Date $end){
		
		$broadcasts = new Admin_Model_DbTable_Programs();
		$result = $broadcasts->fetchPremieres($start, $end); // Fast search for premieres
		//var_dump($result);
		//die(__FILE__.': '.__LINE__);
		$this->chunks = array_chunk($result, 500);
		//return $result;
		
	}
	
	/**
	 * @param stdClass $data
	 * @param string $log_class
	 */
	protected function updateProgramProps (Zend_Db_Table_Row $data, $log_class=null) {

		if (!$log_class)
		$log_class = __CLASS__;
		
		/*if ( $this->_program->hash == '72ccbe6e07086474b4ed0aad7c107fa5' ) {
			var_dump($this->_program);
			die(__FILE__.': '.__LINE__);
		}*/
		
		$table = new Admin_Model_DbTable_ProgramsProps();
		$table_info = $table->info();
		$newData=array( 'premiere_date'=>$data->start->toString(DATE_MYSQL) );
		foreach ($data->toArray() as $k=>$v) {
			if (in_array($k, $table_info['cols']))
			$newData[$k]=$v;
		}
		
		if ($this->saveChanges) {
			if ( !$found = $table->find( $data->hash ) ) {
				try {
					$table->insert($newData);
					$info = print_r( $newData, true );
					Xmltv_Logger::write( __METHOD__."\n". $info, Zend_Log::INFO, $log_class.'.log' );
				} catch (Exception $e) {
					echo "Не могу создать новую запись свойств программы. ".$e->getMessage();
					if (Xmltv_Config::getDebug())
					echo $e->getTrace();
				}
			} else {
				try {
					$table->update($newData, "`hash`='".$data->hash."'");
					$info = print_r( $newData, true );
					Xmltv_Logger::write( __METHOD__."\n". $info, Zend_Log::INFO, $log_class.'.log' );
				} catch (Exception $e) {
					echo "Не могу обновить данные. ".$e->getMessage();
					if (Xmltv_Config::getDebug())
					echo $e->getTrace();
				}
				
			}
		} 
		$info = print_r( $newData, true );
		Xmltv_Logger::write( __METHOD__."\n". $info, Zend_Log::INFO, $log_class.'.log' );
		return true;	
	}
	
	
}