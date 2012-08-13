<?php
class SitemapController extends Zend_Controller_Action
{
	public function __call ($method, $arguments) {

		header( 'HTTP/1.0 404 Not Found' );
		$this->_helper->layout->setLayout( 'error' );
		$this->view->render();
	}
	
	public function init () {
		$this->_request->setParam('format', 'xml');
		$contextSwitch = $this->_helper->getHelper('contextSwitch');
        $contextSwitch->addActionContext('sitemap', 'xml')->initContext();
	}
	
	public function sitemapAction(){
		
		$this->_helper->layout->disableLayout();
		
		$channels = new Xmltv_Model_Channels();
		$published = $channels->getPublished();
		$ch_aliases = array();
		$d = new Zend_Date( null, null, 'ru' ) ; 
		$week_start = $this->_helper->weekDays(array('method'=>'getStart', 'data'=>array('date'=>$d) ));
		$week_start = $week_start->toString('YYYY-MM-dd');
		
		foreach ($published as $i) {
			$ch_aliases[]=Xmltv_String::strtolower( $i['alias'] );
		}
		
		$this->view->assign( 'channel_aliases', $ch_aliases );
		$this->view->assign( 'week_start', $week_start );
		//$this->view->render('xml.phtml');
		
		//die(__FILE__.': '.__LINE__);
		
	}
	
	private function _channelsSitemap(){
	
	}
	
}