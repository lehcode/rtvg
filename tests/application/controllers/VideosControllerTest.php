<?php
class VideosControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    /**
     * (non-PHPdoc)
     * @see Zend_Test_PHPUnit_ControllerTestCase::setUp()
     */
    public function setUp()
    {
    	$this->bootstrap = new Zend_Application(
    		APPLICATION_ENV,
    		APPLICATION_PATH . '/configs/application.ini'
    	);
    	parent::setUp();
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