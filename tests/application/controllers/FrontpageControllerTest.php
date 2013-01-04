<?php
class FrontpageControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{
	/**
	 * (non-PHPdoc)
	 * @see Zend_Test_PHPUnit_ControllerTestCase::setUp()
	 */
	public function setUp()
	{
		$this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
		parent::setUp();
	}
	
	public function testIndexAction(){
		
		$urlParams = $this->urlizeOptions( array(
			'module'=>'default',
			'controller'=>'frontpage',
			'action'=>'index', ));
		$url = $this->url( $urlParams );
		$this->dispatch($url);
		
		// assertions
		$this->assertModule( $urlParams['module'] );
		$this->assertController( $urlParams['controller'] );
		$this->assertAction( $urlParams['action'] );
		//$this->assertQueryCount( "ul#fpnav", 1 );
		
		
	}
	
	
}