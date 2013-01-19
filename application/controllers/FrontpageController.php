<?php
/**
 * Frontend index controller
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: FrontpageController.php,v 1.3 2013-01-19 10:11:13 developer Exp $
 *
 */
class FrontpageController extends Xmltv_Controller_Action
{
    	
	/**
	 * Data for frontpage view
	 */
	public function indexAction () {
		
	    $this->view->assign('is_frontpage', true);
	
	}
	
}