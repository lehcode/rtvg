<?php

/**
 * Frontend Channels controller
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: ChannelsController.php,v 1.7 2012-08-13 13:20:15 developer Exp $
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
		header( 'HTTP/1.0 404 Not Found' );
		$this->_helper->layout->setLayout( 'error' );
		$this->view->render();
	}

	/**
	 * All channels list
	 */
	public function listAction () {
		
		$this->view->baseUrl = $this->getRequest()->getBaseUrl();
		$model = new Xmltv_Model_Channels();
		$rows = $model->getPublished();
		
		$c = 0;
		foreach ($rows as $row) {
			$rows[$c] = $model->fixImage( $row, $this->view->baseUrl() );
			$c++ ;
		}		
		$this->view->assign( 'channels', $rows );
		$this->view->assign('pageclass', 'allchannels');
		
	}
	
	/**
	 * Fetch typeahead items
	 */
	public function typeheadAction () {
		$response=array();
		$channels = new Xmltv_Model_Channels();
		$response = $channels->getTypeheadItems();
		$this->view->assign('response', $response);
	}
	
	/**
	 * Category of channels
	 */
	public function categoryAction() {
		
		//Zend_Debug::dump( $this->_getAllParams());
		
		$params = $this->_getAllParams();
		$cats_table = new Xmltv_Model_DbTable_ChannelsCategories();
		$row = $cats_table->fetchRow("`alias`='".$params['category']."'");
		$this->view->assign( 'cat_title', $row->title );
		
		$channels_model = new Xmltv_Model_Channels();
		$rows = $channels_model->getCategory($params['category']);
		$c = 0;
		foreach ($rows as $row) {
			$rows[$c] = $channels_model->fixImage( $row, $this->view->baseUrl() );
			$c++ ;
		}
		$this->view->assign('channels', $rows);
		$this->view->assign('category', $params['category']);
		$this->view->assign('pageclass', 'category');
		
		$this->render('list');
		
	}
	
	public function channelWeekAction(){
	
		//var_dump($this->_getAllParams());
		//die(__FILE__.': '.__LINE__);
		
		if ( $this->_helper->requestValidator( array('method'=>'isValidRequest', 'action'=>$this->_getParam('action'))) === true ){ 
			
			$channels = new Xmltv_Model_Channels();
			$channel  = $channels->getByAlias( $this->_getParam('channel') );

			/*
			 * initialize week start and week end dates
			 */
			$d = $this->_getParam('start', null)!==null ? new Zend_Date($this->_getParam('start'), null, 'ru') : new Zend_Date( null, null, 'ru' ) ; 
			$start = $this->_helper->weekDays(array('method'=>'getStart', "data"=>array('date'=>$d) ));
			$d = $this->_getParam('end', null)!==null ? new Zend_Date($this->_getParam('end'), null, 'ru') : new Zend_Date( null, null, 'ru' ) ; 
			$end = $this->_helper->weekDays(array('method'=>'getEnd', "data"=>array('date'=>$d) ));

			try {
				$schedule = $channels->getWeekSchedule($channel, $start, $end);
			} catch (Zend_Exception $e) {
				echo $e->getMessage();
			}
			/*
			 * re-initialize $start date
			 */
			$d = $this->_getParam('start', null)!==null ? new Zend_Date($this->_getParam('start'), null, 'ru') : new Zend_Date( null, null, 'ru' ) ; 
			$start = $this->_helper->weekDays(array('method'=>'getStart', "data"=>array('date'=>$d) ));
			
			$this->view->assign('channel', $channel);
	    	$this->view->assign('days', $schedule);
	    	$this->view->assign('week_start', $start);
	    	$this->view->assign('week_end', $end);
	    	$this->view->assign('hide_sidebar', 'left');
	    	$this->view->assign( 'sidebar_videos', true );
	    	$this->view->assign('pageclass', 'channel-week');
	    	
	    	$channels->addHit( $channel->ch_id );
			
		} else {
    		throw new Exception("Неверные данные", 500);
    		exit();
    	}
		
	}
	
	
	
}

