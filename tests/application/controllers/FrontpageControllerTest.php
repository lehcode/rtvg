<?php
class FrontpageControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    
    public function setUp()
	{
		$this->bootstrap = new Zend_Application(
			APPLICATION_ENV,
			APPLICATION_PATH . '/configs/application.ini'
		);
        # Warning:
        PHPUnit_Framework_Error_Warning::$enabled = TRUE;
        # notice, strict:
        PHPUnit_Framework_Error_Notice::$enabled = FALSE;
		parent::setUp();
	}
	
	public function testIndexAction(){
		
	    $urlParams = $this->urlizeOptions( array(
			'module'=>'default',
			'controller'=>'frontpage',
			'action'=>'index', ));
		$url = $this->url( $urlParams );
		$this->dispatch( $url );
		
		// assertions
		$this->assertModule( $urlParams['module'] );
		$this->assertController( $urlParams['controller'] );
		$this->assertAction( $urlParams['action'] );
		$this->assertQueryCount( "ul#fpnav", 1 );
		
		
	}
	
	
}