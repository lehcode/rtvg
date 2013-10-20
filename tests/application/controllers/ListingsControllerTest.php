<?php
/**
 * Listings Controller Tests
 * 
 * Marking test incomplete: http://www.phpunit.de/manual/current/en/incomplete-and-skipped-tests.html 
 */
class ListingsControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{
	    
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

        $front = $this->getFrontController();
        if($front->getParam('bootstrap') === null) {
            $front->setParam('bootstrap', $this->_application->getBootstrap());
        }
        
        $router = new Xmltv_Plugin_Router();
        $router->setRouter($front->getRouter());
		$front->setRouter($router->getRouter());
    }
    
    /**
     * @group listings
     */
    public function testDayListingAction(){
		
        $channels = $this->_channelsModel->getPublished();
        $this->assertNotEmpty($channels);
        $channel = $channels[array_rand($channels, 1)];
        
        $channelsCategories = new Xmltv_Model_DbTable_ChannelsCategories();
        $chCats = $channelsCategories->fetchAll(null, "title ASC");
        $this->assertNotEmpty($chCats);
        
        // Test without date
        $urlParams  = $this->urlizeOptions( array(
            'module'=>'default',
            'controller'=>'listings',
            'action'=>'day-listing',
            'channel'=>$channel['alias'],
        ));
        $url = $this->url( $urlParams, 'default_listings_day-listing', null, true );
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
            $list = array_slice($list, 0, 3);
            $listingVideos = $this->_videosModel->ytListingRelatedVideos( $list, $channel['title'], $date );
        }
        
        //$this->assertQueryContentContains( "#maincontent h1", $channel['title']);
        
        // Test with date
        $urlParams  = $this->urlizeOptions( array(
            'module'=>'default',
            'controller'=>'listings',
            'action'=>'day-listing',
            'channel' =>$channel['alias'],
            'date'=>Zend_Date::now()->toString('dd-MM-YYYY'),
        ));
        $url = $this->url( $urlParams, 'default_listings_day-listing', null, true );
        $this->dispatch($url);

        // Assertions
        $this->assertNotRedirect();
        $this->assertModule( $urlParams['module'] );
        $this->assertController( $urlParams['controller'] );
        $this->assertAction( $urlParams['action'] );
        
        //$this->assertQueryCount("#maincontent h1", 1 );
        
        //$list = $this->_bcModel->getProgramsForDay( $date, $channel['id'] );
        //$this->assertNotEmpty($list);
        
	}
    
    /**
     * @group listings
     */
    public function testDayDateAction(){
	
        $channels = $this->_channelsModel->getPublished();
        $channel = $channels[array_rand($channels, 1)];
        $this->assertNotEmpty($channels);
        $urlParams = $this->urlizeOptions( array(
	    	'module'=>'default',
	    	'controller'=>'listings',
	    	'action'=>'day-date',
	    	'channel'=>$channel['alias'],
            'date'=>Zend_Date::now()->subDay(rand(1,7))->toString('dd-MM-YYYY'),
	    ));
        $url = $this->url( $urlParams, 'default_listings_day-date' );
	    $this->dispatch($url);
	    
	    $this->assertModule( $urlParams['module'] );
		$this->assertController( $urlParams['controller'] );
		$this->assertAction( 'day-listing' );
		
	}
	
    /**
     * @group listings
     */
	public function testProgramWeekAction(){
		
        $channels = $this->_channelsModel->getPublished();
        $this->assertNotEmpty($channels);
        $channel = $channels[array_rand($channels, 1)];
        
        $bcs = new Xmltv_Model_ProgramsTest();
        
        $ws = new Zend_Date();
		if ($ws->toString(Zend_Date::WEEKDAY_DIGIT, 'ru')!=1) {
			while ($ws->toString(Zend_Date::WEEKDAY_DIGIT, 'ru')!=1) {
				$ws->subDay(1);		
			};
		}
        
        $we = new Zend_Date();
        if ($we->toString(Zend_Date::WEEKDAY_DIGIT, 'ru')!=0) {
		    while ($we->toString(Zend_Date::WEEKDAY_DIGIT, 'ru')!=0) {
		        $we->addDay(1);
		    }
   		}
        
        var_dump(count($bcs->thisWeekBroadcasts($channel['id'], $ws, $we)));
        
        die(__FILE__ . ': ' . __LINE__);
        
        $urlParams  = $this->urlizeOptions( array(
	    	'module'=>'default',
	    	'controller'=>'listings',
	    	'action'=>'program-week',
            'channel'=>$channel['alias'],
            'alias'=>$bc['alias'],
	    ));
	    $url = $this->url( $urlParams, 'default_listings_program-week', null, true );
	    $this->dispatch($url);
        
        $this->markTestIncomplete();
        
	    /*
	    $this->assertModule( $urlParams['module'] );
        $this->assertController( $urlParams['controller'] );
        $this->assertAction( $urlParams['action'] );
		*/
	}
	

    /**
     * @group listings
     */
	public function testProgramDayAction(){
		
        $urlParams = $this->urlizeOptions( array(
	    	'module'=>'default',
	    	'controller'=>'listings',
	    	'action'=>'program-day',
	    ));
        $url = $this->url( $urlParams );
	    $this->dispatch($url);
        
        $this->assertModule( $urlParams['module'] );
	    $this->assertController( $urlParams['controller'] );
	    $this->assertAction( $urlParams['action'] );
        
        $this->markTestIncomplete();
	    
        /*
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
	    */
	    
	}
    
    /**
     * @group listings
     */
    public function testOutdatedAction(){
        
        $urlParams = $this->urlizeOptions( array(
	    	'module'=>'default',
	    	'controller'=>'listings',
	    	'action'=>'outdated',
	    ));
        $url = $this->url( $urlParams );
	    $this->dispatch($url);
        
        $this->assertModule( $urlParams['module'] );
	    $this->assertController( $urlParams['controller'] );
	    $this->assertAction( $urlParams['action'] );
        
        $this->markTestIncomplete();
    }
	
}