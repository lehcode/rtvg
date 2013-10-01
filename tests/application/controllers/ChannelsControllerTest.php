<?php
/**
 * PHPUnit test controller
 * @version $Id:$
 */
class ChannelsControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    public function setUp()
	{
        $this->bootstrap = array($this, 'appBootstrap');
        parent::setUp();
        $this->_bcModel = new Xmltv_Model_Programs();
        $this->_channelsModel = new Xmltv_Model_Channels();
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
    
    public function testListAction(){

        $urlParams = $this->urlizeOptions( array(
            'module'=>'default',
            'controller'=>'channels',
            'action'=>'list', ));
        
        $url = $this->url( $urlParams, 'default_channels_list' );
		$this->dispatch($url);
        
        // Routing
        $this->assertModule( $urlParams['module'] );
		$this->assertController( $urlParams['controller'] );
		$this->assertAction( $urlParams['action'] );
		
        // Channels categories
		$cats = $this->_channelsModel->channelsCategories();
		$this->assertNotEmpty($cats);
        
        // Top programs list
        $amt = (int)Zend_Registry::get('site_config')->top->channels->get('amount');
        $now = Zend_Date::now();
        $weekDays = new Zend_Controller_Action_Helper_WeekDays();
        $week_start = $weekDays->getStart($now);
	    $week_end   = $weekDays->getEnd($now);
        $topBc = $this->_bcModel->topPrograms($amt, $week_start, $week_end);
		$this->assertNotEmpty($topBc);
        
        // Channels list
        $channels = $this->_channelsModel->getPublished();
        $this->assertNotEmpty($channels);
        
        
        // DOM
        $this->assertQueryCountMin("div#col_l", 1 );
		$this->assertQueryCountMin("div#col_r", 1 );
		$this->assertQueryCountMin("div#channels h3.channeltitle", 1 );
	}


}