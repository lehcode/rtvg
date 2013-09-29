<?php

class ErrorControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
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
	
	public function testIndexAction () {
		
		$urlParams = $this->urlizeOptions( array(
            'module'=>'default',
            'controller'=>'error',
            'action'=>'index', ));
		$url = $this->url( $urlParams );
		$this->dispatch($url);
		
		// assertions
		$this->assertModule( $urlParams['module'] );
		//$this->assertController( $urlParams['controller'] );
		$this->assertController( $urlParams['action'] );
        $this->assertResponseCode(302);
	
	}


}

