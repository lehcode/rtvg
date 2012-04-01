<?php

/**
 * Frontend Channels controller
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: ChannelsController.php,v 1.3 2012-04-01 04:55:49 dev Exp $
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

}

