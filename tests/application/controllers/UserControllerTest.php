<?php

class UserControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{

    public function setUp()
	{
        $this->bootstrap = array($this, 'appBootstrap');
        parent::setUp();
	}
    
    public function appBootstrap()
    {
        $this->_application = new Zend_Application(APPLICATION_ENV,
              APPLICATION_PATH . '/configs/application.ini'
        );
        $this->_application->bootstrap();

        $front = $this->getFrontController();
        if($front->getParam('bootstrap') === null) {
            $front->setParam('bootstrap', $this->_application->getBootstrap());
        }
        
        $router = new Xmltv_Plugin_Router();
        $router->setRouter($front->getRouter());
		$front->setRouter($router->getRouter());
    }

    public function testIndexAction()
    {
        $urlParams = $this->urlizeOptions( array(
            'module'=>'default',
            'controller'=>'user',
            'action'=>'index', ));
		$url = $this->url( $urlParams, 'default_user_index' );
		$this->dispatch($url);
        
        $this->assertModule( $urlParams['module'] );
		$this->assertController( $urlParams['controller'] );
		$this->assertAction( 'login' );
        $this->assertResponseCode(200);
    }


}



