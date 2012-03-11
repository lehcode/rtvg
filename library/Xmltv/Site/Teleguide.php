<?php
class Xmltv_Site_Teleguide extends Xmltv_Site 
{
	
	public $pagesLinks=array();
	public $alphaLinks=array();
	public $moviesLinks=array();
	public $proxy = array('host'=>'','port'=>3128);
	public $limit = 25;
	
	private static $_instance;
	protected $_DOM;
	protected $_HTML;
	private $_parser;
	private $_config;
	private $_encoding='windows-1251';
	private $_retry=array();
	
	protected $_siteKey='teleguide';
	protected $_baseUrl;
	
	
	public function __construct() { 
		parent::__construct();
		// load parser
		$this->_parser = Xmltv_Parser_StringParser::getInstance();
		// setup date object
		$this->_date = new Zend_Date();
		$this->_baseUrl = $this->_config->{$this->_siteKey}->baseUrl;
		
		
		
	}
	
	
	public function setConfig($config=null){
		if(!$config)
		return;
		$this->_config = new Zend_Config_Xml(APPLICATION_PATH . '/configs/sites.xml', 'movies');
	}
	
	
	private function _getNodeValue($dom_path='', $regex=null) {
		
		$value = trim($this->_DOM->query($dom_path)->current()->nodeValue);
		
		if ($regex) {
			$regex = addslashes($regex);
			$value = preg_replace("/$regex/u", '', $value);
		}
		
		return $value;
	}
	
	/**
	 * @return string
	 */
	public function getEncoding() {
		return $this->_encoding;
	}

	/**
	 * @param $_encoding
	 */
	public function setEncoding($encoding) {
		$this->_encoding = $encoding;
	}

	

	public function getPaginationLinks(){
		$this->pagesLinks=array();
		try {
			$dom = $this->_DOM->query('table.main .pagination');
		} catch (Zend_Exception $e) {
			echo $e->getMessage();
			die();
		}
		
		$dom_links = $dom->next()->getElementsByTagName('a');
		foreach ($dom_links as $current) {
			$this->pagesLinks[]=str_replace($this->_baseUrl, '', $current->getAttribute('href'));
		}
		$this->pagesLinks=array_unique($this->pagesLinks);
		
	}
	
	
	
	public function getAlphaLinks(){
		$this->alphaLinks=array();
		try {
			$dom = $this->_DOM->query('table.main .pagination');
		} catch (Zend_Exception $e) {
			echo $e->getMessage();
			die();
		}
		
		$dom_links = $dom->current()->getElementsByTagName('a');
		foreach ($dom_links as $current) {
			$this->alphaLinks[]=str_replace($this->_baseUrl, '', $current->getAttribute('href'));
		}
		$this->alphaLinks=array_unique($this->alphaLinks);
		
	}
	
	
	
	public function getMoviesLinks(){
		
		$this->moviesLinks=array();
		try {
			$tables = $this->_DOM->query('table.main');
		} catch (Zend_Exception $e) {
			echo $e->getMessage();
			die();
		}
		
		foreach ($tables as $current){
			$attrs = $current->attributes;
			$tds   = $current->getElementsByTagName('td');
			if ($tds->length==2) {
				$links = $tds->item(1)->getElementsByTagName('a');
				if ($links->length>10) {
					foreach ($links as $link){
						$link_text = $this->fix_encoding($link->nodeValue);
						$href = $link->getAttribute('href');
						if ($href && preg_match('/^\/film[0-9]+\.html$/', trim($link->getAttribute('href'))))
						$this->moviesLinks[]=$link->getAttribute('href');
					}
				}
			}
		}
		$this->moviesLinks=array_unique($this->moviesLinks);
		
	}
	
