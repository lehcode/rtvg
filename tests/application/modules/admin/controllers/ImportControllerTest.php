<?php
/**
 * PHPUnit test controller
 * @version $Id:$
 */
class ImportControllerTest extends Zend_Test_PHPUnit_ControllerTestCase 
{
    
    public $bootstrap = array('App', 'bootstrap');
    
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
     * @group importControllerActions
     */
    public function testRemoteAction(){
        
        $url = 'admin/import/remote/site/teleguide';
        //$this->dispatch($url);
        
        // assertions
        //$this->assertModule( $urlParams['module'] );
        //$this->assertController( $urlParams['controller'] );
        //$this->assertAction( $urlParams['action'] );
        
        $this->markTestIncomplete();
        
        
    }
    
    /**
     * @group admin
     */
    public function testXmlParseChannelsAction(){
        
        $url = 'admin/import/xml-parse-channels';
        //$this->dispatch($url);
        
        // assertions
        //$this->assertModule( $urlParams['module'] );
        //$this->assertController( $urlParams['controller'] );
        //$this->assertAction( $urlParams['action'] );
        
        $this->markTestIncomplete();
        
    }
    
    
}