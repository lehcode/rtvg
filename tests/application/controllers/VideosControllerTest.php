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
    
    /**
     * 
     */
    public function testIndexAction(){
    	
        $urlParams = $this->urlizeOptions( array(
        	'module'=>'default',
        	'controller'=>'videos',
        	'action'=>'index', ));
        $url = $this->url( $urlParams );
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
        $url = $this->url( $urlParams );
        $this->dispatch($url);
        
        // assertions
        $this->assertModule( $urlParams['module'] );
        $this->assertController( $urlParams['controller'] );
        $this->assertAction( $urlParams['action'] );
        
        $videoDesc = 'Testing Description for PHPUnit';
        $vidId = 'biFodVJiqpU';
        
        
    }
}