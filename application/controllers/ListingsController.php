<?php
/**
 * Programs listings display
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @version $Id: ListingsController.php,v 1.39 2013-04-06 22:35:03 developer Exp $
 *
 */
class ListingsController extends Rtvg_Controller_Action
{
    
    /**
     * @var Xmltv_Model_Articles
     */
    private $articlesModel;
    
    /**
     * (non-PHPdoc)
     * @see Zend_Controller_Action::init()
     */
    public function init()
    {
        parent::init();
        
        $ajaxContext = $this->_helper->getHelper( 'AjaxContext' );
        $ajaxContext->addActionContext( 'update-comments', 'html' )
            ->initContext();
        
        if ($this->getRequest()->getMethod()=='POST'){
            $this->_helper->layout()->setLayout('access-denied');
            return;
        }
        
        if (!$this->_request->isXmlHttpRequest()){
            $this->view->assign( 'pageclass', parent::pageclass(__CLASS__) );
        }
        
        $this->articlesModel = new Xmltv_Model_Articles();
    }

    /**
     * Index page
     */
    public function indexAction ()
    {
        $this->_forward( 'day-date' );
    }

    /**
     * Forward request to dayListingAction()
     */
    public function dayDateAction(){
        
        parent::validateRequest();
        
        if (($date = $this->_getParam('date', null))===null){
            $date = $this->bcModel->listingDate();
        }
        
        $this->_forward('day-listing', null, null, array(
            'date'=>$date,
            'channel'=>$this->_getParam('channel')
        ));
        
    }
    
