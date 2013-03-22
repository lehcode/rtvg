<?php
/**
 * Backend content controller
 * 
 * @author     Antony Repin <egeshisolutions@gmail.com>
 * @subpackage backend
 * @version    $Id: ContentController.php,v 1.3 2013-03-22 17:51:44 developer Exp $
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
	    $this->mainModel = new Admin_Model_Articles();
	}
	
	public function articlesAction()
	{
	    
	    $this->view->assign('messages', $this->_flashMessenger->getMessages());
	    
		$page = (int)$this->input->getEscaped( 'page' );
		$amt = 25;
		$paginator = $this->mainModel->getList();
		$paginator->setCurrentPageNumber( $page );
		$paginator->setItemCountPerPage( $amt );
		$paginator->setPageRange(1);
		$this->view->assign( 'list', $paginator->getCurrentItems() );
		$this->view->assign( 'pagenav', $paginator );
		$this->view->assign( 'actions_menu', new Zend_Navigation( 
			new Zend_Config_Xml( APPLICATION_PATH . '/configs/nav/admin/articles-menu.xml', 'nav' )));
	
		
	}
	/*
	private function _editArticle() 
	{
		if (!$this->input->getEscaped('idx')){
			$this->view->assign('new', true);
		} else {
		    $this->view->assign('new', false);
		}
	}
	*/
	
	public function editAction()
	{
		
		//var_dump($this->_getAllParams());
		$this->view->assign('new', false);
	    if (!$this->input->getEscaped('idx')){
	    	$this->view->assign('new', true);
	    }
	    $authorsModel = new Xmltv_Model_Authors();
	    
		switch($this->input->getEscaped('do')){
			default:
			case'edit':
			    
				// Collect categories
				$this->view->assign( 'categories', $this->mainModel->allCategories(true) );
				$this->view->assign( 'authors', $authorsModel->allAuthors(true) );
				if (null !== ($idx = $this->input->getEscaped( 'idx' ))) {
					$this->view->assign( 'article', $this->mainModel->getArticle($idx[0]) );
				}
				$this->view->assign( 'hide_breadcrumb', true);
				$this->view->assign( 'article_menu', new Zend_Navigation( 
					new Zend_Config_Xml( APPLICATION_PATH . '/configs/nav/admin/edit-article-menu.xml', 'nav' )));
				return $this->render( 'edit-article' );
			break;
			
			case 'delete':
			    if (is_array($this->input->getEscaped('idx'))){
			        foreach ($this->input->getEscaped('idx') as $id){
			            $this->mainModel->deleteArticle( (int)$id );
			        }
			    } else {
			        $this->mainModel->deleteArticle( (int)$id );
			    }
			    $this->_flashMessenger->addMessage( "Статья удалена" );
			    return $this->redirect($this->view->baseUrl('admin/content/articles'));
			break;
			
			case 'toggle':
				die(__FILE__.': '.__LINE__);
			break;

			case 'save':
			    
			    $form = new Xmltv_Form_EditArticle( null, array( 
					'article'=>$this->_getAllParams(),
					'categories'=>$this->mainModel->allCategories(true),
					'authors'=>$authorsModel->allAuthors(true),
					'user'=>$this->user,
				));
				if (!$form->isValid($this->_getAllParams())){
				    $this->getResponse()->setHttpResponseCode(404);
				    return;
				}
			    
				$newArticleProps = array();
			    $incomeTypes = array('is_cpa', 'is_ref', 'is_paid');
			    $submitted = $this->_getAllParams();
			    
			    //var_dump($submitted);
			    //die(__FILE__.': '.__LINE__);
			    
			    $newArticleProps['title'] = $this->input->getEscaped('title');
			    $newArticleProps['alias'] = $this->input->getEscaped('alias');
			    $newArticleProps['published'] = (int)$this->input->getEscaped('published');
			    foreach ($incomeTypes as $type){
			        $newArticleProps[$type]=0;
			        if ($submitted['income']==$type){
			        	$newArticleProps[$type]=1;
			        }
			    }
			    $newArticleProps['id'] = (int)$this->input->getEscaped('idx');
			    $newArticleProps['content_cat'] = (int)$this->input->getEscaped('contcat');
			    $newArticleProps['channel_cat'] = (int)$this->input->getEscaped('chcat');
			    $newArticleProps['prog_cat'] = (int)$this->input->getEscaped('progcat');
			    $newArticleProps['tags'] = $this->input->getEscaped('tags');
			    $newArticleProps['intro'] = $this->input->getEscaped('intro');
			    $newArticleProps['body']  = $this->input->getEscaped('body');
			    $newArticleProps['metadesc']  = (string)$this->input->getEscaped('metadesc');
			    $newArticleProps['metakeys']  = (string)$this->input->getEscaped('metakeys');
			    $newArticleProps['author'] = (int)$this->input->getEscaped('author');
			    $d = new Zend_Date($this->input->getEscaped('added'), 'dd.MM.YYYY');
			    $newArticleProps['added'] = $d->toString('YYYY-MM-dd');
			    $d = new Zend_Date($this->input->getEscaped('publish_up'), 'dd.MM.YYYY');
			    $newArticleProps['publish_up'] = $d->toString('YYYY-MM-dd');
			    $d = new Zend_Date($this->input->getEscaped('publish_down'), 'dd.MM.YYYY');
			    $newArticleProps['publish_down'] = $d->toString('YYYY-MM-dd');
			    
			    //var_dump($this->getAllParams());
			    //var_dump($newArticleProps);
			    //die(__FILE__.': '.__LINE__);
			    
			    try {
			        $this->mainModel->saveArticle($newArticleProps);
			    } catch (Zend_Db_Adapter_Exception $e) {
			        if (APPLICATION_ENV!='production'){
			        	throw new Zend_Exception($e->getMessage(), $e->getCode());
			        } else {
			            $this->_flashMessenger->addMessage( Rtvg_Message::ERR_WRONG_PARAM, 501 );
			        }
			    }
			    
			    $this->_flashMessenger->addMessage( 'Статья <b>'.$newArticleProps['title'].'</b> сохранена' );
			    return $this->redirect($this->view->baseUrl('admin/content/articles'));
			    
			    
			break;
		}
		
	}
	
	
}