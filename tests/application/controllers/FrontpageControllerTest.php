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
        $this->_bcModel = new Xmltv_Model_Programs();
        $this->_channelsModel = new Xmltv_Model_Channels();
        $this->_articlesModel = new Xmltv_Model_Articles();
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
        $front = $this->getFrontController();
        if($front->getParam('bootstrap') === null) {
            $front->setParam('bootstrap', $this->_application->getBootstrap());
        }
        
        $router = new Xmltv_Plugin_Router();
        $router->setRouter($front->getRouter());
		$front->setRouter($router->getRouter())
            ->registerPlugin( new Xmltv_Plugin_Init( APPLICATION_ENV ) )
            ->registerPlugin( new Xmltv_Plugin_Auth( APPLICATION_ENV ) )
        ;
    }
	
	public function testIndexAction(){
		
	    $urlParams = $this->urlizeOptions( array(
			'module'=>'default',
			'controller'=>'frontpage',
			'action'=>'index', ));
		try{
            $url = $this->url( $urlParams, 'default' );
        } catch (Zend_Controller_Router_Exception $e){
            $url = '/';
        }
        $this->dispatch( $url );
        
        // Routing
		$this->assertModule( $urlParams['module'] );
		$this->assertController( $urlParams['controller'] );
		$this->assertAction( $urlParams['action'] );
        $this->assertNotRedirect();
        
        // Frontpage listing
        $channelsAmt = (int)Zend_Registry::get('site_config')->top->broadcasts->get('amount');
        $this->assertNotEmpty($channelsAmt);
        $top = $this->_bcModel->topPrograms($channelsAmt);
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