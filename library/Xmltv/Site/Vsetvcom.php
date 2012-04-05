<?php
class Xmltv_Site_Vsetvcom extends Xmltv_Site 
{
	
	public function __construct() {
		parent::__construct(); 
		$this->_parser = Xmltv_Parser_StringParser::getInstance();
		$this->_date = new Zend_Date();
		//var_dump($this);
		//die(__FILE__.': '.__LINE__);
	}
	
	/**
	 * @return string
	 */
	public function getBaseUrl () {

		return $this->_baseUrl;
	}


	/**
	 * @param $_baseUrl string
	 */
	public function setBaseUrl ($url = null) {

		$this->_baseUrl = $url;
	}

	
}