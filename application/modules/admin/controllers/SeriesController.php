<?php
/**
 *
 * Backend series management
 *
 * @author     Antony Repin <egeshisolutions@gmail.com>
 * @subpackage backend
 * @version    $Id: SeriesController.php,v 1.2 2013-03-16 12:46:19 developer Exp $
 */
class Admin_SeriesController extends Zend_Controller_Action
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

