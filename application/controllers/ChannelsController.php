<?php

/**
 * Frontend Channels controller
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: ChannelsController.php,v 1.4 2012-05-20 08:59:46 dev Exp $
 *
 */
class ChannelsController extends Zend_Controller_Action
{


	public function init () {
		$ajaxContext = $this->_helper->getHelper( 'AjaxContext' );
		$ajaxContext->addActionContext( 'typehead', 'json' )
			->initContext();
		$this->view->setScriptPath(APPLICATION_PATH . 
					'/views/scripts/');
	}


	public function indexAction () {

		$this->_forward( 'frontpage', 'index' );
	}


	public function __call ($method, $arguments) {
		throw new Exception("Ошибка сервера", 500);
		$this->_redirect( '/error' );
	}


	public function listAction () {
		
		$this->view->baseUrl = $this->getRequest()->getBaseUrl();
		$model = new Xmltv_Model_Channels();
		$rows = $model->getPublished();
		$channels = array();
		$c = 0;
		foreach ($rows as $row) {
			$item = $row->toArray();
			$channels[$c] = $model->fixImage( $item, $this->view->baseUrl() );
			$c++ ;
		}
		$this->view->assign( 'channels', $channels );
	}
	
	public function typeheadAction () {
		$response=array();
		$channels = new Xmltv_Model_Channels();
		$response = $channels->getTypeheadItems();
		$this->view->assign('response', $response);
	}
	
	public function categoryAction() {
		
		$params = $this->_getAllParams();
		$cats_table = new Xmltv_Model_DbTable_ChannelsCategories();
		$row = $cats_table->fetchRow("`alias`='".$params['category']."'");
		$this->view->assign( 'cat_title', $row->title );
		
		$channels_model = new Xmltv_Model_Channels();
		$rows = $channels_model->getCategory($params['category']);
		$c = 0;
		$channels = array();
		foreach ($rows as $row) {
			$channels[$c] = $channels_model->fixImage( $row, $this->view->baseUrl() );
			$c++ ;
		}
		$this->view->assign('channels', $channels);
		
		$this->render('list');
		
	}
	
	public function channelWeekAction(){
	
		//var_dump($this->_getAllParams());
		//die(__FILE__.': '.__LINE__);
		
		if ( $this->_helper->requestValidator( array('method'=>'isValidRequest', 'action'=>$this->_getParam('action'))) === true ){ 
			
			$channels = new Xmltv_Model_Channels();
			$channel  = $channels->getByAlias( $this->_getParam('channel') );

			//var_dump($channel);
			
			$d = $this->_getParam('start', null)!==null ? new Zend_Date($this->_getParam('start')) : new Zend_Date() ; 
			$start = $this->_helper->weekDays(array('method'=>'getStart', "data"=>array('date'=>$d) ));
			$d = $this->_getParam('end', null)!==null ? new Zend_Date($this->_getParam('end')) : new Zend_Date() ; 
			$end = $this->_helper->weekDays(array('method'=>'getEnd', "data"=>array('date'=>$d) ));
			
			Zend_Registry::set('ch_id', $channel->ch_id);
			Zend_Registry::set('week_start', $start);
			Zend_Registry::set('week_end', $end->subMinute(1));
			Zend_Registry::set('show_relevant_videos', true);
			
			$schedule = $channels->getWeekSchedule();
			//var_dump($schedule);
			
		} else {
    		throw new Exception("Неверные данные", 500);
    		exit();
    	}
		
    	$this->view->assign('channel_title', $channel->title);
    	
		//die(__FILE__.': '.__LINE__);
		
	}
	

}

