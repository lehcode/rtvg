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


	public function cleanProgramTitle ($title=null) {
		if(  !$title ) return;
		return str_replace( array('"'), '', $title );
	}


	public function makeProgramAlias ($title=null) {
		if(  !$title ) return;
		$todash=new Zend_Filter_Word_SeparatorToDash();
		$result=$todash->filter( $title );
		$info['title']=Xmltv_String::str_ireplace( 'Премьера.', '', $info['title'] );
		$info['title']=Xmltv_String::str_ireplace( ' Премьера', '', $info['title'] );
		$info['title']=Xmltv_String::str_ireplace( ' Премьера ', '', $info['title'] );
		$info['title']=Xmltv_String::str_ireplace( 'Премьера', '', $info['title'] );
		$result=preg_replace( '/[:;_,\.\[\]\(\)\\`\{\}"\!\+\?]/ius', '-', $result );
		$result=preg_replace( '/[0-9]+-я.*серия/ius', '', $result );
		$result=preg_replace( '/[0-9]+-я.*-.*[0-9]+-я.*серии/ius', '', $result );
		$result=preg_replace( '/[0-9]+-я и [0-9]+-я/ius', '', $result );
		$result=preg_replace( '/часть-[0-9]+-я/ius', '', $result );
		$result=preg_replace( '/[0-9]+-я часть/ius', '', $result );
		$result=Xmltv_String::str_ireplace( 'ё', 'е', $result );
		$result=Xmltv_String::str_ireplace( 'Ё', 'Е', $result );
		$result=Xmltv_String::str_ireplace( ' +', ' плюс', $result );
		$result=str_replace( array('--'), '-', $result );
		$result=str_replace( '--', '-', $result );
		$result=trim( $result, '- ' );
		return Xmltv_String::strtolower( $result );
	}


	public function makeTitles ($info=array()) {
		
		if( empty( $info ) ) return;
		
		$tolower=new Zend_Filter_StringToLower( $this->_tolower_options );
		$trim=new Zend_Filter_StringTrim( $this->_trim_options );
		$info['title']=str_replace( array('"', "'"), '', $info['title'] );
		$info['title']=$trim->filter( $info['title'] );
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
		return $info;
	}


	public function setProgramCategory ($info=array(), $predefined=null) {
		
		if( empty( $info ) ) return;
		
		$categories=new Admin_Model_DbTable_ProgramsCategories();
		$tolower=new Zend_Filter_StringToLower( $this->_tolower_options );
		
		$cat_list=$categories->fetchAll();
		foreach ($cat_list as $c) {
			if( $tolower->filter( $c->title ) == $tolower->filter( $predefined ) ) {
				$info['category']=$c->id;
			} else {
				if(  !( strlen( $predefined ) > 1 ) ) {
					return $info;
				}
				try {
					$categories->insert( array('title'=>$predefined) );
				} catch (Exception $e) {
					continue;
				}
			}
		}
		return $info;
	
	}


	public function savePremiere ($info=array()) {
		
		if( empty( $info ) ) return;
		
		$programs=new Admin_Model_DbTable_Programs();
		$props=new Admin_Model_DbTable_ProgramsProps();
		
		$info['alias']=str_replace( '--', '-', $this->makeProgramAlias( $info['title'] ) );
		$info['new']=1;
		
		//var_dump($info);
		//die(__FILE__.': '.__LINE__);
		
		$new=$props->createRow();
		$new->title_alias=$info['alias'];
		$new->premiere=1;
		$new->premiere_date=$info['start'];
		
		try {
			$new->save();
			$programs->update( $info, "`hash`='" . $info['hash'] . "'" );
		} catch (Exception $e) {
			$programs->update( $info, "`hash`='" . $info['hash'] . "'" );
		}
	

	}


	public function getCredits ($input=null) {
		if(  !$input ) return;
		
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
	public function saveDescription ($desc=null, $alias=null) {
		
		if( empty( $desc ) ||  !$alias ) return;
		
		$descriptions=new Admin_Model_DbTable_ProgramsDescriptions();
		$desc_len=Xmltv_String::strlen( $desc );
		$description=array('intro'=>'', 'body'=>'');
		$parts=explode( '. ', $desc );
		
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
			
			$description['title_alias']=$alias;
			
			try {
				$descriptions->insert( $description );
			} catch (Exception $e) {
				return;
			}
		} else {
			$description['intro']=implode( '. ', $parts ) . '.';
		}
		//var_dump($desc_len);
	//die(__FILE__.': '.__LINE__);
	}


	public function cleanDescription ($input=null) {
		
		if(  !$input ) return;

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
		
		if( empty( $credits ) || empty( $info ) ) return;
		
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
		
		if( empty( $existing ) ) return;
		if( empty( $info ) ) return;
		
		$props = new Admin_Model_DbTable_ProgramsProps();
		$serializer = new Zend_Serializer_Adapter_Json();
		
		$p_props = $props->find( $info['alias'] );
		
		if( count( $p_props ) == 0 ) {
			$p_props = $props->createRow();
			$p_props->title_alias = $info['alias'];
			try {
				$p_props->actors = $serializer->serialize( array($existing['id']) );
				$p_props->save();
			} catch (Exception $e) {
				var_dump($e->getCode());
				echo "Не могу добавить актера: " . $e->getMessage();
				var_dump($e->getTrace());
				die( __FILE__ . ': ' . __LINE__ );
			}
		} else {
			try {
				$p_props = $p_props->current()->toArray();
				$p_props['title_alias'] = $info['alias'];
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
		
		if( empty( $existing ) ) return;
		if( empty( $info ) ) return;
		
		//var_dump(func_get_args());
		//die(__FILE__.': '.__LINE__);
		


		$props = new Admin_Model_DbTable_ProgramsProps();
		$serializer = new Zend_Serializer_Adapter_Json();
		$p_props = $props->find( $info['alias'] );
		
		if( count( $p_props ) == 0 ) {
			$p_props = $props->createRow();
			$p_props->title_alias = $info['alias'];
			try {
				$p_props->directors = $serializer->serialize( array($existing['id']) );
				$p_props->save();
			} catch (Exception $e) {
				echo "Не могу добавить режиссера: " . $e->getMessage();
				die( __FILE__ . ': ' . __LINE__ );
			}
		} else {
			try {
				$p_props = $p_props->current()->toArray();
				$p_props['title_alias'] = $info['alias'];
				$persons = $serializer->unserialize( $p_props['directors'] );
				if(  !( in_array( $existing['id'], $persons ) ) ) {
					$persons[] = $existing['id'];
				}
				$p_props['directors'] = $serializer->serialize( $persons );
				$props->update( $p_props, "`title_alias`='" . $info['alias'] . "'" );
			} catch (Exception $e) {
				if( $e->getCode() == 0 ) {
					$p_props['directors'] = $serializer->serialize( array($existing['id']) );
					try {
						$props->update( $p_props, "`title_alias`='" . $info['alias'] . "'" );
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
		
		if( empty( $info ) ) return;
		
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
		
		if( empty( $info ) ) return;
		
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
		|| preg_match( '/^борьба\.? (.+)$/iu', $info['title'] ) 
		|| preg_match( '/^.+\. Бойцовский клуб\.? (.+)$/u', $info['title'] ) 
		|| preg_match( '/^.* бокс\.? (.+)$/iu', $info['title'] ) 
		|| preg_match( '/^л[её]гкая атлетика\.? (.+)$/iu', $info['title'] ) 
		|| preg_match( '/^(стиль\.)? спортивные танцы\.? (.+)$/iu', $info['title'] ) ) {
			
			//var_dump($info['title']);
			//var_dump(strstr($info['title'], '.'));
			//die(__FILE__.': '.__LINE__);
			
			if (strstr($info['title'], '.')) {
				$parts = explode( '.', $info['title'] );
					
				//var_dump($parts);
				
				$info['title'] = $trim->filter( $parts[0] ) . '. ' . $trim->filter( $parts[1] );
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
		
		if( empty( $info ) ) return;
		
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


	private function _checkSeries ($info=array()) {
		
		if( empty( $info ) ) return;
		
		$trim=new Zend_Filter_StringTrim( $this->_trim_options );
		
		if( preg_match( '/[0-9]+-я /iu', $info['title'] ) 
		|| preg_match( '/сери[яиал]/', $info['title'] ) ) {
			$info=$this->setProgramCategory( $info, 'Сериал' );
		}
		return $info;
	}


	private function _checkLive ($info = array()) {
		
		if( empty( $info ) ) return;
		
		$tolower = new Zend_Filter_StringToLower( $this->_tolower_options );
		$trim = new Zend_Filter_StringTrim( $this->_trim_options );
		
		if( preg_match( '/(.+\.?) прям(ая|ой) (трансляция|эфир)/iu', $info['title'], $m ) 
		|| preg_match( '/(.+\.?) трансляция из.+$/iu', $info['title'], $m ) 
		|| Xmltv_String::stristr( $info['title'], 'live' ) ) {
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
		
		if( empty( $info ) ) return;
		
		if( preg_match( '/^внимание\!.+$/iu', $info['title'] ) 
		|| Xmltv_String::stristr( $info['title'], 'профилактика' ) 
		|| Xmltv_String::stristr( $info['title'], 'канал заканчивает' ) ) {
			$info['title']='Перерыв';
			$info['sub_title']='';
		}
		return $info;
	
	}


	private function _processCartoonsTitle ($info=array()) {
		
		if( empty( $info ) ) return;
		
		if( Xmltv_String::stristr( $info['title'], 'мультф') 
		|| Xmltv_String::stristr( $info['title'], 'мультиплик') ) {
			$info=$this->setProgramCategory( $info, 'Мультфильм' );
		}
		return $info;
	}


	private function _processMusicTitle ($info=array()) {
		
		if( empty( $info ) ) return;
		
		if( preg_match( '/ ?музыка /isu', $info['title'] ) 
		|| preg_match( '/ ?клип(ы)? /isu', $info['title'] ) 
		|| preg_match( '/ ?music ?/isu', $info['title'] ) ) {
			$info=$this->setProgramCategory( $info, 'Музыка' );
		}
		return $info;
	
	}


	private function _processMiscTitle ($info=array()) {
		
		if( empty( $info ) ) return;
		
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
		
		if( empty( $info ) ) return;
		return $info;
	
	}
	
	private function _processSportsAnalyticsTitle($info=array()){
		
		if( empty( $info ) ) return;
		
		if( Xmltv_String::stristr($info['title'], 'обзор матчей') ) {
			$info=$this->setProgramCategory( $info, 'Спортивная аналитика' );
		}
		return $info;
		
	}
	
}

