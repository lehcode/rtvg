<?php



/**
 * 
 * @author toshihir
 * @package rutvgid
 *
 */
class Admin_Model_Programs 
	{
	
	protected $debug;
	protected $_tableName = 'rtvg_programs';
	protected $_table;
	protected $_programs_props_table = 'rtvg_programs_props';
	protected $_programs_descs_table = 'rtvg_programs_descriptions';
	protected $_programs_ratings_table = 'rtvg_programs_ratings';
	
	private $_trim_options=array('charlist'=>' -');
	private $_tolower_options=array('encoding'=>'UTF-8');
	private $_regex_list='/[:;_,\.\[\]\(\)\\`\{\}"\!\+\?]+/';
	private $_logger;

	/**
	 * Model onstructor
	 */
	public function __construct(){
		$this->_table = new Admin_Model_DbTable_Programs();
		$this->_programs_props_table   = new Admin_Model_DbTable_ProgramsProps();
		$this->_programs_descs_table   = new Admin_Model_DbTable_ProgramsDescriptions();
		$this->_logger = new Zend_Log( new Zend_Log_Writer_Stream( ROOT_PATH . '/log/programs.log' ) );
	}
	
	public function archivePrograms(Zend_Date $start, Zend_Date $end){
		
		if (!is_a($start, 'Zend_Date') || !isset($start))
			throw new Exception(__METHOD__." - Wrong start date!", 500);
			
		if (!is_a($end, 'Zend_Date') || !isset($end))
			throw new Exception(__METHOD__." - Wrong end date!", 500);
		
		
		$start = $start->addDay(1);
		
		$where = "`start`<'".$start->toString("YYYY-MM-dd 00:00:00")."'";
		if ($end) {
			$where .= " AND `end`>='".$end->toString("YYYY-MM-dd 00:00:00")."'";
		}
		
		var_dump($where);
		//die(__FILE__.': '.__LINE__);
		
		$batch_size = 500;
		$programsAcrhive     = new Admin_Model_DbTable_ProgramsArchive();
		$descriptionsAcrhive = new Admin_Model_DbTable_ProgramsDescriptionsArchive();
		$descriptions        = new Admin_Model_DbTable_ProgramsDescriptions();
		
		$list = $this->_table->fetchAll($where);
		
		ini_set('max_execution_time', 0);
		
		do {
			
			if (count($list)>0){
				
				var_dump(count($list));
				//die(__FILE__.': '.__LINE__);
				
				foreach ($list as $i) {
					
					//var_dump($i);
					//die(__FILE__.': '.__LINE__);
					
					$descData = array();
					$descRow = $descriptions->fetchRow("`hash`='".$i->hash."'");
					if (!empty($descRow)) {
						$descData = $descRow->toArray();
					}
					$programData = $i->toArray();
					
					try {
						$programsAcrhive->insert($programData);
						if (!empty($descData)) {
							try {
								$descriptionsAcrhive->insert($descData);
							} catch (Exception $e) {
								echo $e->getMessage();
								exit();
							}
						}
					} catch (Exception $e) {
						try {
							$programsAcrhive->update($programData, "`hash`='".$programData['hash']."'");
							if (!empty($descData)) {
								try {
									$descriptionsAcrhive->update($descData, "`hash`='".$programData['hash']."'");
								} catch (Exception $e) {
									echo $e->getMessage();
								exit();
								}
								
							}
						} catch (Exception $e) {
							throw new Exception($e->getMessage(), 500, true);
							exit();
						}
					}
					
					
					try {
						$this->_table->delete("`hash`='".$programData['hash']."'");
					} catch (Exception $e) {
						echo $e->getMessage();
						exit();
					}
					
					
					if (!empty($descData)) {
						try {
							$descriptions->delete("`hash`='".$programData['hash']."'");
						} catch (Exception $e) {
							echo $e->getMessage();
							exit();
						}
						
					}
				}
			} else {
				echo "За этот период программ не найдено";
				exit();
			}
			
		} while(!count($list)>0);
		
		return true;
		
		
	}
	
	
	
	public function makeAlias($input=null){
		
		return $this->_makeAlias($input);
	}
	
	public function makeTitles ($info=array()) {
		
		if( empty( $info ) )
		throw new Exception("Не указано название программы для ".__METHOD__, 500);
		
		$tolower = new Zend_Filter_StringToLower( $this->_tolower_options );
		
		$info['title'] = str_replace('...', '…', $info['title']);
		if (Xmltv_Config::getDebug()) {
			$trimmed = trim($info['title'], ' -');
			if (empty($trimmed)) {
				if (Xmltv_Config::getDebug()) {
					var_dump(func_get_args());
					var_dump($info);
					die(__FILE__.': '.__LINE__);
				} else
				throw new Exception(__LINE__.": Пустое название в ".__METHOD__, 500);
			}
		}
		
		$info['sub_title']='';
		
		$info = $this->_checkLive( $info );
		if (Xmltv_Config::getDebug()) {
			$trimmed = trim($info['title'], ' -');
			if (empty($trimmed)) {
				if (Xmltv_Config::getDebug()) {
					var_dump(func_get_args());
					var_dump($info);
					die(__FILE__.': '.__LINE__);
				} else
				throw new Exception(__LINE__.": Пустое название в ".__METHOD__, 500);
			}
		}
		
		$info = $this->_processSportsTitle( $info );
		if (Xmltv_Config::getDebug()) {
			$trimmed = trim($info['title'], ' -');
			if (empty($trimmed)) {
				if (Xmltv_Config::getDebug()) {
					var_dump(func_get_args());
					var_dump($info);
					die(__FILE__.': '.__LINE__);
				} else
				throw new Exception(__LINE__.": Пустое название в ".__METHOD__, 500);
			}
		}
		
		$info = $this->_processSportsAnalyticsTitle( $info );
		if (Xmltv_Config::getDebug()) {
			$trimmed = trim($info['title'], ' -');
			if (empty($trimmed)) {
				if (Xmltv_Config::getDebug()) {
					var_dump(func_get_args());
					var_dump($info);
					die(__FILE__.': '.__LINE__);
				} else
				throw new Exception(__LINE__.": Пустое название в ".__METHOD__, 500);
			}
		}
		
		$info = $this->_processNewsTitle( $info );
		if (Xmltv_Config::getDebug()) {
			$trimmed = trim($info['title'], ' -');
			if (empty($trimmed)) {
				if (Xmltv_Config::getDebug()) {
					var_dump(func_get_args());
					var_dump($info);
					die(__FILE__.': '.__LINE__);
				} else
				throw new Exception(__LINE__.": Пустое название в ".__METHOD__, 500);
			}
		}
		
		$info = $this->_processDocumentaryTitle( $info );
		if (Xmltv_Config::getDebug()) {
			$trimmed = trim($info['title'], ' -');
			if (empty($trimmed)) {
				if (Xmltv_Config::getDebug()) {
					var_dump(func_get_args());
					var_dump($info);
					die(__FILE__.': '.__LINE__);
				} else
				throw new Exception(__LINE__.": Пустое название в ".__METHOD__, 500);
			}
		}
		
		$info = $this->_processCartoonsTitle( $info );
		if (Xmltv_Config::getDebug()) {
			$trimmed = trim($info['title'], ' -');
			if (empty($trimmed)) {
				if (Xmltv_Config::getDebug()) {
					var_dump(func_get_args());
					var_dump($info);
					die(__FILE__.': '.__LINE__);
				} else
				throw new Exception(__LINE__.": Пустое название в ".__METHOD__, 500);
			}
		}
		
		$info = $this->_processMusicTitle( $info );
		if (Xmltv_Config::getDebug()) {
			$trimmed = trim($info['title'], ' -');
			if (empty($trimmed)) {
				if (Xmltv_Config::getDebug()) {
					var_dump(func_get_args());
					var_dump($info);
					die(__FILE__.': '.__LINE__);
				} else
				throw new Exception(__LINE__.": Пустое название в ".__METHOD__, 500);
			}
		}
		
		
		if( $info['ch_id'] >= 100061 && $info['ch_id'] <= 100065 ) {
			$info = $this->_processKinoreysTitle( $info );
			if (Xmltv_Config::getDebug()) {
				$trimmed = trim($info['title'], ' -');
				if (empty($trimmed)) {
					if (Xmltv_Config::getDebug()) {
						var_dump(func_get_args());
						var_dump($info);
						die(__FILE__.': '.__LINE__);
					} else
					throw new Exception(__LINE__.": Пустое название в ".__METHOD__, 500);
				}
			}
		}
		
		$info = $this->_processSeries( $info );
		if (Xmltv_Config::getDebug()) {
			$trimmed = trim($info['title'], ' -');
			if (empty($trimmed)) {
				if (Xmltv_Config::getDebug()) {
					var_dump(func_get_args());
					var_dump($info);
					die(__FILE__.': '.__LINE__);
				} else
				throw new Exception(__LINE__.": Пустое название в ".__METHOD__, 500);
			}
		}
		
		$info = $this->_processMovies( $info );
		if (Xmltv_Config::getDebug()) {
			$trimmed = trim($info['title'], ' -');
			if (empty($trimmed)) {
				if (Xmltv_Config::getDebug()) {
					var_dump(func_get_args());
					var_dump($info);
					die(__FILE__.': '.__LINE__);
				} else
				throw new Exception(__LINE__.": Пустое название в ".__METHOD__, 500);
			}
		}
		
		$info = $this->_processBreak( $info );
		
		$trimmed = trim($info['title'], ' -');
		if (empty($info['title'])) {
			if (Xmltv_Config::getDebug()) {
				var_dump($info);
				die(__FILE__.': '.__LINE__);
			} else
			throw new Exception(__LINE__.": Пустое название в ".__METHOD__, 500);
		} else {
			$info['title'] = $this->_cleanProgramTitle( $info['title'] );
			if (empty($info['title'])) {
				if (Xmltv_Config::getDebug()) {
					var_dump($info);
					die(__FILE__.': '.__LINE__);
				} else
				throw new Exception(__LINE__.": Пустое название в ".__METHOD__, 500);
			}
		}
		
		$info['alias'] = trim( $this->_makeAlias( $info['title'] ), ' -' );
		
		if (!empty($info['sub_title']))
		$info['sub_title'] = $this->_cleanProgramTitle($info['sub_title']);
		
		//var_dump($result_info);
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
		/*
		if ($info['hash'] == md5('12012-04-07 08:45:002012-04-07 09:00:00')) {
			var_dump($info);
			//var_dump(md5('12012-04-07 08:45:002012-04-07 09:00:00'));
			die(__FILE__.': '.__LINE__);
		}
		*/
		
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
	 * Parse XML description
	 * 
	 * @param string $desc
	 * @param string $hash
	 * @return void
	 */
	public function parseXmlDescription ($desc=null) {
		
		if ( !$desc || empty( $desc ) )
		return array('intro'=>'', 'body'=>'');
		
		//$trim = new Zend_Filter_StringTrim();
		$description  = array('intro'=>'', 'body'=>'');
		
		$descriptions = new Admin_Model_DbTable_ProgramsDescriptions();
		$parts = explode( '. ', $desc );
		//$description['hash']=$hash;
		
		if( Xmltv_String::strlen( $desc ) > 256 ) {
			/*
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
			*/
			
			//var_dump($parts);
			//die(__FILE__.': '.__LINE__);
			
			foreach ($parts as $n => $sentence) {
				if( trim( Xmltv_String::strlen( $description['intro'] ) ) < 164 ) {
					$description['intro'].=$sentence . '. ';
					unset( $parts[$n] );
				}
			}
			
			$description['intro']=trim( $description['intro'] );
			$body=implode( '. ', $parts ) . '.';
			
			if( trim( Xmltv_String::strlen( $body ) ) > 1 )
			$description['body']=$body;
			
		} else {
			$description['intro']=implode( '. ', $parts ) . '.';
		}
		
		return $description;
	}
	
	public function saveDescription($desc=array()){
		
		if( empty($desc) || !is_array($desc) )
		throw new Exception("Пропущен или неверно указан один или все параметры для ".__METHOD__, 500);
		
		try {
			$this->_programs_descs_table->insert( $desc );
		} catch (Exception $e) {
			if ($e->getCode()==1062) {
				return true;
				//$this->_programs_descs_table->update( $description, "`hash`='$hash'" );
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
		|| Xmltv_String::stristr( $info['title'], 'кубка дэвиса' )
		|| preg_match( '/плей[- ]?офф/iu', $info['title'] )
		|| Xmltv_String::stristr( $info['title'], 'бокс' )
		|| preg_match( '/^борьба\.? (.+)$/iu', $info['title'] ) 
		|| preg_match( '/^.+\. Бойцовский клуб\.? (.+)$/u', $info['title'] ) 
		|| preg_match( '/^.* ?бокс\.? (.+)$/iu', $info['title'] ) 
		|| preg_match( '/^л[её]гкая атлетика\.? (.+)$/iu', $info['title'] ) 
		|| preg_match( '/^(стиль\.)? спортивные танцы\.? (.+)$/iu', $info['title'] ) 
		|| Xmltv_String::stristr( $info['title'], 'большой ринг' )) {
			
			if (strstr($info['title'], '.')) {
				
				$parts = explode( '.', $info['title'] );
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


	private function _processMovies($info=array()){
		
		if( empty( $info ) ) 
		throw new Exception("Пропущен один или все параметры для ".__METHOD__, 500);
		
		$trim    = new Zend_Filter_StringTrim( $this->_trim_options );
		$tolower = new Zend_Filter_StringToLower( $this->_tolower_options );
		$serializer = new Zend_Serializer_Adapter_Json();
		$logger = new Zend_Log( new Zend_Log_Writer_Stream( ROOT_PATH . '/log/parse-movies.log' ) );
		
		if (Xmltv_String::stristr($info['title'], 'в многосерийном фильме') ) {
			
			$info = $this->setProgramCategory($info, 'Многосерийный фильм');
			$actors_table = new Admin_Model_DbTable_Actors();
			$props_table = new Admin_Model_DbTable_ProgramsProps();
			
			if (preg_match_all('/(\p{Cyrillic}+ \p{Cyrillic}+,? )+ в?/iu', $info['title'], $m)) {
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
						$info['alias'] = $this->_makeAlias(trim($parts[1]));
					} else {
						if (Xmltv_Config::getDebug()) {
							$msg = print_r($info, true);
							$logger->debug( __LINE__.':'. $msg );
						}
					}
					
				} else {
					if (Xmltv_Config::getDebug()) {
						$msg = print_r($info, true);
						$logger->debug( __LINE__.':'. $msg );
					}
				}
				
				//$parts = explode('. ', $info['title']);
				
				
			} else {
				if (Xmltv_Config::getDebug()) {
					$msg = print_r($info, true);
					$logger->debug( __LINE__.':'. $msg );
				}
			}
		} elseif ( preg_match('/^.*премьера[\.!]? "?(\p{Cyrillic}+ \p{Cyrillic}+) в .+ "?(.+)"/iu', $info['title'], $m)
		|| preg_match('/Премьера. Фильм (\p{Cyrillic}+ \p{Cyrillic}+) "(.+)"/iu', $info['title'], $m) ) {
			//die(__FILE__.': '.__LINE__);
			$actor = explode( ' ', trim( $m[1] ) );
			$actor_id = $this->_saveActor(array(
				'f_name'=>trim($actor[0]),
				's_name'=>trim($actor[1])
			));
			$props = $this->_addActorToProps($actor_id, $info);
			$info['title']='Премьера. "'.$m[2].'"';
			$info['alias']= $this->_makeAlias($m[2]);
			
		}
		/**
		 * @example Премьера. Борис Невзоров в детективе "Найти и обезвредить" из цикла "Бандитский Петербург".
		 */
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
			$info['alias'] = $this->_makeAlias($info['title']);
		}
		
		/**
		 * @example Борис Невзоров,Ирина Шмелева,Михаил Жигалов и Нина Русланова в детективе "Найти и обезвредить".
		 */
		if (Xmltv_String::stristr($info['title'], 'в детективе') ) {
			
			if (Xmltv_Config::getDebug()) {
				$parts = explode('в детективе', $info['title']);
				$msg = print_r($info, true);
				$logger->debug( __LINE__.':'. $msg.': '.$parts );
			}
			
			$info = $this->setProgramCategory($info, 'Остросюжетный детектив');
			
		}
		
		if (Xmltv_String::stristr($info['title'], 'многосерийный фильм') ) {
			
			if (Xmltv_Config::getDebug()) {
				$parts = explode('многосерийный фильм', $info['title']);
				$msg   = print_r($info, true);
				$logger->debug( __LINE__.':'. $msg.': '.$parts );
			}
			
			$info = $this->setProgramCategory($info, 'Многосерийный фильм');
			$info['alias'] = Xmltv_String::str_ireplace('многосерийный фильм', '', $info['title']);
			
			
		}
		
		if (Xmltv_String::stristr($info['title'], 'криминальный сериал')) {

			if (Xmltv_Config::getDebug()) {
				$parts = explode('криминальный сериал', $info['title']);
				$msg = print_r($info, true);
				$logger->debug( __LINE__.':'. $msg.': '.$parts );
			}
			
			$info = $this->setProgramCategory($info, 'Криминальный сериал');
			$info['alias'] = Xmltv_String::str_ireplace('криминальный сериал', '', $info['title']);
			
			
		}
		
		if (Xmltv_String::stristr($info['title'], 'остросюжетный детектив')) {
			
			if (Xmltv_Config::getDebug()) {
				$msg = print_r($info, true);
				$logger->debug( __LINE__.':'. $msg );
			}
			
			$info = $this->setProgramCategory($info, 'Остросюжетный детектив');
			$info['alias'] = $this->_makeAlias( $info['title'] );
			$info['title'] = Xmltv_String::str_ireplace('остросюжетный детектив', '', $info['title']);
			
		}
		
		$info['alias'] = $trim->filter($info['alias']);
		return $info;
		
	}
	
	private function _processSeries ($info=array()) {
		
		if( empty( $info ) ) 
		throw new Exception("Пропущен один или все параметры для ".__METHOD__, 500);
		
		$trim    = new Zend_Filter_StringTrim( $this->_trim_options );
		$tolower = new Zend_Filter_StringToLower( $this->_tolower_options );
		
		if( (preg_match( '/[0-9]+-я /iu', $info['title'] ) || preg_match( '/[0-9]+-я /iu', $info['sub_title'] )) ||
			( preg_match( '/сери[яиал]/iu', $info['title'] ) || preg_match( '/сери[яиал]/iu', $info['sub_title'] ) ) ) {
			$info = $this->setProgramCategory( $info, 'Сериал' );
		}
		
		$logger = new Zend_Log( new Zend_Log_Writer_Stream( ROOT_PATH . '/log/parse-series.log' ) );
		
		if (preg_match_all('/(\p{Cyrillic}+ \p{Cyrillic}+,? ? в [теле]?сериале) "(.+)"/iu', $info['title'], $m)) {
			if (Xmltv_Config::getDebug()) {
				$msg = print_r($info, true);
				$logger->debug( __LINE__.':'. $msg );
			}
			$actors = Xmltv_String::str_ireplace(' в', '', $m[0][0]);
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


	private function _processBreak ($info=array()) {
		
		if( empty( $info ) )
		throw new Exception("Пропущен один или все параметры для ".__METHOD__, 500);
		
		if( preg_match( '/^внимание\!?.+$/iu', $info['title'] ) 
		|| Xmltv_String::stristr( $info['title'], 'профилактика' ) 
		|| Xmltv_String::stristr( $info['title'], 'канал заканчивает' ) ) {
			$info['title']='Перерыв в вещании канала';
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

	/*
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
	*/

	private function _processKinoreysTitle ($info=array()) {
		
		if( empty( $info ) ) 
		throw new Exception("Пропущен один или все параметры для ".__METHOD__, 500);
		
		return $info;
	
	}
	
	private function _processSportsAnalyticsTitle($info=array()){
		
		if( empty( $info ) )
		throw new Exception("Пропущен один или все параметры для ".__METHOD__, 500);
		
		if( Xmltv_String::stristr($info['title'], 'обзор матч') ) {
			$info = $this->setProgramCategory( $info, 'Спортивная аналитика' );
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
			throw new Exception(__METHOD__." - Не передан XML файл для обработки.", 500);
		
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
		
		//var_dump($info);
		//var_dump($xml->category);
		//die(__FILE__.': '.__LINE__);
		
		$info ['title'] = (string)$xml->title;
		$info ['alias'] = $this->_makeAlias((string)$xml->title);
		$info ['sub_title'] = '';
		//$info ['hash'] = $hash;
		//$info = $this->makeTitles ( $info );
		/*
		if (Xmltv_String::stristr($tolower->filter((string)$xml->title), 'премьера') ||
		Xmltv_String::stristr($tolower->filter((string)$xml->title), 'premiere')) {
			$info = $this->savePremiere($info);
		}
		*/
		
		return $info;
		
	}
	
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
	
	private function _saveActor($data=array()){
		
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
	
	public function saveProgram($info=array()){
		
		if (empty($info) || !is_array($info))
		throw new Exception("Пропущен один или все параметры для ".__METHOD__, 500);
		
		try {
			$hash = $this->_table->insert($info);
		} catch (Exception $e) {
			if ($e->getCode()==1062) {
				//$this->_table->update($info, "`hash`='".$info['hash']."'");
				return $info['hash'];
			} else {
				echo __METHOD__.' error#'.$e->getCode().': '.$e->getMessage();
				die(__FILE__.': '.__LINE__);
			}
		}
		
		if (!$hash)
		throw new Exception("Ошибка сохранения в ".__METHOD__."<br />Данные: ".print_r($info, true), 500);
		
		return $hash;
	}
	
	public function findProgram($hash=null){
	
		if (!$hash)
		throw new Exception("Пропущен один или все параметры для ".__METHOD__, 500);
		
		return $this->_table->find($hash)->current();
		
	}
	
	public function getProgramsCountForWeek(Zend_Date $weekStart, Zend_Date $weekEnd){
		return $this->_table->getProgramsCountForWeek($weekStart, $weekEnd);
	}
	
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
			$this->_logger->log(__METHOD__.': '.$message, Zend_Log::ERR);
			throw new Exception($message, 500);
		}
		
		return $info;
	}
	
	public function getChannelIdFromXml(SimpleXMLElement $xml){
		$attrs   = $xml->attributes();
		return (int)$attrs->channel;
	}
	
	public function getProgramStartFromXml(SimpleXMLElement $xml){
		
		$attrs   = $xml->attributes();
		$d = (string)$attrs->start;
		$f = Zend_Date::YEAR."-".Zend_Date::MONTH."-".Zend_Date::DAY." ".Zend_Date::HOUR.':'.Zend_Date::MINUTE.':'.Zend_Date::SECOND.' '.Zend_Date::GMT_DIFF;
		$date_str = $this->_getDateString($d);
		$date = new Zend_Date($date_str, $f);
		return $date->toString("yyyy-MM-dd HH:mm:ss");
		
	}
	
	public function getProgramEndFromXml(SimpleXMLElement $xml){
		
		$attrs   = $xml->attributes();
		$d = (string)$attrs->stop;
		$date_str = $this->_getDateString($d);
		$f = Zend_Date::YEAR."-".Zend_Date::MONTH."-".Zend_Date::DAY." ".Zend_Date::HOUR.':'.Zend_Date::MINUTE.':'.Zend_Date::SECOND.' '.Zend_Date::GMT_DIFF;
		$date = new Zend_Date($date_str, $f);
		return $date->toString("yyyy-MM-dd HH:mm:ss");
		
	}

	public function getProgramTitleFromXml(SimpleXMLElement $xml){
		return $this->_makeAlias( (string)$xml->title );
	}
	
	public function deletePrograms(Zend_Date $start, Zend_Date $end, $linked=false){
		
		if (!$linked) {
			try {
				$this->_table->delete(array(
					"`start` >= '".$start->toString('yyyy-MM-dd')." 00:00:00'",
					"`start` < '".$end->toString('yyyy-MM-dd')." 23:59:59'"
				));
				
			} catch (Exception $e) {
				echo $e->getMessage();
				die(__FILE__.': '.__LINE__);
			}
		} else {
			try {
				$this->_table->deleteProgramsWithInfo($start, $end);
			} catch (Exception $e) {
				echo $e->getMessage();
				die(__FILE__.': '.__LINE__);
			}
		}
		
	}
	
	/**
	 * @param string $title
	 * @return string
	 */
	private function _cleanProgramTitle ($title=null) {
		
		if(  !$title )
		throw new Exception("Не указан один или более параметров для ".__METHOD__, 500);
		
		$trim    = new Zend_Filter_StringTrim($this->_trim_options);
		$result  = $trim->filter($title);
		$replace = new Zend_Filter_Word_SeparatorToSeparator( '"', '' );
		$result  = $replace->filter($result);
		$replace = new Zend_Filter_Word_SeparatorToSeparator( '  ', ' ' );
		$result  = $replace->filter($result);
		
		$result  = preg_replace('/(.*)[теле]сериал(.*)/', '\1 \2', $result);
		
		return $result;
	}
	
	private function _makeAlias($input=null){
		
		if( !$input ) {
			//return 'неизвестная-программа';
			throw new Exception("Не указано название прграммы для ".__METHOD__, 500);
		}
			
		$result = $input;
		if (Xmltv_String::stristr($result, 'серия') || Xmltv_String::stristr($result, 'серии')) {
			$regexp = new Zend_Filter_PregReplace(array('match'=>'/\(([0-9]+)-я серия - "(.+)"\. ([0-9]+)-я серия - "(.+)"\)/iu', 'replace'=>'\1-\2-\3-\4'));
			$result = $regexp->filter($result);
			$regexp = new Zend_Filter_PregReplace(array('match'=>'/[0-9]+-я.*[и |-|- ][0-9]+-я сери[я|и]/iu', 'replace'=>''));
			$result = $regexp->filter($result);
			$regexp = new Zend_Filter_PregReplace(array('match'=>'/[0-9]+-я серия/iu', 'replace'=>''));
			$result = $regexp->filter($result);
		}
		
		
		//$regexp = new Zend_Filter_PregReplace(array('match'=>'/[0-9]+-я.*-.*[0-9]+-я.*серии/iu', 'replace'=>''));
		//$result = $regexp->filter($result);
		//$regexp = new Zend_Filter_PregReplace(array('match'=>'/[0-9]+-я и [0-9]+-я/iu', 'replace'=>''));
		//$result = $regexp->filter($result);
		if (Xmltv_String::stristr($result, 'часть')) {
			$regexp = new Zend_Filter_PregReplace(array('match'=>'/часть-[0-9]+-я\.?/iu', 'replace'=>''));
			$result = $regexp->filter($result);
			$regexp = new Zend_Filter_PregReplace(array('match'=>'/[0-9]+-я часть\.?/iu', 'replace'=>''));
			$result = $regexp->filter($result);
		}
		if (Xmltv_String::stristr($result, 'сезон')) {
			$regexp = new Zend_Filter_PregReplace(array('match'=>'/(сезон-[0-9]+-й\.?)|([0-9]+-й-сезон\.?)/iu', 'replace'=>''));
			$result = $regexp->filter($result);
		}
		
		if (Xmltv_String::stristr($result, 'премьера')) {
			$regexp = new Zend_Filter_PregReplace(array('match'=>'/^(.*)-?премьера-?(.*)$/iu', 'replace'=>'\1\2'));
			$result = $regexp->filter($result);
		}
		
		//$slash  = new Zend_Filter_Word_SeparatorToSeparator( '/', '-' );
		//$result = $slash->filter($result);
		
		$result = Xmltv_String::str_ireplace('криминальный сериал', '', $result);
		$result = Xmltv_String::str_ireplace('остросюжетный сериал', '', $result);
		$result = Xmltv_String::str_ireplace('остросюжетный детектив', '', $result);
		
		$result=Xmltv_String::str_ireplace( 'ё', 'е', $result );
		$result=Xmltv_String::str_ireplace( 'Ё', 'Е', $result );
		$result=Xmltv_String::str_ireplace( '+', '-плюс-', $result );
		
		$result = preg_replace('/[^0-9\p{Cyrillic}\p{Latin}]+/ui', ' ', $result);
		$result = preg_replace('/[\s"«»]+/u', ' ', $result);
		//$result = preg_replace('/^[-]+/u', '', $result);
		
		$todash=new Zend_Filter_Word_SeparatorToDash(' ');
		$result=$todash->filter( $result );
		
		/*
		do {
			$result=str_replace( '--', '-', $result );
		} while(strstr($result, '--'));
		*/
		$result = trim($result, ' -');
		
		//$tolower = new Zend_Filter_StringToLower();
		$result  = Xmltv_String::strtolower($result);
		/*
		if ($input=='МультиПочта') {
			var_dump($result);
			die(__FILE__.': '.__LINE__);
		}
		
		$clean = array(
			'пионеры-глубин',
			'приют',
			'хочу-знать-с-михаилом-ширвиндтом',
			'новости',
			'телеканал-доброе-утро',
			'контрольная-закупка',
			'жить-здорово',
			'модный-приговор',
			'новости-с-субтитрами',
			'сердце-марии',
		);
		if (!in_array($result, $clean)) {
			var_dump($input);
			var_dump($result);
			die(__FILE__.': '.__LINE__);
		}
		*/
		return Xmltv_String::strtolower( $result );
		
	}
	
}

