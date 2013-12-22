<?php
/**
 * Frontend Sitemap controller
 * 
 * @author  Antony Repin
 * @uses    Xmltv_Controller_Action
 * @version $Id: SitemapController.php,v 1.11 2013-04-06 22:35:03 developer Exp $
 *
 */
class SitemapController extends Rtvg_Controller_Action
{
    
    /**
	 * (non-PHPdoc)
	 * @see Xmltv_Controller_Action::init()
	 */
    public function init () {
        
        parent::init();
        
        $this->_request->setParam('format', 'xml');
		$this->_helper->getHelper('contextSwitch')
            ->addActionContext('sitemap', 'xml')
            ->initContext();
        $this->getResponse()
            ->setHeader('Content-type', 'text/xml');
        $this->_helper->layout->disableLayout();
	}
    
    /**
	 * Generate sitemap
	 */
	public function sitemapAction(){
		
	    if ($this->cache->enabled){
		    
		    (APPLICATION_ENV!='production') ?  $this->cache->setLifetime(60) : $this->cache->setLifetime(86400*3);
		    $f = "/Listings";
		    $hash = 'sitemap';
		    
		    if (($list = $this->cache->load( $hash, 'Core', $f ))===false) {
		        $list = $this->channelsModel->getPublished( $this->view );
		        $this->cache->save( $list, $hash, 'Core', $f );
		    }
		} else {
		    $list = $this->channelsModel->getPublished( $this->view );
		}
        
		$aliases = array();
		foreach ($list as $i) {
			$aliases[]=Xmltv_String::strtolower( $i['alias'] );
		}
		$this->view->assign( 'channel_aliases', $aliases );
		
		/**
		 * Detect start of a week
		 */
		$ws = $this->_helper->getHelper( 'weekDays' )->getStart( Zend_Date::now());
		$this->view->assign( 'week_start', $ws );
		$we = $this->_helper->getHelper( 'weekDays' )->getEnd( Zend_Date::now());
        $this->view->assign( 'week_end', $we );
		
		/*
		 * Выбор программ, которые выходят в эфир на этой неделе
		 */
		if ($this->cache->enabled){
		    $this->cache->setLifetime(86400);
		    $f = "/Listings";
		    $hash = 'sitemap_e1';
		    if (($list = $this->cache->load($hash, 'Core', $f))===false) {
		    	$list = $this->bcModel->rssWeek( $ws, $we );
		    	$this->cache->save( $list, $hash, 'Core', $f );
		    }
		} else {
		    $list = $this->bcModel->rssWeek( $ws, $we );
		}
		$this->view->assign('week_items', $list);
		
		
	}
	
}