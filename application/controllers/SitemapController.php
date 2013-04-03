<?php
/**
 * Frontend Sitemap controller
 * 
 * @author  Antony Repin
 * @uses    Xmltv_Controller_Action
 * @version $Id: SitemapController.php,v 1.10 2013-04-03 04:08:15 developer Exp $
 *
 */
class SitemapController extends Rtvg_Controller_Action
{
    
    /**
     * @var Xmltv_Model_Sitemap
     */
    private $_model;
    
	/**
	 * (non-PHPdoc)
	 * @see Xmltv_Controller_Action::init()
	 */
    public function init () {
        
        parent::init();
        $this->_request->setParam('format', 'xml');
		$ajaxContext = $this->_helper->getHelper('contextSwitch');
        $ajaxContext->addActionContext('sitemap', 'xml')->initContext();
        $this->_helper->layout->disableLayout();
        $this->_model = new Xmltv_Model_Sitemap();
        
	}
	
	/**
	 * Generate sitemap
	 */
	public function sitemapAction(){
		
	    $channelsModel  = new Xmltv_Model_Channels();
	    
		if ($this->cache->enabled){
		    
		    $this->cache->setLifetime(86400*7);
		    $f = "/Listings";
		    $hash = 'sitemap';
		    
		    if (($list = $this->cache->load( $hash, 'Core', $f ))===false) {
		        $list = $channelsModel->getPublished( $this->view );
		        $this->cache->save( $list, $hash, 'Core', $f );
		    }
		} else {
		    $list = $channelsModel->getPublished( $this->view );
		}
		
		$aliases = array();
		foreach ($list as $i) {
			$aliases[]=Xmltv_String::strtolower( $i['alias'] );
		}
		$this->view->assign( 'channel_aliases', $aliases );
		
		/**
		 * Detect start of a week
		 */
		$weekStart = $this->_helper->getHelper( 'weekDays' )->getStart( Zend_Date::now());
		$this->view->assign( 'week_start', $weekStart );
		$weekEnd = $this->_helper->getHelper( 'weekDays' )->getEnd( Zend_Date::now());
		
		/*
		 * Выбор программ, которые выходят в эфир на этой неделе
		 */
		if ($this->cache->enabled){
		    $this->cache->setLifetime(86400);
		    $f = "/Listings";
		    $hash = 'sitemap_e1';
		    if (($list = $this->cache->load($hash, 'Core', $f))===false) {
		    	$list = $this->_model->weekListing( $weekStart, $weekEnd );
		    	$this->cache->save( $list, $hash, 'Core', $f );
		    }
		} else {
		    $list = $this->_model->weekListing( $weekStart, $weekEnd );
		}
		$this->view->assign('week_items', $list);
		
		
	}
	
}