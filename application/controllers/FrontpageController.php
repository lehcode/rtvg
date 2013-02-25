<?php

/**
 * Frontend index controller
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: FrontpageController.php,v 1.5 2013-02-25 11:40:40 developer Exp $
 *
 */
class FrontpageController extends Xmltv_Controller_Action
{
    
    const TOP_CHANNELS_AMT = 30;
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
	        $this->cache->setLifetime(300);
	        $hash = md5('frontpage-channels-'.self::TOP_CHANNELS_AMT);
	        $this->cache->setLocation(ROOT_PATH.'/cache');
	        
	        if (APPLICATION_ENV=='development'){
		        var_dump($this->cache);
		        //die(__FILE__.': '.__LINE__);
	        }
	        
	        $f = '/Listings/Frontpage';
		    if (($this->list = $this->cache->load( $hash, 'Core', $f))===false) {
		        $this->_getList();
		        $this->cache->save( $this->list, $hash, 'Core', $f);
		    }
	    } else {
		    $this->_getList();
	    }
	    
	    if (APPLICATION_ENV=='development'){
	    	//var_dump($this->list);
	    	//die(__FILE__.': '.__LINE__);
	    }
	    
	    $this->view->assign( 'list', $this->list );
	    
	    // Channels data for dropdown
		if ($this->cache->enabled){
		    $this->cache->setLifetime(300);
		    $this->cache->setLocation(ROOT_PATH.'/cache');
		    
			if (APPLICATION_ENV=='development'){
		        var_dump($this->cache);
		        //die(__FILE__.': '.__LINE__);
	        }
	        
	        $hash = md5('frontpage-channels-'.self::TOP_CHANNELS_AMT);
	        $f = '/Channels';
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
	
	public function singleChannelAction(){
		
	    if (parent::requestParamsValid()){
	        
	        $channelId = $this->input->getEscaped('id');
	        
	        if ($this->cache->enabled){
	            $this->cache->setLifetime(86400);
	            $hash = md5('channel-info-'.$channelId);
	            $f = '/Channels';
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
	            $this->cache->setLifetime(300);
	            $hash = md5('frontpage-channel-'.$channelId);
	            $f = '/Listings/Frontpage';
	            if (($list = $this->cache->load( $hash, 'Core', $f))===false) {
	            	$list = $this->programsModel->frontpageListing($ch);
	            	$this->cache->save( $list, $hash, 'Core', $f);
	            }
	        }
	        $keys = array_keys($list);
	        $this->view->assign( 'list', $list[$keys[0]] );
			
	    }
	    
	}
	
}