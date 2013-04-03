<?php
class Rtvg_Acl_IsNotBadBotAssertion implements Zend_Acl_Assert_Interface
{
    
    private $_userAgent;
    
    /**
     * (non-PHPdoc)
     * @see Zend_Acl_Assert_Interface::assert()
     */
    public function assert( Zend_Acl $acl,
    		Zend_Acl_Role_Interface $role = null,
    		Zend_Acl_Resource_Interface $resource = null,
    		$privilege = null )
    {
        
        //die(__FILE__.': '.__LINE__);
        
        $this->_userAgent = Zend_Controller_Front::getInstance()
        	->getParam('bootstrap')
        	->getResource('useragent');
        $userAgent = $this->_userAgent->getUserAgent();
        $badAgents = array(
        	'-',
        	'Windows NT 6.1',
        	'Mozilla/5.0 (compatible; SearchBot)',
        	'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.2) Gecko/20100115 Firefox/3.6',
        	'Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.9.2.15) Gecko/20110303 Firefox/3.6.15',
        	'Mozilla/5.0 (Windows NT 6.1; rv:12.0) Gecko/20100101 Firefox/12.0',
        	'Mozilla/5.0 (Windows; U; Windows NT 6.1; rv:11.0) Gecko/20100101 Firefox/11.0',
        	'Mozilla/5.0 (X11; Linux i686; rv:6.0) Gecko/20100101 Firefox/6.0',
        );
        foreach ($badAgents as $bad){
        	if ($userAgent==$bad) {
        	    //die(__FILE__.': '.__LINE__);
        	    $this->_exception();
        	}
        }
        
        $badAgents = array(
        	'libwww-perl',
        	'MJ12bot',
        	'Nutch',
        	'cr4nk',
        	'Epiphany/2.',
        	'SISTRIXCrawler',
        	'SearchBot',
        );
        foreach ($badAgents as $string){
        	if (stristr($userAgent, $string)) {
        		$this->_exception();
        	}
        }
        
        //var_dump($userAgent);
        //die(__FILE__.': '.__LINE__);
        
        // Check by browser type
        $browserType = $this->_userAgent->getBrowserType();
        
        $allowedTypes = array( 'desktop', 'mobile', 'validator', 'feed', null, '' );
        if (!in_array($browserType, $allowedTypes) ){
            $this->_exception();
        }
        
        $badIps = array(
        	'46.164.176.66',
        	'38.99.82.243',
        );
        if (in_array($_SERVER['HTTP_HOST'], $badIps)) {
        	$this->_exception();
        }
        
        return true;
        
    }
    
    private function _exception(){
        throw new Zend_Exception( Rtvg_Message::ERR_NO_AUTH, 401 );
    }
    
}