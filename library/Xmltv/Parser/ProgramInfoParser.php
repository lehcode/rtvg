<?php
class Xmltv_Parser_ProgramInfoParser extends Xmltv_Parser_StringParser
{
	
	protected $_title='';
	protected $_alias='';
	protected $_sub_title='';
	protected $_desc;
	protected $_program;
	protected $_stringParser;
	protected $_db;
	protected $_categories;
	protected $chunks=array();
	public $saveChanges=false;
	
	public function __construct($config=array()){
		
		$this->_stringParser = parent::getInstance();
		
		if (isset($config['program']) && !empty($config['program']))
		$this->_program = $config['program'];
		
		$this->_desc->intro='';
		$this->_desc->body='';
		
		$logfile = ROOT_PATH.'/log/'.__CLASS__.'.log';
		if (is_file($logfile))
		unlink($logfile);
		
	}
	
	public function process(Zend_Date $start, Zend_Date $end, $return=false){
		
		$this->_loadPrograms( $start, $end );
		
		if ($return===true)
		return;
	}
	
	protected function setDescription($intro=null, $body=''){
		
		if (!$intro && empty($this->_desc->intro))
		throw new Exception("Неверные параметры переданы для ".__METHOD__, 500);
		
		$this->cleanDescription($intro, $body);
		
	}
	
	
	protected function getDescription(){
		
		if (!empty($this->_desc))
		return $this->_desc; 
		else {
			
			if (empty($this->_src_desc))
			return false;
			
			$this->cleanDescription( $this->_src_desc );
			return $this->_desc;
			
		}
		
	}
	
	/**
	 * @return object //Current program
	 */
	public function getProgram(){
		return $this->_program;
	}
	
	
	/**
	 * Generate and assign program title
	 */
	protected function setTitle(){
		$this->_title = $this->_program->title;
	}
	
	/**
	 * Generate and assign program title alias
	 */
	protected function setAlias(){
		
		$result  = $this->_program->title;
		$regexp  = new Zend_Filter_PregReplace(array('match'=>'/[:;_,\.\[\]\(\)\\`\{\}"\!\+\?]+/', 'replace'=>' '));
		$todash  = new Zend_Filter_Word_SeparatorToDash();
		$tolower = new Zend_Filter_StringToLower();
		$result  = $tolower->filter( $todash->filter( $regexp->filter( $result ) ) );		
		$this->_alias = $this->cleanAlias( $result );
	}
	
	/**
	 * Normalize alias using alias-specific rules
	 * 
	 * @param string $input
	 * @return string
	 */
	protected function cleanAlias($input=null){
		
		if(  !$input )
		throw new Exception("Не указан параметр для ".__METHOD__, 500);
		
		$result = $input;
		
		$result = Xmltv_String::str_ireplace( 'ё', 'е', $result );
		$result = Xmltv_String::str_ireplace( 'Ё', 'Е', $result );
		$result = Xmltv_String::str_ireplace( '+', '-плюс-', $result );
		$result = str_replace(array(':', ';', '.', ',', '[', ']', '(', ')', '`', '«', '»', '"', '!', '+', '?'), '-', $result);
		
		$regex  = new Zend_Filter_PregReplace('/\s+/ius', '-');
		$result = $regex->filter($result);
		
		do {
			$result=str_replace( '--', '-', $result );
		} while(strstr($result, '--'));
		
		$result = trim($result, ' -');
		
		return $result;
		
	}
	
	
	/**
	 * Normalize program description
	 */
	protected function cleanDescription () {
		
		return $this->_program->desc_intro;
		//die(__FILE__.': '.__LINE__);
		
		$trim = new Zend_Filter_StringTrim(array('charlist'=>' :;.'));
		
		$input = $this->_program->desc_intro." ".$this->_program->desc_body;
		
		if( strstr( $input, '…' ) ) {
			
			$d=explode( '…', $input );
			$d=trim( $d[0] );
			$parts=explode( '.', $d );
			$this->_description = $trim->filter( $parts[0] );
			
		} else {
			var_dump($input);
			die(__FILE__.': '.__LINE__);
			if( Xmltv_String::stristr( $input, 'В ролях:' ) ) {
				$parts = explode( 'В ролях:', $input );
			} elseif( Xmltv_String::stristr( $input, 'Режиссер:' ) ) {
				$parts = explode( 'Режиссер:', $input );
			}
			
			//$parts = explode( '.', $trim->filter( $parts[0] ) );
			//$desc['intro'] = trim( $parts[0] ) . '. ' . trim( $parts[1] ) . '.';
			//unset( $parts[0] );
			//unset( $parts[1] );
			//$desc['body'] = trim( implode( '. ', $parts ) ) . '.';
			//var_dump($desc);
			
			/*
			$last_space = Xmltv_String::strrpos( $desc, '. ' );
			$last_sentence = Xmltv_String::substr( $desc, $last_space + 1 );
			$first_dot = Xmltv_String::strpos( $last_sentence, '.' ) > 0 ? Xmltv_String::strpos( 
			$last_sentence, '.' ) : Xmltv_String::strlen( $last_sentence );
			$desc = Xmltv_String::substr( $desc, 0, $last_space + 1 ) . ' ' . Xmltv_String::substr( 
			$last_sentence, 0, $first_dot + 1 );
			*/
			
		}
	}
	
