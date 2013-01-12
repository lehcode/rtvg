<?php
/**
 * Frontend Sitemap controller
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: SitemapController.php,v 1.4 2013-01-12 09:06:22 developer Exp $
 *
 */
class SitemapController extends Xmltv_Controller_Action
{
	
    public function init () {
        
        parent::init();
        $this->_request->setParam('format', 'xml');
		$contextSwitch = $this->_helper->getHelper('contextSwitch');
        $contextSwitch->addActionContext('sitemap', 'xml')->initContext();
        $this->_helper->layout->disableLayout();
        
	}
	
	public function sitemapAction(){
		
		$channelsModel  = new Xmltv_Model_Channels();
		$published = $channelsModel->getPublished();
		$aliases = array();
		$week_start = $this->_helper->getHelper('weekDays')->getStart( Zend_Date::now());
		$week_start = $week_start->toString('YYYY-MM-dd');
		
		foreach ($published as $i) {
			$aliases[]=Xmltv_String::strtolower( $i['alias'] );
		}
		
		$this->view->assign( 'channel_aliases', $aliases );
		$this->view->assign( 'week_start', $week_start );
		
	}
	
}