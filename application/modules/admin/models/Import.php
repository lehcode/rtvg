<?php
/**
 * Backend import actions
 * 
 * @author     Antony Repin <egeshisolutions@gmail.com>
 * @package backend
 * @version    $Id: Import.php,v 1.5 2013-04-03 04:08:16 developer Exp $
 *
 */
class Admin_Model_Import
{
	
	private $_broadcasts; 
	private $_propsTable; 
	private $_program_info; 

	public function __construct(){
		$this->_broadcasts = new Admin_Model_DbTable_Programs();
    	$this->_propsTable    = new Admin_Model_DbTable_ProgramsProps();
	}
	
	public function isPremiere($title=null){
		if (!$title)
		throw new Exception("Пропущен один или все параметры для ".__METHOD__, 500);
		
		if (Xmltv_String::stristr($title, 'премьера'))
		return true;
		
		return false;
		
	}
	
	
	public function savePremiere($xml){
		
		if (!$xml)
		throw new Exception("Пропущен один или все параметры для ".__METHOD__, 500);
		
		$f = Zend_Date::YEAR."-".Zend_Date::MONTH."-".Zend_Date::DAY." ". Zend_Date::HOUR.':'.Zend_Date::MINUTE.':'.Zend_Date::SECOND.' '.Zend_Date::GMT_DIFF;
		$date_str = $this->_getDateString((string)$xml->attributes()->start);
		$dates['start'] = new Zend_Date($date_str, $f, 'ru');
		$date_str = $this->_getDateString((string)$xml->attributes()->stop);
		$dates['end'] = new Zend_Date($date_str, $f, 'ru');
		$start = $dates['start']->toString("YYYY-MM-dd HH:mm:ss");
		$end   = $dates['end']->toString("YYYY-MM-dd HH:mm:ss");
		$hash = md5((int)$xml->attributes()->channel.$start.$end);
		
		try {
			if (!$data = $this->_broadcasts->fetchRow("`hash`='$hash'")) {
				$programs = new Admin_Model_Broadcasts();
				$data = $programs->parseProgramXml($xml);
			} else {
				$data = $data->toArray();
			}
			$data['title'] = $this->_cleanPremiereTitle($data['title']);
			$data['new']=1;
			$this->_saveProgramInfo($data, $hash);
			$this->_updatePremiereInfo($data['start'], $hash);
			
		} catch (Exception $e) {
			echo __METHOD__.' error#'.$e->getCode().': '.$e->getMessage();
			die(__FILE__.': '.__LINE__);
		}
		
		$this->setProgramInfo($data);
		
	}
	
	private function _getDateString($input=null){
	    
		if(!$input)	
		    return;
		
		$date['year']      = substr($input, 0, 4);
		$date['month']     = substr($input, 4,2);
		$date['day']       = substr($input, 6,2);
		$date['hours']     = substr($input, 8,2);
		$date['minutes']   = substr($input, 10,2);
		$date['seconds']   = substr($input, 12,2);
		$date['gmt_diff']  = substr($input, 16,4);
		return $date['year'].'-'.$date['month'].'-'.$date['day'].' '.$date['hours'].':'.$date['minutes'].':'.$date['seconds'].' '.$date['gmt_diff'];
		
	}
	
	public function getData(){
		return $this->_data;
	}
	/**
	 * @return array
	 */
	public function getProgramInfo () {

		return $this->_program_info;
	}

	/**
	 * @param array
	 */
	public function setProgramInfo ($programInfo) {

		$this->_program_info = $programInfo;
	}
	
	private function _saveProgramInfo ($data=array(), $hash=null){
		
		if (!$data || !is_array($data) || !$hash)
		throw new Exception("Пропущен один или все параметры для ".__METHOD__, 500);
		
		//var_dump($data);
		//die(__FILE__.': '.__LINE__);
		
		try {
			$this->_broadcasts->insert($data);
		} catch (Exception $e) {
			if ($e->getCode()==1062){
				try {
					$this->_broadcasts->update($data, "`hash`='$hash'");
				} catch (Exception $e) {
					echo __METHOD__.' Ошибка#'.$e->getCode().': '.$e->getMessage();
					die(__FILE__.': '.__LINE__);
				}
			} else {
				echo '<b>'.__METHOD__.' Ошибка#</b>'.$e->getCode().': '.$e->getMessage();
				die(__FILE__.': '.__LINE__);
			}
		}
	}
	
	private function _updatePremiereInfo($start=null, $hash=null){
		
		if (!$hash || !$start)
		throw new Exception("Пропущен один или все параметры для ".__METHOD__, 500);
		
		try {
			if (!$props = $this->_propsTable->fetchRow("`hash`='$hash'")) {
				$props = $this->_propsTable->createRow(array('hash'=>$hash), true);
			} 
			$props->premiere=1;
			$props->premiere_date=$start;
			$this->_propsTable->update($props->toArray(), "`hash`='$hash'");
		} catch (Exception $e) {
			echo __METHOD__.' error#'.$e->getCode().': '.$e->getMessage();
			die(__FILE__.': '.__LINE__);
		}
	}
	
	private function _cleanPremiereTitle($input=null){
		
		if (!$input)
		throw new Exception("Пропущен один или все параметры для ".__METHOD__, 500);
		
		$replace = new Zend_Filter_PregReplace(array('match'=>'/ ?премьера ?/iu', 'replace'=>''));
		$result = $replace->filter($input);
		
		$trim = new Zend_Filter_StringTrim(array('charlist'=>' -.'));
		$result = $trim->filter($result);
		
		return $result;
		
	}

	public function parseSavedPrograms($parsers=array(), Zend_Date $start, Zend_Date $end, $save=false){
		
		if (empty($parsers))
		throw new Exception("Пропущен параметр для ".__METHOD__, 500);
		
		$result = array();
    	foreach ($parsers as $parserClass) {
			
    		$parser = new $parserClass();
    		try  {
    			
    			if ((bool)$save===true)
    			$parser->saveChanges=true;
    			
    			$r = $parser->process($start, $end);
    			if (empty($r)) {
    				$data = print_r( $parser->getProgram(), true );
    				echo("<b>Ошибка обработки программы. Парсер: $parserClass</b>");
    				if (Xmltv_Config::getDebug())
    				Zend_Debug::dump($data);
    				die( __FILE__.': '.__LINE__ );
    			}
    			
    			foreach ($r as $item)
    			array_push($result, $item);
    	
    		} catch (Exception $e) {
    			echo $e->getMessage();
    			if (Xmltv_Config::getDebug())
    			Zend_Debug::dump($e->getTrace());
    		}
    		
    	}
    	
    	//var_dump(count($result));
    	//var_dump($result[0]);
    	//die(__FILE__.': '.__LINE__);
    	
    	return $result;
    	
	}
	
}

