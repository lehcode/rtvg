<?php
/**
 * Process various types of search and display results
 *
 * @author  Antony Repin
 * @uses    Xmltv_Controller_Action
 * @version $Id: SearchController.php,v 1.5 2013-03-05 06:53:19 developer Exp $
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
    	
        parent::init();
        
    	$ajaxContext = $this->_helper->getHelper( 'AjaxContext' );
    	$ajaxContext->addActionContext( 'typeahead', 'json' )
    		->initContext();
    
    	$this->validator = $this->_helper->getHelper( 'RequestValidator');
    	parent::_initCache();
    	$this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
    	$this->initView();
    	
    	$this->cache->setLocation($this->cacheRoot);
    }
    
    
    /**
     * Index page
     * Redirect to frontpage
     */
    public function searchAction () {
        
        try {
            parent::requestParamsValid();
        } catch (Zend_Filter_Exception $e) {
            throw new Zend_Exception($e->getMessage(), $e->getCode, $e);
        }
        
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