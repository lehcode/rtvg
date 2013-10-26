<?php

class Admin_Model_Actors
{

	private $_trim_options=array('charlist'=>' "\'.,:;-+');
	private $_tolower_options=array('encoding'=>'UTF-8');
	
	public function getDuplicates(){
		
		$actors = new Admin_Model_DbTable_Actors();
		$dupes  = array();
		$all    = $actors->fetchAll()->toArray();
		$total  = count($all);
		$chunks = array_chunk($all, 250);
		$p=0;
		$tolower = new Zend_Filter_StringToLower($this->_tolower_options);
		foreach ($chunks as $chunk) {
			foreach ($chunk as $item) {
				$p++;
				$d = $actors->fetchAll(array(
					"`s_name` = '".$item['s_name']."'"),  "id DESC");
				if (count($d)>1) {
					$result=array('total'=>$total, 'processed'=>$p);
					foreach ($d as $row) {
						$result[] = $row->toArray();
					}
					$clones = array();
			    	$origin = $result[0];
			    	unset($result[0]);
			    	foreach ($result as $dupe) {
			    		/*
			    		$origin_f = $tolower->filter(Xmltv_String::str_ireplace('ё', 'е', $origin['f_name']));
			    		$origin_m = $tolower->filter(Xmltv_String::str_ireplace('ё', 'е', $origin['m_name']));
			    		$origin_s = $tolower->filter(Xmltv_String::str_ireplace('ё', 'е', $origin['s_name']));
			    		$dupe_f   = $tolower->filter(Xmltv_String::str_ireplace('ё', 'е', $dupe['f_name']));
			    		$dupe_m   = $tolower->filter(Xmltv_String::str_ireplace('ё', 'е', $dupe['m_name']));
			    		$dupe_s   = $tolower->filter(Xmltv_String::str_ireplace('ё', 'е', $dupe['s_name']));
			    		*/
			    		$origin_name = $origin['f_name'].' '.$origin['m_name'].' '.$origin['s_name'].' '.$origin['rank'];
			    		$dupe_name   = $dupe['f_name'].' '.$dupe['m_name'].' '.$dupe['s_name'].' '.$dupe['rank'];
			    		if ($origin_name==$dupe_name) {
			    			$clones[]=$dupe;
			    		}
			    	}
			    	$result['origin']=$origin;
			    	
			    	if (!empty($clones)) {
			    		$result['clones']=$clones;
			    		return $result;
			    	} else {
			    		continue;
			    	}
				}
				
			}
		}
	}
	
	public function fixNames(){
		
		$actors = new Admin_Model_DbTable_Actors();
		$all = $actors->fetchAll()->toArray();
		$chunks = array_chunk($all, 250);
		foreach ($chunks as $chunk) {
			foreach ($chunk as $item) {
				
				$parts = explode(' ', $item['name']);
				
				if (count($parts)>3) {
					$actors->delete("`id`='".$item['id']."'");
				} elseif (count($parts)==3) {
					$item['name'] = trim($parts[0]);
					$m_name = trim($parts[1]);
					if (mb_strstr($m_name, '(')) {
						$actors->delete("`id`='".$item['id']."'");
					} else {
						$item['m_name'] = $m_name;
						$item['s_name'] = trim($expl[2]);
						$actors->update($item, "`id`='".$item['id']."'");
					}
					
				} elseif (count($parts)==2) {
					$f = $this->_fixNameRank($item);
					$parts = explode(' ', $f['name']);
					$name = trim($parts[0]);
					if (mb_strlen($name)>2) {
						$f['f_name'] = $name;
						$f['s_name'] = trim($parts[1]);
						$actors->update($f, "`id`='".$f['id']."'");
					} elseif (mb_strlen($name)>=32) {
						$actors->delete("`id`='".$item['id']."'");
					}
					
				} else { 
					if (!$item['name_en']) {
						$tl = new Xmltv_Cyrillic_Translit();
						$translit = $tl->transliterate_return($item['name'].' '.$item['m_name'].' '.$item['s_name']);
						if (!preg_match('/[a-zA-Z- ]+/', $translit)) {
							Zend_Debug::dump($translit);
							die(__FILE__.': '.__LINE__);
						}
					}
				}
				
			}
		}
        
		return true;
        
	}
	
	private function _fixNameRank ($data = null) {
		if(  !$data ) return;
		if( Xmltv_String::stristr( $data['name'], '-мл' ) ) {
			$data['name'] = Xmltv_String::str_ireplace( '-младший', '', $data['name'] );
			$data['name'] = Xmltv_String::str_ireplace( '-мл', '', $data['name'] );
			$data['rank'] = 'мл.';
		}
		if( Xmltv_String::stristr( $data['name'], '-ст' ) ) {
			$data['name'] = Xmltv_String::str_ireplace( '-старший', '', $data['name'] );
			$data['name'] = Xmltv_String::str_ireplace( '-ст', '', $data['name'] );
			$data['rank'] = 'ст.';
		}
		return $data;
	
	}
	
}

