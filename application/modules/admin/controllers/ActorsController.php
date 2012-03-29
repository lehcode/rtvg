<?php

class Admin_ActorsController extends Zend_Controller_Action
{

    public function init()
    {
        $this->_helper->layout->setLayout('admin');
    }

    public function indexAction()
    {
        $this->_forward('list');
    }


}

