<?php
/**
 * Frontend videos controller
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: VideosController.php,v 1.2 2012-05-24 20:49:35 dev Exp $
 *
 */
class VideosController extends Zend_Controller_Action
{
	
	private $_requestParams;
	
	public function __call ($method, $arguments) {
		header( 'HTTP/1.0 404 Not Found' );
		$this->_helper->layout->setLayout( 'error' );
		$this->view->render();
	}
	
	public function init () {
		$this->view->setScriptPath( APPLICATION_PATH . '/views/scripts/' );
		$this->siteConfig = Zend_Registry::get( 'site_config' )->site;
		$this->_requestParams = $this->_getAllParams();
	}
	
	public function indexAction () {

		$this->_forward( 'show-video' );
	}
	
	public function showVideoAction(){
		
		//var_dump($this->_requestParams);
		//var_dump($this->_helper->requestValidator( array('method'=>'isValidRequest', 'action'=>$this->_getParam('action'))));
		//die(__FILE__.': '.__LINE__);
		
		if ( $this->_helper->requestValidator( array('method'=>'isValidRequest', 'action'=>$this->_getParam('action'))) === true ){ 
			
			$videos_model = new Xmltv_Model_Videos();
			
			$video = $videos_model->getVideo( $this->_getParam('id'), true );
			$this->view->assign( 'main_video', $video );
			
			$related = $videos_model->getRelatedVideos( $video );
			$this->view->assign( 'related_videos', $related );
			
		} else {
			throw new Zend_Exception("Неверные данные", 500);
			//$this->_redirect('/error', array('exit'=>true));
		}
		
		
		
		//die(__FILE__.': '.__LINE__);
		
	}
}