<?php
/**
 * Backend content controller
 * 
 * @author	 Antony Repin <egeshisolutions@gmail.com>
 * @package backend
 * @version	$Id: ContentController.php,v 1.5 2013-04-03 04:08:16 developer Exp $
 *
 */
class Admin_ContentController extends Rtvg_Controller_Admin
{

    /**
     * @var Xmltv_Model_DbTable_Articles
     */
    protected $articlesTable;
    
    const HEAD_TITLE_PATTERN = "%s --> %s --> %s";
    
	/**
	 * (non-PHPdoc)
	 * @see Zend_Controller_Action::init()
	 */
	public function init() 
	{
		parent::init();
		$this->mainModel = new Admin_Model_Articles();
	}
	
	/**
	 * Index
	 */
	public function indexAction()
	{
		return $this->_redirect($this->view->url(array(), 'default_content_blog'));
	}
	
	/**
	 * Manage articles 
	 */
	public function articlesAction()
	{
		
	    parent::validateRequest();
	    
	    $this->view->assign( 'messages', $this->_flashMessenger->getMessages() );
		
		$page = (int)$this->input->getEscaped( 'page' );
		$amt = 25;
		$paginator = $this->mainModel->getList();
		$paginator->setCurrentPageNumber( $page );
		$paginator->setItemCountPerPage( $amt );
		$paginator->setPageRange(1);
		$this->view->assign( 'list', $paginator->getCurrentItems() );
		$this->view->assign( 'pagenav', $paginator );
		$this->view->headTitle( sprintf( self::HEAD_TITLE_PATTERN,  $_SERVER['HTTP_HOST'], $this->user->display_name, "Работа со статьями"));
		$this->view->assign( 'actions_menu', new Zend_Navigation( 
			new Zend_Config_Xml( APPLICATION_PATH . '/configs/nav/admin/articles-menu.xml', 'nav' )));
		
		$sorterScript = (APPLICATION_ENV=='development')? 'js/j/jquery.tablesorter.js' : 'js/j/jquery.tablesorter.min.js' ;
		$this->view->headScript()->appendFile( $this->view->baseUrl( $sorterScript ) );
		
	}
	
	
	/**
	 * Edit article
	 * 
	 * @throws Zend_Exception
	 */
	public function editAction()
	{
		
	    parent::validateRequest();
		
	    $this->view->assign('new', false);
		if (!$this->input->getEscaped('idx')){
			$this->view->assign('new', true);
		}
		$authorsModel = new Xmltv_Model_Authors();
		
		switch($this->input->getEscaped('do')){
		    /*
		     * #######################################
		     * Edit/new
		     * #######################################
		     */
			default:
			case'edit':
			    
			    if (APPLICATION_ENV=='development'){
			    	var_dump($this->_getAllParams());
			    	//die(__FILE__.': '.__LINE__);
			    }
			    
			    $this->view->headTitle( sprintf( self::HEAD_TITLE_PATTERN,  $_SERVER['HTTP_HOST'], $this->user->display_name, "Работа со статьями"));
			    $this->view->headScript()
			    	->appendFile( 'http://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.0/jquery.validate.js' )
			    	->appendFile( 'http://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.0/localization/messages_ru.js' );
				// Collect categories
				$this->view->assign( 'categories', $this->mainModel->allCategories(true) );
				// Collect authors
				$this->view->assign( 'authors', $authorsModel->allAuthors(true) );
				if (null !== ($idx = $this->input->getEscaped( 'idx' ))) {
				    $article = $this->mainModel->getArticle($idx[0]);
				    if (APPLICATION_ENV=='development'){
				    	//var_dump($article);
				    	//die(__FILE__.': '.__LINE__);
				    }
					$this->view->assign( 'article', $article );
				}
				$this->view->assign( 'hide_breadcrumb', true);
				$messages = array();
				foreach ($this->_flashMessenger->getMessages() as $message){
				    foreach ($message as $k=>$msg){
				    	$messages[$k]=$msg;
				    }
				}
				$this->view->assign( 'messages', $messages);
				$this->view->assign( 'article_menu', new Zend_Navigation( 
					new Zend_Config_Xml( APPLICATION_PATH . '/configs/nav/admin/edit-article-menu.xml', 'nav' )));
				
				$this->view->assign('ac_tags', $this->mainModel->getContentTags());
				
				return $this->render( 'edit-article' );
				
			break;
			
			/*
			 * #######################################
			 * Delete from DB
			 * #######################################
			 */
			case 'delete':
			    //die(__FILE__.': '.__LINE__);
			    if (is_array($this->input->getEscaped('idx'))){
					foreach ($this->input->getEscaped('idx') as $id){
						$this->mainModel->deleteArticle( (int)$id );
					}
				} else {
				    die(__FILE__.': '.__LINE__);
					$this->mainModel->deleteArticle( (int)$id );
				}
				$this->_flashMessenger->addMessage( array('message'=>"Статья удалена") );
				$this->redirect($this->view->baseUrl('admin/content/articles'));
			break;
			
			/**
			 * #######################################
			 * Toggle published/unpublished
			 * #######################################
			 */
			case 'toggle':
			    if (!is_array($this->_getParam('idx'))){
			        return $this->_redirect( $this->view->url('admin/error/error'), array('exit'=>true) );
			    }
			    foreach ($this->_getParam('idx') as $id){
			        $this->mainModel->toggleArticleState((int)$id);
			    }
			    $this->_flashMessenger->addMessage( array('message'=>"Статьи изменены") );
			    $this->redirect($this->view->baseUrl('admin/content/articles'));
			break;

			/*
			 * #######################################
			 * Add/update
			 * #######################################
			 */
			case 'save':
				
				$form = new Xmltv_Form_EditArticle( null, array( 
					'article'=>$this->_getAllParams(),
					'categories'=>$this->mainModel->allCategories(true),
					'authors'=>$authorsModel->allAuthors(true),
					'user'=>$this->user,
				));
				
				if (false === $this->validateForm($form, $this->_getAllParams())){
					foreach ($this->FormErrors as $e) {
					    $this->_flashMessenger->addMessage(array('error'=>$e));
					}
					$idx = $this->_getParam('idx');
					$url = $this->view->baseUrl('admin/content/edit').'?do=edit&idx[]='.$idx[0];
					return $this->_redirect( $url, array( 'exit'=>true, 'params'=>$this->_getAllParams() ) );
				}
				
				$article = array();
				$idx = $this->_getParam('idx');
				$this->getRequest()->setParam('id', $idx[0]);
				$incomeTypes = array('is_cpa', 'is_ref', 'is_paid');
				$submitted = $this->_getAllParams();
				$table = new Admin_Model_DbTable_Articles();
				foreach ($table->info( Zend_Db_Table_Abstract::METADATA ) as $col){
				    foreach ($this->_getAllParams() as $param=>$value){
				        if ($param==$col['COLUMN_NAME']){
				            switch ($col['DATA_TYPE']){
				            	case 'int':
				            	    $article[$param] = (int)$value;
				            	break;
				            	case 'date':
				            	    $d = new Zend_Date($value, 'dd.MM.YYYY');
				            	    $article[$param] = $d->toString('YYYY-MM-dd');
				            	break;
				            	default:
				            		$article[$param] = $value;
				            }
				        }
				    }
				}
				
				$article['alias']  = Admin_Model_Programs::makeAlias( $article['title'] );
				foreach ($incomeTypes as $type){
					$article[$type]=0;
					if ($type==$this->input->getEscaped('income')){
						$article[$type]=1;
					}
				}
				
				if(APPLICATION_ENV=='development'){
				    //var_dump($this->_getAllParams());
				    //var_dump($article);
				    //die(__FILE__.': '.__LINE__);
				}

				
				try {
					$this->mainModel->saveArticle( $article );
				} catch (Zend_Db_Adapter_Exception $e) {
					if (APPLICATION_ENV!='production'){
						throw new Zend_Exception( $e->getMessage(), $e->getCode() );
					} else {
					    $this->_flashMessenger->addMessage( array('error'=>Rtvg_Message::ERR_CANNOT_SAVE_ROW));
						$this->_flashMessenger->addMessage( array('error'=>Rtvg_Message::ERR_WRONG_PARAM));
						return;
					}
				}
				
				
				$this->_flashMessenger->addMessage( array(
					'message' => 'Статья <b>'.$article['title'].'</b> сохранена'
				));
				return $this->redirect( $this->view->baseUrl('admin/content/articles' ), array('exit'=>true));
				
				
			break;
		}
		
	}
	
	
}