<?php
/**
 * Frontend index controller
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: FrontpageController.php,v 1.4 2013-02-15 00:44:02 developer Exp $
 *
 */
class FrontpageController extends Xmltv_Controller_Action
{
    
    const TOP_CHANNELS_AMT = 20;
    protected $list;
    	
	/**
	 * Data for frontpage view
	 */
	public function indexAction () {
		
	    $this->view->assign('pageclass', 'frontpage');
	    $this->view->assign('featured', $this->getFeaturedChannels());
	    
	    $this->cache->setLifetime(600);
	    $hash = md5(__METHOD__);
	    $f = '/Listings/Frontpage';
	    if ($this->cache->enabled){
		    if (!$list = $this->cache->load( $hash, 'Core', $f)) {
		        $this->_getList();
		        $this->cache->save( $list, $hash, 'Core', $f);
		    } else {
			    $this->_getList();
		    }
	    } else {
		    $this->_getList();
	    }
	    $this->view->assign( 'list', $this->list );
	    
	}
	
	private function _getList(){
		$this->list = $this->programsModel->frontpageListing( $this->_helper->top( 'topchannels', array('amt'=>self::TOP_CHANNELS_AMT)));
	}
	
}