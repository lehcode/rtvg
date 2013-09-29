<?php

class IndexControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{

	public function setUp()
	{
		$this->bootstrap = new Zend_Application(
			APPLICATION_ENV,
			APPLICATION_PATH.'/configs/application.ini');
		parent::setUp();
	}
	
	public function testIndexAction () {
		
		$urlParams = $this->urlizeOptions(array(
			'module'=>'default',
			'controller'=>'index',
			'action'=>'index',
		));
        $url = $this->url( $urlParams );
		$this->dispatch($url);
		
		// assertions
		$this->assertModule( $urlParams['module'] );
		$this->assertController( $urlParams['controller'] );
		$this->assertController( $urlParams['action'] );
		//$this->assertRoute('default_frontpage_index');
	
	}


}

