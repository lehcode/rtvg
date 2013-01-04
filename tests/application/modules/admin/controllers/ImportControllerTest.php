<?php
class ImportControllerTest extends Zend_Test_PHPUnit_ControllerTestCase 
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
    
    
    public function testRemoteAction(){
        
        $urlParams = $this->urlizeOptions( array(
        		'module'=>'admin',
        		'controller'=>'import',
        		'action'=>'remote', ));
        $url = $this->url( $urlParams );
        /*
        $this->dispatch($url);
        
        // assertions
        $this->assertModule( "admin" );
        $this->assertController( $urlParams['controller'] );
        $this->assertAction( $urlParams['action'] );
        */
        
    }
    
    public function testXmlParseChannelsAction($xml_file=null){
        
        $urlParams = $this->urlizeOptions( array(
        		'module'=>'admin',
        		'controller'=>'import',
        		'action'=>'xml-parse-channels', ));
        $url = $this->url( $urlParams );
        /*
        $this->dispatch($url);
        
        // assertions
        $this->assertModule( "default" );
        $this->assertController( $urlParams['controller'] );
        $this->assertAction( $urlParams['action'] );
        */
        
    }
    
    
}