<?php
/**
 * @version $Id:$
 */
class FrontpageControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    
    private $_bcModel;
    private $_channelsModel;
    private $_articlesModel;
    
    public function setUp()
	{
        $this->bootstrap = array($this, 'appBootstrap');
        parent::setUp();
        $this->_bcModel = new Xmltv_Model_BroadcastsTest();
        $this->_channelsModel = new Xmltv_Model_Channels();
        $this->_articlesModel = new Xmltv_Model_Articles();
	}
    
    public function appBootstrap()
    {
        $this->_application = new Zend_Application(APPLICATION_ENV,
              APPLICATION_PATH . '/configs/application.ini'
        );
        $this->_application->bootstrap();

        $front = $this->getFrontController();
        if($front->getParam('bootstrap') === null) {
            $front->setParam('bootstrap', $this->_application->getBootstrap());
        }

        $router = new Xmltv_Plugin_Router();
        $router->setRouter($front->getRouter());
		$front->setRouter($router->getRouter());
    }
	
    /**
     * @group frontpageControllerActions
     */
	public function testIndexAction(){
		
	    $urlParams = $this->urlizeOptions( array(
			'module'=>'default',
			'controller'=>'frontpage',
			'action'=>'index', ));
		try{
            $url = $this->url( $urlParams, 'default' );
        } catch (Zend_Controller_Router_Exception $e){
            $url = '';
        }
        $this->dispatch( $url );
        
        // Routing
		$this->assertModule( $urlParams['module'] );
		$this->assertController( $urlParams['controller'] );
		$this->assertAction( 'index' );
        $this->assertNotRedirect();
        
        // Frontpage listing
        $channelsAmt = (int)Zend_Registry::get('site_config')->frontend->frontpage->channels;
        $this->assertNotEmpty($channelsAmt);
        $top = $this->_channelsModel->topChannels($channelsAmt);
        $this->assertNotEmpty($top);
        $list = $this->_bcModel->frontpageListing($top);
        $this->assertNotEmpty($list);
        
        
        // Channels data for dropdown
        $channels = $this->_channelsModel->allChannels("title ASC");
        $this->assertNotEmpty($channels);
        
        // Articles
        $articlesAmt = (int)Zend_Registry::get('site_config')->frontend->frontpage->get('articles');
        $this->assertNotEmpty($articlesAmt);
        $articles = $this->_articlesModel->frontpageItems( $articlesAmt );
        $this->assertNotEmpty($articles);
        
        // DOM elements
		$this->assertQueryCount("div.navbar", 1);
		$this->assertQueryCount("select#channel", 1);
		$this->assertQueryCount("div#listing", 1);
		
		
	}
	
	
}