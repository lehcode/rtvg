<?php
class Rtvg_Acl_IsNotBotAssertion implements Zend_Acl_Assert_Interface
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
		
	    /**
	     * @var Zend_Http_UserAgent_AbstractDevice
	     */
	    $device = $this->_userAgent->getDevice();
	    
	    if (APPLICATION_ENV=='development'){
	        //var_dump($this->_userAgent->getBrowserType());
	        //var_dump($this->_userAgent->getUserAgent());
	    	//die(__FILE__.': '.__LINE__);
	    }
	    
	    $allowedTypes = array( 'desktop', 'mobile', 'validator', 'feed' );
	    $bots=array(
    		'google',
    		'yandex',
    		'mail.ru',
    		'ahrefs',
    		'rambler',
    		'wget',
    		'php',
    		'zend',
    		'spider',
    		'bot',
    		'crawler',
	    );
	    
	    $userAgent = $this->_userAgent->getUserAgent();
	    foreach ($bots as $string){
	    	if (stristr($userAgent, $string)) {
	    	    //die(__FILE__.': '.__LINE__);
	    		return false;
	    	}
	    }
	    
	    $browserType = $this->_userAgent->getBrowserType();
	    if ( in_array($browserType, $allowedTypes) ){
	        //die(__FILE__.': '.__LINE__);
	        return true;
	    }
	    
	    //die(__FILE__.': '.__LINE__);
	    return false;

	}

}