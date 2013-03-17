<?php
/**
 * Backend content controller
 * 
 * @author     Antony Repin <egeshisolutions@gmail.com>
 * @subpackage backend
 * @version    $Id: ContentController.php,v 1.2 2013-03-17 18:34:58 developer Exp $
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
		parent::validateRequest();
		if ($this->isAllowed){
			$model = new Admin_Model_Articles();
			$page = (int)$this->input->getEscaped( 'page' );
			$amt = 25;
			$paginator = $model->getList();
			$paginator->setCurrentPageNumber( $page );
			$paginator->setItemCountPerPage( $amt );
			$paginator->setPageRange(1);
			$this->view->assign( 'list', $paginator->getCurrentItems() );
			$this->view->assign( 'pagenav', $paginator );
			$this->view->assign( 'actions_menu', new Zend_Navigation( 
				new Zend_Config_Xml( APPLICATION_PATH . '/configs/nav/admin/articles-menu.xml', 'nav' )));
		}
		
	}
	
	public function editAction()
	{
		if (null === $this->input->getEscaped('id')){
			$this->view->assign('new', true);
		}
	}
	
	
	
}