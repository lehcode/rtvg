<?php
/**
 * Frontend errors handling
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @uses	Zend_Controller_Action
 * @version $Id: ErrorController.php,v 1.20 2013-04-11 05:21:11 developer Exp $
 *
 */
class ErrorController extends Zend_Controller_Action
{

	/**
	 * FlashMessenger
	 *
	 * @var Zend_Controller_Action_Helper_FlashMessenger
	 */
	protected $_flashMessenger = null;
	
	private $pageclass='error';
	
	public function init(){
	    
		$ajaxContext = $this->_helper->getHelper( 'AjaxContext' );
		$ajaxContext->addActionContext( 'ajax-error', 'json' )
			->initContext();
		$this->_flashMessenger = $this->_helper->getHelper( 'FlashMessenger' );
		$this->_helper->layout->setLayout( 'error' );
		if (!$this->_request->isXmlHttpRequest()){
			$this->view->assign( 'pageclass', $this->pageclass );
		}
		
	}
	
	public function errorAction()
	{
		$errors = $this->_getParam('error_handler');
		
		if (!$errors || !$errors instanceof ArrayObject) {
			$this->view->message = 'Ошибка';
			return;
		}
		
		$userAgent = Zend_Controller_Front::getInstance()
			->getParam('bootstrap')
			->getResource('useragent');
		
		$msg = "Параметры запроса:\n";
		$msg .= "\tIP: ".$_SERVER['REMOTE_ADDR']."\n".
		"\tMethod: ".$_SERVER['REQUEST_METHOD']."\n".
		"\tURI: ".urldecode( $_SERVER['REQUEST_URI'] )."\n".
		"\tBrowser Type: ".$userAgent->getBrowserType()."\n\n".
		"\tUser-Agent: ".$_SERVER['HTTP_USER_AGENT']."\n\n".
		"\tReferer: ".( isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'none' )."\n\n";
		foreach ( $errors->request->getParams() as $key=>$val){
			$msg .= $key.': '.$val."\n\n";
		}
		
		
		$this->logMessage( $msg );
		if (isset($errors->exception)){
			$this->sendEmail( $msg, $errors->exception );
		} else {
			$this->sendEmail( $msg );
		}
		
		//$this->view->setScriptPath(APPLICATION_PATH . '/layouts/scripts/');
		
		switch ($errors->type) {
			
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
				// 404 error -- controller or action not found
				$this->getResponse()->setHttpResponseCode(404);
				$priority = Zend_Log::NOTICE;
				$this->view->message = 'Страница не найдена';
				$this->view->assign( 'messages', $this->_flashMessenger->getMessages() );
				//$this->_helper->layout->setLayout( 'not-found' );
			break;
			
			default:
				// application error
				$this->getResponse()->setHttpResponseCode(500);
				$priority = Zend_Log::CRIT;
				$this->view->message = 'Ошибка приложения';
				$this->view->assign( 'messages', $this->_flashMessenger->getMessages() );
				//$this->_helper->layout->setLayout( 'app-error' );
			break;
			
		}
		
		// conditionally display exceptions
		if ($this->getInvokeArg('displayExceptions')==true) {
			$this->view->exception = $errors->exception;
			$this->view->request = $errors->request;
		}
		
	}

	/**
	 * 
	 * @return Zend_Log
	 */
	public function getLog()
	{
		if (!$bootstrap = $this->getInvokeArg('bootstrap'))
		return false;
		
		if (!$bootstrap->hasResource('Log')) {
			return false;
		}
		/**
		 * 
		 * @var Zend_Log
		 */
		$log = $bootstrap->getResource('Log');
		return $log;
		
	}
	
	public function ajaxErrorAction(){
		die("AJAX error!");
	}
	
	public function missingPageAction(){
		$this->getResponse()->setHttpResponseCode( 404 );
		$this->view->assign('hide_sidebar', 'right');
	}
	
	public function invalidInputAction(){
		$this->getResponse()->setHttpResponseCode( 500 );
		$this->view->assign('hide_sidebar', 'right');
	}

	public function accessDeniedAction(){
		$this->getResponse()->setHttpResponseCode( 401 );
		$this->view->assign('hide_sidebar', 'right');
	}
	
	protected function logMessage($msg=null, $priority=Zend_Log::WARN, $exception=null){
		
		//Log exception, if logger available
		$logger = $this->getLog();
		if ($logger) {
			$logger->log( $msg, $priority, $exception );
		}
		
	}
	
	protected function sendEmail($msg=null, $exception=null){
	
		$senderEmail='dev@egeshi.com';
		$mail = new Zend_Mail('UTF-8');
		$mail->setBodyText( $exception."\n\n".$msg );
		$mail->setFrom( $senderEmail, 'Rutvgid Error');
		$mail->addTo( 'egeshisolutions@gmail.com', 'Admin');
		if ($exception){
			$mail->setSubject( $exception->getMessage() );
		} else {
			$mail->setSubject( "Внимание! Ошибка!" );
		}
		
		if (APPLICATION_ENV=='production') {
			$t = new Zend_Mail_Transport_Smtp('smtp.gmail.com', array(
				'auth' => 'login',
				'ssl' => 'ssl',
				'port' => 465,
				'username' => $senderEmail,
				'password' => '3k2mzE9bE2iheEMi9RqcVu5t'
			));
		} else {
			$t = new Zend_Mail_Transport_File(array( 'path'=>APPLICATION_PATH.'/../mail' ));
		}
		
		//Send
		try {
			$mail->send($t);
		} catch (Zend_Mail_Exception $e) {
			$this->logMessage( $e->getMessage(), Zend_log::CRIT );
		}
		
	}

}

