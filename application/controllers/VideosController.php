<?php
/**
 * Frontend videos controller
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: VideosController.php,v 1.6 2012-08-13 13:20:15 developer Exp $
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
			
			//$videos_model = new Xmltv_Model_Videos();
			
			$youtube = new Xmltv_Youtube();
			
			try {
				$video = $youtube->fetchVideo( $this->_decodeId( $this->_getParam( 'id' )));
				$this->view->assign( 'main_video', $video );
			} catch (Exception $e) {
				echo $e->getMessage();
			}
			
			try {
				$related = $youtube->fetchRelated( $video->getVideoId() );
				$this->view->assign( 'related_videos', $related );
			} catch (Exception $e) {
				echo $e->getMessage();
			}
			
			
			$this->view->assign( 'pageclass', 'show-video' );
			
		} else {
			$this->_redirect('/горячие-новости', array('exit'=>true));
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
		//die(__FILE__.': '.__LINE__);
		
		if ( $this->_isValidRequest( $this->_getParam( 'action' ) )) { 
			$this->view->assign( 'pageclass', 'show-tag' );
			$this->view->assign( 'tag', base64_decode( $this->_getParam( 'p' ) ) );
			$this->view->assign( 'tag-alias', $this->_getParam( 'tag' ) );
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
    	$this->_redirect('/горячие-новости' );
    	
    }
    
	private function _decodeId($input=null){
		
		if (!$input)
		throw new Zend_Exception("Не указан один или более параметров для ".__FUNCTION__, 500);
		
		return base64_decode( strrev($input).'=');
		
	}
    
	
}