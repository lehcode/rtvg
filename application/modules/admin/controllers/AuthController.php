<?php
/**
 * User management for frontend
 *
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @uses    Xmltv_Controller_Action
 * @version $Id: AuthController.php,v 1.4 2013-03-24 03:02:28 developer Exp $
 *
 */

class Admin_AuthController extends Rtvg_Controller_Admin
{

    //sesssion
    protected $_storage;
    
    public function init()
    {
        parent::init();
    }
    
    /**
     * Define session namespace
     * 
     * @return Zend_Session_Namespace
     */
    public function getStorage()
    {
    	if (null === $this->_storage) {
    		$this->_storage = new Zend_Session_Namespace(__CLASS__);
    	}
    	return $this->_storage;
    }
    

    public function indexAction()
    {
        parent::validateRequest();
        
        if ($this->isAllowed===true){
        	return $this->_forward( 'login' );
        }
        
        return $this->render( 'access-denied' );
        
    }
    
    /**
     * Process login
     */
    public function loginAction(){
    	
        $form = new Xmltv_Form_Login();
        $formValidator = $this->_helper->getHelper('ValidateForm');
        $r = $this->getRequest();
        
        if (APPLICATION_ENV=='development'){
            //var_dump($r->isPost());
            //var_dump($this->isAllowed);
            //die(__FILE__.': '.__LINE__);
        }
        
        if ($r->isPost() && ($this->isAllowed === true)) {
            
            $postData = $r->getPost();
            
            if (APPLICATION_ENV=='development'){
            	//var_dump( $formValidator->direct( $form, $postData) );
            	//var_dump( !($errors = $formValidator->direct( $form, $postData)) || !empty($errors) );
            	//die(__FILE__.': '.__LINE__);
            }
            
            if(!($errors = $formValidator->direct( $form, $postData)) || (is_array($errors) && !empty($errors))) {  //i.e. no errors
                
                if (APPLICATION_ENV=='development'){
                	//var_dump($errors);
                	//die(__FILE__.': '.__LINE__);
                }
                
                foreach ($errors as $e) {
                	$this->_flashMessenger->addMessage($e);
                }
                
                /* 
                $c=0;
                while (!empty($errors)) {
                    $e = $errors[$c];
                    unset($errors[$c]);
                    throw new Zend_Controller_Dispatcher_Exception($errors[$c]);
                    $c++;
                }
                 */
                $this->_redirect( $this->view->baseUrl( 'admin/error/error' ), array('exit'=>true) );
                
            } else {
	            
                parent::validateRequest();
                
                if ($this->input->getEscaped('openid')===null || $this->input->getEscaped('pw')===null) {
	            	$this->render('wrong-credentials');
	            	return;
	            }
	            $openId = $this->input->getEscaped('openid');
	            
	            $authAdapter = new Zend_Auth_Adapter_DbTable( Zend_Registry::get('db_local') );
	            $usersTable  = new Xmltv_Model_DbTable_Users();
	            $authAdapter->setTableName( $usersTable->getName() )
	            	->setIdentityColumn( 'email')
	            	->setIdentity( $this->input->getEscaped('openid'))
	            	->setCredentialColumn( 'hash')
	            	->setCredential( md5($this->input->getEscaped('pw')) );
                
                // Perform the authentication query, saving the result
	            $auth = Zend_Auth::getInstance();
	            $result = $auth->authenticate($authAdapter);
	            
	            if (APPLICATION_ENV=='development'){
	            	//var_dump($result);
	            	//var_dump($result->isValid());
	            	//die(__FILE__.': '.__LINE__);
	            }
	            
	            if ($result->isValid()) {
	            
	            	$identity = $result->getIdentity();
	            	$data = $authAdapter->getResultRowObject( null, 'hash' );
	            
	            	if (APPLICATION_ENV=='development'){
	            		//var_dump($identity);
	            		//var_dump($data);
	            		//die(__FILE__.': '.__LINE__);
	            	}
	            
	            	$auth->getStorage()->write( $data );
	            	$this->_redirect( $this->view->baseUrl('admin') );
	            	 
	            } else {
	            	foreach ($result->getMessages() as $msg){
	            		$this->_flashMessenger->addMessage($msg);
	            	}
	            	$this->_redirect( $this->errorUrl, array( 'exit'=>true ) );
	            }
	            
	        }
            
        }
        
        $this->_redirect( $this->errorUrl, array( 'exit'=>true ) );
        
    }
    
    /**
     * User profile page
     */
    public function profileAction(){
    	
        return $this->_forward( 'profile', 'user', 'default' );
        
        
    }
    
    /**
     * Process user log-out
     */
    public function logoutAction(){
    	
        $form = new Xmltv_Form_Logout();
        $formValidator = $this->_helper->getHelper('ValidateForm');
        $r = $this->getRequest();
        if ($r->isPost()) {
            
        	$postData = $r->getPost();
        	if(($errors = $formValidator->direct( $form, $postData))===true) {  //i.e. no errors
        	    
        	    parent::validateRequest();
        	    
        	    $auth = Zend_Auth::getInstance();
        	    $auth->clearIdentity();
        	    $auth->getStorage()->clear();
        	    $this->_flashMessenger->addMessage( Rtvg_Message::MSG_LOGGED_OUT );
        	    Zend_Session::destroy();
        	    $this->_redirect( $this->view->baseUrl( 'admin' ) );
        	    
        	} else {
        	    foreach ($errors as $e) {
        	    	$this->_flashMessenger->addMessage($e);
        	    }
        	    $this->_redirect( $this->errorUrl, array( 'exit'=>true ) );
        	}
        }
        
        
        
    }
    
    /**
     * Process error
     */
    /*
    public function errorAction(){
    	
        $msg='';
        //var_dump($this->_flashMessenger->getMessages());
        //die();
        foreach ($this->_flashMessenger->getMessages() as $m){
			if (is_array($m)) {
			    //var_dump($m);
			    //die();
				foreach ($m as $string) {
					$msg.=$string;
				}
			} else {
				$msg.=$m;
			}
        }
        throw new Zend_Controller_Dispatcher_Exception($msg); 
        
        //$this->view->assign('messages', $this->_flashMessenger->getMessages());
        
    }
    */


}

