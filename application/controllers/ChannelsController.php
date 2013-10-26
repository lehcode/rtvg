<?php
/**
 * Frontend Channels controller
 * 
 * @author  Antony Repin
 *
 */
class ChannelsController extends Rtvg_Controller_Action
{
    
    /**
     * Cache root for this controller
     * @var string
     */
    protected $cacheRoot = '/Channels';
    
    /**
     * (non-PHPdoc)
     * @see Zend_Controller_Action::init()
     */
    public function init () {
        
        parent::init();
        
        $ajaxContext = $this->_helper->getHelper( 'AjaxContext' );
        $ajaxContext
            ->addActionContext( 'typeahead', 'json' )
            ->addActionContext( 'alias', 'json' )
            ->addActionContext( 'new-comments', 'html' )
            ->initContext();
           
           if (false === (bool)$this->_request->isXmlHttpRequest()){
               $this->view->assign( 'pageclass', parent::pageclass(__CLASS__) );
               $this->view->assign( 'hide_sidebar', null );
           }
           
    }
    

    /**
     * Index page. Redirect to channels list
     */
    public function indexAction () {
        $this->redirect( $this->view->url(array(
            'module'=>'default', 
            'controller'=>'channels',
            'action'=>'list'
        )));
    }

    /**
     * All channels list
     */
    public function listAction () {
        
        parent::validateRequest();
        
        $this->view->headLink()
            ->prependStylesheet( 'http://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css', 'screen');

        $this->channelsModel = new Xmltv_Model_Channels();
        $this->view->assign( 'hide_sidebar', false );
        $this->view->assign( 'gcse', false );

        if ($this->cache->enabled){

            $this->cache->setLifetime(86400);
            $f = '/Channels';

            $hash = Rtvg_Cache::getHash('published_channels');
            if (!$rows = $this->cache->load($hash, 'Core', $f)) {
                $rows = $this->channelsModel->getPublished();
                $this->cache->save($rows, $hash, 'Core', $f);
            }
        } else {
            $rows = $this->channelsModel->getPublished();
        }

        (APPLICATION_ENV=='development') ? Zend_Registry::get('fireLog')->log($rows, Zend_Log::INFO) : null ;

        $this->view->assign('channels', $rows);

        /*
         * ######################################################
         * Channels categories
         * ######################################################
        */
        $this->view->assign('channels_cats', $this->getChannelsCategories());

        /*
         * #####################################################################
         * Данные для модуля самых популярных программ
         * #####################################################################
         */
        $top = $this->bcModel->topBroadcasts();
        (APPLICATION_ENV=='development') ? Zend_Registry::get('fireLog')->log($top, Zend_Log::INFO) : null ;
        $this->view->assign('top_programs', $top);
        
        
    }
    
    /**
     * Channels for typeahead script
     */
    public function typeaheadAction () {
        
        if (!$this->_request->isXmlHttpRequest()){
            throw new Zend_Exception( Rtvg_Message::ERR_INVALID_INPUT, 401 );
        }
        
        parent::validateRequest();

        $hash = Rtvg_Cache::getHash( 'typeahead_all' );
        if ($this->cache->enabled) {
            $this->cache->setLifetime(86400*7);
            $f = "/Channels";
            if (($items = $this->cache->load( $hash, 'Core', $f))===false){
                $items = $this->channelsModel->allChannels( 'title ASC' );
                $this->cache->save($items, $hash, 'Core', $f);
            }
        } else {
            $items = $this->channelsModel->allChannels( 'title ASC' );
        }
        
        $result = array();
        foreach ($items as $k=>$row){
            $result[$k]['title'] = $row['title'];
            $result[$k]['alias'] = $row['alias'];
        }
        
        $this->view->assign( 'result', $result );
        
    }
    
    /**
     * Channels from particular category
     */
    public function categoryAction() {
        
        if (parent::validateRequest()) {
           
            $this->view->assign('pageclass', 'category');
            $this->view->headLink()
                ->prependStylesheet( 'http://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css', 'screen');
            
            $this->channelsModel = new Xmltv_Model_Channels();
            $catProps = $this->channelsModel->category( $this->input->getEscaped('category') );
            if ($catProps===false){
                $this->getResponse()->setHttpResponseCode(404);
                $this->_helper->layout()->setLayoutPath(APPLICATION_PATH.'/layouts/scripts/');
                $this->_helper->layout()->setLayout('not-found');
                return;
            }
            
            if (isset($_GET['RTVG_PROFILE'])){
                //Zend_Debug::dump($this->cache->enabled);
                //die(__FILE__.': '.__LINE__);
            }
            
            if ($this->cache->enabled){
                
                $this->cache->setLifetime(86400);
                $f = "/Channels/Category";
                
                $hash = md5('category_'.$catProps['alias']);
                if (!$rows = $this->cache->load($hash, 'Core', $f)){
                    $rows = $this->channelsModel->categoryChannels($catProps['alias']);
                    
                    if (isset($_GET['RTVG_PROFILE'])){
                        //Zend_Debug::dump($rows);
                        //die(__FILE__.': '.__LINE__);
                    }
                    
                    foreach ($rows as $k=>$row) {
                        $rows[$k]['icon'] = $this->view->baseUrl('images/channel_logo/'.$row['icon']);
                    }
                    $this->cache->save($rows, $hash, 'Core', $f);
                }
                
            } else {
                $rows = $this->channelsModel->categoryChannels($catProps['alias']);
                foreach ($rows as $k=>$row) {
                    $rows[$k]['icon'] = $this->view->baseUrl('images/channel_logo/'.$row['icon']);
                }
            }
            
            $this->view->assign('channels', $rows);
            
            /*
             * #####################################################################
             * Данные для модуля самых популярных программ
             * #####################################################################
             */
            $this->view->assign('top_programs', $this->topPrograms());
            
            /*
             * ######################################################
             * Channels categories
             * ######################################################
            */
            if ($this->cache->enabled){
                
                $this->cache->setLifetime(86400);
                $f = "/Channels";
                
                $hash  = $this->cache->getHash("channelscategories");
                if (!$cats = $this->cache->load($hash, 'Core', $f)) {
                    $cats = $this->channelsModel->channelsCategories();
                    $this->cache->save($cats, $hash, 'Core', $f);
                }
            } else {
                $cats = $this->channelsModel->channelsCategories();
            }
            
            $this->view->assign('channels_cats', $cats);
            
            
            $this->render('list');
            
        }
        
    }
    
