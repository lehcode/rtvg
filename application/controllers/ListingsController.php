<?php
/**
 * Frontend programs listings controller
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: ListingsController.php,v 1.2 2012-04-01 04:55:49 dev Exp $
 *
 */
class ListingsController extends Zend_Controller_Action
{

	protected $siteConfig;
	private   $_requestParams;
	
	public function __call ($method, $arguments) {

		header( 'HTTP/1.0 404 Not Found' );
		$this->_helper->layout->setLayout( 'error' );
		$this->view->render();
		//return;
	}


	public function init () {

		$this->view->setScriptPath( APPLICATION_PATH . '/views/scripts/' );
		$this->siteConfig = Zend_Registry::get( 'site_config' )->site;
		$this->_requestParams = $this->_getAllParams();
		
	}


	public function indexAction () {

		$this->_forward( 'day' );
	}


	public function dayDateAction(){
		$this->_forward('day');
	}
	
	public function dayAction () {

		if( !$this->_validateRequest() ) {
			throw new Zend_Exception("Неверные данные", 500);
			$this->_redirect('/error', array('exit'=>true));
		}
		
		$table = new Xmltv_Model_DbTable_Channels();
		$ch = $table->find( $this->_requestParams['channel'] );
		$ch = $ch->toArray();
		$ch = $ch[0];
		
		$today = @isset( $this->_requestParams['date'] ) ? new Zend_Date( 
		$this->_requestParams['date'], null, 'ru' ) : new Zend_Date( null, null, 'ru' );
		
		$model = new Xmltv_Model_Programs();
		try {
			$list = $model->getProgramsForDay( $today, $ch['ch_id'] );
		} catch (Exception $e) {
			echo $e->getMessage();
		}
		
		$this->view->assign( 'channel', $ch );
		$this->view->assign( 'programs', $list );
		$this->view->assign( 'today', $today );
		
	}


	public function programTodayAction () {

		if( !$this->_validateRequest() ) {
			throw new Zend_Exception("Неверные данные", 500);
			$this->_redirect('/error', array('exit'=>true));
		
		} else {
			
			$programs = new Xmltv_Model_Programs();
			$channels = new Xmltv_Model_Channels();
			$listing = $programs->getProgramThisDay( $this->_requestParams['program'], 
			$this->_requestParams['channel'] );
			$this->view->assign( 'programs', $listing );
			$this->view->assign( 'program_alias', $this->_requestParams['program'] );
			$this->view->assign( 'channel', 
			$channels->getByAlias( $this->_requestParams['channel'] ) );
			$this->render();
		
		}
	}
	
	public function programWeekAction(){
		
		
		
	}
	
	private function _validateRequest(){
		
		$filters = array('*'=>'StringTrim', '*'=>'StringToLower');
		$validators = array(
			'channel'=>array(new Zend_Validate_Regex( '/^[\p{L}0-9- ]+$/iu' )), 
			'program'=>array(new Zend_Validate_Regex( '/^[\p{L}0-9- ]+$/iu' )), 
			'module'=>array(new Zend_Validate_Regex( '/^[a-z]+$/u' )), 
			'controller'=>array(new Zend_Validate_Regex( '/^[a-z]+$/' )), 
			'action'=>array(new Zend_Validate_Regex( '/^[a-z-]+$/' ))
		);
		if( @isset( $this->_requestParams['date'] ) ) {
			$validators['date'] = array(
			new Zend_Validate_Regex( '/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/' ));
		}
		$input = new Zend_Filter_Input( $filters, $validators, $this->_requestParams );
		
		if( $input->isValid() ) {
			return true;
		}
		return false;
	}
	/**
	 * @return array
	 */
	public function getRequestParams () {

		return $this->_requestParams;
	}

	/**
	 * @param $requestParams 
	 */
	public function setRequestParams ($requestParams=null) {
		
		$this->_requestParams = $requestParams;
	}


	
}

