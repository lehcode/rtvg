<?php
class ListingsControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{
	
	protected $frontController;
	const ERR_WRONG="---Wrong for ";
	const ERR_MISSING="---Missing for ";
	const MARK_INCOMPLETE=" has not been implemented yet.";
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
		$this->frontController = Zend_Controller_Front::getInstance();
		parent::setUp();
	}
	
	
	public function testIndexAction(){
	    
	    $urlParams = $this->urlizeOptions( array(
			'module'=>'default',
			'controller'=>'error',
			'action'=>'error',
		));
		$url = $this->url( $urlParams );
		$this->dispatch($url);
		
		// assertions
		$this->assertModule( $urlParams['module'] );
		$this->assertController( $urlParams['controller'] );
		$this->assertAction( $urlParams['action'] );
		
	}
	
	
	public function testDayDateAction(){
	
		$urlParams = $this->urlizeOptions( array(
	    	'module'=>'default',
	    	'controller'=>'listings',
	    	'action'=>'day-date',
	    ));
	    $url = $this->url( $urlParams );
	    $this->dispatch($url);
	    
	    /*
	     * http://www.phpunit.de/manual/current/en/incomplete-and-skipped-tests.html
	     */
	    $this->markTestIncomplete( __FUNCTION__.self::MARK_INCOMPLETE );
		
	}
	
	
	public function testDayListingAction(){
		
	    require_once APPLICATION_PATH.'/../tests/library/rtvg_channels.php';
	    $max = 2;
	    $keys = array();
	    do{
	        $k = rand(0, count($rtvg_channels)-1);
	        if (!in_array($k, $keys))
	        	$keys[] = rand(0, count($rtvg_channels)-1);
	    } while( count($keys)<$max );
	    
	    $today = Zend_Date::now()->toString('dd-MM-yyyy');
	    $siteConfig = Zend_Registry::get('site_config');
	    foreach ($keys as $key){
	        
	        $channel = $rtvg_channels[$key];
			$urlParams  = $this->urlizeOptions( array(
				'module'=>'default',
				'controller'=>'listings',
				'action'=>'day-listing',
			));
			$this->request->setQuery(array(
				'channel' => $channel['alias'],
				'date' => $today,
			));
			$url = $this->url( $urlParams );
			$this->dispatch($url);
			
			// assertions
			$this->assertModule( $urlParams['module'] );
			$this->assertController( $urlParams['controller'], self::ERR_WRONG.$channel['title'] );
			$this->assertAction( $urlParams['action'] );
			$this->assertNotRedirect();
			
			$this->assertQueryCountMin("ul#channels_categories li", 23 );
			$this->assertQueryCountMin("ul#program-top li", (int)$siteConfig->topprograms->channellist->get('amount') );
			$this->assertQueryContentContains( "#maincontent h1", $channel['title']);
			//$this->assertQueryCount("div#programs-carousel", 1, self::ERR_MISSING.$channel['title'] );
			//$this->assertQueryCountMin("div#programs-carousel .programcontainer", 1, self::ERR_MISSING.$channel['title'] );
	    }
		
	}
	
	
	public function testProgramWeekAction(){
		
	    $urlParams  = $this->urlizeOptions( array(
	    	'module'=>'default',
	    	'controller'=>'listings',
	    	'action'=>'program-week',
	    	'channel'=>'discovery-science',
	    	'alias'=>'техноигрушки',
	    ));
	    $url = $this->url( $urlParams );
	    $this->dispatch($url);
	    
	    /*
	     * http://www.phpunit.de/manual/current/en/incomplete-and-skipped-tests.html
	     */
	    $this->markTestIncomplete( __FUNCTION__.self::MARK_INCOMPLETE );
		
	}
	
	
	public function testProgramDayAction(){
		
	    $urlParams = $this->urlizeOptions( array(
	    	'module'=>'default',
	    	'controller'=>'listings',
	    	'action'=>'program-day',
	    ));
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
	    // assertions
	    /*
	    $this->assertModule( $urlParams['module'] );
	    $this->assertController( $urlParams['controller'] );
	    $this->assertAction( $urlParams['action'] );
	    $this->assertNotRedirect();
	    */
	     /*
	     * http://www.phpunit.de/manual/current/en/incomplete-and-skipped-tests.html
	     */
	    $this->markTestIncomplete( __FUNCTION__.self::MARK_INCOMPLETE );
	    
	}
	
}