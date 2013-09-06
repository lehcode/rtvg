<?php
/**
 *
 * Controller for archiving tasks
 *
 * @author     Antony Repin <egeshisolutions@gmail.com>
 * @package backend
 * @version    $Id: ActorsController.php,v 1.2 2013-03-16 12:46:19 developer Exp $
 *
 */
class Admin_ActorsController extends Rtvg_Controller_Admin
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