    /**
     * Programs listing for 1 particular day
     * @throws Zend_Exception
     */
    public function dayListingAction () {
        
        parent::validateRequest();
        
        $pageClass = parent::pageclass(__CLASS__);
        $this->view->assign( 'pageclass', $pageClass );
        $this->view->assign( 'hide_sidebar', 'right' );
        $this->view->assign( 'vk_group_init', false );
        $this->view->headLink()
            ->prependStylesheet( 'http://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css', 'screen');
        
        $channel = $this->channelInfo($this->_getParam('channel'));
        
        if (!isset($channel['id']) || empty($channel['id'])){
            throw new Zend_Exception("Channel not found.");
        }
        $this->view->assign('channel', $channel );
        
        //Данные для модуля категорий каналов
        $cats = $this->getChannelsCategories();
        $this->view->assign('channels_cats', $cats);
        
        //Текущая дата
        $listingDate = $this->bcModel->listingDate($this->input);
        $this->view->assign('listing_date', $listingDate);
        
        //Assign today's date to view 
        if ($listingDate->isToday()) {
            $this->view->assign('is_today', true);
        } else {
            $this->view->assign('is_today', false);
            $this->view->assign('pageclass', $pageClass.' other-day');
        }
        
        //Detect timeshift and adjust listing time
        $timeShift = (int)$this->input->getEscaped( 'tz', 'msk' );
        $listingDate = $timeShift!='msk' ? $listingDate->addHour( $timeShift ) : $listingDate ;
        
        $this->view->assign('timeshift', $timeShift);
        $this->view->assign('listing_date', $listingDate);
        
        //Данные для модуля самых популярных программ
        $top = $this->bcModel->topBroadcasts();
        $this->view->assign('bc_top', $top);
        
        //Fetch programs list for day and make decision on current program
        $amt = 4;
        if ($this->cache->enabled) {
            $this->cache->setLifetime(3600);
            $f = "/Listings/Programs";
            $hash = Rtvg_Cache::getHash( $channel['alias'].'_'.$listingDate->toString('DDD') );
            if (!$list = $this->cache->load( $hash, 'Core', $f)) {
                if ($listingDate->isToday()){
                    $list = $this->bcModel->getBroadcastsForDay( $listingDate, $channel['id'], $amt );
                } else {
                    $list = $this->bcModel->getBroadcastsForDay( $listingDate, $channel['id'] );
                }
                $this->cache->save( $list, $hash, 'Core', $f);
            }
            
        } else {
             if ($listingDate->isToday()){
                $list = $this->bcModel->getBroadcastsForDay( $listingDate, $channel['id'], $amt );
            } else {
                $list = $this->bcModel->getBroadcastsForDay( $listingDate, $channel['id'] );
            }
        }
        
        
        if (!empty($list)){
            
            $list[0]['now_showing']=true;
            $this->view->assign( 'programs', $list );
            $currentBc = $list[0];

            //Articles
            $amt = 10;
            if ($this->cache->enabled) {
                $this->cache->setLifetime(86400);
                $f = "/Content/Articles";
                $hash = 'dayListingArticles_'.Rtvg_Cache::getHash( $channel['id'] );
                if (!$articles = $this->cache->load( $hash, 'Core', $f)) {
                    $articles = $this->articlesModel->dayListingArticles( $currentBc, $amt );
                    $this->cache->save( $articles, $hash, 'Core', $f);
                }
            } else {
                $articles = $this->articlesModel->dayListingArticles( $currentBc, $amt );
            }

            $this->view->assign( 'announces', $articles );
            $this->view->assign( 'show_announces', true );

            //Update start and end times of each program in listing
            if ($this->_getParam('tz', null)!==null) {
                if ($timeShift!=0){
                    foreach ($list as $item) {
                        $item['start'] = $item['start']->addHour($timeShift);
                        $item['end']   = $item['end']->addHour($timeShift);
                        $this->view->headMeta()->setName('robots', 'noindex,follow');
                    }
                }
            }
            
            if ($listingDate->isToday()) {
                
                $listingVideos = array();
            
                // Запрос в файловый кэш
                if ($this->cache->enabled){

                    $t = (int)Zend_Registry::get( 'site_config' )->cache->youtube->listings->get( 'lifetime' );
                    $t>0 ? $this->cache->setLifetime($t): $this->cache->setLifetime(86400) ;
                    $f = '/Listings/Videos';
                    $hash = Rtvg_Cache::getHash( 'listingVideo_'.$channel['title'].'-'.$listingDate->toString( 'YYYY-MM-dd' ));

                    if (parent::$videoCache) {

                        // Ищем видео в кэше БД если он включен
                        if (false === ($listingVideos = $this->vCacheModel->listingRelatedVideos( array_slice($list, 0, 3), $channel['title'], $listingDate ))){

                            if (false === ($listingVideos=$this->cache->load( $hash, 'Core', $f) )){

                                // Если не найдено ни в одном из кэшей, то делаем запрос к Yoututbe
                                $listingVideos = $this->videosModel->ytListingRelatedVideos( array_slice($list, 0, 3), $channel['title'], $listingDate );

                                if (!count($listingVideos) || ($listingVideos===false)) {
                                    return false;
                                }

                                // Сохраняем в кэш БД если он включен
                                if (parent::$videoCache===true){
                                    foreach ($listingVideos as $k=>$vid) {
                                        $listingVideos[$k]['hash'] = $k;
                                        $this->vCacheModel->saveListingVideo( $listingVideos[$k] );
                                    }
                                }
                                // Сохранение в файловый кэш
                                $this->cache->save( $listingVideos, $hash, 'Core', $f);
                            }     
                        }

                    } else {

                        if ((false === ($listingVideos=$this->cache->load( $hash, 'Core', $f)))){
                            // Если не найдено ни в одном из кэшей, то делаем запрос к Yoututbe
                            $listingVideos = $this->videosModel->ytListingRelatedVideos( array_slice($list, 0, 3), $channel['title'], $listingDate );

                            if (!count($listingVideos) || ($listingVideos===false)) {
                                return false;
                            }

                            $this->cache->save($listingVideos, $hash, 'Core', $f);

                        }
                    }               
                } else {
                    // Кэширование не используется 
                    // запрос к Yoututbe
                    $listingVideos = $this->videosModel->ytListingRelatedVideos( array_slice($list, 0, 3), $channel['title'], $listingDate );
                }
                $this->view->assign('listing_videos', $listingVideos);
                
            }
            
        }
        
        
        
        
        /*
        if ($listingDate->isToday() && (int)Zend_Registry::get('site_config')->channels->comments->enabled===1){
            //Комменты
            if ($this->cache->enabled){
                $t = (int)Zend_Registry::get( 'site_config' )->cache->system->lifetime;
                $t>0 ? $this->cache->setLifetime($t): $this->cache->setLifetime(86400);
                $f = '/Listings/Comments';
                $hash = Rtvg_Cache::getHash('comments-channel-'.(int)$channel['id']);
                $comments = $this->cache->load($hash, 'Core', $f);
                if (empty($comments)){
                    $comments = $this->commentsModel->channelComments( $channel['id'] );
                    $this->cache->save($comments, $hash);
                }
            } else {
                $comments = $this->commentsModel->channelComments( $channel['id'] );
            }
            
            try{
                $feed = $this->commentsModel->getYandexRss( 'канал '.$channel['title'] );
            } catch (Exception $e){
                if(get_class($e)=='Zend_Loader_PluginLoader_Exception'){
                    //skip
                }
            }
            
            $newComments = $this->commentsModel->parseYandexFeed($feed);
            if (count($newComments)){
                $this->commentsModel->saveChannelComments($newComments, $channel['id']);
                $comments = array_merge($comments, $newComments);
            }
            
            $this->view->assign('comments', $comments);
        }
         * 
         */
        
        //Данные для модуля видео в правой колонке
        $vids = $this->channelSidebarVideos($channel);
        $this->view->assign('sidebar_videos', $vids );
        
        //Tinyurl data
        $tinyUrl = $this->getTinyUrl(array('channel'=>$channel['alias']), 
            'default_listings_day-listing',
            array(
                $this->_getParam('module'),
                $this->_getParam('controller'),
                $this->_getParam('action'),
                (int)$channel['id'],
            )
        );
        $this->view->assign('short_link', $tinyUrl);
        
        //Add hit for channel and model
        $this->channelsModel->addHit( (int)$channel['id'] );
        $this->view->assign('featured', $this->getFeaturedChannels());
        
        //Unhide both sidebars
        $this->view->assign('hide_sidebar', 'none');
        
        // Ad codes
        $ads = $this->_helper->getHelper('AdCodes');
        $adCodes = $ads->direct(1, 'random', 300, 250);
        $this->view->assign('ads', $adCodes);
        
        
    }
    
