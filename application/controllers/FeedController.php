<?php
/**
 * Feeds Controller
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @version $Id: FeedController.php,v 1.1 2013-04-05 23:42:04 developer Exp $
 *
 */
class FeedController extends Rtvg_Controller_Action
{
    /**
     * @var Xmltv_Model_Channels
     */
    protected $channelsModel;
    
    /**
     * (non-PHPdoc)
     * @see Rtvg_Controller_Action::init()
     */
    public function init()
    {
        parent::init();
        $ajaxContext = $this->_helper->getHelper('contextSwitch');
        $ajaxContext->addActionContext('sitemap', 'xml')->initContext();
        $this->getResponse()->setHeader('Content-type', 'text/xml');
        $this->_helper->layout->disableLayout();
    }
    
    /**
     * Redirect to RSS
     */
    public function indexAction()
    {
        parent::validateRequest();
        
        $feedsList = array( 'channels', 'articles', 'listings');
        
        die(__FILE__.': '.__LINE__);
        //$this->_redirect( $this->view->baseUrl( 'feed/atom/'.$this->_getParam('c') ));
    }
    
    
    /**
     * RSS
     */
    public function rssAction()
    {
    	
        parent::validateRequest();
        
        $feed = new Zend_Feed_Writer_Feed;
        //$feed->setTitle('');
        die(__FILE__.': '.__LINE__);
        
    }
    
    /**
     * Atom
     */
    public function atomAction()
    {
    	
        parent::validateRequest();
        
        //var_dump($this->_getAllParams());
        //var_dump($this->channelsModel->getById( $this->input->getEscaped('channel') ));
        //die(__FILE__.': '.__LINE__);
        
        if (!$this->input->getEscaped('channel')){
            $this->render('no-channel');
        }
        
        $channel = $this->channelsModel->getById( $this->input->getEscaped('channel') );
        $feed = new Zend_Feed_Writer_Feed;
        $timespan = $this->_getParam('timespan', 'сегодня');
        if ($timespan=='неделя'){
            $feed->setTitle('Канал на неделю');
            $feed->setLink( $this->view->url( array(
            	'channel'  => $channel['alias'],
            	'timespan' => $timespan ), 'default_channels_channel-week' ) );
        } else {
            $feed->setTitle('Канал сегодня');
        	$feed->setLink( $this->view->url( array(
            	'channel'  => $channel['alias'],
            	'timespan' => $timespan ), 'default_listings_day-listing' ) );
        }
        
        
        var_dump($feed);
        die(__FILE__.': '.__LINE__);
        
    }
}