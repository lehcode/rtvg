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
        
        $this->_helper->getHelper( 'AjaxContext' )
            ->addActionContext( 'typeahead', 'json' )
            ->addActionContext( 'alias', 'json' )
            //->addActionContext( 'new-comments', 'html' )
            ->initContext();
           
           if (false === (bool)$this->_request->isXmlHttpRequest()){
               $this->view->assign( 'pageclass', parent::pageclass(__CLASS__) );
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
        
        $this->channelsModel = new Xmltv_Model_Channels();
        
        if ($this->cache->enabled){
            (APPLICATION_ENV!='production') ? $this->cache->setLifetime(100) : $this->cache->setLifetime(86400);
            $f = '/Channels';
            (APPLICATION_ENV=='testing') ? $notEmpty = true : $notEmpty = false ;
            $hash = Rtvg_Cache::getHash('published_channels');
            if (!$rows = $this->cache->load($hash, 'Core', $f)) {
                $rows = $this->channelsModel->getPublished($notEmpty);
                $this->cache->save($rows, $hash, 'Core', $f);
            }
        } else {
            $rows = $this->channelsModel->getPublished($notEmpty);
        }
        $this->view->assign('channels', $rows);

        //Channels categories
        $cats = $this->channelsCategories();
        $this->view->assign('channelsCategories', $cats);

        //Данные для модуля самых популярных программ
        $top = $this->bcModel->topBroadcasts();
        $this->view->assign('bcTop', $top);
        
        $ads = $this->_helper->getHelper('AdCodes');
        $adCodes = $ads->direct(2, 350, 240);
        $this->view->assign('ads', $adCodes);
        
        
    }
    
    /**
     * Channels for typeahead script
     */
    public function typeaheadAction () {
        
        if (!$this->_request->isXmlHttpRequest() && APPLICATION_ENV=='production'){
            throw new Zend_Exception( Rtvg_Message::ERR_INVALID_INPUT, 404 );
        }
        
        parent::validateRequest();
        
        $this->_helper->layout->disableLayout();

        $hash = Rtvg_Cache::getHash( 'typeahead_all' );
        if ($this->cache->enabled && APPLICATION_ENV!='development') {
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
        
        echo Zend_Json::encode($result);
        
        
        
    }
    
    /**
     * Channels from particular category
     */
    public function categoryAction() {
        
        parent::validateRequest();
        
        $this->view->assign('pageclass', 'channelsCategory');
        
        $this->channelsModel = new Xmltv_Model_Channels();
        $catProps = $this->channelsModel->category( $this->input->getEscaped('category') );
        if ($catProps===false){
            $this->getResponse()->setHttpResponseCode(404);
            $this->_helper->layout()->setLayoutPath(APPLICATION_PATH.'/layouts/scripts/');
            $this->_helper->layout()->setLayout('not-found');
            return;
        }
        $this->view->assign('category', $catProps);
        
        $ads = $this->_helper->getHelper('AdCodes');
        $adCodes = $ads->direct(2, 300, 240);
        $this->view->assign('ads', $adCodes);
        
        if ($this->cache->enabled){
            (APPLICATION_ENV != 'production') ? $this->cache->setLifetime(100) : $this->cache->setLifetime(604800);
            $f = "/Channels/Category";
            $hash = $this->cache->getHash('category_'.$catProps['alias']);
            if (!$rows = $this->cache->load($hash, 'Core', $f)){
                $rows = $this->channelsModel->categoryChannels($catProps['alias']);
                $this->cache->save($rows, $hash, 'Core', $f);
            }

        } else {
            $rows = $this->channelsModel->categoryChannels($catProps['alias']);
        }
        
        $this->view->assign('channels', $rows);

        //Данные для модуля самых популярных программ
        $this->view->assign('bcTop', $this->bcModel->topBroadcasts());
        
        //Channels categories
        $cats = $this->channelsCategories();
        $this->view->assign('channelsCategories', $cats);
        
        $this->render('list');
        
    }
    
    /**
     * Week listing for channel
     * @throws Exception
     */
    public function channelWeekAction(){
        
        // Validation routines
        parent::validateRequest();
        
        $this->view->assign('pageclass', 'channelWeek');

        // Channel properties
        $channel = $this->channelsModel->getByAlias( $this->input->getEscaped('channel') );
        $this->view->assign('channel', $channel);

        //Week start and end dates
        $ws = $this->_helper->getHelper('weekDays')->getStart();
        $this->view->assign('week_start', $ws);
        $we = $this->_helper->getHelper('weekDays')->getEnd();
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

        //Ad codes for right sidebar
        $ads = $this->_helper->getHelper('AdCodes');
        $adCodes = $ads->direct(2, 300, 240);
        $this->view->assign('ads', $adCodes);
        
        //Channels categories
        $cats = $this->channelsCategories();
        $this->view->assign('channelsCategories', $cats);
        
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
     * AJAX action
     * Get channel alias from $_GET['title']
     */
    public function aliasAction(){
        
        if (!$this->_request->isXmlHttpRequest()){
            throw new Zend_Exception( Rtvg_Message::ERR_INVALID_INPUT, 401 );
        }
        
        parent::validateRequest();
        
        $title = $this->input->getEscaped('t');
        $this->view->assign( 'alias', $this->channelsModel->getByTitle( $title )->alias );
        
    }
    
    /**
     * Channel live casting page
     */
    public function liveAction(){
        
        parent::validateRequest();
        
        $this->view->assign('pageclass', 'live');
        
        $channel = $this->channelsModel->getByAlias( $this->input->getEscaped('channel') );
        $this->view->assign('channel', $channel);
        
        //Ad codes
        $ads = $this->_helper->getHelper('AdCodes');
        $adCodes = $ads->direct(2, 300, 240);
        $this->view->assign('ads', $adCodes);
        
        //Channels top
        $chTop = $this->channelsModel->topChannels(10);
        $this->view->assign('channelsTop', $chTop );
        
        //TinyUrl
        $tinyUrl = $this->getTinyUrl(array('channel'=>$channel['alias']), 
            'default_channels_live',
            array(
                $this->_getParam('module'),
                $this->_getParam('controller'),
                $this->_getParam('action'),
                (int)$channel['id'],
            )
        );
        $this->view->assign('tinyUrl', $tinyUrl);
        
        //Add hit for channel
        $this->channelsModel->addHit( (int)$channel['id'] );
        $this->view->assign('featured', $this->getFeaturedChannels());
        
        //Channel news
        if ($channel['rss_url']){
            try{
                $news = $this->channelsModel->channelFeed($channel, 10);
            } catch (Exception $e){
                //skip
            }
            $this->view->assign( 'channelNews', $news);
        }
        
        //Данные для модуля самых популярных программ
        $top = $this->bcModel->topBroadcasts(20);
        $this->view->assign('bcTop', $top);
        
        //Текущая дата
        $date = $this->bcModel->listingDate( $this->input );
        $this->view->assign('listing_date', $date);
        
        //Channel videos
        $vids = $this->channelRelatedVideos($channel, 10);
        $this->view->assign('sidebarVideos', $vids);
        
    }
    
}

