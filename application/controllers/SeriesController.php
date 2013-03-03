<?php
/**
 * Frontend Series controller
 * 
 * @author  Antony Repin
 * @uses    Xmltv_Controller_Action
 * @version $Id: SeriesController.php,v 1.5 2013-03-03 23:34:13 developer Exp $
 *
 */
class SeriesController extends Xmltv_Controller_Action
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
		
		$model = new Xmltv_Model_Programs();
		$data['date'] = new Zend_Date(null, null, 'ru');
		$weekStart = $this->_helper->WeekDays( array( 'method'=>'getStart', 'data'=>$data));
		$data['date'] = new Zend_Date(null, null, 'ru');
		$weekEnd = $this->_helper->WeekDays( array( 'method'=>'getEnd', 'data'=>$data));
		$seriesList = $model->getCategoryForPeriod( $weekStart, $weekEnd, $this->categoriesMap['series'] );
		//var_dump($weekStart->toString('YYYY-MM-dd'));
		//var_dump($weekEnd->toString('YYYY-MM-dd'));
		
		
		//var_dump($seriesList);
		//die(__FILE__.': '.__LINE__);
		
		//$this->render('under-constriction');
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