    /**
     * Выпуски выбранной пользователем передачи сегодня и 
     * список похожих по названию передач сегодня на других каналах
     * 
     * @throws Zend_Exception
     */
    public function broadcastDayAction () {
        
        parent::validateRequest();
            
        $this->view->assign( 'pageclass', 'broadcast-day' );
        $bcAlias = $this->input->getEscaped('alias');
        
        if ( $this->input->getEscaped( 'date' )=='неделя' ) {
            return $this->_forward( 'broadcast-week', 'listings', 'default', array( 
                'date'=>Zend_Date::now()->toString('dd-MM-yyyy')));
        }
        
        $bcAlias = $this->input->getEscaped( 'alias' );
        
        //Channel properties
        $channel = $this->channelInfo( $this->input->getEscaped( 'channel' ) );
        $this->view->assign( 'channel', $channel );
        
        //Decision on listing timespan (date|сегодня) 
        $dg = $this->input->getEscaped('date');
        $listingDate = Zend_Date::now();
        if ($dg!='сегодня' && !empty($dg)) {
            if (preg_match('/^[\d]{4}-[\d]{2}-[\d]{2}$/', $dg)) {
                $listingDate = new Zend_Date($this->input->getEscaped('date'), 'YYYY-MM-dd');
            } else {
                $listingDate = Zend_Date::now();
            }
        }
        $this->view->assign( 'date', $listingDate );
        
        $this->view->assign('notfound', false);
        $this->view->assign('nosimilar', false);
        
        if ($this->cache->enabled && APPLICATION_ENV!='development'){
            
            $this->cache->setLifetime( 86400 );
            $f = '/Listings/Program/Day';
            
            $hash = $this->cache->getHash( 'broadcast-day-single_'.$bcAlias.'_'.$channel['id'] );
            if (false === ($broadcasts = $this->cache->load( $hash, 'Core', $f ))) {
                $broadcasts = $this->bcModel->getBroadcastThisDay( $bcAlias, $channel['id'], $listingDate, 1 );
                $this->cache->save( $broadcasts, $hash, 'Core', $f );
            }
        } else {
            $broadcasts = $this->bcModel->getBroadcastThisDay( $bcAlias, $channel['id'], $listingDate, 1 );
        }
        
        if ($broadcasts===false){
            return $this->render('no-repeat-today');
        }
        
        $current = $broadcasts[0];
        $this->view->assign('current', $current);
        $this->view->assign('broadcasts', $broadcasts);
        
        $ads = $this->_helper->getHelper('AdCodes');
        $adCodes = $ads->direct(1, 'random', 300, 250);
        $this->view->assign('ads', $adCodes);
        
        //Список программ
        if (count($broadcasts)>1){
            
            //Данные для модуля категорий каналов
            $cats = parent::getChannelsCategories();
            $this->view->assign( 'channels_cats', $cats );
            
            //Короткая ссылка на страницу
            $tinyUrl = $this->getTinyUrl(array('channel'=>$channel['alias']), 'default_listings_day-listing', array(
                $this->_getParam('module'),
                $this->_getParam('controller'),
                $this->_getParam('action'),
                $channel['id'],
            ));
            $this->view->assign('short_link', $tinyUrl);
            
        } else {
            
            if ($this->cache->enabled && APPLICATION_ENV!='development'){
                 
                $this->cache->setLifetime(3600*6);
                $f = '/Listings/Similar/Day';
            
                $hash = $this->cache->getHash('similarPrograms_'.$bcAlias);
                if (($similarPrograms = $this->cache->load( $hash, 'Core', $f))===false) {
                    $similarPrograms = $this->bcModel->getSimilarProgramsForDay (
                            $listingDate,
                            $this->input->getEscaped('alias'),
                            $current['channel_id']);
                    $this->cache->save($similarPrograms, $hash, 'Core', $f);
                }
                 
            } else {
                
                $similarPrograms = $this->bcModel->getSimilarProgramsForDay (
                    $listingDate,
                    $current['alias'],
                    $current['channel_id']
                );
            }
             
            $this->view->assign( 'similar', array());
            
            
        }
        
        //Add hit to broadcast
        $this->bcModel->addHit( $current['hash'] );
        
    }
    
    
    /**
     * 
     * @throws Zend_Exception
     */
    public function broadcastWeekAction(){
        
        if (parent::validateRequest()) {
            
            $this->view->assign( 'pageclass', 'broadcast-week' );
            $bcAlias = $this->input->getEscaped('alias');
            
            $channel = parent::channelInfo( $this->input->getEscaped('channel') );
            if (!isset($channel['id'])){
                $this->view->assign('hide_sidebar', 'right');
                return $this->render('channel-not-found');
            }
            $this->view->assign( 'channel', $channel );
            
            $dg = $this->input->getEscaped('date');
            $listingDate = Zend_Date::now();
            if ($dg!='сегодня' && $dg!='неделя') {
                if (preg_match('/^[\d]{2}-[\d]{2}-[\d]{4}$/', $dg)) {
                    $listingDate = new Zend_Date($this->input->getEscaped('date'), 'dd-MM-yyyy');
                }
            }
            
            if (!$this->checkDate($listingDate)){
                $this->view->assign('hide_sidebar', 'right');
                return $this->_forward('outdated');
            }
            
            $weekDays = $this->_helper->getHelper('weekDays');
            $weekStart = $weekDays->getStart( $listingDate );
            $this->view->assign('week_start', $weekStart);
            $weekEnd = $weekDays->getEnd( $listingDate );
            $this->view->assign('week_end', $weekEnd);
            
            //Данные для модуля самых популярных программ
            $this->view->assign('bc_top', parent::topBroadcasts());
            
            //Данные для модуля категорий каналов
            $cats = $this->getChannelsCategories();
            $this->view->assign('channels_cats', $cats);
            
            //Список передач
            if ($this->cache->enabled){
                $this->cache->setLifetime(21600);
                $f = '/Listings/Program/Week';
                $hash = md5('currentProgram_'.$bcAlias.'_'.$channel['id']);
                if (!$list = $this->cache->load( $hash, 'Core', $f )) {
                    $list = $this->bcModel->broadcastThisWeek( $bcAlias, $channel['id'], $weekStart, $weekEnd );
                    $this->cache->save($list, $hash, 'Core', $f );
                }
            } else {
                $list = $this->bcModel->broadcastThisWeek( $bcAlias, $channel['id'], $weekStart, $weekEnd);
            }
            
            $this->view->assign( 'list', $list );
            
            if ($this->cache->enabled){
                $this->cache->setLifetime(86400);
                $f = '/Listings/Similar/Week';
                $hash = $this->cache->getHash( $bcAlias.'_'.$channel['id'] );
                if (($similarBcs = $this->cache->load( $hash, 'Core', $f))===false) {
                    $similarBcs = $this->bcModel->similarBroadcastsThisWeek( $bcAlias, $weekStart, $weekEnd, $channel['id'] );
                    $this->cache->save($similarBcs, $hash, 'Core', $f);
                }
            } else {
                $similarBcs = $this->bcModel->similarBroadcastsThisWeek( $bcAlias, $weekStart, $weekEnd, $channel['id'] );
            }
            $this->view->assign( 'similar', $similarBcs );
            
            $ads = $this->_helper->getHelper('AdCodes');
            $adCodes = $ads->direct(1, 'random', 300, 250);
            $this->view->assign('ads', $adCodes);
            
            if(empty($list[0]) && !empty($similarBcs)){
                return $this->render('similar-week');
            }
            
            if (empty($list[0]) && empty($similarBcs)) {
                $this->view->assign('hide_sidebar', 'right');
                return $this->render('broadcast-not-found');
            }
            
            $this->bcModel->addHit( $list[0]['hash'] );
            return $this->render('broadcast-week');
            
        }
        
    }
    
