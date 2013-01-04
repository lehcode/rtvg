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
			'controller'=>'listings',
			'action'=>'index',
		));
		$url = $this->url( $urlParams );
		$this->dispatch($url);
		
		/*
	     * http://www.phpunit.de/manual/current/en/incomplete-and-skipped-tests.html
	     */
	    $this->markTestIncomplete( __FUNCTION__.self::MARK_INCOMPLETE );
		
		
	}
	
	
	public function testDayDateAction(){
	
		$urlParams = $this->urlizeOptions( array(
	    	'module'=>'default',
	    	'controller'=>'listings',
	    	'action'=>'day-date',
	    	//'channel'=>$channel['alias'],
	    	//'date'=>$now->toString('dd-MM-yyyy'),
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
	    $now = Zend_Date::now();
	    
	    foreach ($rtvg_channels as $channel){
	        
			$urlParams  = $this->urlizeOptions( array(
					'module'=>'default',
					'controller'=>'listings',
					'action'=>'day-listing',
					'channel'=>$channel['alias'],
		    		//'date'=>$now->toString('dd-MM-yyyy')
			));
			$url = $this->url( $urlParams );
			
			$this->request->setQuery(array('channel'=>$channel['alias']));
			$this->dispatch($url);
			
			// assertions
			$this->assertModule( $urlParams['module'] );
			$this->assertController( $urlParams['controller'], self::ERR_WRONG.$channel['title'] );
			$this->assertAction( $urlParams['action'] );
			$this->assertNotRedirect();
			
			$this->assertQueryCountMin("div#col_l .module", 2 );
			$this->assertQueryCountMin("div#col_r .module", 1 );
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
	    		//'channel'=>'discovery-science',
	    		//'alias'=>'техноигрушки',
	    ));
	    $url = $this->url( $urlParams );
	    $this->dispatch($url);
	    
	    /*
	     * http://www.phpunit.de/manual/current/en/incomplete-and-skipped-tests.html
	     */
	    $this->markTestIncomplete( __FUNCTION__.self::MARK_INCOMPLETE );
		
	}
	
	
	public function testProgramDayAction(){
		
	    $urlParams  = $this->urlizeOptions( array(
	    		'module'=>'default',
	    		'controller'=>'listings',
	    		'action'=>'program-day',
	    		//'channel'=>'discovery-science',
	    		//'alias'=>'техноигрушки',
	    ));
	    $url = $this->url( $urlParams );
	    $this->dispatch($url);
	    
	     /*
	     * http://www.phpunit.de/manual/current/en/incomplete-and-skipped-tests.html
	     */
	    $this->markTestIncomplete( __FUNCTION__.self::MARK_INCOMPLETE );
	    
	}
	
}