<?php
/**
 * Frontend programs listings controller
 * 
 * @author  Antony Repin
 * @uses    Xmltv_Controller_Action
 * @version $Id: TorrentsController.php,v 1.5 2013-03-03 23:34:13 developer Exp $
 *
 */
class TorrentsController extends Xmltv_Controller_Action
{
	
	/**
	 * 
	 * Search for torrents on given keyword
	 * @throws Zend_Exception
	 */
	public function finderAction(){
		
		//var_dump($this->_getAllParams());
		//die(__FILE__.': '.__LINE__);
		
		if ($this->requestParamsValid()){
		    
			try {
				$url = 'http://torrent-poisk.com/search.php?q='.urlencode($this->_getParam('w'));
				//var_dump($url);
				$curl = new Xmltv_Parser_Curl();
				$curl->setOption(CURLOPT_CONNECTTIMEOUT, 5);
				$curl->setOption(CURLOPT_TIMEOUT, 5);
				$curl->setUrl($url);
				//$curl->setUserAgent($_SERVER['HTTP_USER_AGENT']);
				$torrents = array();
				parent::getCache()->setLifetime(86400);
				$hash = Xmltv_Cache::getHash($url);
				if (($html = parent::getCache()->load($hash, 'Core', '/Torrents'))===false) {
					$html  = $curl->fetch(Xmltv_Parser_Curl::PAGE_HTML);
					parent::getCache()->save($html, $hash, 'Core', '/Torrents');
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
		
		
		
	}
	
	/**
	 * Validate nad filter request parameters
	 *
	 * @throws Zend_Exception
	 * @throws Zend_Controller_Action_Exception
	 * @return boolean
	 */
	protected function requestParamsValid(){
	
		// Validation routines
		$this->input = $this->validator->direct(array('isvalidrequest', 'vars'=>$this->_getAllParams()));
		if ($this->input===false) {
			if (APPLICATION_ENV=='development'){
				//var_dump($this->_getAllParams());
				die(__FILE__.': '.__LINE__);
			} elseif(APPLICATION_ENV!='production'){
				throw new Zend_Exception(self::ERR_INVALID_INPUT, 500);
			}
			$this->_redirect($this->view->url(array(), 'default_error_missing-page'), array('exit'=>true));
	
		} else {
	
			foreach ($this->_getAllParams() as $k=>$v){
				if (!$this->input->isValid($k)) {
					throw new Zend_Controller_Action_Exception("Invalid ".$k.'!');
				}
			}
	
			return true;
	
		}
	
	}
	
}