<?php
class Admin_SystemController extends Rtvg_Controller_Admin
{
	public function init(){
		parent::init();
	}
	
	public function indexAction()
	{
		//
	}

	public function cacheAction()
	{
		//
	}

	public function phpinfoAction()
	{
		$this->view->assign( 'phpinfo', phpinfo() );
	}
}