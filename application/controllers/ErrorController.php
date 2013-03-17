<?php
/**
 * Frontend errors handling
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @uses	Zend_Controller_Action
 * @version $Id: ErrorController.php,v 1.16 2013-03-17 18:34:58 developer Exp $
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
	
	public function init(){
		$ajaxContext = $this->_helper->getHelper( 'AjaxContext' );
		$ajaxContext->addActionContext( 'ajax-error', 'json' )
			->initContext();
		$this->_flashMessenger = $this->_helper->getHelper( 'FlashMessenger' );
		$this->_helper->layout->setLayout( 'error' );
	}
	
	public function errorAction()
	{
		$errors = $this->_getParam('error_handler');
		
		if (!$errors || !$errors instanceof ArrayObject) {
			$this->view->message = 'Ошибка';
			return;
		}
		
		$msg = "Параметры запроса:\n";
		$params = $errors->request->getParams();
		foreach ( $errors->request->getParams() as $key=>$val){
			$msg .= $key.': '.$val."\n";
		}
		
		$this->logMessage( $msg );
		$this->sendEmail( $msg, $errors->exception );
		
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
	
	protected function logMessage($msg=null, $priority=Zend_Log::WARN){
		
		//Log exception, if logger available
		$logger = $this->getLog();
		if ($logger) {
			$logger->log( $msg, $priority, $errors->exception );
		}
		
	}
	
	protected function sendEmail($msg=null, $exception=null){
	
		$senderEmail='dev@egeshi.com';
		$mail = new Zend_Mail('UTF-8');
		$mail->setBodyText( $errors->exception."\n\n".$msg );
		$mail->setFrom( $senderEmail, 'Rutvgid Error');
		$mail->addTo( 'egeshisolutions@gmail.com', 'Bugs');
		if ($errors->exception){
			$mail->setSubject( $errors->exception->getMessage() );
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
			$t = new Zend_Mail_Transport_File(array(
				'path'=>ROOT_PATH.'/log/mail'
			));
		}
		
		//Send
		$sent = true;
		try {
			$mail->send($t);
		} catch (Zend_Mail_Exception $e) {
			$this->logMessage( $e->getMessage(), Zend_log::CRIT );
		}
		
	}

}

