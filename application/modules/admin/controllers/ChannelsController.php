<?php
/**
 * 
 * Backend channels management
 * 
 * @author     Antony Repin <egeshisolutions@gmail.com>
 * @package backend
 * @version    $Id: ChannelsController.php,v 1.4 2013-03-17 18:34:58 developer Exp $
 */
class Admin_ChannelsController extends Rtvg_Controller_Admin
{

    public function init()
    {
        parent::init();
    }

    public function indexAction()
    {
        $this->_forward('list');
    }

	public function listAction()
    {
        die(__FILE__.': '.__LINE__);
    }

}

