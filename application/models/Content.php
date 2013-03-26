<?php
/**
 * Articles model
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @version $Id: Content.php,v 1.1 2013-03-26 20:02:05 developer Exp $
 *
 */
class Xmltv_Model_Articles extends Xmltv_Model_Abstract
{
	/**
	 * @var Zend_Db_Adapater_Mysqli
	 */
	protected $db;
		
	public function __construct(array $config=null)
	{
		parent::__construct($config);
	}
	
	/**
	 * @return array
	 */
	public function getItems(){
		
	    $this->view->assign( 'messages', $this->_flashMessenger->getMessages() );
	    
	    $page = (int)$this->input->getEscaped( 'page' );
	    $amt = 25;
	    $paginator = $this->mainModel->getList();
	    $paginator->setCurrentPageNumber( $page );
	    $paginator->setItemCountPerPage( $amt );
	    $paginator->setPageRange(1);
	    $this->view->assign( 'list', $paginator->getCurrentItems() );
	    $this->view->assign( 'pagenav', $paginator );
	    $this->view->headTitle("Новости");
	    
	}
	
	
	
	
}