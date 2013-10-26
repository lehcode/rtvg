<?php
/**
 * Frontend Series controller
 * 
 * @author  Antony Repin
 * @uses    Xmltv_Controller_Action
 * @version $Id: SeriesController.php,v 1.6 2013-03-06 21:59:19 developer Exp $
 *
 */
class SeriesController extends Rtvg_Controller_Action
{
	public function init () {
		//$ajaxContext = $this->_helper->getHelper( 'AjaxContext' );
		//$ajaxContext->addActionContext( 'typeahead', 'json' )
		$this->view->setScriptPath(APPLICATION_PATH . 
					'/views/scripts/');
	}
	
	protected $categoriesMap = array(
		'series'=>5
	);
	
	public function indexAction () {

		$this->_forward( 'series-week' );
	}
	
	public function seriesWeekAction(){
		
		$data['date'] = new Zend_Date(null, null, 'ru');
		$weekStart = $this->_helper->WeekDays( array( 'method'=>'getStart', 'data'=>$data));
		$data['date'] = new Zend_Date(null, null, 'ru');
		$weekEnd = $this->_helper->WeekDays( array( 'method'=>'getEnd', 'data'=>$data));
		$seriesList = $this->bcModel->getCategoryForPeriod( $weekStart, $weekEnd, $this->categoriesMap['series'] );
		
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
			
			default:
				return false;
		}
		
		$input = new Zend_Filter_Input($filters, $validators, $this->_getAllParams());
    	
		if ($input->isValid())
    	return true;
    	else
    	throw new Zend_Exception("Неверные данные", 500);
    }
}