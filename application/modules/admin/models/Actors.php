<?php

class Admin_Model_Actors
{

	public function getDuplicates(){
		
		$actors = new Admin_Model_DbTable_Actors();
		$dupes  = array();
		$all    = $actors->fetchAll()->toArray();
		$total  = count($all);
		$chunks = array_chunk($all, 250);
		$p=0;
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
			    		$origin_f = mb_strtolower(str_replace('ё', 'е', $origin['f_name']));
			    		$origin_m = mb_strtolower(str_replace('ё', 'е', $origin['m_name']));
			    		$origin_s = mb_strtolower(str_replace('ё', 'е', $origin['s_name']));
			    		$dupe_f = mb_strtolower(str_replace('ё', 'е', $dupe['f_name']));
			    		$dupe_m = mb_strtolower(str_replace('ё', 'е', $dupe['m_name']));
			    		$dupe_s = mb_strtolower(str_replace('ё', 'е', $dupe['s_name']));
			    		$origin_name = $origin_f.' '.$origin_m.' '.$origin_s.' '.$origin['rank'];
			    		$dupe_name   = $dupe_f.' '.$dupe_m.' '.$dupe_s.' '.$dupe['rank'];
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
				
				$expl = explode(' ', $item['name']);
				
				if (count($expl)>3) {
					$actors->delete("`id`='".$item['id']."'");
				} elseif (count($expl)==3) {
					$item['name'] = trim($expl[0]);
					$m_name = trim($expl[1]);
					if (mb_strstr($m_name, '(')) {
						$actors->delete("`id`='".$item['id']."'");
					} else {
						$item['m_name'] = $m_name;
						$item['s_name'] = trim($expl[2]);
						$actors->update($item, "`id`='".$item['id']."'");
					}
					//var_dump($item);
					//die(__FILE__.': '.__LINE__);
				} elseif (count($expl)==2) {
					$f = $this->_fixNameRank($item);
					$expl = explode(' ', $f['name']);
					$name = trim($expl[0]);
					if (mb_strlen($name)>2) {
						$f['f_name'] = $name;
						$f['s_name'] = trim($expl[1]);
						$actors->update($f, "`id`='".$f['id']."'");
					} elseif (mb_strlen($name)>=32) {
						$actors->delete("`id`='".$item['id']."'");
					}
					//var_dump($p);
					//var_dump($expl);
					//die(__FILE__.': '.__LINE__);
				} else { 
					if (!$item['name_en']) {
						$tl = new Xmltv_Cyrillic_Translit();
						$translit = $tl->transliterate_return($item['name'].' '.$item['m_name'].' '.$item['s_name']);
						if (!preg_match('/[a-zA-Z- ]+/', $translit)) {
							var_dump($translit);
							die(__FILE__.': '.__LINE__);
						}
					}
				}
				
			}
		}
		return true;
		//die(__FILE__.': '.__LINE__);
	}
	
	private function _fixNameRank($data=null){
		if (!$data) return;
		if (stristr($data['name'], '-мл')) {
			$data['name']=str_ireplace(array('-младший'),'',$data['name']);
			$data['name']=str_ireplace(array('-мл'),'',$data['name']);
			$data['rank']='мл.';
		}
		if (stristr($data['name'], '-ст')) {
			$data['name']=str_ireplace(array('-старший'),'',$data['name']);
			$data['name']=str_ireplace(array('-ст'),'',$data['name']);
			$data['rank']='ст.';
		}
		return $data;
		
	}
	
	
	/*
	public function getClonesOf($id=null, $name=null) {
		
		if (!$id || !$name) return;
		
		var_dump(func_get_args());
		
		$actors = new Admin_Model_DbTable_Actors();
		$d = $this->getDuplicates();
		//var_dump($actors);
		//die();
		$r = array();
		if (count($d)) {
			foreach ($d as $row) {
				var_dump($row->toArray());
			}
			die(__FILE__.': '.__LINE__);
		}
		return $r;
		
	}
	*/
}

