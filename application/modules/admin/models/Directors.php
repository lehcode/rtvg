<?php

class Admin_Model_Directors
{
	
	public function getDuplicates(){
		
		$table  = new Admin_Model_DbTable_Directors();
		$dupes  = array();
		$all    = $table->fetchAll()->toArray();
		$total  = count($all);
		$chunks = array_chunk($all, 250);
		$p=0;
		foreach ($chunks as $chunk) {
			foreach ($chunk as $item) {
				//var_dump($item);
				$p++;
				$d = $table->fetchAll(array(
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
				//die(__FILE__.': '.__LINE__);
			}
		}
		//die(__FILE__.': '.__LINE__);
	}
	
	public function fixNames(){
		$table = new Admin_Model_DbTable_Directors();
		$all = $table->fetchAll()->toArray();
		$chunks = array_chunk($all, 250);
		foreach ($chunks as $chunk) {
			foreach ($chunk as $item) {
				$parts = explode(' ', $item['f_name']);
				if (count($parts)==3) {
					//var_dump($parts);
					$table->update(array(
						'f_name'=>trim($parts[0]),
						'm_name'=>trim($parts[1]),
						's_name'=>trim($parts[2])), "`f_name`='".$item['f_name']."'");
				} elseif (count($parts)==2) {
					$table->update(array(
						'f_name'=>trim($parts[0]),
						'm_name'=>'',
						's_name'=>trim($parts[1])), "`f_name`='".$item['f_name']."'");
				} elseif (count($parts)>3) {
					$table->delete("`id`='".$item['id']."'");
				} else {
					continue;
				}
			}
		}
		//die(__FILE__.': '.__LINE__);
	}

}

