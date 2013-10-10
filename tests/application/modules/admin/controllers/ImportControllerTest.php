<?php
/**
 * PHPUnit test controller
 * @version $Id:$
 */
class ImportControllerTest extends Zend_Test_PHPUnit_ControllerTestCase 
{
    
    public $bootstrap = array('App', 'bootstrap');
    
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

        /**
         * Fix for ZF-8193
         * http://framework.zend.com/issues/browse/ZF-8193
         * Zend_Controller_Action->getInvokeArg('bootstrap') doesn't work
         * under the unit testing environment.
         */
        $front = Zend_Controller_Front::getInstance();
        if($front->getParam('bootstrap') === null) {
            $front->setParam('bootstrap', $this->_application->getBootstrap());
        }
        
    }
    
    
    public function testRemoteAction(){
        
        $urlParams = $this->urlizeOptions( array(
            'module'=>'admin',
            'controller'=>'import',
            'action'=>'remote', ));
        $url = $this->url( $urlParams );
        
        $this->dispatch($url);
        
        // assertions
        $this->assertModule( "admin" );
        $this->assertController( $urlParams['controller'] );
        $this->assertAction( $urlParams['action'] );
        
        
    }
    
    public function testXmlParseChannelsAction($xml_file=null){
        
        $urlParams = $this->urlizeOptions( array(
            'module'=>'admin',
            'controller'=>'import',
            'action'=>'xml-parse-channels', ));
        $url = $this->url( $urlParams );
        
        $this->dispatch($url);
        
        // assertions
        $this->assertModule( "default" );
        $this->assertController( $urlParams['controller'] );
        $this->assertAction( $urlParams['action'] );
        
    }
    
    
}