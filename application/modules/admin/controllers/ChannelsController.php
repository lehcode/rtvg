<?php
/**
 * 
 * Backend channels management
 * 
 * @author     Antony Repin <egeshisolutions@gmail.com>
 * @subpackage backend
 * @version    $Id: ChannelsController.php,v 1.3 2013-03-16 12:46:19 developer Exp $
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
        //
    }

}

