<?php
/**
 * Frontend Sitemap controller test
 *
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: SitemapControllerTest.php,v 1.1 2013-01-19 10:11:14 developer Exp $
 *
 */
class SitemapControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    
    public function setUp()
	{
        $this->bootstrap = array($this, 'appBootstrap');
        parent::setUp();
	}
    
    public function appBootstrap()
    {
        $this->_application = new Zend_Application(APPLICATION_ENV,
              APPLICATION_PATH . '/configs/application.ini'
        );
        $this->_application->bootstrap();

        /**
         * Fix for ZF-8193
         * http://framework.zend.com/issues/browse/ZF-8193
         * Zend_Controller_Action->getInvokeArg('bootstrap') doesn't work
         * under the unit testing environment.
         */
        $front = Zend_Controller_Front::getInstance();
        if($front->getParam('bootstrap') === null) {
            $front->setParam('bootstrap', $this->_application->getBootstrap());
        }
    }
    
    public function testIndexAction(){
        $urlParams = $this->urlizeOptions( array(
            'module'=>'default',
            'controller'=>'sitemap',
            'action'=>'index', ));
		$url = $this->url( $urlParams );
		$this->dispatch($url);
        
        $this->assertModule( $urlParams['module'] );
		$this->assertController( $urlParams['controller'] );
		$this->assertAction( $urlParams['action'] );
    }
    
    public function testSitemapAction(){
    	
        $urlParams = $this->urlizeOptions( array(
            'module'=>'default',
            'controller'=>'sitemap',
            'action'=>'sitemap', ));
		$url = $this->url( $urlParams );
		$this->dispatch($url);
        
        $this->assertModule( $urlParams['module'] );
		$this->assertController( $urlParams['controller'] );
		$this->assertAction( $urlParams['action'] );
        
    }
}