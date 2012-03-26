<?php
/**
 * 
 * @author toshihir
 * @package rutvgid
 *
 */
class Admin_Model_Programs
{

	public function cleanProgramTitle($title=null){
		if (!$title) return;
		return str_replace(array('"'), '', $title);
	}
	
	
	public function makeProgramAlias($title=null){
		if (!$title) return;
		$f = new Zend_Filter_Word_SeparatorToDash();
		$result= $f->filter($title);
        $result = preg_replace('/[:;_,\.\[\]\(\)\\`\{\}"\!\+\?]/ius', '-', $result);
        $result = preg_replace('/[0-9]+-я.*серия/ius', '', $result);
        $result = preg_replace('/[0-9]+-я.*-.*[0-9]+-я.*серии/ius', '', $result);
        $result = preg_replace('/[0-9]+-я и [0-9]+-я/ius', '', $result);
        $result = preg_replace('/часть-[0-9]+-я/ius', '', $result);
        $result = Xmltv_String::str_ireplace('ё', 'е', $result);
        $result = Xmltv_String::str_ireplace('Ё', 'Е', $result);
        $result = str_replace(' +', ' плюс', $result);
        $result = str_replace(array('--'), '-', $result);
        $result = str_replace('--', '-', $result);
        $result = trim($result, '- ');
        return Xmltv_String::strtolower($result);
	}
	
	public function makeTitles($info=array()){
		
		if (empty($info)) return;
		$info['title'] = preg_replace('/^"(.+)"$/', '\1', $info['title']);
		if (Xmltv_String::strlen($info['title'])<=128) {
			$info['sub_title'] = '';
		} else {
			$parts = explode('.', $info['title']);
			$info['title'] = trim($parts[0]);
			$info['sub_title'] = trim($parts[1]);
		}
		return $info;
	}
	
	
	
	public function setProgramCategory($info=array(), $predefined=null){
		
		if (empty($info)) return;
		
		$categories   = new Admin_Model_DbTable_ProgramsCategories();
		$cat_list = $categories->fetchAll();
		if ($predefined) {
			foreach ($cat_list as $c) {
				if (mb_strtolower($c->title)==mb_strtolower($predefined))
				$info ['category']=$c->id;
				else {
					try {
						$categories->insert(array('title'=>$predefined));
					} catch (Exception $e) {
						continue;
					}
				}
			}
		} else {
			if (preg_match('/новости/isu', $info['title'])) {
				$c = $categories->fetchRow("`title`='Новости'");
				$info['category'] = (int)$c->id;
			}
		}
		
		return $info;
		
	}
	
	public function savePremiere($info=array()){
		
		if (empty($info))
		return;
		
		$programs = new Admin_Model_DbTable_Programs ();
		$props = new Admin_Model_DbTable_ProgramsProps ();
		$info ['title'] = Xmltv_String::str_ireplace ( 'Премьера', '', $info ['title'] );
		$info ['title'] = Xmltv_String::str_ireplace ( 'Премьера.', '', $info ['title'] );
		$info ['title'] = Xmltv_String::str_ireplace ( ' Премьера', '', $info ['title'] );
		$info ['title'] = Xmltv_String::str_ireplace ( ' Премьера ', '', $info ['title'] );
		$info ['alias'] = str_replace ( '--', '-', $this->makeProgramAlias ( $info ['title'] ) );
		$new = $props->createRow ();
		$new->title_alias = $info ['alias'];
		$new->premiere = 1;
		$new->premiere_date = $info ['start'];
		
		try {
			$new->save();
			$programs->update($info, "`hash`='".$info['hash']."'");
		} catch (Exception $e) {
			$programs->update($info, "`hash`='".$info['hash']."'");
		}
		
		
		
	}
	
