<?php
/**
 * Check if enough priviliges to access resource
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @version $Id: Banner.php,v 1.2 2013-03-10 02:45:15 developer Exp $
 *
 */
class Zend_Controller_Action_Helper_Banner extends Zend_Controller_Action_Helper_Url
{
	/**
	 * Pick random ad
	 * 
	 * @param array $options
	 */
    public function random( array $options=null ){
    	
        die(__FILE__.': '.__LINE__);
    	
    }
	
    /**
     * (non-PHPdoc)
     * @see Zend_Controller_Action_Helper_Url::direct()
     * @param string $method
     * @param array  $params
     * @throws Zend_Exception
     */
 	public function direct($method=null, array $params=null)
    {
        
        if (!$method){
            throw new Zend_Exception( 'Не указан $method' );
        }
        
    	return $this->$method( $params );
    }
}