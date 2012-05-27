<?php
/**
 * Frontend Series controller
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: SeriesController.php,v 1.1 2012-05-27 20:05:50 dev Exp $
 *
 */
class SeriesController extends Zend_Controller_Action
{
	public function init () {
		//$ajaxContext = $this->_helper->getHelper( 'AjaxContext' );
		//$ajaxContext->addActionContext( 'typehead', 'json' )
		$this->view->setScriptPath(APPLICATION_PATH . 
					'/views/scripts/');
	}
	
	public function indexAction () {

		$this->_forward( 'series-week' );
	}
	
	public function seriesWeekAction(){
		$this->render('under-constriction');
	}
	
	private function _isValidRequest($action=null) {
		
    	if (!$action)
		return false;
		
		$filters = array( '*'=>'StringTrim', '*'=>'StringToLower' );
		$validators = array(
	    	'module'=>array(
	    		new Zend_Validate_Regex('/^[a-z]+$/')),
	    	'controller'=>array(
	    		new Zend_Validate_Regex('/^[a-z]+$/')),
	    	'action'=>array(
	    		new Zend_Validate_Regex('/^[a-z-]+$/')),
	    	'format'=>array(
	    		new Zend_Validate_Regex('/^html|json$/')));
		switch ($action) {
			/*
			case 'show-video':
				$validators['id']    = array( new Zend_Validate_Regex( '/^[\p{L}\p{N}]+/iu' ));
				$validators['alias'] = array( new Zend_Validate_Regex( '/^[\p{L}\p{N}-]+/iu' ));
				break;
			case 'show-tag':
				$validators['id']    = array( new Zend_Validate_Regex( '/^[\p{L}\p{N}]+/iu' ));
				$validators['tag'] = array( new Zend_Validate_Regex( '/^[\p{L}\p{N}-]+/iu' ));
				break;
			*/
			default:
				return false;
		}
		
		$input = new Zend_Filter_Input($filters, $validators, $this->_getAllParams());
    	
		//var_dump($input->isValid());
		//die(__FILE__.': '.__LINE__);
		
		if ($input->isValid())
    	return true;
    	else
    	throw new Zend_Exception("Неверные данные", 500);
    }
}