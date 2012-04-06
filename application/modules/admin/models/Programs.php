<?php



/**
 * 
 * @author toshihir
 * @package rutvgid
 *
 */
class Admin_Model_Programs 
	{
	
	private $_trim_options=array('charlist'=>' "\'.,:;-+');
	private $_tolower_options=array('encoding'=>'UTF-8');
	private $_regex_list='/[:;_,\.\[\]\(\)\\`\{\}"\!\+\?]+/';
	protected $debug;
	protected $_tableName = 'rtvg_programs';
	protected $_table;
	protected $_programs_props_table = 'rtvg_programs_props';
	protected $_programs_descs_table = 'rtvg_programs_descriptions';
	protected $_programs_ratings_table = 'rtvg_programs_ratings';

	public function __construct(){
		$this->_table = new Admin_Model_DbTable_Programs();
		$this->_programs_props_table   = new Admin_Model_DbTable_ProgramsProps();
		$this->_programs_descs_table   = new Admin_Model_DbTable_ProgramsDescriptions();
		//$this->_programs_ratings_table = new Admin_Model_DbTable_ProgramsRati
	}

	public function cleanProgramTitle ($title=null) {
		
		if(  !$title )
		throw new Exception("Не указано название программы для ".__METHOD__, 500);
		
		$trim = new Zend_Filter_StringTrim($this->_trim_options);
		$result = $trim->filter($title);
		$regex = new Zend_Filter_PregReplace( array('match'=>'/"/iu', 'replace'=>'') );
		$result = $regex->filter($result);
		return $result;
	}


	public function cleanAlias ($alias=null) {
		
		if( !$alias )
		throw new Exception("Не указано название программы для ".__METHOD__, 500);
		
		//var_dump($title);
		
		//$todash=new Zend_Filter_Word_SeparatorToDash();
		//$result=$todash->filter( $title );
		$result = $alias;
		$regexp = new Zend_Filter_PregReplace(array('match'=>'/[0-9]+-я.*серия\.?/iu', 'replace'=>''));
		$result = $regexp->filter($result);
		$regexp = new Zend_Filter_PregReplace(array('match'=>'/[0-9]+-я.*-.*[0-9]+-я.*серии/iu', 'replace'=>''));
		$result = $regexp->filter($result);
		$regexp = new Zend_Filter_PregReplace(array('match'=>'/[0-9]+-я и [0-9]+-я/iu', 'replace'=>''));
		$result = $regexp->filter($result);
		$regexp = new Zend_Filter_PregReplace(array('match'=>'/часть-[0-9]+-я\.?/iu', 'replace'=>''));
		$result = $regexp->filter($result);
		$regexp = new Zend_Filter_PregReplace(array('match'=>'/[0-9]+-я часть\.?/iu', 'replace'=>''));
		$result = $regexp->filter($result);
		$regexp = new Zend_Filter_PregReplace(array('match'=>'/(сезон-[0-9]+-й\.?)|([0-9]+-й-сезон\.?)/iu', 'replace'=>''));
		$result = $regexp->filter($result);
		$regexp = new Zend_Filter_PregReplace(array('match'=>'/[:;_,\.\[\]\(\)\\`\{\}"\!\+\?]+/', 'replace'=>''));
		$result = $regexp->filter($result);
		$regexp = new Zend_Filter_PregReplace(array('match'=>'/^(.*)-?премьера-?(.*)$/iu', 'replace'=>'\1\2'));
		$result = $regexp->filter($result);
		
		$result = Xmltv_String::str_ireplace('криминальный сериал', '', $result);
		$result = Xmltv_String::str_ireplace('остросюжетный сериал', '', $result);
		$result = Xmltv_String::str_ireplace('остросюжетный детектив', '', $result);
		
		$result=Xmltv_String::str_ireplace( 'ё', 'е', $result );
		$result=Xmltv_String::str_ireplace( 'Ё', 'Е', $result );
		$result=Xmltv_String::str_ireplace( '+', '-плюс-', $result );
		
		$todash=new Zend_Filter_Word_SeparatorToDash();
		$result=$todash->filter( $result );
		
		do {
			$result=str_replace( '--', '-', $result );
		} while(strstr($result, '--'));
		
		$trim = new Zend_Filter_StringTrim(array('charlist'=>'-'));
		$result = $trim->filter($result);
		
		$tolower = new Zend_Filter_StringToLower();
		$result  = $tolower->filter($result);
		/*
		if (Xmltv_String::stristr($alias, '-марии')) {
			var_dump($alias);
			die(__FILE__.': '.__LINE__);
		}
		*/
		//var_dump($result);
		
		return $result;
	}


	public function makeTitles ($info=array()) {
		
		if( empty( $info ) )
		throw new Exception("Не указано название программы для ".__METHOD__, 500);
		
		$tolower = new Zend_Filter_StringToLower( $this->_tolower_options );
		$trim    = new Zend_Filter_StringTrim( $this->_trim_options );
		
		//$info['title']=str_replace( array('"', "'"), '', $info['title'] );
		//$info['title']=$trim->filter( $info['title'] );
		
		$info['sub_title']='';
		
		$info=$this->_checkLive( $info );
		$info=$this->_processSportsTitle( $info );
		$info=$this->_processSportsAnalyticsTitle( $info );
		$info=$this->_processNewsTitle( $info );
		$info=$this->_processDocumentaryTitle( $info );
		$info=$this->_processCartoonsTitle( $info );
		$info=$this->_processMusicTitle( $info );
		$info=$this->_processMiscTitle( $info );
		if( $info['ch_id'] >= 100061 && $info['ch_id'] <= 100065 ) {
			$info=$this->_processKinoreysTitle( $info );
		}
		$info=$this->_checkBreak( $info );
		$info=$this->_checkSeries( $info );
		$info=$this->_checkMovies( $info );
		
		//var_dump($info);
		//die(__FILE__.': '.__LINE__);
		
		
		$info['alias']=$trim->filter( $info['alias'] );
		
		//var_dump($info);
		//die(__FILE__.': '.__LINE__);
		
		return $info;
	}


	public function setProgramCategory ($info=array(), $xml_title=null) {
		
		if( empty( $info ) ) 
		return array();
		
		$categories = new Admin_Model_DbTable_ProgramsCategories();
		$tolower    = new Zend_Filter_StringToLower( $this->_tolower_options );
		
		$cat_list=$categories->fetchAll();
		$exists = false;
		foreach ($cat_list as $c) {
			if( $tolower->filter( $c->title ) == $tolower->filter( $xml_title ) ) {
				$info['category']=$c->id;
				$exists=true;
			}
		}
		
		if (!$exists){
			//die(__FILE__.': '.__LINE__);
			if(  !( strlen( $xml_title ) > 1 ) ) 
			return $info;
			
			try {
				$categories->insert( array('title'=>$xml_title) );
			} catch (Exception $e) {
				echo __FUNCTION__.': '. $e->getMessage();
				die(__FILE__.': '.__LINE__);
			}
		}
		
		//die(__FILE__.': '.__LINE__);
		return $info;
	
	}


	public function savePremiere ($info=array()) {
		
		if( empty( $info ) ) 
		return array();
		
		$programs = new Admin_Model_DbTable_Programs();
		$props    = new Admin_Model_DbTable_ProgramsProps();
		$trim     = new Zend_Filter_StringTrim();
		
		$info['new']=1;
		
		
		if ($info['hash'] != md5('12012-04-07 08:45:002012-04-07 09:00:00')) {
			var_dump($info);
			die(__FILE__.': '.__LINE__);
		}
		
		
		$new = $props->createRow();
		$new->hash=$info['hash'];
		$new->premiere=1;
		$new->premiere_date=$info['start'];
		
		$info['title'] = Xmltv_String::ucfirst( $trim->filter( preg_replace('/премьера[ \.]?/iu', '', $info['title']) ) );
		
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


	public function getCredits ($input=null) {
		
		if(  !$input ) 
		throw new Exception("Не передан параметр для ".__METHOD__, 500);;
		
		$result['actors']=array();
		$result['directors']=array();
		$tolower=new Zend_Filter_StringToLower( $this->_tolower_options );
		if( strstr( $tolower->filter( $input ), 'в ролях' ) ) {
			$d=explode( 'В ролях:', $input );
			//var_dump($d);
			$actors=$d[1];
			$p=explode( 'Режиссер', $actors );
			//var_dump($p);
			$result['actors']=explode( ', ', trim( $p[0], '.…: ' ) );
			$result['directors']=explode( ', ', trim( $p[1], '.…: ' ) );
		
		} elseif( strstr( $tolower->filter( $input ), 'режиссер' ) ) {
			
			$p=explode( 'Режиссер', $input );
			$result['actors']=array();
			$result['directors']=explode( ', ', trim( $p[1], '.…: ' ) );
		
		} else {
			return $result;
			//var_dump($input);
		//die(__FILE__.': '.__LINE__);
		}
		
		return $result;
	
	}


	/**
	 * @param string $desc
	 * @param string $hash
	 * @return void
	 */
	public function parseDescription ($desc=null, $hash=null) {
		
		if( !$hash )
		throw new Exception("Пропущен один или все параметры для ".__METHOD__, 500);
		
		$description  = array('intro'=>'', 'body'=>'');
		
		if ( empty( $desc ) )
		return $description;
		
		$descriptions = new Admin_Model_DbTable_ProgramsDescriptions();
		$desc_len     = Xmltv_String::strlen( $desc );
		$parts=explode( '. ', $desc );
		
		$description['hash']=$hash;
		
		if( $desc_len > 256 ) {
			
			foreach ($parts as $n => $sentence) {
				if( Xmltv_String::stristr( $sentence, 'в ролях' ) ) {
					unset( $parts[$n] );
				}
				if( Xmltv_String::stristr( $sentence, 'режиссер' ) ) {
					unset( $parts[$n] );
				}
				if( trim( Xmltv_String::strlen( $sentence ) ) < 24 ) {
					unset( $parts[$n] );
				}
			}
			//var_dump($parts);
			foreach ($parts as $n => $sentence) {
				if( trim( Xmltv_String::strlen( $description['intro'] ) ) < 164 ) {
					$description['intro'].=$sentence . '. ';
					unset( $parts[$n] );
				}
			}
			
			$description['intro']=trim( $description['intro'] );
			$body=implode( '. ', $parts ) . '.';
			
			if( trim( Xmltv_String::strlen( $body ) ) > 1 ) $description['body']=$body;
			
		} else {
			$description['intro']=implode( '. ', $parts ) . '.';
		}
		
		return $description;
	}
	
	public function saveDescription($description=array(), $hash=null){
		
		if( empty( $description ) | !$hash )
		throw new Exception("Пропущен один или все параметры для ".__METHOD__, 500);
		
		try {
			$this->_programs_descs_table->insert( $description );
		} catch (Exception $e) {
			if ($e->getCode()==1062) {
				$this->_programs_descs_table->update( $description, "`hash`='$hash'" );
			} else {
				echo __FUNCTION__.' Error# '.$e->getCode().': '. $e->getMessage();
				die(__FILE__.': '.__LINE__);
			}
		}
		
	}


	public function cleanDescription ($input=null) {
		
		if(  !$input ) 
		throw new Exception("Пропущен один или все параметры для ".__METHOD__, 500);

		//var_dump($input);
		//die(__FILE__.': '.__LINE__);
		
		$trim = new Zend_Filter_StringTrim(array('charlist'=>' :;.'));
		
		if( strstr( $input, '…' ) ) {
			$d=explode( '…', $input );
			$d=trim( $d[0] );
			$parts=explode( '.', $d );
			return $trim->filter( $parts[0] );
			//$desc['intro']=trim( $parts[0] ) . '. ' . trim( $parts[1] ) . '.';
			//unset( $parts[0] );
			//unset( $parts[1] );
			//$desc['body']=trim( implode( '. ', $parts ) ) . '.';
		
		} else {
			
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
		
		return;
	}


	/**
	 * @param array $credits
	 * @param array $info
	 */
	public function saveCredits ($credits=array(), $info=array()) {
		
		if( empty( $credits ) || empty( $info ) ) 
		throw new Exception("Пропущен один или все параметры для ".__METHOD__, 500);;
		
		$table=new Admin_Model_DbTable_Actors();
		$tolower=new Zend_Filter_StringToLower( $this->_tolower_options );
		$props=new Admin_Model_DbTable_ProgramsProps();
		
		//var_dump(func_get_args());
		//die(__FILE__.': '.__LINE__);
		


		foreach ($credits['actors'] as $k => $p) {
			$exists=false;
			$parts=explode( ' ', $p );
			if( count( $parts ) == 3 ) {
				
				$snames=$table->fetchAll( 
				"`f_name` LIKE '%" . $tolower->filter( $parts[0] ) . "%' 
					AND `m_name` LIKE '%" . $tolower->filter( $parts[1] ) . "%' 
					AND `s_name` LIKE '%" . $tolower->filter( $parts[2] ) . "%'" );
				
				if( count( $snames ) > 0 ) {
					foreach ($snames as $sn) {
						$existing=$tolower->filter( sprintf( "%s %s %s", $sn->f_name, $sn->m_name, $sn->s_name ) );
						$exists=false;
						if( $existing == $tolower->filter( implode( ' ', $parts ) ) ) {
							$exists=true;
							$existing=$sn->toArray();
							break;
						}
					}
				}
				
				if(  !$exists ) {
					$existing=$this->_addCreditsName( $parts, 'actor', $info );
				}
				$this->_updateProgramActors( $existing, $info );
			
			} elseif( count( $parts ) == 2 ) {
				
				$snames=$table->fetchAll( 
				"`f_name` LIKE '%" . $tolower->filter( $parts[0] ) . "%' AND `s_name` LIKE '%" . $tolower->filter( 
				$parts[1] ) . "%'" );
				if( count( $snames ) > 0 ) {
					foreach ($snames as $sn) {
						$existing=$tolower->filter( sprintf( "%s %s", $sn->f_name, $sn->s_name ) );
						$exists=false;
						if( $existing == $tolower->filter( implode( ' ', $parts ) ) ) {
							$exists=true;
							$existing=$sn->toArray();
							break;
						}
					}
				}
				
				if( $exists === false ) {
					$this->_addCreditsName( $parts, 'actor', $info );
				} else {
					$this->_updateProgramActors( $existing, $info );
				}
			
			} elseif( count( $parts ) > 3 ) {
				/* ошибка в данных */
				unset( $credits['actors'][$k] );
			} 
		
		}
		
		$table=new Admin_Model_DbTable_Directors();
		foreach ($credits['directors'] as $k => $p) {
			$exists=false;
			$parts=explode( ' ', $p );
			if( count( $parts ) == 2 ) {
				$snames=$table->fetchAll( 
				"`f_name` LIKE '%" . $tolower->filter( $parts[0] ) . "%' AND `s_name` LIKE '%" . $tolower->filter( 
				$parts[1] ) . "%'" );
				if( count( $snames ) > 0 ) {
					foreach ($snames as $sn) {
						$existing=$tolower->filter( sprintf( "%s %s", $sn->f_name, $sn->s_name ) );
						$exists=false;
						if( $existing == $tolower->filter( implode( ' ', $parts ) ) ) {
							$exists=true;
							$existing=$sn->toArray();
							break;
						}
					}
				}
				
				if( $exists === false ) {
					$this->_addCreditsName( $parts, 'director', $info );
				} else {
					$this->_updateProgramDirectors( $existing, $info );
				}
			} elseif( count( $parts ) == 3 ) {
				$snames=$table->fetchAll( 
				"`f_name` LIKE '%" . $tolower->filter( $parts[0] ) . "%' 
					AND `m_name` LIKE '%" . $tolower->filter( $parts[1] ) . "%' 
					AND `s_name` LIKE '%" . $tolower->filter( $parts[2] ) . "%'" );
				
				if( count( $snames ) > 0 ) {
					foreach ($snames as $sn) {
						$existing=$tolower->filter( sprintf( "%s %s %s", $sn->f_name, $sn->m_name, $sn->s_name ) );
						$exists=false;
						if( $existing == $tolower->filter( implode( ' ', $parts ) ) ) {
							$exists=true;
							$existing=$sn->toArray();
							break;
						}
					}
				}
				
				if(  !$exists ) {
					$existing=$this->_addCreditsName( $parts, 'director', $info );
				}
				$this->_updateProgramDirectors( $existing, $info );
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
		
		if( $type == 'actor' ) $table=new Admin_Model_DbTable_Actors();
		if( $type == 'director' ) $table=new Admin_Model_DbTable_Directors();
		
		$found=false;
		try {
			
			if( count( $parts ) == 2 ) {
				$found=$table->fetchRow( "`f_name`='%" . $parts[0] . "%' AND `s_name`='%" . $parts[1] . "%'" );
				if(  !$found ) $new=$table->createRow( array('f_name'=>$parts[0], 's_name'=>$parts[1]) );
			}
			
			if( count( $parts ) == 3 ) {
				$found=$table->fetchRow( 
				"`f_name`='%" . $parts[0] . "%' AND `m_name`='%" . $parts[1] . "%' AND `s_name`='%" . $parts[2] . "%'" );
				if(  !$found ) $new=$table->createRow( 
				array('f_name'=>$parts[0], 'm_name'=>$parts[1], 's_name'=>$parts[2]) );
			}
			
			$new=$new->save();
			$new=$table->find( $new )->current()->toArray();
			return $new;
		
		} catch (Exception $e) {
			echo "Не могу сохранить запись актера";
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
		
		$props = new Admin_Model_DbTable_ProgramsProps();
		$serializer = new Zend_Serializer_Adapter_Json();
		
		$p_props = $props->find( $info['alias'] );
		
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
				$p_props = $p_props->current()->toArray();
				$p_props['hash'] = $info['hash'];
				$persons = $serializer->unserialize( $p_props['actors'] );
				if(  !( in_array( $existing['id'], $persons ) ) ) {
					$persons[] = $existing['id'];
				}
				$p_props['actors'] = $serializer->serialize( $persons );
				$props->update( $p_props, "`title_alias`='" . $info['alias'] . "'" );
			} catch (Exception $e) {
				if( $e->getCode() == 0 ) {
					$p_props['actors'] = $serializer->serialize( array($existing['id']) );
					try {
						$props->update( $p_props, "`title_alias`='" . $info['alias'] . "'" );
					} catch (Exception $e) {
						echo "Не могу обновить актера: " . $e->getMessage();
						die( __FILE__ . ': ' . __LINE__ );
					}
				}
			}
			//$p_props['actors'] = $serializer->serialize( $actors );
		//$props->update( $p_props, "`title_alias`='" . $info['alias'] . "'" );
		

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
		$p_props = $props->find( $info['alias'] );
		
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
				$p_props = $p_props->current()->toArray();
				$p_props['hash'] = $info['hash'];
				$persons = $serializer->unserialize( $p_props['directors'] );
				if(  !( in_array( $existing['id'], $persons ) ) ) {
					$persons[] = $existing['id'];
				}
				$p_props['directors'] = $serializer->serialize( $persons );
				$props->update( $p_props, "`hash`='" . $info['hash'] . "'" );
			} catch (Exception $e) {
				if( $e->getCode() == 0 ) {
					$p_props['directors'] = $serializer->serialize( array($existing['id']) );
					try {
						$props->update( $p_props, "`hash`='" . $info['hash'] . "'" );
					} catch (Exception $e) {
						echo "Не могу обновить режиссера: " . $e->getMessage();
						die( __FILE__ . ': ' . __LINE__ );
					}
				}
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


	private function _processSportsTitle ($info=array()) {
		
		if( empty( $info ) ) 
		throw new Exception("Пропущен один или все параметры для ".__METHOD__, 500);
		
		$trim=new Zend_Filter_StringTrim( $this->_trim_options );
		
		if( Xmltv_String::stristr($info['title'], 'чемпионат ')
		|| Xmltv_String::stristr( $info['title'], 'кубок ' )
		|| Xmltv_String::stristr( $info['title'], 'лига чемпионов' )
		|| Xmltv_String::stristr( $info['title'], 'американский футбол' ) 
		|| Xmltv_String::stristr( $info['title'], 'мини-футбол' ) 
		|| Xmltv_String::stristr( $info['title'], 'хокке'  ) 
		|| Xmltv_String::stristr( $info['title'], 'баскетбол' ) 
		|| Xmltv_String::stristr( $info['title'], 'теннис' ) 
		|| Xmltv_String::stristr( $info['title'], 'волейбол' ) 
		|| Xmltv_String::stristr( $info['title'], 'гандбол' ) 
		|| Xmltv_String::stristr( $info['title'], 'регби' ) 
		|| Xmltv_String::stristr( $info['title'], 'биатлон' ) 
		|| Xmltv_String::stristr( $info['title'], 'снукер' )
		|| Xmltv_String::stristr( $info['title'], 'фигурное катание' )
		|| Xmltv_String::stristr( $info['title'], 'автоспорт' )
		|| Xmltv_String::stristr( $info['title'], 'тимберспорт' )
		|| preg_match( '/плей[- ]?офф/iu', $info['title'] )
		|| Xmltv_String::stristr( $info['title'], 'бокс' )
		|| preg_match( '/^борьба\.? (.+)$/iu', $info['title'] ) 
		|| preg_match( '/^.+\. Бойцовский клуб\.? (.+)$/u', $info['title'] ) 
		|| preg_match( '/^.* ?бокс\.? (.+)$/iu', $info['title'] ) 
		|| preg_match( '/^л[её]гкая атлетика\.? (.+)$/iu', $info['title'] ) 
		|| preg_match( '/^(стиль\.)? спортивные танцы\.? (.+)$/iu', $info['title'] ) ) {
			/*
			if (Xmltv_String::stristr( $info['title'], 'бокс' )) {
				var_dump($info['title']);
				var_dump(strstr($info['title'], '.'));
				die(__FILE__.': '.__LINE__);
			}
			*/
			if (strstr($info['title'], '.')) {
				$parts = explode( '.', $info['title'] );
					
				//var_dump($parts);
				if (Xmltv_String::strlen($trim->filter( $parts[1] ))==1 && isset($parts[2])) {
					$info['title'] = $trim->filter( $parts[0] ) . '. ' . $trim->filter( $parts[1] ).'. '.$trim->filter( $parts[2] );
					unset( $parts[2] );
				} else {
					$info['title'] = $trim->filter( $parts[0] ) . '. ' . $trim->filter( $parts[1] );
				}
				unset( $parts[0] );
				unset( $parts[1] );
				$info['sub_title'] = strlen( $trim->filter( implode( '. ', $parts ) ) ) > 1 . '.' ? $trim->filter( 
				implode( '. ', $parts ) . '.' ) : '';
				/*if ($info['sub_title']!='') {
					var_dump($info);
					die(__FILE__.': '.__LINE__);
				}*/
				$info = $this->setProgramCategory( $info, 'Спорт' );
			} else {
				$info['sub_title'] = '';
				$info = $this->setProgramCategory( $info, 'Спорт' );
			}
		} 
		return $info;
	}


	private function _processDocumentaryTitle ($info=array()) {
		
		if( empty( $info ) ) 
		throw new Exception("Пропущен один или все параметры для ".__METHOD__, 500);
		
		$trim=new Zend_Filter_StringTrim( $this->_trim_options );
		
		if( Xmltv_String::stristr( $info['title'], 'дворцы европы'  ) ) {
			$parts=explode( '.', $info['title'] );
			$info['title']=$trim->filter( $parts[0] );
			$info['sub_title']=$trim->filter( $parts[1] );
			$info=$this->setProgramCategory( $info, 'Документальный фильм' );
		} elseif( preg_match( '/^(aсademia\.) (.+\.?)$/iu', $info['title'], $m ) ) {
			$parts=explode( '.', $m[2] );
			$info['title']=$trim->filter( $m[1] ) . ' ' . $trim->filter( $parts[0] );
			$info['sub_title']=$trim->filter( $parts[1] );
			$info=$this->setProgramCategory( $info, 'Документальный фильм' );
		} elseif( preg_match( '/(хроники московского быта\.?)(.*)/iu', $info['title'], $m ) 
		|| preg_match( '/(тайны нашего кино\.?)(.*)/iu', $info['title'], $m ) ) {
			$info['title']=@isset( $m[1] ) ? $m[1] : $info['title'];
			$info['sub_title']=@isset( $m[2] ) ? $m[2] : '';
			$info=$this->setProgramCategory( $info, 'Документальный фильм' );
		} elseif (Xmltv_String::stristr($info['title'], 'документал')) {
			$info=$this->setProgramCategory( $info, 'Документальный фильм' );
		}
		
		return $info;
	}


	private function _checkMovies($info=array()){
		
		if( empty( $info ) ) 
		throw new Exception("Пропущен один или все параметры для ".__METHOD__, 500);
		
		$trim    = new Zend_Filter_StringTrim( $this->_trim_options );
		$tolower = new Zend_Filter_StringToLower( $this->_tolower_options );
		$serializer = new Zend_Serializer_Adapter_Json();
		
		if (Xmltv_String::stristr($info['title'], 'в многосерийном фильме') ) {
			
			$info = $this->setProgramCategory($info, 'Многосерийный фильм');
			$actors_table = new Admin_Model_DbTable_Actors();
			$props_table = new Admin_Model_DbTable_ProgramsProps();
			
			if (preg_match_all('/(\p{Cyrillic}+ \p{Cyrillic}+,? ? в?)+/iu', $info['title'], $m)) {
				
				$actors = Xmltv_String::str_ireplace(' в', '', $m[0][0]);
				if (Xmltv_String::stristr($actors, ',')) {
					
					if ($actors = explode(',', $actors)) {
						
						foreach ($actors as $k=>$a) {
								
							$aa = explode(' ', trim($a));
							if ( count( $aa==2 ) ) {
								
								$aaa['f_name']=trim($aa[0]);
								$aaa['s_name']=trim($aa[1]);
								$actor_id = $this->_saveActor($aaa);
								$props = $this->_addActorToProps($actor_id, $info);
								
							}
						}
						
						$parts = explode('в многосерийном фильме', $info['title']);
						if( Xmltv_String::stristr($parts[0], 'премьера') ) {
							$info['title']='Премьера. ';
						}
						$info['title'].= trim($parts[1]);
						$info['alias'] = $this->cleanAlias(trim($parts[1]));

						
					} else {
						//var_dump($info);
						die(__FILE__.': '.__LINE__);
					}
					
				} elseif (preg_match('/^премьера. (\p{Cyrillic}+ \p{Cyrillic}+) в многосерийном фильме "(.+)"/iu', $info['title'], $m)) {
					$actor = explode( ' ', trim( $m[1] ) );
					$actor_id = $this->_saveActor(array(
						'f_name'=>trim($actor[0]),
						's_name'=>trim($actor[1])
					));
					$props = $this->_addActorToProps($actor_id, $info);
					$info['title']='Премьера. "'.$m[2].'"';
					$info['alias']= $this->cleanAlias($m[2]);
					
				} elseif(preg_match('/Премьера. Фильм (\p{Cyrillic}+ \p{Cyrillic}+) "(.+)"/ius', $info['title'], $m)) {
					//var_dump($m);
					die(__FILE__.': '.__LINE__);
				}  else {
					//var_dump($info);
					die(__FILE__.': '.__LINE__);
				}
				
				//$parts = explode('. ', $info['title']);
				
				
			} else {
				//var_dump($info);
				die(__FILE__.': '.__LINE__);
			}
		}
		
		if (Xmltv_String::stristr($info['title'], 'в детективе') ) {
			$info = $this->setProgramCategory($info, 'Остросюжетный детектив');
			if(preg_match('/^Премьера. (\p{Cyrillic}+ \p{Cyrillic}+) в детективе "(.+)" из цикла "(.+)"/iu', $info['title'], $m)) {
				$actor = explode(' ', trim( $m[1] ) );
				if (count($actor)==2) {
					$actor_id = $this->_saveActor(array(
						'f_name'=>$actor[0],
						's_name'=>$actor[1]
					));
					$this->_addActorToProps($actor_id, $info);
				}
				
				$info['title'] = 'Премьера. "'.$m[3].'. '.$m[2].'"';
				$info['alias'] = $this->cleanAlias($info['title']);
			}
		}
		
		if (Xmltv_String::stristr($info['title'], 'многосерийный фильм') ) {
			$info = $this->setProgramCategory($info, 'Многосерийный фильм');
			$info['alias'] = Xmltv_String::str_ireplace('многосерийный фильм', '', $info['title']);
			$parts = explode('в детективе', $info['title']);
			//var_dump($parts);
			die(__FILE__.': '.__LINE__);
		}
		
		if (Xmltv_String::stristr($info['title'], 'криминальный сериал')) {
			$info = $this->setProgramCategory($info, 'Криминальный сериал');
			$info['alias'] = Xmltv_String::str_ireplace('криминальный сериал', '', $info['title']);
			$parts = explode('в детективе', $info['title']);
			//var_dump($parts);
			die(__FILE__.': '.__LINE__);
		}
		
		if (Xmltv_String::stristr($info['title'], 'остросюжетный детектив')) {
			$info = $this->setProgramCategory($info, 'Остросюжетный детектив');
			$info['alias'] = $this->cleanAlias( $info['title'] );
			$info['title'] = Xmltv_String::str_ireplace('остросюжетный детектив', '', $info['title']);
		}
		
		$info['alias'] = $trim->filter($info['alias']);
		return $info;
		
	}
	
	private function _checkSeries ($info=array()) {
		
		if( empty( $info ) ) 
		throw new Exception("Пропущен один или все параметры для ".__METHOD__, 500);
		
		$trim    = new Zend_Filter_StringTrim( $this->_trim_options );
		$tolower = new Zend_Filter_StringToLower( $this->_tolower_options );
		
		if( (preg_match( '/[0-9]+-я /iu', $info['title'] ) || preg_match( '/[0-9]+-я /iu', $info['sub_title'] )) ||
			( preg_match( '/сери[яиал]/iu', $info['title'] ) || preg_match( '/сери[яиал]/iu', $info['sub_title'] ) ) ) {
			
			$info = $this->setProgramCategory( $info, 'Сериал' );
			$info['alias'] = $this->cleanAlias($info['title']);
			/*
			if ($info['hash']=='9a8298a407354469ac9a2e9a620da9b9') {
				var_dump($info);
				die(__FILE__.": ".__LINE__);
			}
			*/
			//var_dump($info);
			//die(__FILE__.': '.__LINE__);
		}
		return $info;
	}


	private function _checkLive ($info = array()) {
		
		if( empty( $info ) ) 
		throw new Exception("Пропущен один или все параметры для ".__METHOD__, 500);
		
		$tolower = new Zend_Filter_StringToLower( $this->_tolower_options );
		$trim = new Zend_Filter_StringTrim( $this->_trim_options );
		
		if( preg_match( '/(.+\.?) прям(ая|ой) (трансляция|эфир)/iu', $info['title'], $m ) 
		|| preg_match( '/(.+\.?) трансляция из.+$/iu', $info['title'], $m ) 
		|| Xmltv_String::stristr( $info['title'], 'live' )) {
			$info['title'] = $trim->filter( Xmltv_String::str_ireplace( 'live', '', $info['title'] ) );
			$info['live'] = 1;
			$parts = explode( '.', $m[1] );
			$info['title'] = $trim->filter( $parts[0] ) . '. ' . $trim->filter( $parts[1] );
			unset( $parts[0] );
			unset( $parts[1] );
			$info['sub_title'] = implode( '. ', $parts );
		}
		return $info;
	}


	private function _checkBreak ($info=array()) {
		
		if( empty( $info ) )
		throw new Exception("Пропущен один или все параметры для ".__METHOD__, 500);
		
		if( preg_match( '/^внимание\!?.+$/iu', $info['title'] ) 
		|| Xmltv_String::stristr( $info['title'], 'профилактика' ) 
		|| Xmltv_String::stristr( $info['title'], 'канал заканчивает' ) ) {
			$info['title']='Перерыв';
			$info['sub_title']='';
		}
		return $info;
	
	}


	private function _processCartoonsTitle ($info=array()) {
		
		if( empty( $info ) ) 
		throw new Exception("Пропущен один или все параметры для ".__METHOD__, 500);
		
		if( Xmltv_String::stristr( $info['title'], 'мультф') 
		|| Xmltv_String::stristr( $info['title'], 'мультиплик') ) {
			$info=$this->setProgramCategory( $info, 'Мультфильм' );
		}
		return $info;
	}


	private function _processMusicTitle ($info=array()) {
		
		if( empty( $info ) ) 
		throw new Exception("Пропущен один или все параметры для ".__METHOD__, 500);
		
		if( preg_match( '/ ?музыка /isu', $info['title'] ) 
		|| preg_match( '/ ?клип(ы)? /isu', $info['title'] ) 
		|| preg_match( '/ ?music ?/isu', $info['title'] ) ) {
			$info=$this->setProgramCategory( $info, 'Музыка' );
		}
		return $info;
	
	}


	private function _processMiscTitle ($info=array()) {
		
		if( empty( $info ) ) 
		throw new Exception("Пропущен один или все параметры для ".__METHOD__, 500);
		
		$trim=new Zend_Filter_StringTrim( array('charlist'=>$this->_trim_thars) );
		
		if( preg_match( '/^(\.\.\.).*(.+).*(\.\.\.)$/iu', $info['title'], $m ) ) {
			$info['title']=Xmltv_String::ucfirst( $trim->filter( $m[2] ) );
		}
		if( preg_match( '/^(И другие)\.\.\.(.+)$/iu', $info['title'], $m ) ) {
			$info['title']=Xmltv_String::ucfirst( $trim->filter( $m[2] ) ) . ' ' . Xmltv_String::strtolower( 
			$trim->filter( $m[1] ) );
		}
		if( preg_match( '/(.+)\((.+)\)/', $info['title'], $m ) ) {
			$info['title']=$trim->filter( $m[1] );
			$info['sub_title']=$trim->filter( $m[2] );
		}
		return $info;
	}


	private function _processKinoreysTitle ($info=array()) {
		
		if( empty( $info ) ) 
		throw new Exception("Пропущен один или все параметры для ".__METHOD__, 500);
		
		return $info;
	
	}
	
	private function _processSportsAnalyticsTitle($info=array()){
		
		if( empty( $info ) )
		throw new Exception("Пропущен один или все параметры для ".__METHOD__, 500);
		
		if( Xmltv_String::stristr($info['title'], 'обзор матчей') ) {
			$info=$this->setProgramCategory( $info, 'Спортивная аналитика' );
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
		
		$result = $this->_table->fetchAll(array(
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
	
	public function parseProgramXml($xml=null){
		
		if( !$xml )
		throw new Exception("Пропущен один или все параметры для ".__METHOD__, 500);
		
		$info    = array();
		$attrs   = $xml->attributes();
		$tolower = new Zend_Filter_StringToLower();
		/*
		 * calcuate dates
		 */
		$d = (string)$attrs->start;
		$f = Zend_Date::YEAR."-".Zend_Date::MONTH."-".Zend_Date::DAY." ".
			Zend_Date::HOUR.':'.Zend_Date::MINUTE.':'.Zend_Date::SECOND.' '.Zend_Date::GMT_DIFF;
		$date_str = $this->_getDateString($d);
		$dates['start'] = new Zend_Date($date_str, $f);
		$d = (string)$attrs->stop;
		$date_str = $this->_getDateString($d);
		$dates['end'] = new Zend_Date($date_str, $f);
		$info ['start'] = $dates['start']->toString("yyyy-MM-dd HH:mm:ss");
		$info ['end']   = $dates['end']->toString("yyyy-MM-dd HH:mm:ss");
		/*
		 * channel ID
		 */
		$info ['ch_id'] = (int)$attrs->channel;
		/*
		 * category
		 */
		$cat_title = @isset($xml->category) ? (string)$xml->category : 0 ;
		$info = $this->setProgramCategory($info, $cat_title);
		/*
		 * program title
		 */
		$info ['title'] = ( string ) $xml->title;
		$info = $this->makeTitles ( $info );
		
		//var_dump($info);
		//die(__FILE__.': '.__LINE__);
		
		/*
		 * program alias
		 */
		$a = isset($info ['alias']) && !empty($info['alias']) ? $info['alias'] : $info['title'] ;
		//if (empty($a)) {
		$info ['alias'] = $this->cleanAlias ( $a );
		//}
		//var_dump($info);
		//die(__FILE__.': '.__LINE__);
		/*
		 * program hash
		 */
		$info ['hash']  = $this->getHash ( (int)$attrs->channel, $info ['start'], $info ['end'] );
		/*
		if ($info['hash']=='9a8298a407354469ac9a2e9a620da9b9') {
			var_dump($info);
			die(__FILE__.": ".__LINE__);
		}
		*/
		/*
		 * premiere processing
		 */
		if (Xmltv_String::stristr($tolower->filter((string)$xml->title), 'премьера') ||
		Xmltv_String::stristr($tolower->filter((string)$xml->title), 'premiere'))
		$info = $this->savePremiere($info);
		
		//var_dump($info);
		//die(__FILE__.': '.__LINE__);
		
		return $info;
		
	}
	
	private function _getDateString($input=null){
		if(!$input) return;
		$date['year']      = substr($input, 0, 4);
		$date['month']     = substr($input, 4,2);
		$date['day']       = substr($input, 6,2);
		$date['hours']     = substr($input, 8,2);
		$date['minutes']   = substr($input, 10,2);
		$date['seconds']   = substr($input, 12,2);
		$date['gmt_diff']  = substr($input, 16,4);
		return $date['year'].'-'.$date['month'].'-'.$date['day'].' '.$date['hours'].':'.$date['minutes'].':'.$date['seconds'].' '.$date['gmt_diff'];
		
	}
	
	private function _saveActor($data=array()){
		
		if (empty($data))
		throw new Exception("Пропущен один или все параметры для ".__METHOD__, 500);
		
		$table = new Admin_Model_DbTable_Actors();
		$actor_found = $table->fetchRow(array(
			"`f_name`='".$data['f_name']."'",
			"`s_name`='".$data['s_name']."'"
		))->toArray();
		
		if (empty($actor_found)) {
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
		
		//die(__FILE__.': '.__LINE__);
		
		//return $props; 
	}
	
	public function saveProgram($info=array()){
		
		if (empty($info) || !is_array($info))
		throw new Exception("Пропущен один или все параметры для ".__METHOD__, 500);
		
		try {
			$hash = $this->_table->insert($info);
		} catch (Exception $e) {
			if ($e->getCode()==1062) {
				$this->_table->update($info, "`hash`='".$info['hash']."'");
				$hash = $info['hash'];
			} else {
				echo __METHOD__.' error#'.$e->getCode().': '.$e->getMessage();
				die(__FILE__.': '.__LINE__);
			}
		}
		
		if (!$hash)
		throw new Exception("Ошибка сохранения в ".__METHOD__, 500);
		
		return $hash;
	}
	
	public function findProgram($hash=null){
	
		if (!$hash)
		throw new Exception("Пропущен один или все параметры для ".__METHOD__, 500);
		
		return $this->_table->find($hash)->current();
		
	}
	
	
}

