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
	    
	    $bots=array(
	    	'google',
	    	'yandex',
	    	'mail.ru',
	    	'ahrefs',
	    	'wget',
	    	'php',
	    );
	    foreach ($bots as $name){
	    	if ( stristr( $this->_userAgent->getUserAgent(), $name )){
	    	    return false;
	    	}
	    }
	    
	    
	    switch ($this->_userAgent->getBrowserType()){
	        
	    	case 'desktop':
	    	case 'mobile':
	    	case 'validator':
	    	case 'feed':
	    	    return true;
	    	break;
	    	
	    	case 'bot':
	    	case 'checker':
	    	case 'spam':
	    	    return false;
	    	break;
	    	    
	    	
	    }
		
		return true;

	}

	protected function _userAgentIsBot()
	{
		// ...
	}
}