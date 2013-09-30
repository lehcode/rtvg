<?php
/**
 * PHPUnit test controller
 * @version $Id:$
 */
class ChannelsControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    public $bootstrap = array('App', 'bootstrap');
    
	/**
	 * @see Zend_Test_PHPUnit_ControllerTestCase::setUp()
	 */
	public function setUp()
	{
        $this->bootstrap = array($this, 'appBootstrap');
        parent::setUp();
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
        $front = Zend_Controller_Front::getInstance();
        if($front->getParam('bootstrap') === null) {
            $front->setParam('bootstrap', $this->_application->getBootstrap());
        }
        
        $router = new Xmltv_Plugin_Router();
        $router->setRouter($front->getRouter());
		$front->setRouter($router->getRouter())
            //->registerPlugin( new Xmltv_Plugin_Init( APPLICATION_ENV ) )
        ;
        
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
        $this->assertNotRedirect();
		$this->assertController( $urlParams['controller'] );
		$this->assertAction( $urlParams['action'] );
		
        
		$this->assertQueryCountMin("div#col_l", 1 );
		$this->assertQueryCountMin("div#col_r", 1 );
		$this->assertQueryCountMin("div#channels h3.channeltitle", 1 );
		
        $channelsModel = new Xmltv_Model_Channels();
        $bcModel = new Xmltv_Model_Programs();
		
        //Check channels categories
		$cats = $channelsModel->channelsCategories();
		$this->assertNotEmpty($cats);
        
        //Check top programs list
        $amt = (int)Zend_Registry::get('site_config')->top->channels->get('amount');
        $now = Zend_Date::now();
        $weekDays = new Zend_Controller_Action_Helper_WeekDays();
        $week_start = $weekDays->getStart($now);
	    $week_end   = $weekDays->getEnd($now);
        $topBc = $bcModel->topPrograms($amt, $week_start, $week_end);
		$this->assertNotEmpty($topBc);
        
        //Channels list assertion
        $channels = $channelsModel->getPublished();
        $this->assertNotEmpty($channels);
        
	}


}