	public function getMovieInfo($uri=null){
		
		try {
			$this->fetchPage($uri, $this->_encoding);
		} catch (Exception $e) {
			echo $e->getMessage();
			die(__METHOD__);
		}
		
		//var_dump($this->_HTML);
		//die();
		
		try {
			$dom_tables = $this->_DOM->query('table');
		} catch (Exception $e) {
			echo $e->getMessage();
			die(__METHOD__);
		}
		
		$submenus=array();
		foreach ($dom_tables as $table) {
			if ($table->getAttribute('class')=='submenus') {
				if ($table->nextSibling !== null && $table->nextSibling->tagName=='table') {
					$table2 = $table->nextSibling;
					if ($table2->getAttribute('class')=='main') {
						$table3 = $table2->nextSibling;
						if ($table3->getAttribute('class')=='main') {
							$dom_title = $table;
							$dom_desc = $table2;
							$dom_images = $table3;
							//var_dump($table3->getAttribute('class'));
						}
					}
					
				}
			}
		}
		//var_dump($dom_title);
		//var_dump($dom_desc);
		//var_dump($dom_images);
		
		if (!$dom_title || !$dom_desc || !$dom_images) {
			//throw new Exception("Empty data received from page ".$this->_baseUrl.$uri);
			$this->_retry[]=$uri;
		}
		
		$info = array();
		foreach ($dom_title->getElementsByTagName('td') as $td) {
			$v = trim($td->nodeValue);
			if (!empty($v) && strlen($v)>2) {
				//var_dump($this->fix_encoding($v));
				$title_text=$this->fix_encoding($v);
				
				preg_match('/^(.+)\(/', $title_text, $matches);
				$info['title']['ru']=trim($matches[1], ' "\'');
				
				preg_match('/\((.+)\)/', $title_text, $matches);
				if (@$matches[1])
				$info['title']['orig']=$matches[1];
				else
				$info['title']['orig']='';
				
			}
		}
		
		foreach ($dom_desc->getElementsByTagName('td') as $td) {
			foreach ($td->getElementsByTagName('img') as $img) {
				$info['poster'] = $img->getAttribute('src');
			}
			if ($td->getAttribute('width')=='100%') {
				$text = $this->fix_encoding($td->nodeValue);
				$text = preg_replace('/\s/', ' ', $text);
				//var_dump($text);
				preg_match('/Премьера в мире: ([0-9]{2}.[0-9]{2}.[0-9]{4})/isU', $text, $m);
				if (@$m[1] && !empty($m[1])) {
					$pr = new Zend_Date($m[1]);
					$info['premiere']=$pr->toString(DATE_MYSQL_SHORT);
				}
				preg_match('/Премьера в России: ([0-9]{2}.[0-9]{2}.[0-9]{4})/isU', $text, $m);
				if (@$m[1] && !empty($m[1])) {
					$pr = new Zend_Date($m[1]);
					$info['premiere_ru']=$pr->toString(DATE_MYSQL_SHORT);
				}
				preg_match('/Продолжительность: ([0-9]+) минут/isU', $text, $m);
				if (@$m[1] && !empty($m[1])) {
					$info['length']=(int)$m[1];
				}
				preg_match('/Жанр: (.+) Режиссёр:/isU', $text, $m);
				if (@$m[1] && !empty($m[1])) {
					$info['genre']=trim($m[1]);
				}
				preg_match('/Режиссёр: (.+) В ролях:/isU', $text, $m);
				if (@$m[1] && !empty($m[1])) {
					$info['director']=trim($m[1]);
				}
				preg_match('/Сюжет: (.+)$/isU', $text, $m);
				if (@$m[1] && !empty($m[1])) {
					$info['story']=trim($m[1]);
				}
				preg_match('/В ролях: (.+) Сюжет:/isU', $text, $m);
				if (@$m[1] && !empty($m[1])) {
					$actors=trim($m[1]);
					$actors = explode('/', $actors);
					foreach ($actors as $k=>$a){
						$actors[$k]=trim($a);
					}
					$info['actors']=$actors;
				}
			}
		}
		var_dump($info);
		die(__METHOD__);
	}
	
	public function saveInfo($target='file'){
		
	}
	
}