	public function getCredits($input=null){
		if (!$input) return;
		
		$result['actors'] = array();
		$result['directors'] = array();
		$tolower = new Zend_Filter_StringToLower();
		if (strstr($tolower->filter($input), 'в ролях')) {
			$d = explode('В ролях:', $input);
			//var_dump($d);
			$actors = $d[1];
			$p = explode('Режиссер', $actors);
			//var_dump($p);
			$result['actors']    = explode( ', ', trim($p[0], '.…: ') );
			$result['directors'] = explode( ', ', trim($p[1], '.…: ') );

		} elseif (strstr($tolower->filter($input), 'режиссер')) {
			
			$p = explode('Режиссер', $input);
			$result['actors']    = array();
			$result['directors'] = explode( ', ', trim($p[1], '.…: ') );
			
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
	public function saveDescription($desc=null, $alias=null){
		
		if (empty($desc) || !$alias) return;
		
		$descriptions = new Admin_Model_DbTable_ProgramsDescriptions();
		$desc_len     = Xmltv_String::strlen($desc);
		$description  = array('intro'=>'', 'body'=>'');
		$parts = explode('. ', $desc);
		
		//var_dump($desc);
		//var_dump($desc_len);
		//die(__FILE__.': '.__LINE__);
		
		if ($desc_len>256) {
			
			foreach ($parts as $n=>$sentence) {
				if (Xmltv_String::stristr($sentence, 'в ролях')) {
					unset($parts[$n]);
				} 
				if (Xmltv_String::stristr($sentence, 'режиссер')) {
					unset($parts[$n]);
				} 
				if (trim(Xmltv_String::strlen($sentence))<24) {
					unset($parts[$n]);
				} 
			}
			//var_dump($parts);
			foreach ($parts as $n=>$sentence) {
				if (trim(Xmltv_String::strlen($description['intro']))<164) {
					$description['intro'] .= $sentence.'. ';
					unset($parts[$n]);
				}
			}
			
			$description['intro'] = trim($description['intro']);
			$body  = implode('. ', $parts).'.';
			
			if (trim(Xmltv_String::strlen($body))>1)
			$description['body'] = $body;
			
			$description['title_alias'] = $alias;

			try {
				$descriptions->insert($description);
			} catch (Exception $e) {
				return;
			} 
		} else {
			$description['intro']=implode('. ', $parts).'.';
		}
		//var_dump($desc_len);
		//die(__FILE__.': '.__LINE__);
	}
	
	public function getDescription($input=null){
		
		if (!$input) return;
		
		//var_dump($input);
		//die(__FILE__.': '.__LINE__);
		
		if (strstr($input, '…')) {
			$d = explode('…', $input);
			$d = trim($d[0]);
			$expl = explode('.', $d);
			$desc['intro'] = trim($expl[0]).'. '.trim($expl[1]).'.';
			unset($expl[0]); unset($expl[1]);
			$desc['body'] = trim(implode('. ', $expl)).'.';
			return $desc;
			
		} else {
		
			$last_space    = Xmltv_String::strrpos($input, '. ');
			$last_sentence = Xmltv_String::substr($input, $last_space+1);
			$first_dot     = Xmltv_String::strpos($last_sentence, '.')>0 ? Xmltv_String::strpos($last_sentence, '.') : Xmltv_String::strlen($last_sentence) ;
			$desc = Xmltv_String::substr($input, 0, $last_space+1).' '.Xmltv_String::substr($last_sentence, 0, $first_dot+1);
			if ($desc) {
				return $desc;
			}
			return;
		}
	}
	
	/**
	 * @param array $credits
	 * @param array $info
	 */
	public function saveCredits($credits=array(), $info=array()){
		
		if (empty($credits) || empty($info))
		return;
		
		$actors_table = new Admin_Model_DbTable_Actors();
		$tolower      = new Zend_Filter_StringToLower();
		$props        = new Admin_Model_DbTable_ProgramsProps();
		
		//var_dump(func_get_args());
		//die(__FILE__.': '.__LINE__);
		
		foreach ($credits['actors'] as $k=>$p) {
			$exists=false;
			$parts = explode(' ', $p);
			if (count($parts)==3){
				
				$snames = $actors_table->fetchAll("`f_name` LIKE '%".$tolower->filter($parts[0])."%' 
					AND `m_name` LIKE '%".$tolower->filter($parts[1])."%' 
					AND `s_name` LIKE '%".$tolower->filter($parts[2])."%'");
				
				if (count($snames)>0) {
					foreach ($snames as $sn) {
						$existing = $tolower->filter( sprintf( "%s %s %s",  $sn->f_name, $sn->m_name, $sn->s_name ) );
						$exists=false;
						if ($existing == $tolower->filter( implode(' ', $parts) )) {
							$exists=true;
							$existing = $sn->toArray();
							break;
						}	
					}
				} 
				
				if (!$exists){ 
					$existing = $this->_addCreditsName($parts, 'actor', $info);
				} 
				$this->_updateProgramProperties($existing, $info);
				
			} elseif (count($parts)==2){
				
				//var_dump($tolower->filter($parts[0]));
				//var_dump($tolower->filter($parts[1]));
				
				$snames = $actors_table->fetchAll("`f_name` LIKE '%".$tolower->filter($parts[0])."%' AND `s_name` LIKE '%".$tolower->filter($parts[1])."%'");
				if (count($snames)>0) {
					foreach ($snames as $sn) {
						$existing = $tolower->filter( sprintf( "%s %s",  $sn->f_name, $sn->s_name ) );
						$exists=false;
						if ($existing == $tolower->filter( implode(' ', $parts) )) {
							$exists=true;
							$existing = $sn->toArray();
							break;
						}
					}
				} 
				
				if ($exists===false){ 
					$this->_addCreditsName($parts, 'actor', $info);
				} else {
					$this->_updateProgramProperties($existing, $info);
				}
				
				
			} elseif (count($parts)>3) {
				/* ошибка в данных */
				unset($credits['actors'][$k]);
			} else {
				continue;
			}
			
		}
		
	}
	
	private function _addCreditsName($parts=array(), $type='actor', $info=array()){
		
		$serializer = new Zend_Serializer_Adapter_Json();
		$props = new Admin_Model_DbTable_ProgramsProps();
		
		if ($type=='actor')
		$table =  new Admin_Model_DbTable_Actors();
		if ($type=='director')
		$table =  new Admin_Model_DbTable_Directors();
		
		$found=false;
		try {
			
			if (count($parts)==2) {
				$found = $table->fetchRow("`f_name`='%".$parts[0]."%' AND `s_name`='%".$parts[1]."%'");
				if (!$found)
				$new = $table->createRow(array('f_name'=>$parts[0], 's_name'=>$parts[1]));
			}
			
			if (count($parts)==3) {
				$found = $table->fetchRow("`f_name`='%".$parts[0]."%' AND `m_name`='%".$parts[1]."%' AND `s_name`='%".$parts[2]."%'");
				if (!$found)
				$new  = $table->createRow(array('f_name'=>$parts[0], 'm_name'=>$parts[1], 's_name'=>$parts[2]));
			}
			
			$new = $new->save();
			$new = $table->find ( $new )->current ()->toArray ();
			return $new;
			
		} catch (Exception $e) {
			echo "Не могу сохранить запись актера";
			die(__FILE__.': '.__LINE__);
		}
		//die(__FILE__.': '.__LINE__);
	}
	
	/**
	 * @param array $existing
	 * @param array $info
	 * @return void
	 */
	private function _updateProgramProperties($existing=array(), $info=array()){
		
		if (empty($existing))return;
		if (empty($info))return;
		
		//var_dump(func_get_args());
		//die(__FILE__.': '.__LINE__);
		
		$props      = new Admin_Model_DbTable_ProgramsProps();
		$serializer = new Zend_Serializer_Adapter_Json();
		$alias = Xmltv_String::str_ireplace('премьера', '', $info['alias']);
		$alias = Xmltv_String::str_ireplace('--', '-', $alias);
		$alias = Xmltv_String::trim($alias, '- ');
		
		$p_props = $props->find($alias);
		if (count($p_props)==0) {
			
			$p_props = $props->createRow();
			$p_props->title_alias = $alias;
			try {
				$p_props->actors = $serializer->serialize( array($existing['id']) );
				$p_props->save();
			} catch (Exception $e) {
				echo "Не могу обновить свойства передачи: ".$e->getMessage();
				die(__FILE__.': '.__LINE__);
			}
		} else {
			
			try{
				$p_props = $p_props->current()->toArray();
				$p_props['title_alias'] = $alias;
				$actors  = $serializer->unserialize($p_props['actors']);
				if (!(in_array($existing['id'], $actors))) {
					$actors[] = $existing['id'];
				}
				$p_props['actors'] = $serializer->serialize( $actors );
				$props->update($p_props, "`title_alias`='$alias'");
			} catch (Exception $e) {
				echo "Не могу обновить свойства передачи: ".$e->getMessage();
				die(__FILE__.': '.__LINE__);
			}
		}
	}
	
	public function getActorsNames(){
		
	}
	
	public function getHash($channel_id=null, $start=null, $end=null){
		
		if (!$channel_id || !$start || !$end)
		return;
		
		return md5($channel_id.$start.$end);
		
	}
	
	
}