    /**
     * Search for channel
     */
    public function searchAction(){
    
        if ( parent::validateRequest()){
            $model   = new Xmltv_Model_Channels();
            $search  = $this->_getParam('fs');
            $channel = $model->getByTitle($search);
            $redirectUrl = $this->view->url(array(1=>$this->view->escape($channel['alias'])), 'default_listings_day-listing');
            $this->_redirect( $redirectUrl, array('exit'=>true));
        } else {
            $this->_redirect( $this->view->url(array(), 'default_error_missing-page'), array('exit'=>true));
        }
    
    }
    
    /**
     * Категория программ за неделю
     */
    public function categoryAction(){
    
        $cats = $this->getProgramsCategories();
        
        if ( parent::validateRequest( array('programsCategories'=>$cats))){
            
            $category = $this->bcModel->getCategoryByAlias( $this->input->getEscaped('category') );
            $this->view->assign('category', $category);
            $categoryId = $category['id'];
            $now = Zend_Date::now();
            
            //Данные для модуля самых популярных программ
            $top = $this->bcModel->topBroadcasts();
            $this->view->assign('bc_top', $top);
            
            //Данные для модуля категорий каналов
            $cats = $this->getChannelsCategories();
            $this->view->assign('channels_cats', $cats);
            
            $ads = $this->_helper->getHelper('AdCodes');
            $adCodes = $ads->direct(1, 'random', 300, 250);
            $this->view->assign('ads', $adCodes);
            
            switch ($this->input->getEscaped('timespan')){
                    
                case 'неделя':
                    
                    $weekStart = $this->weekDays->getStart( $now);
                    $weekEnd   = $this->weekDays->getEnd( $now);
                    
                    if ($this->cache->enabled){
                        $hash = "categoryWeek_$categoryId";
                        $this->cache->setLifetime( 86400);
                        $f = "/Listings/Category/Week";
                        if (($list = $this->cache->load( $hash, 'Core', $f))===false){
                            $list = $this->bcModel->categoryWeek( $categoryId, $weekStart, $weekEnd);
                            $this->cache->save( $list, $hash, 'Core', $f);
                        }
                    } else {
                        $list = $this->bcModel->categoryWeek( $categoryId, $weekStart, $weekEnd);
                    }
                    
                    
                    $this->view->assign( 'weekStart', $weekStart);
                    $this->view->assign( 'weekEnd', $weekEnd);
                    $this->view->assign( 'list', $list);
                    $this->view->assign( 'pageclass', 'category-week');
                    return $this->render( 'category-week');
    
                case 'сегодня':
                    
                    if ($this->cache->enabled){
                        $hash = "categoryDay_".$categoryId."_".$now->toString("ddd");
                        $this->cache->setLifetime( 7200);
                        $f = "/Listings/Category/Day";
                        if (($list = $this->cache->load( $hash, 'Core', $f))===false){
                            $list = $this->bcModel->categoryDay( $categoryId, $now);
                            $this->cache->save( $list, $hash, 'Core', $f);
                        }
                    } else {
                        $list = $this->bcModel->categoryDay( $categoryId, $now);
                    }
                    
                    $this->view->assign('list', $list);
                    $this->view->assign('pageclass', 'category-day');
                    $this->view->assign('today', $now);
                    return $this->render('category-day');
                    
            }
    
        } else {
            $this->_redirect( $this->view->url(array(), 'default_error_missing-page'), array('exit'=>true));
        }
    
    }
    
    /**
     * Outdated listing
     */
    public function outdatedAction(){
        
    }
    
    
    
    
    /**
     * 
     */
    public function premieresWeekAction(){
        
        $this->view->assign('hide_sidebar', 'right');
        
    }
    
    /**
     * All series in the week
     */
    public function seriesWeekAction(){
    
        $data['date'] = new Zend_Date(null, null, 'ru');
        $weekStart = $this->_helper->WeekDays( array( 'method'=>'getStart', 'data'=>$data));
        $data['date'] = new Zend_Date(null, null, 'ru');
        $weekEnd = $this->_helper->WeekDays( array( 'method'=>'getEnd', 'data'=>$data));
        $seriesList = $this->bcModel->getCategoryForPeriod( $weekStart, $weekEnd, $this->categoriesMap['series'] );
        
    }
    
    
    
}

