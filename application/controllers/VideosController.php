<?php
/**
 * Frontend videos controller
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: VideosController.php,v 1.4 2012-05-30 21:46:59 dev Exp $
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
		//var_dump($this->_isValidRequest ($this->_getAllParams()));
		//die(__FILE__.': '.__LINE__);
		
		if ( $this->_isValidRequest($this->_getParam('action')) ) {  
			
			$videos_model = new Xmltv_Model_Videos();
			
			$video = $videos_model->getVideo( $this->_getParam('id'), true );
			$this->view->assign( 'main_video', $video );
			
			$related = $videos_model->getRelatedVideos( $video );
			$this->view->assign( 'related_videos', $related );
			
		} else {
			exit("Неверные данные");
		}
		
	}
	
	
	public function showVideoCompatAction(){
		
		//var_dump($this->_requestParams);
		//var_dump($this->_isValidRequest($this->_getAllParams()));
		//die(__FILE__.': '.__LINE__);
		
		if ( $this->_isValidRequest($this->_getParam('action')) ) {  
			
			$videos_model = new Xmltv_Model_Videos();
			
			$id = base64_decode($this->_getParam('id'));
			
			//var_dump($id);
			//die(__FILE__.': '.__LINE__);
			
			$video = $videos_model->getVideo( $id, false );
			$this->view->assign( 'main_video', $video );
			
			$related = $videos_model->getRelatedVideos( $video );
			$this->view->assign( 'related_videos', $related );
			
			$this->render('show-video');
			
		} else {
			exit("Неверные данные");
		}
		
	}
	
	public function showTagAction(){
		
		//var_dump($this->_getAllParams());
		
		if ( $this->_isValidRequest( $this->_getParam( 'action' ) )) { 
			$safeTag = new Zend_Filter_HtmlEntities();
			$videos = new Xmltv_Model_Videos();
			$tag = $safeTag->filter( $videos->convertTag(  $this->_getParam( 'tag' )));
			$this->view->assign('video_data', array($tag));
		} else {
			exit("Неверные данные");
		}
				
	}
	
	private function _isValidRequest($action=null) {
		
    	if (!$action)
		return false;
		
		//var_dump( $action );
		//var_dump( $this->_getAllParams() );
		//preg_match('/^[\p{L}\p{N}]+\=/i', $this->_getParam('id'), $m ) ;
		//var_dump($m);
		//die(__FILE__.': '.__LINE__);

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
			case 'show-video':
				$validators['id']    = array( new Zend_Validate_Regex( '/^[\p{L}\p{N}]+/iu' ));
				$validators['alias'] = array( new Zend_Validate_Regex( '/^[\p{L}\p{N}-]+/iu' ));
				break;
			case 'show-tag':
				$validators['id']  = array( new Zend_Validate_Regex( '/^[\p{L}\p{N}]+/iu' ));
				$validators['tag'] = array( new Zend_Validate_Regex( '/^[\p{L}\p{N}-]+/iu' ));
				break;
			case 'show-video-compat':
				$validators['id'] = array( new Zend_Validate_Regex( '/^[\p{L}\p{N}]+\=$/i' ));
				break;
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