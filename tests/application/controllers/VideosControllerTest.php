<?php
class VideosControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    /**
     * (non-PHPdoc)
     * @see Zend_Test_PHPUnit_ControllerTestCase::setUp()
     */
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
    
    /**
     * 
     */
    public function testIndexAction(){
    	
        $urlParams = $this->urlizeOptions( array(
        	'module'=>'default',
        	'controller'=>'videos',
        	'action'=>'index', ));
        $url = $this->url( $urlParams, 'default_videos_index' );
        $this->dispatch($url);
        
        // assertions
        $this->assertModule( $urlParams['module'] );
        $this->assertController( $urlParams['controller'] );
        $this->assertAction( 'show-video' );
        
    }
    
    public function testShowVideoAction(){
        
        $urlParams = $this->urlizeOptions( array(
        		'module'=>'default',
        		'controller'=>'videos',
        		'action'=>'show-video', ));
        $url = $this->url( $urlParams, 'default_videos_show-video' );
        $this->dispatch($url);
        
        // assertions
        $this->assertModule( $urlParams['module'] );
        $this->assertController( $urlParams['controller'] );
        $this->assertAction( $urlParams['action'] );
        
        $this->markTestIncomplete();
        
        
    }
}