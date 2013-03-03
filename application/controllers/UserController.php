<?php
/**
 * User management for frontend
 *
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @uses    Xmltv_Controller_Action
 * @version $Id: UserController.php,v 1.1 2013-03-03 18:55:38 developer Exp $
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
            if(($errors = $formValidator->direct( $form, $postData))===true) {  //i.e. o errors
                
                $this->requestParamsValid();
                
                $openId = null;
                if ((bool)$this->input->getEscaped('openid')===false) {
                	$this->render('wrong-credentials');
                	return;
                }
                $openId = $this->input->getEscaped('openid');
                
                $result = $this->usersModel->authenticate($openId, $this->getResponse());
                
                if ($result->isValid()) {
                    die(__FILE__.': '.__LINE__);
                	$identity = $result->getIdentity();
                	if (!$identity['Profile']['display_name']) {
                		return $this->_helper->redirector->gotoSimpleAndExit('update', 'profile');
                	}
                	$this->_redirect('/');
                } else {
                    die(__FILE__.': '.__LINE__);
                	$this->view->errorMessages = $result->getMessages();
                }
                
            }
        } else {
            foreach ($errors as $e) {
            	$this->_flashMessenger->addMessage($e);
            }
        }
        
        
        /*
        $dbConf = Zend_Registry::get('db_local');
        $e = $this->input->getEscaped('user');
        $p = $this->input->getEscaped('pass');
        $authAdapter = new Zend_Auth_Adapter_DbTable( $dbConf);
        $authAdapter->setTableName( $dbConf->get('tbl_prefix')."_users" )
        	->setIdentityColumn('email')
        	->setIdentity( $e )
        	->setCredentialColumn('hash')
        	->setCredential( md5($p) );
        
        $result = $this->_auth->authenticate($authAdapter);
        
        switch ($result->getCode()) {
        
        	case Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND:
        		// Выполнить действия при несуществующем идентификаторе
        	    $this->_forward('not-found', null, null, $this->_getAllParams());
        		break;
        
        	case Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID:
        		// Выполнить действия при некорректных учетных данных
        	    $this->_forward('wrong-password', null, null, $this->_getAllParams());
        		break;
        
        	case Zend_Auth_Result::SUCCESS:
        		// Выполнить действия при успешной аутентификации
        	    $this->_forward('profile', null, null, $this->_getAllParams());
        		break;
        
        	default:
        		// Выполнить действия для остальных ошибок
        	    $this->_forward('error', 'error' , 'default', $this->_getAllParams() );
        		break;
        }
        */
        
    }


}

