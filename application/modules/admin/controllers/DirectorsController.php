<?php
/**
 *
 * Backend directors management
 *
 * @author     Antony Repin <egeshisolutions@gmail.com>
 * @package backend
 * @version    $Id: DirectorsController.php,v 1.2 2013-03-16 12:46:19 developer Exp $
 */
class Admin_DirectorsController extends Rtvg_Controller_Admin
{

    public function init()
    {
        parent::init();
    }

    public function indexAction()
    {
        $this->_forward('list');
    }


}

