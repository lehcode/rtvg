<?php
class Xmltv_Block_Premieres extends Xmltv_Block 
{
	public function getHtml(){
		
		$programs = new Xmltv_Model_DbTable_Programs();
		$programs_props = new Xmltv_Model_DbTable_ProgramsProps();
		$programs_descriptions = new Xmltv_Model_DbTable_ProgramsDescriptions();
		$date = new Zend_Date(null, null, 'ru');
		$today = $date->toString(Zend_Date::WEEKDAY_DIGIT);
		if ($today==0) {
			$date->subDay(6, 'ru');
		} elseif ($today>1) {
			do {
				$date->subDay(1, 'ru');	
			} while( $date->toString(Zend_Date::WEEKDAY_DIGIT)>1 );
		} 
		$week_first = $date;
		
		$date = new Zend_Date(null, null, 'ru');
		if ($today>0) {
			do {
				$date->addDay(1, 'ru');	
			} while( $date->toString(Zend_Date::WEEKDAY_DIGIT)<6 );
			$date->addDay(1, 'ru');
		} 
		$week_last = $date;
		
		$programs_list = $programs->fetchAll(array(
			"`start` >= '".$week_first->toString('yyyy-MM-dd 00:00:00')."'",
			"`end` <= '".$week_last->toString('yyyy-MM-dd 23:59:59')."'",
			"`new` = '1'"
		));
		var_dump(count($programs_list));
		var_dump($week_first->toString('yyyy-MM-dd 00:00:00'));
		var_dump($week_last->toString('yyyy-MM-dd 23:59:59'));
		die(__FILE__.': '.__LINE__);
		//var_dump($today->toString(Zend_Date::WEEKDAY_DIGIT));
		
		
		
		//$programs->fetchAll();
		
		
		ob_start();
		?>
		
		<?php 
		return ob_get_clean();
	}
}