<?php
/**
 * Process various types of search and display results
 *
 * @author  Antony Repin
 * @uses    Xmltv_Controller_Action
 * @version $Id: SearchController.php,v 1.9 2013-04-03 04:08:15 developer Exp $
 *
 */
class SearchController extends Rtvg_Controller_Action
{
    
    /**
     * Root folder for cache
     * @var string
     */
    protected $cacheRoot='/Search';
    
    /**
     * Model
     * @var Xmltv_Model_Search
     */
    private $_searchModel;
    
    /**
     * (non-PHPdoc)
     * @see Zend_Controller_Action::init()
     */
    public function init () {
    	
        parent::init();
        $this->_searchModel = new Xmltv_Model_Search();
        
    }
    
    
    /**
     * Index page
     * Redirect to frontpage
     */
    public function searchAction () {
        
        if (parent::validateRequest()) {
            
            $search = $this->input->getEscaped('searchinput');
            $type = $this->input->getEscaped('type');
            $type = (!isset($type) || $type=='web') ? 'web' : 'channel' ;
            $script = "search/$type.phtml";
            
            //if ($this->input->getEscaped('type')=='channel'){
                
            	$channelsModel = new Xmltv_Model_Channels();
            	$result = $channelsModel->searchChannel( $search);
            	$this->view->assign('result', $result);
            	$this->renderScript( 'search/channel.phtml' );
            /* 	
            } else {
                
                $f = '/Search/Torrents';
                die("Not implemented");
                $hash = Rtvg_Cache::getHash( $this->input->getEscaped('searchinput') );
                
                if ($this->cache->enabled){
                	if (($html = $this->cache->load($hash, 'Core', $f))===false) {
                		
                		$this->cache->save($html, $hash, 'Core', $f);
                	}
                } else {
                	$html = $curl->fetch(Xmltv_Parser_Curl::PAGE_HTML);
                }
            }
             */
        }
        
        
        
        
    }
    
}