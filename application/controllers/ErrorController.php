<?php
/**
 * Frontend errors handling
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @uses    Zend_Controller_Action
 * @version $Id: ErrorController.php,v 1.12 2013-03-10 02:45:15 developer Exp $
 *
 */
class ErrorController extends Zend_Controller_Action
{

	public function init(){
		$ajaxContext = $this->_helper->getHelper( 'AjaxContext' );
		$ajaxContext->addActionContext( 'ajax-error', 'json' )
			->initContext();
	}
	
	public function errorAction()
    {
        $errors = $this->_getParam('error_handler');
        
        if (!$errors || !$errors instanceof ArrayObject) {
            $this->view->message = 'You have reached the error page';
            return;
        }
        
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
        
        //Log exception, if logger available
        $logger = $this->getLog();
        if ($logger) {
            $logger->log( $this->view->message, $priority, $errors->exception );
            $msg = "Параметры запроса:\n";
            $params = $errors->request->getParams();
            foreach ( $errors->request->getParams() as $key=>$val){
            	$msg .= $key.': '.$val."\n";
            }
            $logger->log($msg, $priority);
        }
        
        $senderEmail='dev@egeshi.com';
        $mail = new Zend_Mail('UTF-8');
        $mail->setBodyText( "Ошибка:\n".$errors->exception."\n\n".$msg );
        $mail->setFrom( $senderEmail, 'Rutvgid Error');
        $mail->addTo( 'egeshisolutions@gmail.com', 'Bugs');
        $mail->setSubject( "Ошибка!");
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
            	'path'=>ROOT_PATH.'/log/mail'
            ));
        }
        
        //Send
		$sent = true;
		try {
		    $mail->send($t);
		} catch (Zend_Mail_Exception $e) {
		    $logger->log( $e->getMessage(), Zend_log::CRIT );
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

}

