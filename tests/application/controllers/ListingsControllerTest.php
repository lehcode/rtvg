<?php
class ListingsControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{
	
	const ERR_WRONG_CONTROLLER="---Wrong controller for ";
	const ERR_MISSING="---Missing for ";
	const MARK_INCOMPLETE=" has not been implemented yet.";
    
    /**
     *
     * @var Xmltv_Model_Programs
     */
    private $_bcModel;
    
    /**
     *
     * @var Xmltv_Model_Channels
     */
    private $_channelsModel;
    
    /**
     *
     * @var type 
     */
    private $_videosModel;
	
    public function setUp()
	{
        $this->bootstrap = array($this, 'appBootstrap');
        parent::setUp();
        $this->_bcModel = new Xmltv_Model_Programs();
        $this->_channelsModel = new Xmltv_Model_Channels();
        $this->_videosModel = new Xmltv_Model_Videos();
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
    
    public function testDayDateAction(){
	
        $channels = $this->_channelsModel->getPublished();
        $this->assertNotEmpty($channels);
        $urlParams = $this->urlizeOptions( array(
	    	'module'=>'default',
	    	'controller'=>'listings',
	    	'action'=>'day-date',
	    	'channel'=>$channels[rand(0, count($channels)-1)]['alias'],
            'date'=>Zend_Date::now()->subDay(rand(1,7))->toString('dd-MM-YYYY'),
	    ));
        $url = $this->url( $urlParams, 'default_listings_day-date' );
	    $this->dispatch($url);
	    
	    $this->assertModule( $urlParams['module'] );
		$this->assertController( $urlParams['controller'] );
		$this->assertAction( 'day-listing' );
		
	}
	
	
	public function testDayListingAction(){
		
        $channels = $this->_channelsModel->getPublished();
        $this->assertNotEmpty($channels);
        
        $channelsCategories = new Xmltv_Model_DbTable_ChannelsCategories();
        $chCats = $channelsCategories->fetchAll(null, "title ASC");
        $this->assertNotEmpty($chCats);
        
        $channel = $channels[rand(0, count($channels)-1)];
        
        // Test without date
        $urlParams  = $this->urlizeOptions( array(
            'module'=>'default',
            'controller'=>'listings',
            'action'=>'day-listing',
            'channel' =>$channel['alias'],
        ));
        $url = $this->url( $urlParams, 'default_listings_day-listing' );
        $this->dispatch($url);
        
        // Assertions
        $this->assertModule( $urlParams['module'] );
        $this->assertController( $urlParams['controller'] );
        $this->assertAction( $urlParams['action'] );
        $this->assertNotRedirect();
        
        $this->assertQueryCount("#maincontent h1", 1 );
        
        $date = Zend_Date::now();
        if ((bool)($list = $this->_bcModel->getProgramsForDay( $date, $channel['id'], 4 ))!==false){
            $this->assertNotEmpty($list);
            $listingVideos = $this->_videosModel->ytListingRelatedVideos( array_slice($list, 0, 3), $channel['title'], $date );
            $this->assertNotEmpty($listingVideos);
        }
        
        //$this->assertQueryContentContains( "#maincontent h1", $channel['title']);
        
        
        // Test with date
        $channel = $channels[rand(0, count($channels)-1)];
        $date = Zend_Date::now();
        
        $urlParams  = $this->urlizeOptions( array(
            'module'=>'default',
            'controller'=>'listings',
            'action'=>'day-listing',
            'channel' =>$channel['alias'],
            'date'=>$date->toString('dd-MM-YYYY'),
        ));
        $url = $this->url( $urlParams, 'default_listings_day-listing' );
        $this->dispatch($url);

        // Assertions
        $this->assertModule( $urlParams['module'] );
        $this->assertController( $urlParams['controller'], self::ERR_WRONG_CONTROLLER.$channel['title'] );
        $this->assertAction( $urlParams['action'] );
        
        $this->assertQueryCount("#maincontent h1", 1 );
        
        $list = $this->_bcModel->getProgramsForDay( $date, $channel['id'] );
        $this->assertNotEmpty($list);
        
	}
	
    /*
	public function testProgramWeekAction(){
		
	    $urlParams  = $this->urlizeOptions( array(
	    	'module'=>'default',
	    	'controller'=>'listings',
	    	'action'=>'program-week'
	    ));
	    $url = $this->url( $urlParams );
	    $this->dispatch($url);
	    
	    $this->assertModule( $urlParams['module'] );
        $this->assertController( $urlParams['controller'] );
        $this->assertAction( $urlParams['action'] );
        
        //http://www.phpunit.de/manual/current/en/incomplete-and-skipped-tests.html
	    $this->markTestIncomplete( __FUNCTION__.self::MARK_INCOMPLETE );
		
	}
	*/
	
    /*
	public function testProgramDayAction(){
		
	    $urlParams = $this->urlizeOptions( array(
	    	'module'=>'default',
	    	'controller'=>'listings',
	    	'action'=>'program-day',
	    ));
        
        $this->assertModule( $urlParams['module'] );
	    $this->assertController( $urlParams['controller'] );
	    $this->assertAction( $urlParams['action'] );
	    
        //http://www.phpunit.de/manual/current/en/incomplete-and-skipped-tests.html 
	    $this->markTestIncomplete( __FUNCTION__.self::MARK_INCOMPLETE );
        
	   
	    $maxChannels=3;
	    $maxPrograms=5;
	    $channelsModel = new Xmltv_Model_Channels();
	    $channels = $channelsModel->allChannels();
	    $this->request->setQuery(array(
	    		'channel' => 'discovery-science',
	    		'date' => 'техноигрушки',
	    ));
	    $url = $this->url( $urlParams );
	    $this->dispatch($url);
	    
	    
	}
	*/
}