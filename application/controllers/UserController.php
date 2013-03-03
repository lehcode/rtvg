<?php
/**
 * User management for frontend
 *
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @uses    Xmltv_Controller_Action
 * @version $Id: UserController.php,v 1.2 2013-03-03 23:34:13 developer Exp $
 *
 */

class UserController extends Xmltv_Controller_Action
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
        return $this->_forward('login');
    }
    
    public function loginAction(){
    	
        $form = new Xmltv_Form_Login();
        $formValidator = $this->_helper->getHelper('ValidateForm');
        $r = $this->getRequest();
        if ($r->isPost()) {
            $postData = $r->getPost();
            if(($errors = $formValidator->direct( $form, $postData))===true) {  //i.e. no errors
                
                $this->requestParamsValid();
                
                $openId = null;
                if ((bool)$this->input->getEscaped('openid')===false) {
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
                	->setCredential( md5($this->input->getEscaped('passwd')) );
                
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
                		var_dump($identity);
                		var_dump($data);
                		die(__FILE__.': '.__LINE__);
                	}
                		
                	$auth->getStorage()->write( $data );
                	$this->_redirect('/моя-страница');
                	
                } else {
                	foreach ($result->getMessages() as $msg){
                	    $this->_flashMessenger->addMessage($msg);
                	}
                }
            } else {
	            foreach ($errors as $e) {
	            	$this->_flashMessenger->addMessage($e);
	            }
	        }
            
        } else {
            foreach ($errors as $e) {
            	$this->_flashMessenger->addMessage($e);
            }
        }
        
    }
    
    public function profileAction(){
    	
        $this->view->assign( 'messages', $this->_flashMessenger->getMessages() );
        
        
    }
    
    public function logoutAction(){
    	
        $form = new Xmltv_Form_Logout();
        $formValidator = $this->_helper->getHelper('ValidateForm');
        $r = $this->getRequest();
        if ($r->isPost()) {
        	$postData = $r->getPost();
        	if(($errors = $formValidator->direct( $form, $postData))===true) {  //i.e. no errors
        	    
        	    $this->requestParamsValid();
        	    
        	    $auth = Zend_Auth::getInstance();
        	    $auth->clearIdentity();
        	    Zend_Session::destroy();
        	    
        	    Xmltv_Bootstrap_Auth::setCurrentUser( $this->usersModel->defaultUser() );
        	    $this->user = $this->usersModel->defaultUser();
        	    
        	    if (APPLICATION_ENV=='development'){
        	    	//var_dump($this->user);
        	   		//die(__FILE__.': '.__LINE__);
        	    }
        	    
        	    $this->_redirect('/');
        	    
        	} else {
        	    foreach ($errors as $e) {
        	    	$this->_flashMessenger->addMessage($e);
        	    }
        	}
        }
        
        
        
    }


}

