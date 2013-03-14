<?php
/**
 * Frontend index controller
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @uses    Xmltv_Controller_Action
 * @version $Id: FrontpageController.php,v 1.9 2013-03-14 14:43:23 developer Exp $
 *
 */

class FrontpageController extends Rtvg_Controller_Action
{
    
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
    }
    	
	/**
	 * Data for frontpage view
	 */
	public function indexAction () {
		
	    $this->view->assign('pageclass', 'frontpage');
	    
	    if ($this->cache->enabled){
	        
	        $this->cache->setLifetime(600);
	        $this->cache->setLocation(ROOT_PATH.'/cache');
	        $f = '/Listings/Frontpage';
	        
	        $hash = md5('frontpage-channels-'.self::TOP_CHANNELS_AMT);
		    if (($this->list = $this->cache->load( $hash, 'Core', $f))===false) {
		        $this->_getList();
		        $this->cache->save( $this->list, $hash, 'Core', $f);
		    }
	    } else {
		    $this->_getList();
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
	    
	}
	
	private function _getList(){
		$this->list = $this->programsModel->frontpageListing( $this->_helper->top( 'topchannels', array('amt'=>self::TOP_CHANNELS_AMT)));
	}
	
	/**
	 * Display single channel listings
	 */
	public function singleChannelAction(){
		
	    if (!$this->_request->isXmlHttpRequest()){
	        throw new Zend_Exception(parent::ERR_INVALID_INPUT.__METHOD__, 500);
	    }
	    
	    if (parent::validateRequest()){
	        
	        $channelId = $this->input->getEscaped('id');
	        
	        if ($this->cache->enabled){
	            
	            $this->cache->setLifetime(86400);
	            $this->cache->setLocation(ROOT_PATH.'/cache');
	            $f = '/Channels';
	            $hash = md5('channel-info-'.$channelId);
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
	            
	            $this->cache->setLocation(ROOT_PATH.'/cache');
	            $this->cache->setLifetime(300);
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