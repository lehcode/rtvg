<?php
/**
 * Frontend index controller
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @version $Id: FrontpageController.php,v 1.12 2013-04-11 05:21:11 developer Exp $
 *
 */

class FrontpageController extends Rtvg_Controller_Action
{
    
    /**
     * @var Xmltv_Model_Articles
     */
    private $articlesModel;
    
    const TOP_CHANNELS_AMT = 20;
    protected $list;
    
    /**
     * (non-PHPdoc)
     * @see Zend_Controller_Action::init()
     */
    public function init () {
    
    	parent::init();
    
    	/**
    	 * Change layout for AJAX requests
    	 */
    	if ($this->getRequest()->isXmlHttpRequest()) {
    		$ajaxContext = $this->_helper->getHelper( 'AjaxContext' );
			$ajaxContext->addActionContext( 'single-channel', 'html' )
				->initContext();
    	}
    	
    	$this->articlesModel = new Xmltv_Model_Articles();
    	
    	if (!$this->_request->isXmlHttpRequest()){
			$this->view->assign( 'pageclass', parent::pageclass(__CLASS__) );
			$this->view->assign( 'vk_group_init', false );
		}
    	
    }
    	
	/**
	 * Data for frontpage view
	 */
	public function indexAction () {
		
	    if ($this->cache->enabled){
	        
	        $this->cache->setLifetime(600);
	        $f = '/Listings/Frontpage';
	        
	        $hash = md5('frontpage-channels-'.self::TOP_CHANNELS_AMT);
		    if (($this->list = $this->cache->load( $hash, 'Core', $f))===false) {
		        $this->list = $this->programsModel->frontpageListing( $this->_helper->top( 
					'TopChannels', 
					array( 'amt'=>self::TOP_CHANNELS_AMT )));
		        $this->cache->save( $this->list, $hash, 'Core', $f);
		    }
	    } else {
		    $this->list = $this->programsModel->frontpageListing( $this->_helper->top( 
				'TopChannels', 
				array( 'amt'=>self::TOP_CHANNELS_AMT )));
	    }
	    
	    if (APPLICATION_ENV=='development'){
	        //var_dump($this->list);
	        //die(__FILE__.': '.__LINE__);
	    }
	    
	    $this->view->assign( 'list', $this->list );
	    
	    // Channels data for dropdown
		if ($this->cache->enabled){
		    
		    $this->cache->setLifetime(600);
		    $this->cache->setLocation(ROOT_PATH.'/cache');
		    $f = '/Channels';
			$hash = md5('frontpage-channels-'.self::TOP_CHANNELS_AMT);
	        if (($channels = $this->cache->load( $hash, 'Core', $f))===false) {
		        $channels = $this->channelsModel->allChannels("title ASC");
		        $this->cache->save( $channels, $hash, 'Core', $f);
		    }
	    } else {
		    $channels = $this->channelsModel->allChannels("title ASC");
	    }
	    $this->view->assign( 'channels', $channels );
	    
	    $articlesAmt = 4;
	    $this->view->assign( 'articles_amt', $articlesAmt);
	    if ( $this->cache->enabled && APPLICATION_ENV=='production' ){
	        $this->cache->setLifetime(600);
	        $f = '/Content/Articles';
	        $hash = md5( 'frontpage-articles' );
	        if (($channels = $this->cache->load( $hash, 'Core', $f))===false ) {
	        	$articles = $this->articlesModel->frontpageItems( $articlesAmt );
	        	$this->cache->save( $channels, $hash, 'Core', $f);
	        }
	    } else {
	        $articles = $this->articlesModel->frontpageItems( $articlesAmt );
	    }
	    $this->view->assign( 'articles', $articles );
	    
	}
	
	
	
	/**
	 * Display single channel listings
	 */
	public function singleChannelAction(){
		
	    if (!$this->_request->isXmlHttpRequest()){
	        throw new Zend_Exception( Rtvg_Message::ERR_INVALID_INPUT );
	    }
	    
	    if (parent::validateRequest()){
	        
	        $channelId = $this->input->getEscaped('id');
	        
	        if ($this->cache->enabled){
	            
	            $this->cache->setLifetime(3600);
	            $f = '/Listings/Frontpage';
	            $hash = md5('single_channel_'.$channelId);
	            if (($channel = $this->cache->load( $hash, 'Core', $f))===false) {
	            	$channel = $this->channelsModel->getById($this->input->getEscaped('id'));
	            	$this->cache->save( $channel, $hash, 'Core', $f);
	            }
	            
	        } else {
	            $channel = $this->channelsModel->getById($this->input->getEscaped('id'));
	        }
	        
	        $ch[] = $channel;
	        $this->view->assign( 'channel', $ch[0] );
	        $this->channelsModel->addHit($ch[0]['id']);
	        
	        if ($this->cache->enabled){
	        	
	            $this->cache->setLifetime(1800);
	            $f = '/Listings/Frontpage';
	            $hash = md5('frontpage-channel-'.$channelId);
	            if (($list = $this->cache->load( $hash, 'Core', $f))===false) {
	            	$list = $this->programsModel->frontpageListing($ch);
	            	$this->cache->save( $list, $hash, 'Core', $f);
	            }
	        } else {
	            $list = $this->programsModel->frontpageListing($ch);
	        }
	        
	        if ($list){
	        	$keys = array_keys($list);
	        	$this->view->assign( 'list', $list[$keys[0]] );
	        }
	        
			
	    }
	    
	}
	
}