	/**
	 * @param string $input
	 */
	protected function setSubTitle($input=null){
		
		if( !$input ) 
		$sub_title = $this->_program->title;
		else
		$sub_title = $input;
		
		$this->cleanTitle( $this->_program->title, true );
		
	}
	
	
	/**
	 * Normalize program title
	 * 
	 * @param string $input
	 * @param bool $subtitle
	 * @return string
	 */
	protected function cleanTitle ($input = null, $subtitle = false) {

		if(  !$input &&  !$subtitle )
		throw new Exception( "Не указан параметр для " . __METHOD__, 500 );
		
		$result = $input;
		
		$regex  = new Zend_Filter_PregReplace( '/[\'\(\)]/iu', '' );
		$result = $regex->filter( $result );
		
		$result = str_replace('  ', ' ', $result);
		
		$trim   = new Zend_Filter_StringTrim( array('charlist'=>' .-') );
		$result = $trim->filter( $result );
		
		$result = str_replace(array('/.  -/', ', ,'), ',', $result);
		$result = str_replace(array('. ,'), '.', $result);
		
		$result = str_replace('"', '', $result);
		$result = preg_replace('/\s+/ius', ' ', $result);
		
		if( $subtitle )
			$this->_sub_title = $result;
		else
			$this->_title = $result;
	
		return $result;
			
	}


	/**
	 * Assign new properties to current program
	 */
	public function setProgram () {

		$this->_program->title = $this->_title;
		$this->_program->alias = $this->_alias;
		$this->_program->sub_title = $this->_sub_title;
		$this->_program->desc_intro = $this->_desc->intro;
		$this->_program->desc_body  = $this->_desc->body;
		
	}


	/**
	 * @param stdClass $data
	 * @param string $log_class
	 */
	protected function updateProgramInfo (stdClass $data, $log_class=null) {
		
		if (!$log_class)
		$log_class = __CLASS__;
		
		$table = new Admin_Model_DbTable_Programs();
		$table_info = $table->info();
		$newData=array();
		foreach ($data as $k=>$v) {
			if (in_array($k, $table_info['cols']))
			$newData[$k]=$v;
		}
		
		if ($this->saveChanges) {
			
			try {	
				$table->update($newData, "`hash`='".$data->hash."'");
				$info = print_r( $newData, true );
				Xmltv_Logger::write( __METHOD__."\n". $info, Zend_Log::INFO, $log_class.'.log' );
			} catch (Exception $e) {
				echo $e->getMessage();
				if (Xmltv_Config::getDebug())
				echo $e->getTrace();
			}
		} else {
			$info = print_r( $newData, true );
			Xmltv_Logger::write( __METHOD__."\n". $info, Zend_Log::INFO, $log_class.'.log' );
		}
		
	}


	/**
	 * @param stdClass $data
	 * @param string $log_class
	 */
	protected function updateProgramProps (stdClass $data, $log_class=null) {

		if (!$log_class)
		$log_class = __CLASS__;
		
		$table = new Admin_Model_DbTable_ProgramsProps();
		$table_info = $table->info();
		$newData=array( 'premiere_date'=>$data->start->toString(DATE_MYSQL) );
		foreach ($data as $k=>$v) {
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
		} else {
			$info = print_r( $newData, true );
			Xmltv_Logger::write( __METHOD__."\n". $info, Zend_Log::INFO, $log_class.'.log' );
		}
		
	}


	/**
	 * @param stdClass $data
	 */
	protected function updateProgramDesc (stdClass $data, $log_class=null) {

		if (!$log_class)
		$log_class = __CLASS__;
		Xmltv_Logger::write( __LINE__.': '. print_r( $data, true ), Zend_Log::INFO, $log_class.'.log' );
		
	}
	
	/**
	 * Check if value matches rules for parser
	 * 
	 * @param string $input
	 * @return bool
	 */
	protected function matches($input=null){
		
		if(  !$input ) throw new Exception( 
		"Не указан параметр для " . __METHOD__, 500 );
		
		return false;
	}
	
