<?php
/**
 * Frontend search controller
 *
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: SearchController.php,v 1.1 2013-01-12 09:06:22 developer Exp $
 *
 */
class SearchController extends Xmltv_Controller_Action
{
    
    /**
     * Root folder for cache
     * @var string
     */
    protected $cacheRoot='/Search';
    
    /**
     * (non-PHPdoc)
     * @see Zend_Controller_Action::init()
     */
    public function init () {
    	
    	$ajaxContext = $this->_helper->getHelper( 'AjaxContext' );
    	$ajaxContext->addActionContext( 'typeahead', 'json' )
    		->initContext();
    
    	$this->validator = $this->_helper->getHelper( 'RequestValidator');
    	$this->_initCache();
    	$this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
    	$this->initView();
    }
    
    /**
     * Initialize caching
     */
    private function _initCache(){
    	$this->cache = new Xmltv_Cache();
    	$this->cache->lifetime = (int)Zend_Registry::get('site_config')->cache->system->get('lifetime');
    	$this->cache->setLocation($this->cacheRoot);
    }
    
    
    /**
     * Index page
     * Redirect to frontpage
     */
    public function searchAction () {
        
        if ( parent::requestParamsValid() ){
            
            $search = $this->input->getEscaped('searchinput');
            $type = $this->input->getEscaped('type');
            $script = 'search/'.$this->input->getEscaped('type').'.phtml';
            
            if ($this->input->getEscaped('type')=='channel'){
                $channelsModel = new Xmltv_Model_Channels();
                $result = $channelsModel->searchChannel( $search);
                $this->view->assign('result', $result);
                $this->renderScript( 'search/channel.phtml' );
            }
        }
        
    }
    
}