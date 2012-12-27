<?php
/**
 * Frontend programs listings controller
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: TorrentsController.php,v 1.2 2012-12-27 17:04:37 developer Exp $
 *
 */
class TorrentsController extends Zend_Controller_Action
{
	
	public function __call ($method, $arguments) {
		if (APPLICATION_ENV=='production'){
			header( 'HTTP/1.0 404 Not Found' );
			$this->_helper->layout->setLayout( 'error' );
			$this->view->render();
		}
	}
	
	public function init(){
		$this->view->setScriptPath( APPLICATION_PATH . '/views/scripts/' );
		$this->siteConfig = Zend_Registry::get( 'site_config' )->site;
	}
	
	/**
	 * 
	 * Search for torrents on given keyword
	 * @throws Zend_Exception
	 */
	public function finderAction(){
		
		//var_dump($this->_getAllParams());
		//die(__FILE__.': '.__LINE__);
		/*
		if ($this->_validateRequest()){
			try {
				$url = 'http://torrent-poisk.com/search.php?q='.urlencode($this->_getParam('w'));
				//var_dump($url);
				$curl = new Xmltv_Parser_Curl();
				$curl->setOption(CURLOPT_CONNECTTIMEOUT, 5);
				$curl->setOption(CURLOPT_TIMEOUT, 5);
				$curl->setUrl($url);
				$curl->setUserAgent($_SERVER['HTTP_USER_AGENT']);
				$torrents = array();
				$cache = new Xmltv_Cache(array('location'=>'/cache/Listings'));
				$cache->setLifetime(86400);
				$hash = $cache->getHash($url);
				if (($html=$cache->load($hash, 'Core', '/Torrents'))===false) {
					$html = $curl->fetch(Xmltv_Parser_Curl::PAGE_HTML);
					$cache->save($html, $hash, 'Core', '/Torrents');
				}
				if ($html){
					$dom = new DOMDocument('1.0', 'UTF-8');
			    	$dom->preserveWhiteSpace = false;
			    	$dom->recover = true;
			    	$dom->strictErrorChecking = false;
			    	@$dom->loadHTML($html);
			    	$xpath = new DOMXPath($dom);
		    		$links = $xpath->query("descendant-or-self::a[contains(concat(' ', normalize-space(@class), ' '), ' visit ')]");
				}
				foreach ($links as $link){
					//var_dump($link->getAttribute('href'));
					echo($link->nodeValue);
				}
				
				//var_dump($torrentLinks);
				//var_dump(count($torrentLinks));
				//echo $dom->getDocument();
				//die(__FILE__.': '.__LINE__);
				
			} catch (Exception $e) {
				throw new Zend_Exception($e->getMessage(), $e->getCode(), $e);
			}
			
		} else {
			throw new Zend_Exception(__METHOD__." - Неверные данные");
		}
		
		*/
		
	}
	
	/**
	 * 
	 * Request parameters validation
	 */
	private function _validateRequest(){
		
		//var_dump($this->_getAllParams());
		//die(__FILE__.': '.__LINE__);
		
		$filters = array('*'=>'StringTrim', '*'=>'StringToLower');
		$validators = array(
			'module'=>array(new Zend_Validate_Regex( '/^[a-z]+$/u' )), 
			'controller'=>array(new Zend_Validate_Regex( '/^[a-z]+$/' )), 
			'action'=>array(new Zend_Validate_Regex( '/^[a-z-]+$/' )),
		);
		
		$input = new Zend_Filter_Input( $filters, $validators, $this->_requestParams );
		
		if ($this->_getParam('action')=='finder'){
			$validators['w'] = new Zend_Validate_Regex('/[\w\d -+]/');
		}
		
		if( $input->isValid() ) {
			return true;
		}
		return false;
		
	}
	
}