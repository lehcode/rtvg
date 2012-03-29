<?php

/**
 * Frontend Channels controller
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: ChannelsController.php,v 1.2 2012-03-29 18:16:52 dev Exp $
 *
 */
class ChannelsController extends Zend_Controller_Action
{

	public function init () {
		//var_dump($this->_getParam('site.debug', false));
	}


	public function indexAction () {

		$this->_redirect('/телепрограмма');
	}

	/*
	public function noRouteAction () {

		header( 'HTTP/1.0 404 Not Found' );
		//$this->view->render( '404.phtml' );
		//$this->_helper->layout->setLayout('error');
	
	}
	*/
	
	public function __call ($method, $arguments) {

		//$this->noRouteAction();
	
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


}

