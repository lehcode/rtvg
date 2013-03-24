<?php
/**
 *
 * Backend errors management
 *
 * @author     Antony Repin <egeshisolutions@gmail.com>
 * @subpackage backend
 * @version    $Id: ErrorController.php,v 1.6 2013-03-24 03:02:28 developer Exp $
 */
class Admin_ErrorController extends Zend_Controller_Action
{

/**
     * FlashMessenger
     *
     * @var Zend_Controller_Action_HelperflashMessenger
     */
    protected $_flashMessenger = null;
    
	public function init(){
	    $this->isAllowed = $this->_helper->getHelper('IsAllowed')->direct( 'grantAccess', array(
			'privilege'=>$this->_getParam('action', 'index'),
			'module'=>'admin',
			'controller'=>$this->_getParam('controller', 'index'),
			'action'=>$this->_getParam('action', null),
			));
        $this->_helper->layout->setLayout( 'error' );
        $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
	}
	
	public function errorAction()
    {
        
        $errors = $this->_getParam('error_handler');
        
        $messages = $this->_flashMessenger->getMessages();
        if (!empty($messages)){
        	$this->view->assign('messages', $messages);
        	//return $this->render();
        }
        
        if (!$errors || !$errors instanceof ArrayObject) {
            $this->view->message = '';
            return $this->render();
        }
        
        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $priority = Zend_Log::NOTICE;
                $this->view->assign('message', 'Страница не найдена');
                break;
            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $priority = Zend_Log::CRIT;
                $this->view->assign('message', 'Ошибка приложения');
                break;
        }
        
        $msg = "Параметры запроса:\n";
        $params = $errors->request->getParams();
        foreach ( $errors->request->getParams() as $key=>$val){
        	$msg .= $key.': '.$val."\n";
        }
        
        //Log exception, if logger available
        $logger = $this->getLog();
        if ($logger) {
            $logger->log( $msg, $priority, $errors->exception );
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
		
		if (APPLICATION_ENV=='development'){
			var_dump($this->getInvokeArg('displayExceptions')==true);
			die(__FILE__.': '.__LINE__);
		}
 
		// conditionally display exceptions
        if ($this->getInvokeArg('displayExceptions')==true) {
            $this->view->assign('exception', $errors->exception);
            $this->view->assign('request', $errors->request);
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


}

