<?php
class FrontpageControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{
	
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
		//$this->assertQueryCount( "ul#fpnav", 1 );
		
		
	}
	
	
}