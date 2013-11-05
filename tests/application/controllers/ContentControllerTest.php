<?php
/**
 * PHPUnit test controller
 * @version $Id:$
 */
class ContentControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    
    private $_articlesModel;
    
    public function setUp()
	{
        $this->bootstrap = array($this, 'appBootstrap');
        parent::setUp();
        $this->_articlesModel = new Xmltv_Model_Articles();
	}
    
    public function appBootstrap()
    {
        $this->_application = new Zend_Application(APPLICATION_ENV,
              APPLICATION_PATH . '/configs/application.ini'
        );
        $this->_application->bootstrap();

        $front = $this->getFrontController();
        if($front->getParam('bootstrap') === null) {
            $front->setParam('bootstrap', $this->_application->getBootstrap());
        }
        
        $router = new Xmltv_Plugin_Router();
        $router->setRouter($front->getRouter());
		$front->setRouter($router->getRouter());
        
    }
    
    /**
     * @group contentControllerActions
     */
    public function testArticleAction(){
        
        $cat = $this->_articlesModel->randomCategory();
        $catArticles = $this->_articlesModel->categoryItems( $cat['id'] );
        if (count($catArticles)){
            $idx = array_rand($catArticles, 1);
            $article = $catArticles[$idx];

            $urlParams = $this->urlizeOptions( array(
                'module'=>'default',
                'controller'=>'content',
                'action'=>'article',
                'category'=>$cat['alias'],
                'article_alias'=>$article['alias'],
            ));
            $url = $this->url( $urlParams, 'default_content_article' );
            $this->dispatch($url);

            $this->assertResponseCode(200);
            $this->assertModule( $urlParams['module'] );
            $this->assertController( $urlParams['controller'] );
            $this->assertAction( $urlParams['action'] );
        } else {
            $this->markTestIncomplete();
        }
        
        
         
    }
    
    /**
     * @group contentControllerActions
     */
    public function testIndexAction(){
        
        $urlParams = $this->urlizeOptions( array(
            'module'=>'default',
            'controller'=>'content',
            'action'=>'index', ));
        
        $url = $this->url( $urlParams, 'default_content_index' );
		$this->dispatch($url);
        $this->assertResponseCode(200);
        $this->assertModule( $urlParams['module'] );
		$this->assertController( $urlParams['controller'] );
		$this->assertAction( 'blog' );
        
    }
}
