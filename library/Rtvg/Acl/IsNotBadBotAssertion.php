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
        	    $this->_exception();
        	}
        }
        
        $badAgents = array(
        	'libwww-perl',
        	'MJ12bot',
        	'Nutch',
        	'cr4nk',
        	'SISTRIXCrawler',
        	'SearchBot',
        );
        foreach ($badAgents as $string){
        	if (stristr($userAgent, $string)) {
        		$this->_exception();
        	}
        }
        
        // Check by browser type
        $browserType = $this->_userAgent->getBrowserType();
        
        $allowedTypes = array( 'desktop', 'mobile', 'validator', 'feed', null, '' );
        if (!in_array($browserType, $allowedTypes) ){
            $this->_exception();
        }
        
        $badIps = array(
        	'46.164.176.66',
        	'38.99.82.243',
        	'192.168',
        );
        foreach ($badIps as $ip){
            if (stristr($ip, @$_SERVER['HTTP_HOST'])){
                $this->_exception();
            }
        }
        
        return true;
        
    }
    
    private function _exception(){
        throw new Zend_Exception( Rtvg_Message::ERR_NO_AUTH, 401 );
    }
    
}