<?php
/**
 * Frontend Sitemap controller
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: SitemapController.php,v 1.5 2013-01-19 10:11:13 developer Exp $
 *
 */
class SitemapController extends Xmltv_Controller_Action
{
	/**
	 * (non-PHPdoc)
	 * @see Xmltv_Controller_Action::init()
	 */
    public function init () {
        
        parent::init();
        $this->_request->setParam('format', 'xml');
		$contextSwitch = $this->_helper->getHelper('contextSwitch');
        $contextSwitch->addActionContext('sitemap', 'xml')->initContext();
        $this->_helper->layout->disableLayout();
        
	}
	
	/**
	 * Generate sitemap
	 */
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