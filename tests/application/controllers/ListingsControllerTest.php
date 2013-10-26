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
     * @var Xmltv_Model_BroadcastsTest
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
    
    /**
     * @var Zend_Date
     */
    private $weekStart;
    /**
     * @var Zend_Date
     */
    private $weekEnd;
	
    /**
     * 
     */
    public function setUp()
	{
        $this->bootstrap = array($this, 'appBootstrap');
        parent::setUp();
        
        $this->_bcModel = new Xmltv_Model_BroadcastsTest();
        $this->_channelsModel = new Xmltv_Model_Channels();
        $this->_videosModel = new Xmltv_Model_Videos();
        $this->_commentsModel = new Xmltv_Model_Comments();
        
        $this->weekStart = $this->getWeekStart();
        $this->weekEnd = $this->getWeekEnd();
        
	}
    
    /**
     * @codeCoverageIgnore
     */
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
     * @group listingsControllerActions
     */
    public function testDayListingAction(){
		
        $channels = $this->_channelsModel->getPublished(true);
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
        
        $url = $this->url( $urlParams, 'default_listings_day-listing' );
        try {$url = $this->url( $urlParams, 'default_listings_day-listing' );
            $this->dispatch($url);
        } catch (Exception $e) {
            if(get_class($e)=='Zend_Loader_PluginLoader_Exception'){
                //skip
           }
        }
        
        // Assertions
        $this->assertModule( $urlParams['module'] );
        $this->assertController( $urlParams['controller'] );
        $this->assertAction( $urlParams['action'] );
        $this->assertResponseCode(200);
        
        $date = Zend_Date::now();
        if ((bool)($list = $this->_bcModel->getBroadcastsForDay( $date, $channel['id'], 4 ))!==false){
            $this->assertNotEmpty($list);
            $list = array_slice($list, 0, 3);
            $this->_videosModel->ytListingRelatedVideos( $list, $channel['title'], $date );
        }
        
        //Sidebar videos for channel
        $this->_videosModel->sidebarVideos($channel);
        
        $top = $this->_bcModel->topBroadcasts();
        $this->assertNotEmpty($top);
        
        //Channels comments
        $this->_commentsModel->channelComments( $channel['id'] );
        
        
        
	}
    
    /**
     * @group listingsControllerActions
     */
    public function testDayListingToday(){
        
        $channels = $this->_channelsModel->getPublished(true);
        $this->assertNotEmpty($channels);
        $channel = $channels[array_rand($channels, 1)];
        
        // Test with today's date
        $urlParams  = $this->urlizeOptions( array(
            'module'=>'default',
            'controller'=>'listings',
            'action'=>'day-listing',
            'channel' =>$channel['alias'],
            'date'=>Zend_Date::now()->toString('dd-MM-YYYY'),
        ));
        $url = $this->url( $urlParams, 'default_listings_day-listing' );
        $this->dispatch($url);

        // Assertions
        $this->assertResponseCode(200);
        $this->assertModule( $urlParams['module'] );
        $this->assertController( $urlParams['controller'] );
        $this->assertAction( $urlParams['action'] );
        
        //$this->assertQueryCount("#maincontent h1", 1 );
        $this->assertQueryContentContains( "#maincontent h1", $channel['title']);
        
    }
    
    /**
     * @group listingsControllerActions
     */
    public function testDayListingSpecificDay(){
        
        $channels = $this->_channelsModel->getPublished(true);
        $this->assertNotEmpty($channels);
        $channel = $channels[array_rand($channels, 1)];
        
        $urlParams  = $this->urlizeOptions( array(
            'module'=>'default',
            'controller'=>'listings',
            'action'=>'day-date',
            'channel' =>$channel['alias'],
            'date'=>  Zend_Date::now()->subDay(3)->toString('dd-MM-YYYY'),
        ));
        $url = $this->url( $urlParams, 'default_listings_day-listing' );
        $this->dispatch($url);
        
        $this->assertModule( $urlParams['module'] );
        $this->assertController( $urlParams['controller'] );
        $this->assertAction( 'day-listing' );
        
        $this->assertQueryContentContains( "#maincontent h1", $channel['title']);
        
    }
    
    /**
     * @group listingsControllerActions
     */
	public function testBroadcastWeekAction(){
		
        $channels = $this->_channelsModel->getPublished();
        $this->assertNotEmpty($channels);
        $channel = $channels[array_rand($channels, 1)];
        
        $bcs = new Xmltv_Model_BroadcastsTest();
        $weekBcs = $bcs->thisWeekBroadcasts($channel['id'], $this->weekStart, $this->weekEnd);
        $bc = $weekBcs[array_rand($weekBcs, 1)];
        
        $urlParams  = $this->urlizeOptions( array(
	    	'module'=>'default',
	    	'controller'=>'listings',
	    	'action'=>'broadcast-week',
            'channel'=>$channel['alias'],
            'alias'=>$bc['alias'],
            'date'=>'неделя',
	    ));
	    $url = $this->url( $urlParams, 'default_listings_broadcast-week' );
	    $this->dispatch($url);
        
        $this->assertModule( $urlParams['module'] );
        $this->assertController( $urlParams['controller'] );
        $this->assertAction( $urlParams['action'] );
        $bcTop = $this->_bcModel->topBroadcasts();
        $this->assertNotEmpty($bcTop);
        $chCats = $this->_channelsModel->channelsCategories();
        $this->assertNotEmpty($chCats);
        $list = $this->_bcModel->broadcastThisWeek( $bc['alias'], $channel['id'], $this->weekStart, $this->weekEnd);
        $this->assertNotEmpty($list);
        $this->_bcModel->similarBroadcastsThisWeek( $bc['alias'], $this->weekStart, $this->weekEnd, $channel['id'] );
        $this->assertResponseCode(200);
        
        $this->markTestIncomplete();
		
	}
    
    /**
     * @group listingsControllerActions
     */
    public function testCategoryAction(){
        
        $cats = $this->_bcModel->getCategoriesList();
        $cat = $cats[array_rand($cats, 1)];
        
        $urlParams = $this->urlizeOptions( array(
	    	'module'=>'default',
	    	'controller'=>'listings',
	    	'action'=>'category',
	    	'category'=>$cat['alias'],
	    	'timespan'=>'неделя',
	    ));
        $url = $this->url( $urlParams, 'default_listings_category' );
	    $this->dispatch($url);
        
        $this->assertModule( $urlParams['module'] );
	    $this->assertController( $urlParams['controller'] );
	    $this->assertAction( $urlParams['action'] );
        
        $this->_bcModel->categoryWeek( $cat['id'], $this->weekStart, $this->weekEnd);
        $this->_bcModel->categoryDay( $cat['id'] );
        
        $urlParams = $this->urlizeOptions( array(
	    	'module'=>'default',
	    	'controller'=>'listings',
	    	'action'=>'category',
	    	'category'=>$cat['alias'],
	    	'timespan'=>'сегодня',
	    ));
        $url = $this->url( $urlParams, 'default_listings_category' );
	    $this->dispatch($url);
        
        $this->_bcModel->categoryDay( $cat['id']);
        $this->assertResponseCode(200);
        
    }
    
    /**
     * @group listingsControllerActions
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
     * @group listingsControllerActions
     */
	public function testBroadcastDayAction(){
		
        $channels = $this->_channelsModel->getPublished();
        $this->assertNotEmpty($channels);
        $channel = $channels[array_rand($channels, 1)];
        $this->assertNotEmpty($channel);
        
        $bcs = new Xmltv_Model_BroadcastsTest();
        $todayBcs = $bcs->getTodayBroadcasts($channel['id']);
        $this->assertNotEmpty($todayBcs);
        
        $urlParams = $this->urlizeOptions( array(
	    	'module'=>'default',
	    	'controller'=>'listings',
	    	'action'=>'broadcast-day',
            'channel'=>$channel['alias'],
            'alias'=>$todayBcs[array_rand($todayBcs, 1)]['alias'],
	    ));
        $url = $this->url( $urlParams, 'default_listings_broadcast-day' );
	    $this->dispatch($url);
        
        $this->assertModule( $urlParams['module'] );
	    $this->assertController( $urlParams['controller'] );
	    $this->assertAction( $urlParams['action'] );
        
        $this->markTestIncomplete();
	    
	}
    
    /**
     * @group listingsControllerActions
     */
    public function testOutdatedAction(){
        
        $urlParams = $this->urlizeOptions( array(
	    	'module'=>'default',
	    	'controller'=>'listings',
	    	'action'=>'outdated',
	    ));
        $url = $this->url( $urlParams, 'default_listings_outdated' );
	    $this->dispatch($url);
        
        $this->assertModule( $urlParams['module'] );
	    $this->assertController( $urlParams['controller'] );
	    $this->assertAction( $urlParams['action'] );
        
        $this->markTestIncomplete();
    }
    
    private function getWeekStart(){
        $ws = new Zend_Date();
		if ($ws->toString(Zend_Date::WEEKDAY_DIGIT, 'ru')!=1) {
			while ($ws->toString(Zend_Date::WEEKDAY_DIGIT, 'ru')!=1) {
				$ws->subDay(1);		
			};
		}
        return $ws;
    }
    
    private function getWeekEnd(){
        $we = new Zend_Date();
        if ($we->toString(Zend_Date::WEEKDAY_DIGIT, 'ru')!=0) {
		    while ($we->toString(Zend_Date::WEEKDAY_DIGIT, 'ru')!=0) {
		        $we->addDay(1);
		    }
   		}
        return $we;
    }
	
}