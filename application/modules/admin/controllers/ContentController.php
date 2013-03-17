<?php
/**
 * Backend content controller
 * 
 * @author     Antony Repin <egeshisolutions@gmail.com>
 * @subpackage backend
 * @version    $Id: ContentController.php,v 1.1 2013-03-17 00:04:12 developer Exp $
 *
 */
class Admin_ContentController extends Rtvg_Controller_Admin
{
	/**
	 * (non-PHPdoc)
	 * @see Zend_Controller_Action::init()
	 */
	public function init() 
	{
	    parent::init();
	}
	
	public function articlesAction()
	{
		//
	}
	
	public function editArticleAction()
	{
		if (null === $this->input->getEscaped('id')){
			$this->view->assign('new', true);
		}
	}
	
	
	
}