    /**
     * Week listing for channel
     * @throws Exception
     */
    public function channelWeekAction(){
        
        // Validation routines
        if (parent::validateRequest()) {
            
            $this->view->assign('hide_sidebar', 'left');
            //$this->view->assign('sidebar_videos', true);
            $this->view->assign('pageclass', 'channel-week');
            
            // Channel properties
            $this->channelsModel = new Xmltv_Model_Channels();
            $channel = $this->channelsModel->getByAlias( $this->input->getEscaped('channel') );
            $this->view->assign('channel', $channel);
            
            //Week start and end dates
            $ws = $this->_helper->getHelper('weekDays')
                ->getStart();
            $this->view->assign('week_start', $ws);
            $we = $this->_helper->getHelper('weekDays')
                ->getEnd();
            $this->view->assign('week_end', $we);
            
            if ($this->cache->enabled){
                $hash = Rtvg_Cache::getHash('channel_'.$channel['alias'].'_week');
                $f = '/Channels/Week';
                if (!$schedule = $this->cache->load($hash, 'Core', $f)) {
                    $schedule = $this->channelsModel->getWeekSchedule($channel, new Zend_Date($ws), new Zend_Date($we));
                    $this->cache->save($schedule, $hash, 'Core', $f);
                }
            } else {
                $schedule = $this->channelsModel->getWeekSchedule($channel, new Zend_Date($ws), new Zend_Date($we));
            }
            $this->view->assign('days', $schedule);
            
            $this->channelsModel->addHit( $channel['id'] );
            
        }
        
    }
    
    /**
     * Update comments for channel
     */
    public function newCommentsAction(){
         
        $this->_helper->layout->disableLayout();
        
        if (!$this->_request->isXmlHttpRequest()){
            throw new Zend_Exception( Rtvg_Message::ERR_INVALID_INPUT, 401 );
        }
        
        parent::validateRequest();
        
        // Channel properties
        $this->channelsModel = new Xmltv_Model_Channels();
        $channelAlias = $this->input->getEscaped('channel');
        
        if ($this->cache->enabled){
            
            $this->cache->setLifetime(86400*7);
            $f  = '/Channels';
            
            $hash = $this->cache->getHash('channel_'.$channelAlias);
            if (($channel = $this->cache->load($hash, 'Core', $f))===false) {
                $channel = $this->channelsModel->getByAlias( $channelAlias );
                $this->cache->save($channel, $hash, 'Core', $f);
            }
        } else {
            $channel = $this->channelsModel->getByAlias($channelAlias);
        }
        $this->view->assign('channel', $channel);
        
        //Attach comments model
        $commentsModel = new Xmltv_Model_Comments();
        if ($this->cache->enabled){
            $this->cache->setLifetime(86400);
            $f  = '/Feeds/Yandex';
            
            $hash = $this->cache->getHash( 'comments_'.$channelAlias);
            if (($new = $this->cache->load( $hash, 'Core', $f))===false) {
                $feedData = $commentsModel->getYandexRss( array( 'телеканал "'.Xmltv_String::strtolower($channel['title']).'"') );
                if (false !== ($new = $commentsModel->parseYandexFeed( $feedData ))){
                    $commentsModel->saveChannelComments( $new, $channel['id']);
                    $this->cache->save( $new, $hash, 'Core', $f);
                }
            }
        } else {
            $feedData = $commentsModel->getYandexRss( array( 'телеканал "'.Xmltv_String::strtolower($channel['title']).'"') );
            $new      = $commentsModel->parseYandexFeed( $feedData );
        }
        
        $this->view->assign('items', $new);
        
    }
    
    /**
     * AJAX
     * Get channel alias from $_GET['title']
     */
    public function aliasAction(){
        
        if (!$this->_request->isXmlHttpRequest()){
            throw new Zend_Exception( Rtvg_Message::ERR_INVALID_INPUT, 401 );
        }
        
        parent::validateRequest();
        
        $title = $this->input->getEscaped('t');
        $this->view->assign( 'alias', $this->view->escape( $this->channelsModel->getByTitle( $title )->alias ) );
        
    }
    
}

