<?php

class Xmltv_Parser_Listings_Vsetvcom
{

	private $_wrap;
	private $_counter=0;
	protected $channels_info=array();

	public function __construct(){
		
	}
	
	public function setWrap(Zend_Dom_Query_Result $domElement){
		$this->_wrap = $domElement;
	}
	
	public function parseProgramsListing () {

	}
	
	public function parseChannelsList () {
		foreach ($this->_wrap as $main) {
    		foreach($main->childNodes as $element){
    			if ($element->nodeName=='div') {
    				$parts = explode( "\t", html_entity_decode( utf8_decode($element->nodeValue)) );
    				foreach ($parts as $string) {
    					if (preg_match('/^([^a-z]+) ?+$/iu', trim($string), $m)) {
    						$section = Xmltv_String::strtolower($m[1]);
    						if ($section=='украинские каналы (программа на русском языке)') {
    							$table = $element->nextSibling->nextSibling;
    							$this->_parseTable($table, 'ru', 'ua');
    						} elseif ($section=='российские каналы') {
    							$table = $element->nextSibling->nextSibling;
    							$this->_parseTable($table, 'ru', 'ru');
    						} elseif ($section=='европейские каналы') {
    							$table = $element->nextSibling->nextSibling;
    							$this->_parseTable($table, 'var', 'eu');
    						} elseif ($section=='каналы снг') {
    							$table = $element->nextSibling->nextSibling;
    							$this->_parseTable($table, 'var', 'sng');
    						} elseif ($section=='украинские каналы (программа на украинском языке)') {
    							$table = $element->nextSibling->nextSibling;
    							$this->_parseTable($table, 'ua', 'ua');
    						} else {
    							throw new Exception('Неизвестный раздел', 500);
    						}
    					}
    					
    				}
    			}
    		}
    		
    	}
    	sort($this->channels_info);
	}

	private function _parseTable(DOMElement $table, $lang=null, $country=null){
		
		if (empty($table))
		throw new Exception("Неверные параметры для ".__FUNCTION__, 500);
		
		foreach ($table->childNodes as $t){
	    	//var_dump($table->childNodes);
			if ($t->nodeName=='td'){
	    		if ($t->childNodes->length>1){
	    			foreach ($t->childNodes as $candidate)
	    			$this->_pushChannel($candidate, $lang, $country);
	    		}
	    		$this->_counter++;
	    	}
	    }
	}

	private function _pushChannel($candidate=null, $lang=null, $country=null){
		
		if (!$candidate) {
			throw new Exception("Неверные параметры для ".__FUNCTION__, 500);
		}
		
		if (!is_a($candidate, 'DOMElement')) {
			return;
		}
		
		$trim   = new Zend_Filter_StringTrim();
		$aliaser = new Zend_Filter_PregReplace();
		$aliaser->setMatchPattern('/[\.\(\)\?:;\/"\'\! ]+/iu');
		$aliaser->setReplacement('-');
		$plus = new Zend_Filter_Word_SeparatorToSeparator('+', '-плюс-');
		$trim = new Zend_Filter_StringTrim(array('charlist'=>'- '));
		$tolower = new Zend_Filter_StringToLower();
		$tripleDash = new Zend_Filter_Word_SeparatorToSeparator('---', '-');
		$doubleDash = new Zend_Filter_Word_SeparatorToSeparator('--', '-');
		$and = new Zend_Filter_Word_SeparatorToSeparator('&', 'and');
		if ($candidate->nodeName=='span' && $candidate->getAttribute('class')=='name'){
    		$this->channels_info[$this->_counter]['title']= $trim->filter( utf8_decode($candidate->nodeValue) );
    		$this->channels_info[$this->_counter]['alias']= $and->filter( $doubleDash->filter( $tripleDash->filter( $tolower->filter( $trim->filter( $plus->filter( $aliaser->filter( $this->channels_info[$this->_counter]['title'])))))));
    	}
    	
    	if ($candidate->nodeName=='a' && (bool)$candidate->getAttribute('class')===false){
    		$this->channels_info[$this->_counter]['URI'] = '/'.$candidate->getAttribute('href');
    	}
    	
    	if ($candidate->nodeName=='a' && $candidate->getAttribute('class')=='smallgrey'){
    		$this->channels_info[$this->_counter]['site'] = $candidate->getAttribute('href');
    	}
    	
    	$this->channels_info[$this->_counter]['lang']=$lang;
    	$this->channels_info[$this->_counter]['country']=$country;
    	
	}
	/**
	 * @return array
	 */
	public function getChannelsInfo () {

		return $this->channels_info;
	}

}