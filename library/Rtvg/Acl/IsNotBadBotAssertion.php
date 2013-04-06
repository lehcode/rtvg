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
        	'Firefox/3.',
        	'Firefox/4.',
        	'Firefox/5.',
        	'Firefox/6.',
        	'Firefox/7.',
        	'Firefox/8.',
        	'Firefox/9.',
        	'Firefox/10.',
        	'Firefox/11.',
        	'Firefox/12.',
        	'Firefox/13.',
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