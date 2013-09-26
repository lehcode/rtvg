<?php
class ChannelsControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
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
        # Warning:
        PHPUnit_Framework_Error_Warning::$enabled = TRUE;
        # notice, strict:
        PHPUnit_Framework_Error_Notice::$enabled = FALSE;
		parent::setUp();
	}

	public function testListAction(){

        $urlParams = $this->urlizeOptions( array(
            'module'=>'default',
            'controller'=>'channels',
            'action'=>'list', ));
		$url = $this->url( $urlParams );
		$this->dispatch($url);

		// assertions
		$this->assertModule( $urlParams['module'] );
		$this->assertController( $urlParams['controller'] );
		$this->assertAction( $urlParams['action'] );
		
        $this->assertNotRedirect();
		$this->assertQueryCountMin("div#col_l", 1 );
		$this->assertQueryCountMin("div#col_r", 1 );
		$this->assertQueryCountMin("div#channels h3.channeltitle", 1 );
		
		$controller = Zend_Controller_Front::getInstance();
		
        $channelsModel = new Xmltv_Model_Channels();
		
        //Check channels categories
		$cats = $channelsModel->channelsCategories();
		$this->assertNotEmpty($cats);
        
        //Check top programs list
        require_once(APPLICATION_PATH.'/controllers/helpers/Top.php');
		$top = new Zend_Controller_Action_Helper_Top();
		$topPrograms = $top->direct( 'TopPrograms', array( 
			'amt'=>Zend_Registry::get('site_config')->top->channels->get('amount')));
		$this->assertNotEmpty($topPrograms);
        
	}


}