	/*
	protected function getCategories(){
		
		$select = $this->_db->select()
			->from($this->_name, 'title');
		$result = $this->_db->query( $select )->fetchAll( Zend_Db::FETCH_OBJ );
		
	}
	*/
	/*
	protected function setCategory($id=null){
		
		if (!$id)
		$this->_category = $this->_cat_id;
		else
		$this->_category = (int)$id;
		
	}
	
	private function _loadPrograms(Zend_Date $start, Zend_Date $end){
		
		$programsTable = new Admin_Model_DbTable_Programs();
		$programs = $programsTable->fetchProgramsForPeriod($start, $end);
		$this->chunks = array_chunk($programs, 500);
		
	}
	*/
	
	/**
	 * @return stdClass
	 */
	protected function setProgramProps(){
		
		$this->_program->title          = (string)$this->_title;
		$this->_program->alias          = (string)$this->_alias;
		$this->_program->sub_title      = (string)$this->_sub_title;
		$this->_program->desc_intro     = (string)$this->_desc->intro;
		$this->_program->desc_body      = (string)$this->_desc->body;
		
	}
	
	protected function updateActors($list=array()) {
		
		if ( empty($list) || !is_array($list))
		throw new Exception("Не указан параметр для ".__METHOD__, 500);
		
		$table = new Admin_Model_DbTable_Actors();
		$tolower = new Zend_Filter_StringToLower();
		
		foreach ($list as $k=>$p) {
				
			$exists=false;
			$parts = explode( ' ', trim($p) );
			
			//var_dump($parts);
			//die(__FILE__.': '.__LINE__);
			
			if (strstr($parts[1], '.')){
				var_dump($parts);
				die(__FILE__.': '.__LINE__);
			}
			
			if( count($parts) == 2 ) {
				
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
								if ($this->saveChanges) {
									$this->updateProgramActors( $sn, $this->_program );
								} else {
									$error_info = print_r( $sn->toArray(), true );
									Xmltv_Logger::write( __METHOD__.":\n". $error_info, Zend_Log::INFO, __CLASS__.'.log' );
								}
							}
						} catch (Exception $e) {
							echo __FUNCTION__.' Ошибка# '.$e->getCode().': '. $e->getMessage();
							die(__FILE__.': '.__LINE__);
						}
					}
				} else {
					
					//die(__FILE__.': '.__LINE__);
					
					$new = $this->addCreditsName( $parts, 'actor', $this->_program );
					$this->updateProgramActors( $new, $this->_program );
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
								if ($this->saveChanges) {
									$this->updateProgramActors( $sn, $this->_program );
								} else {
									$error_info = print_r( $sn->toArray(), true );
									Xmltv_Logger::write( __METHOD__.":\n". $error_info, Zend_Log::INFO, __CLASS__.'.log' );
								}
							} catch (Exception $e) {
								echo __FUNCTION__.' Error# '.$e->getCode().': '. $e->getMessage();
								die(__FILE__.': '.__LINE__);
							}
						}
					}
				} else {
					
					//die(__FILE__.': '.__LINE__);
					
					$new = $this->addCreditsName( $parts, 'actor', $this->_program );
					$this->updateProgramActors( $new, $this->_program );
				}
			
			}  elseif( count( $parts ) > 3 ) {
				/* ошибка в данных */
				unset( $list[$k] );
			}
			
			}
		
	}
	
	protected function updateProgramActors ( stdClass $existing, stdClass $program_info) {
		
		if( !$existing || !$program_info )
		throw new Exception(__METHOD__."Пропущен один или все параметры для ".__LINE__, 500);
		
		$props = new Admin_Model_DbTable_ProgramsProps();
		$serializer = new Zend_Serializer_Adapter_Json();
		
		$p_props = $props->fetchRow("`hash`='".$program_info->hash."'" );
		if( count( $p_props ) == 0 ) {
			try {
				$p_props = $props->createRow();
				$p_props->hash = $program_info->hash;
				$p_props->actors = $serializer->serialize( array($existing->id) );
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
				$p_props->hash = $program_info->hash;
				$persons = !empty($p_props->actors) ? $p_props->actors : '[]' ;
				$persons = $serializer->unserialize( $persons );
				if(  !( in_array( $existing->id, $persons ) ) ) {
					$persons[] = $existing->id;
				}
				$p_props->actors = $serializer->serialize( $persons );
				$props->update( $p_props->toArray(), "`hash`='" . $program_info->hash . "'" );
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
	
	
	protected function addCreditsName ($parts=array(), $type='actor') {
		
		if( empty( $parts ) )
		throw new Exception(__METHOD__."Пропущен один или все параметры для ".__LINE__, 500);
		
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
			die( __METHOD__.": Не могу сохранить запись. ".__LINE__ );
		}
		//die(__FILE__.': '.__LINE__);
	}
	
}