<?php
class Rtvg_Controller_Error extends Zend_Controller_Action
{
    
    /**
     * Logger
     * @var Zend_Log
     */
    protected $logger;
    
    /**
     * Mailer
     * @var Zend_Mail
     */
    protected $mailer;
    
    /**
     * FlashMessenger
     *
     * @var Zend_Controller_Action_Helper_FlashMessenger
     */
    protected $flashMessenger;
    
    public function init(){
        
        $this->flashMessenger = $this->_helper->getHelper( 'FlashMessenger' );
        $this->logger = $this->_getLog();
        $this->_helper->layout->setLayout( 'admin' );
    }
    
    public function errorAction()
    {
        
        $messages=array();
        foreach ($this->flashMessenger->getMessages() as $m){
        	if (is_array($m)) {
        		//var_dump($m);
        		//die(__FILE__.': '.__LINE__);
        		foreach ($m as $string) {
        			$messages[]=$string;
        		}
        	} else {
        		$messages[]=$m;
        	}
        }
        if (count($messages)){
	        $this->view->assign( 'messages', $messages );
	        return $this->render();
        }
        
        $errors = $this->_getParam('error_handler');
        
        if (!$this->errors || !($this->errors instanceof ArrayObject)) {
    		$this->view->message = 'Произошла ошибка';
    		return;
    	}
    	
    	//var_dump($errors);
    	//die(__FILE__.': '.__LINE__);
    
    	switch ($errors->type) {
    		case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
    		case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
    		case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
    			// 404 error -- controller or action not found
    			$this->getResponse()->setHttpResponseCode(404);
    			$priority = Zend_Log::NOTICE;
    			$this->view->message = 'Страница не найдена';
    			break;
    		default:
    			// application error
    			$this->getResponse()->setHttpResponseCode(500);
    			$priority = Zend_Log::CRIT;
    			$this->view->message = 'Ошибка приложения';
    			break;
    	}
    
    	$msg = "Параметры запроса:\n";
    	$params = $errors->request->getParams();
    	foreach ( $errors->request->getParams() as $key=>$val){
    		$msg .= $key.': '.$val."\n";
    	}
    
    	if ($this->logger) {
    		$this->logger->log( $msg, $priority, $errors->exception );
    	}
    
    	$senderEmail='dev@egeshi.com';
    	$mail = new Zend_Mail('UTF-8');
    	$mail->setBodyText( $errors->exception."\n\n".$msg );
    	$mail->setFrom( $senderEmail, 'Rutvgid Error');
    	$mail->addTo( 'egeshisolutions@gmail.com', 'Bugs');
    	$mail->setSubject( $errors->exception->getMessage() );
    	$mail->setHeaderEncoding( Zend_Mime::ENCODING_BASE64 );
    
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
    				'path'=>APPLICATION_PATH.'/log/mail'
    		));
    	}
    
    	//Send
    	try {
    		$mail->send($t);
    	} catch (Zend_Mail_Exception $e) {
    		$this->logger->log( $e->getMessage(), Zend_log::CRIT );
    	}
    
    	// conditionally display exceptions
    	if ($this->getInvokeArg('displayExceptions')==true) {
    		$this->view->exception = $errors->exception;
    		$this->view->request = $errors->request;
    	}
    
    	$this->view->assign( 'messages', $this->flashMessenger->getMessages() );
    
    
    }
    
    
    
    /**
     *
     * @return Zend_Log
     */
    private function _getLog()
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
}