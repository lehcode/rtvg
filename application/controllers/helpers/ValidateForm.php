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
    		foreach($form->getMessages() as $field => $message) {
    			foreach($message as $error) {
    				$errors[] = array($field => $error);
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