<?php
/**
 * 
 * @author Antony Repin
 * @uses   Zend_Controller_Action_Helper_Abstract
 *
 */
class Xmltv_Controller_Action_Helper_ValidateForm extends Zend_Controller_Action_Helper_Abstract
{

    /**
     * 
     * @param Zend_Form $form
     * @param array $data
     */
	private function _validateForm ( Zend_Form $form, array $data ) {

	    $errors = array();
    	if(!$form->isValid($data)) {
    	    
    	    if (APPLICATION_ENV=='development'){
    	        //var_dump($form->getMessages());
    	        //die(__FILE__.': '.__LINE__);
    	    }
    	    
    	    $formMessages = $form->getMessages();
    		foreach($formMessages as $field=>$message) {
    		    foreach($message as $error) {
    		        if ($field=='openid'){
    		            if (array_key_exists('regexNotMatch', $message)){
    		                $errors[] = array($field => Rtvg_Message::ERR_WRONG_LOGIN);
    		            }
    		        } else {
    		            $errors[] = array($field => Rtvg_Message::ERR_INVALID_INPUT);
    		        }
    			}
    		}
    		return $errors;
    	}
    	return true;
    }
    
    public static function direct(Zend_Form $form, array $data){
    	return self::_validateForm($form, $data);
    }
	
}