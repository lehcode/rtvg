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
    
        $ajaxContext = $this->_helper->getHelper('contextSwitch');
        $ajaxContext
            ->addActionContext( 'timeline', 'json' )
            ->initContext();
            
        //Change layout for AJAX requests
    	if ($this->getRequest()->isXmlHttpRequest()) {
    		$this->_helper->layout->disableLayout();
    	} else {
            $this->view->assign( 'pageclass', parent::pageclass(__CLASS__) );
			$this->view->assign( 'vk_group_init', false );
        }
    	
    	$this->articlesModel = new Xmltv_Model_Articles();
    	
    }
    	
	/**
	 * Data for frontpage view
	 */
	public function indexAction () {
        
        $amt = (int)Zend_Registry::get('site_config')->frontend->frontpage->channels;
        if($amt==0){
            $list = array();
        } else {
            $top = $this->channelsModel->topChannels($amt);
            if ($this->cache->enabled){
                (APPLICATION_ENV=='development') ? $this->cache->setLifetime(100) :  $this->cache->setLifetime(600) ;
                $f = 'Listings/Frontpage';
                $hash = (APPLICATION_ENV=='development') ? 'frontpage_listings' : Rtvg_Cache::getHash('frontpage_listings');
                if ((bool)($list = $this->cache->load( $hash, 'Core', $f))===false) {
                    $list = $this->bcModel->frontpageListing($top);
                    $this->cache->save( $list, $hash, 'Core', $f);
                }
            } else {
                $list = $this->bcModel->frontpageListing($top);
            }
        }
        
        if (count($list)){
            $this->view->assign('list', $list);
        } else {
            $this->view->assign('list', array());
        }
        
        // Channels data for dropdown
		if ($this->cache->enabled && APPLICATION_ENV!='development'){
		    (APPLICATION_ENV=='development') ? $this->cache->setLifetime(100) :  $this->cache->setLifetime(600) ;
		    $f = 'Channels';
			$hash = (APPLICATION_ENV=='development') ? 'channels_dropdown' : Rtvg_Cache::getHash('channels_dropdown') ;
	        if ((bool)($channels = $this->cache->load( $hash, 'Core', $f))===false) {
		        $channels = $this->channelsModel->allChannels("title ASC");
		        $this->cache->save( $channels, $hash, 'Core', $f);
		    }
	    } else {
		    $channels = $this->channelsModel->allChannels("title ASC");
	    }
	    $this->view->assign( 'channels', $channels );
	    
        // Frontpage articles
	    $articlesAmt = (int)Zend_Registry::get('site_config')->frontend->frontpage->get('articles');
	    $this->view->assign( 'articles_amt', $articlesAmt);
        if ($articlesAmt>0){
            if ( $this->cache->enabled ){
                (APPLICATION_ENV=='development') ? $this->cache->setLifetime(100) :  $this->cache->setLifetime(600) ;
                $f = 'Content/Articles';
                $hash = (APPLICATION_ENV=='development') ? 'frontpage_articles' : Rtvg_Cache::getHash('frontpage_articles') ;
                if (($channels = $this->cache->load( $hash, 'Core', $f))===false ) {
                    $articles = $this->articlesModel->frontpageItems( $articlesAmt );
                    $this->cache->save( $channels, $hash, 'Core', $f);
                }
            } else {
                $articles = $this->articlesModel->frontpageItems( $articlesAmt );
            }
            $this->view->assign( 'articles', $articles );
        }
	    
	}
	
	
	
	/**
	 * Display single channel listings
	 */
	public function singleChannelAction(){
		
	    if (!$this->_request->isXmlHttpRequest()){
	        throw new Zend_Exception( "Not found", 404 );
	    }
        
        $this->_helper->layout->disableLayout();
	    
	    if (parent::validateRequest()){
	        
	        $channelId = $this->input->getEscaped('id');
	        
	        if ($this->cache->enabled){
	            (APPLICATION_ENV!='production') ? $this->cache->setLifetime(100) : $this->cache->setLifetime(600);
	            $f = '/Listings/Frontpage';
	            $hash = $this->cache->getHash('single_channel_'.$channelId);
	            if ((bool)($channel = $this->cache->load( $hash, 'Core', $f))===false) {
	            	$channel = $this->channelsModel->getById($this->input->getEscaped('id'));
	            	$this->cache->save( $channel, $hash, 'Core', $f);
	            }
	            
	        } else {
	            $channel = $this->channelsModel->getById($this->input->getEscaped('id'));
	        }
            
            $this->view->assign( 'channel', $channel );
	        $this->channelsModel->addHit( $channel['id'] );
	        
	        if ($this->cache->enabled){
	        	(APPLICATION_ENV!='production') ? $this->cache->setLifetime(100) : $this->cache->setLifetime(600);
	            $f = '/Listings/Frontpage';
	            $hash = md5('frontpage-channel-'.$channelId);
	            if (($list = $this->cache->load( $hash, 'Core', $f)) === false) {
	            	$list = $this->bcModel->frontpageListing( array($channel) );
	            	$this->cache->save( $list, $hash, 'Core', $f);
	            }
	        } else {
	            $list = $this->bcModel->frontpageListing( array($channel) );
	        }
            
            if ($list){
	        	$keys = array_keys($list);
	        	$this->view->assign( 'list', $list[$keys[0]] );
	        }
	        
			
	    }
	    
	}
    
}