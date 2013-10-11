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
        
        if ($r->isPost() && ($this->isAllowed === true)) {
            
            $postData = $r->getPost();
            
            if(!($errors = $formValidator->direct( $form, $postData)) || (is_array($errors) && !empty($errors))) {  //i.e. no errors
                
                while (!empty($errors)) {
                    $e = $errors[$c];
                    unset($errors[$c]);
                    throw new Zend_Exception($e, 500);
                }
                
            } else {
	            
                parent::validateRequest();
                
                if ((bool)$this->input->getEscaped('openid')===false || (bool)$this->input->getEscaped('passwd')===false) {
	            	throw new Zend_Exception("Wrong credentials", 404);
	            }
                
                $login  = $this->input->getEscaped('openid');
                $passwd = md5($this->input->getEscaped('passwd'));
                
                $authAdapter = new Zend_Auth_Adapter_DbTable();
	            $usersTable  = new Xmltv_Model_DbTable_Users();
	            $authAdapter->setTableName( $usersTable->getName() )
	            	->setIdentityColumn( 'email')
	            	->setIdentity($login)
	            	->setCredentialColumn( 'hash')
	            	->setCredential($passwd);
                
                // Perform the authentication query, saving the result
	            $auth = Zend_Auth::getInstance();
	            $result = $auth->authenticate($authAdapter);
	            
                if ($result->isValid()) {
	            
	            	$identity = $result->getIdentity();
	            	$data = $authAdapter->getResultRowObject( null, 'hash' );
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

}

