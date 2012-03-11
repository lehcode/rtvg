<?php

class Xmltv_Model_Programs
{
	public function getProgramsForDay($date=null, $ch_id=null){
		
		$day = $date->toString(DATE_MYSQL_SHORT);
		$table = new Xmltv_Model_DbTable_Programs();
		try {
			$rows = $table->fetchAll(array("`start` LIKE '$day%'", "`ch_id`='$ch_id'"), "start ASC");
		} catch (Exception $e) {
			$e->getMessage();
			return false;
		}
		
		$list = array();
		$c=0;
		
		$desc_table = new Xmltv_Model_DbTable_ProgramsDescriptions();
		foreach ($rows as $prog) {
			
			$prog_start = new Zend_Date($prog->start);
			$prog_end = new Zend_Date($prog->end);
			$list[$c]=$prog->toArray();
			$list[$c]['now_showing'] = false;
			
			$compare_start = $prog_start->compare(Zend_Date::now());
			$compare_end   = $prog_end->compare(Zend_Date::now());
			
			if (($compare_start==-1 || $compare_start==0) && ($compare_end==1 || $compare_end==0))
			$list[$c]['now_showing'] = true;
			
			$list[$c]['start_timestamp'] = $prog_start->toString(Zend_Date::TIMESTAMP);
			$list[$c]['new'] = (int)$list[$c]['new'];
			
			$serializer = Zend_Serializer::factory('Json');
			try {
				$list[$c]['image'] = $serializer->unserialize($list[$c]['image']);
			} catch (Zend_Serializer_Exception $e) {
			    $image = array(
			    	'url'=>'',
			    	'width'=>'',
			    	'height'=>'',
			    );
			    $s = $serializer->serialize($image);
			    $prog->image = $s;
			    $prog->save();
			    $list[$c]['image'] = $serializer->unserialize($s);
			}
			
			$desc = $desc_table->fetchRow("prog_id='$prog->id'");
			$list[$c]['desc'] = array('intro'=>$desc->desc_intro, 'body'=>$desc->desc_body);
			$list[$c]['start'] = new Zend_Date($prog->start);
			$list[$c]['end'] = new Zend_Date($prog->end);
			
			$c++;
		}
		//die();
		return $list;
		
	}

}

