<?php

class ListingsController extends Zend_Controller_Action
{

	protected $siteConfig;
	
	public function __call ($method, $arguments) {

		header( 'HTTP/1.0 404 Not Found' );
		//$this->_helper->layout->setLayout( 'error' );
		//$this->view->render();
		return;
	}


	public function init () {
		$this->view->setScriptPath(APPLICATION_PATH . '/views/scripts/');
		$this->siteConfig = Zend_Registry::get('site_config')->site;
		
	}


	public function indexAction () {

		$this->_forward( 'day' );
	}


	public function dayAction () {

		$request = $this->_getAllParams();
		
		//var_dump($request);
		//var_dump($this->getRequest());
		//var_dump($this->_getParam('alias'));
		//die(__FILE__.': '.__LINE__);
		
		$filters = array('alias'=>'StringTrim', 'module'=>'StringTrim', 
		'controller'=>'StringTrim', 'action'=>'StringTrim');
		$validators = array(
		'alias'=>array(new Zend_Validate_Regex( '/^[\p{L}0-9- ]+$/u' )), 
		'module'=>array(new Zend_Validate_Regex( '/^[a-z]+$/u' )), 
		'controller'=>array(new Zend_Validate_Regex( '/^[a-z]+$/' )), 
		'action'=>array(new Zend_Validate_Regex( '/^[a-z]+$/' )));
		$input = new Zend_Filter_Input( $filters, $validators, $request );
		//var_dump($input->isValid());
		//var_dump($request['alias']);
		if( $input->isValid() ) {
			$table = new Xmltv_Model_DbTable_Channels();
			$ch = $table->find( $request['alias'] );
			$ch = $ch->toArray();
			$ch = $ch[0];
		}
		
		$is_today = true;
		$date = new Zend_Date();
		
		//var_dump($date);
		//die(__FILE__.': '.__LINE__);
		
		$model = new Xmltv_Model_Programs();
		$model->debug = (bool)$this->siteConfig->get('debug', false);
		$list = $model->getProgramsForDay( $date, $ch['ch_id'] );
		//$this->_helper->layout->setLayout( 'list' );
		//var_dump($list);
		//die(__FILE__.': '.__LINE__);
		

		$this->view->assign( 'channel', $ch );
		$this->view->assign( 'programs', $list );
		$this->view->assign( 'is_today', $is_today );
		$this->view->assign( 'date', $date );
		
	}
}

