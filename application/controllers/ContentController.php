<?php
/**
 * 
 * Frontend content display and management
 * 
 * @author Antony Repin <egeshisolutions@gmail.com>
 * @version $Id: ContentController.php,v 1.4 2013-04-03 04:08:15 developer Exp $
 *
 */
class ContentController extends Rtvg_Controller_Action
{
    
    /**
     * @var Xmltv_Model_Articles
     */
    private $articlesModel;
    
    /**
     * @var unknown_type
     */
    private $submenu;
    
    /**
     * (non-PHPdoc)
     * @see Rtvg_Controller_Action::init()
     */
    public function init(){
        
    	parent::init();
    	
    	$this->articlesModel = new Xmltv_Model_Articles();
    	$this->view->assign( 'pageclass', parent::pageclass(__CLASS__));
    	
    	$this->buildSubmenu();
    	
    	if (!$this->_request->isXmlHttpRequest()){
    		$this->view->assign( 'pageclass', parent::pageclass(__CLASS__) );
    	}
    }
    
    /*
     * Функционал
     * 
     * Все статьи ContentController::blogAction(){}
     * 
     * 1. Назначение pageclass (articles)
     * 2. Загружаем статьи
     */
    public function blogAction(){
    	
        parent::validateRequest();
        
        
    }
    
        /*
     * Отдельная статья ContentController::articleAction(){}
     * 
     * 1. Назначение pageclass (article)
     * 2. Ищем видео на Youtube по категории и названию статьи
     * 
     */
    public function articleAction(){
    	
        parent::validateRequest();
        
        $articleAlias = $this->input->getEscaped('article_alias');
        $content_categoryAlias = $this->input->getEscaped('content_cat');
        if (!$articleAlias || is_numeric($articleAlias) || !is_string($articleAlias)){
            throw new Exception( Rtvg_Message::ERR_WRONG_PARAM );
        }
        // Fetch articles
        $articles = $this->articlesModel->singleItem( $articleAlias );
        $article  = $articles[0];
        $this->view->assign('article', $article);
        $this->view->assign('pageclass', 'blog-item');
        // Article tags
        $tags = array();
        $trim = new Zend_Filter_StringTrim();
        foreach ( explode(',', $article['tags']) as $tag ) {
        	$tags[] = $trim->filter( $tag );
        }
        $this->view->assign('tags', $tags);
        
        // META keywords
        $kw = array();
        foreach ($tags as $tag){
        	$kw[] = $this->view->escape( Xmltv_String::strtolower( trim( $tag )));
        }
        $this->view->headMeta()->setName( 'keywords', implode(',',$kw) );
        // META descrption
        $this->view->headMeta()->setName( 'description', $article['metadesc'] );
        // Назначение pageclass
        $this->view->assign('hide_sidebar', 'left');
        // Название страницы
        $this->view->headTitle()->append( $article['title'] );
        // OpenGraph
        $this->view->doctype( 'XHTML1_RDFA' );
        $this->view->assign('ogp_ns', 'http://ogp.me/ns/article');
        $this->view->headMeta()->setName( 'og:type', 'article' );
        $this->view->headMeta()->setName( 'article:title', $article['title'] );
        $this->view->headMeta()->setName( 'article:published_time', $article['publish_up']->toString('YYYY-MM-dd'));
        $this->view->headMeta()->setName('article:section', $article['content_cat_title']);
        $this->view->headMeta()->setName('article:tag', Xmltv_String::strtolower( $article['tags']));
        
        // Fetch related articles
        $this->view->assign('related_articles', $this->articlesModel->relatedItems( $article, 'article' ));
        
    }
    
    /**
     * Статьи на тему тега
     */
    public function tagAction(){
    	
        die(__FILE__.': '.__LINE__);
        
    }
    
    /*
     * Категория статей ContentController::categoryAction(){}
     *
     * 1. Назначение pageclass (articles)
     * 2. Загружаем статьи из категории
     * 3. Ищем видео на Youtube по категории статей
     */
    public function blogCategoryAction(){
    	
        die(__FILE__.': '.__LINE__);
        
    }
    
    private function buildSubmenu(){
    	
        $categories = $this->articlesModel->getCategories();
        
        $pages = array();
        foreach ($categories as $c){
            $pages[] = new Zend_Navigation_Page_Mvc(array(
            	'label'=>$c['title'],
            	'module'=>'default',
            	'controller'=>'content',
            	'action'=>'category',
            	'resource'=>'default:content.blog-category',
            	'params'=>array(
            		'category'=>$c['alias']
            	)
            ));
        }
        
        $this->view->assign( 'submenu', $pages );
        
    }
}