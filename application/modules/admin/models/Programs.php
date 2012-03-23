<?php

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
        $result = preg_replace('/[:;_,\.\[\]\(\)\\`\{\}"\!]/ius', '-', $result);
        $result = preg_replace('/[0-9]+-я.*серия/ius', '', $result);
        $result = preg_replace('/[0-9]+-я.*-.*[0-9]+-я.*серии/ius', '', $result);
        $result = str_replace(array('--'), '-', $result);
        $result = str_replace('--', '-', $result);
        $result = trim($result, '- ');
        return Xmltv_String::strtolower($result);
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
		
		if (empty($info)) return;
		
		$props = new Admin_Model_DbTable_ProgramsProps();
		$new = $props->createRow();
		$new->prog_id  = $info['id'];
		$new->premiere = 1;
		$new->premiere_date = $info['start'];
		$new->save();
		
	}
	
	public function getCredits($input=null){
		if (!$input) return;
		//var_dump($info);
		//die(__FILE__.': '.__LINE__);
		$d = explode('…', $input);
		$p = explode('.  ', $d[1]);
		$result = array();
		$f = new Zend_Filter_StringTrim();
		var_dump($p);
		foreach ($p as $k=>$i) {
			$p[$k]=$f->filter($i);
			if (preg_match('/^В.*ролях:/isu', $p[$k])) {
				$array = preg_replace('/^В.*ролях:(.+)$/isu', '\1', $p[$k]);
				$array = explode(',', $array);
				foreach ($array as $i) {
					$result['actors'][] = trim($f->filter($i), '. ');
				}
			}
			if (preg_match('/^Режиссер.*:/isu', $p[$k])) {
				$array = preg_replace('/^.*Режиссер:(.+)[\.]?.*$/isu', '\1', $p[$k]);
				$array = explode(',', $array);
				foreach ($array as $i) {
					$result['directors'][] = trim($f->filter($i), '. ');
				}
			}
		}
		return $result;
		//die(__FILE__.': '.__LINE__);
	}
	
	public function saveDescription($info=array()){
		if (empty($info)) return;
		$descriptions = new Admin_Model_DbTable_ProgramsDescriptions();
		$desc_len = Xmltv_String::strlen($info['desc']);
		if ($desc_len>256) {
			var_dump($desc_len);
			$desc = explode('. ', $info['desc']);
			var_dump($desc);
			die(__FILE__.': '.__LINE__);
		}
		//var_dump($desc_len);
		die(__FILE__.': '.__LINE__);
	}
	
	public function getDescription($input=null){
		$d = explode('…', $input);
		$d = trim($d[0]);
		$expl = explode('.', $d);
		$desc['intro'] = trim($expl[0]).'. '.trim($expl[1]).'.';
		unset($expl[0]); unset($expl[1]);
		$desc['body'] = trim(implode('. ', $expl)).'.';
		return $desc;
	}
	
	public function saveCredits($credits=array()){
		if (empty($credits)) return;
		var_dump($credits);
		$actors    = new Admin_Model_DbTable_Actors();
		$directors = new Admin_Model_DbTable_Directors();
		
		foreach ($credits['actors'] as $p) {
			
			$expl = explode(' ', $p);
			if (count($expl)==3){
				var_dump($expl);
				die(__FILE__.': '.__LINE__);
			} elseif (count($expl)==2){
				//var_dump($expl);
				//die(__FILE__.': '.__LINE__);
				$fname  = $expl[0];
				$sname  = $expl[1];
				$snames = $actors->fetchAll("`s_name` LIKE '$sname'");
				//var_dump(count($snames));
				if (count($snames)) {
					foreach ($snames as $sn) {
						die(__FILE__.': '.__LINE__);
						//var_dump($snames->current());
						//var_dump($sn->name);
					}
				} else {
					$ad['f_name'] =  $expl[0];
					$ad['s_name'] =  $expl[0];
					$actors->insert();
				}
				//var_dump($snames);
				die(__FILE__.': '.__LINE__);
			} else {
				continue;
			}
			die(__FILE__.': '.__LINE__);
		}
		
		die(__FILE__.': '.__LINE__);
	}
	
	
}

