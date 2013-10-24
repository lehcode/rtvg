<?php
/**
 * Manage listings grabbing
 * 
 * @author     Antony Repin <egeshisolutions@gmail.com>
 * @package backend
 * @version    $Id: GrabController.php,v 1.9 2013-04-12 06:58:27 developer Exp $
 *
 */
class Admin_GrabController extends Rtvg_Controller_Admin
{
	/**
	 * @var Admin_Model_Channels
	 */
    private $channelsModel;

    /**
	 * @var Admin_Model_Broadcasts
	 */
    private $bcModel;
    
    /**
     * (non-PHPdoc)
     * @see Rtvg_Controller_Admin::init()
     */
	public function init()
    {
        parent::init();
        
        $ajaxContext = $this->_helper->getHelper( 'AjaxContext' );
        $ajaxContext->addActionContext( 'do', 'html' )
        	->initContext();
        
        $this->channelsModel = new Admin_Model_Channels();
        $this->bcModel = new Admin_Model_Broadcasts();
        
        
    }


    /**
     * Index
     */
	public function indexAction () 
	{
		$this->_redirect( $this->view->baseUrl( 'admin/grab/listings' ) );
	}
	
	/**
	 * Grab listings index page
	 */
	public function listingsAction()
	{
		//$weekEnd   = $this->_helper->getHelper('weekDays')->getEnd();	
		//$weekStart = $weekEnd->addDay(1);
		//$this->view->assign( 'start', $weekStart );
	}
	
	public function doAction()
	{
	    
	    //var_dump($this->_getAllParams());
	    //die(__FILE__.': '.__LINE__);
	    
	    $this->_helper->getHelper('Layout')->disableLayout();
	    $startDate = new Zend_Date($this->_getParam('weekstart'), 'dd.MM.YYYY');
	    $form = new Xmltv_Form_Admin_SitesToGrab();
	    
	    if( parent::validateForm( $form, $this->_getAllParams() ) ){
	        parent::validateRequest();
	        $this->_forward('grab-tv-yandex', null, null);
	    }
	}
	
	public function grabTvYandexAction(){
		
	    parent::validateRequest();
	    
	    set_time_limit(-1);
	    $startDate  = new Zend_Date($this->_getParam('weekstart'), 'dd.MM.YYYY');
	    
	    $yaDayStart = (int)ceil($startDate->toString('U')/(3600*24));
	    $yaDayCount = 0;
	    $channels = $this->channelsModel->allChannels();
	    $activeChannels = array();
	    
	    do {
	        
	        $yaDay = $yaDayStart+$yaDayCount;
	        $date  = new Zend_Date( $yaDay*3600*24, 'U' );
	        if (APPLICATION_ENV=='development'){
	            echo ('$date: '.$date->toString('YYYY-MM-dd')."<br />");
	            echo ('$yaDayCount: '.$yaDayCount."<br />");
	        	echo ('$yaDay: '.$yaDay."<br />");
	        }
			$collections = array(
		    	'centr_ru'=>"http://m.tv.yandex.ru/213/?day=$yaDay&when=2&channel=146%2C711%2C649%2C162%2C187%2C515%2C353%2C304%2C18%2C79%2C427%2C405%2C511%2C698%2C291%2C740%2C323%2C557%2C898%2C150",
		    	'centr_ua'=>"http://m.tv.yandex.ru/187/?day=$yaDay&when=2&channel=677%2C620%2C326%2C670%2C709%2C140%2C128%2C773%2C583%2C479%2C453%2C281%2C586%2C940%2C632%2C167%2C689%2C607%2C939%2C937",
		    	//'region_ru'=>"http://m.tv.yandex.ru/213?day=$yaDay&when=2&channel=421%2C655%2C335%2C161%2C334",
		    	//'region_ua'=>"http://m.tv.yandex.ru/187/?day=$yaDay&when=2&channel=788%2C627%2C650%2C634%2C641%2C561%2C90",
		    	'topics'=>"http://m.tv.yandex.ru/213/?day=$yaDay&when=2&channel=916%2C917%2C918%2C919%2C921%2C922%2C924%2C925%2C926%2C927%2C928%2C929%2C932%2C933%2C934%2C935%2C710%2C579%2C658%2C365%2C516%2C463%2C601%2C495%2C325%2C409%2C437%2C60%2C23%2C850%2C288%2C661%2C429%2C575%2C608%2C102%2C567%2C55%2C127%2C267%2C309%2C589%2C213%2C521%2C277%2C346%2C454%2C669%2C66%2C747%2C834%2C273%2C123%2C798%2C462%2C22%2C71%2C542%2C618%2C675%2C518%2C12%2C485%2C783%2C617%2C566%2C638%2C743%2C53%2C406%2C663%2C447%2C181%2C173%2C163%2C794%2C716%2C180%2C779%2C686%2C61%2C16%2C502%2C410%2C659%2C615%2C810%2C520%2C352%2C19%2C494%2C598%2C646%2C51%2C138%2C741%2C15%2C801%2C145%2C82%2C765%2C223%2C328%2C31%2C644%2C37%2C434%2C384%2C648%2C313%2C119%2C125%2C789%2C547%2C156%2C455%2C333%2C604%2C376%2C769%2C705%2C21%2C626%2C637%2C477%2C275%2C776%2C555%2C308%2C332%2C849%2C388%2C897%2C425%2C774%2C258%2C389%2C680%2C723%2C154%2C367%2C505%2C595%2C6%2C737%2C481%2C726%2C423%2C113%2C713%2C111%2C662%2C201%2C681%2C322%2C377%2C499%2C134%2C664%2C183%2C697%2C358%2C563%2C311%2C217%2C24%2C799%2C821%2C614%2C153%2C415%2C250%2C8%2C401%2C306%2C214%2C851%2C923%2C920%2C931%2C930%2C994%2C911%2C912%2C983%2C984%2C990%2C989%2C986%2C987%2C988%2C756%2C828%2C355%2C312%2C715%2C777%2C284%2C278%2C797%2C319%2C831%2C757%2C393%2C461%2C631%2C59%2C315%2C442%2C804%2C533%2C25%2C642%2C141%2C552%2C247%2C132%2C39%2C591%2C331%2C731%2C491%2C91%2C554%2C531%2C473%2C412%2C430%2C431%2C11%2C121%2C807%2C363%2C685%2C458%2C509%2C464%2C151%2C730%2C560%2C178%2C35%2C382%2C576%2C349%2C270%2C237%2C852%2C165%2C257%2C249",
		    );
		    
		    $parser = new Xmltv_Parser_Listings_Tvyandexru();
		    $proxyHost = null;
		    $proxyPort = null;
		    if(true === (bool)Zend_Registry::get('site_config')->proxy->get('active')){
		        $proxyHost = Zend_Registry::get('site_config')->proxy->get('host');
		    	$proxyPort = (int)Zend_Registry::get('site_config')->proxy->get('port');
		    }
		    $curlRef = null;
		    
		    foreach ($collections as $collectionCat=>$collectionUrl){
		        
		        if (APPLICATION_ENV=='development'){
		        	echo ('$collectionCat: '.$collectionCat."<br />");
		        }
		        
		        /*
		         * (1) Fetch collectin page HTML
		         */ 
		        $f = '/Grab';
			    if ($this->cache->enabled){
			        $hash = $collectionCat.'_'.$date->toString('YYMMdd');
			        if (false===($page = $this->cache->load($hash, 'Core', $f))){
			    	    $page = $parser->fetchCollectionPage( $collectionUrl, $curlRef, array(
			    	    	'host'=>$proxyHost,
			    	    	'port'=>$proxyPort) );
			    	    $this->cache->save($page, $hash, 'Core', $f);
			    	}
			    } else {
			        $page = $parser->fetchCollectionPage( $collectionUrl, $curlRef, array(
			    	    'host'=>$proxyHost,
			    	    'port'=>$proxyPort) );
			    }
			    
			    $html = $page->getDocument();
			    $dom  = new DOMDocument();
			    $dom->loadHTML( $html );
			    $dom->preserveWhiteSpace = false;
			    $parser->document = $dom;
			    
			    /*
			     * (2) Extract channel tables from collection page
			     */ 
			    $tables = $parser->document->getElementsByTagName('table');
			    $rows   = array();
			    $broadcasts = array();
			    
			    foreach ($tables as $table)
			    {
			    	if($table->getAttribute('class')=='b-schedule__list'){
			    	    
			    	    /*
			    	     * (3) Find channel title and map to our DB
			    	     */
			    		$headers = $table->getElementsByTagName('th');
			    		$parser->yaTitle( trim( $headers->item(1)->nodeValue ) );
			    		$mapped = $parser->mapChannel( $channels );
			    		
			    		if (false !== (bool)($channel = $this->channelsModel->searchChannel( $mapped, true ))){
			    			$activeChannels[] = $channel['id'];
			    		} else {
			    		    throw new Zend_Exception("Channel not found");
			    		}
			    		
			    		$broadcasts[$channel['id']] = array(
			    			'channel_title' => $channel['title'],
			    			'channel_alias' => $this->channelsModel->makeAlias( $channel['title'] ),
			    			'date'          => $date->toString('YYYY-MM-dd'),
			    			'list'          => null
			    		);
			    		
			    		/**
			    		 * (4) Get broadcast URLs from channel collection page
			    		 */
			    		$bsUrls = $parser->extractBroadcastUrls( $table );
			    		
			    		foreach ($bsUrls as $bcUrl){
			    		    
			    		    /**
			    		     * (5) Fetch single broabcast page
			    		     */
			    		    if ($this->cache->enabled){
			    		        
			    		        $dir = APPLICATION_PATH.'/../cache/Grab/'.$channel['id'];
			    		        if (!is_dir($dir)){
			    		        	if(!mkdir($dir, 0700)){
			    		        		throw new Zend_Exception( "Cannot create $dir" );
			    		        	}
			    		        }
			    		         
			    		        $f = '/Grab/'.$channel['id'];
			    		        $hash = md5($bcUrl);
			    		        if (false === (bool)($html = $this->cache->load($hash, 'Core', $f))){
			    		        	$html   = $parser->fetchBroadcastPage( $bcUrl, null, array(
			    		        		'host'=>$proxyHost,
			    		        		'port'=>$proxyPort,
			    		        	) );
			    		        	$this->cache->save($html, $hash, 'Core', $f);
			    		        }
			    		    } else {
			    		        $html = $parser->fetchBroadcastPage( $bcUrl );
			    		    }
			    		    
			    		    /**
			    		     * (6) Parse broadcast page
			    		     */
			    		    if (false !== (bool)($data = $parser->parseBroadcastPage( $html, $date ))){
			    		    	
			    		        if (!isset($data['title']) || empty($data['title'])){
			    		        	break;
			    		        }
			    		        if (!isset($data['start']) || empty($data['start'])) {
			    		            break;
			    		        }
			    		        
			    		        $titles = $this->bcModel->parseTitle( $data['title'] );
			    		        $data = array_merge($titles, $data);
			    		        $titles = $parser->fixTitles( $data );
			    		        $data = array_merge($titles, $data);
			    		        
			    		        //Zend_Debug::dump( $data );
			    		        //die(__FILE__.': '.__LINE__);
			    		        
			    		        $data['start']   = $data['start']->toString("YYYY-MM-dd HH:mm").':00';
        		        		$data['end']     = $data['end']->toString("YYYY-MM-dd HH:mm").':00';
			    		        $data['alias']   = $this->bcModel->makeAlias( $data['title'] );
			    		        $data['channel'] = $channel['id'];
			    		        
			    		        //Zend_Debug::dump( $data );
			    		        //die(__FILE__.': '.__LINE__);
			    		        
			    		        // Create broadcast object
			    		        $newBc  = $this->bcModel->newBroadcast( $data );
			    		        $search = $this->bcModel->getProgramHash( $newBc );
			    		        
			    		        // Check if object already in DB and
			    		        // skip to next broadcast if it is
			    		        if (false !== (bool)$this->bcModel->search( 'hash', $search )) {
			    		        	break;
			    		        }
			    		        $newBc->hash = $search;
			    		        
			    		        // Fix category if needed
			    		        if (!isset($newBc->category) || (false===(bool)$newBc->category)) {
			    		        	$fixCats = array(
			    		        			222=>'Религия',
			    		        			300006=>'Религия',
			    		        			300037=>'Музыка' );
			    		        	if (array_key_exists($newBc->channel, $fixCats)){
			    		        		$newBc->category = $this->bcModel->getProgramCategory( $fixCats[$newBc->channel]);
			    		        	} else {
			    		        		$newBc->category = null;
			    		        	}
			    		        }
			    		        
			    		        if (is_array($newBc->directors) && count($newBc->directors)) {
			    		            $persons = array();
			    		        	foreach ($newBc->directors as $p){
			    		        		if(!$id = $this->bcModel->personId($p, 'director')){
			    		        			$persons[]=(int)$id;
			    		        		}
			    		        	}
			    		        	$newBc->directors = $persons;
			    		        } else {
			    		            $newBc->directors = '';
			    		        }
			    		         
			    		         
			    		        if (is_array($newBc->actors) && count($newBc->actors)) {
			    		            $persons = array();
			    		        	foreach ($newBc->actors as $p){
			    		        		if(!$id = $this->bcModel->personId($p, 'actor')){
			    		        			$persons[]=(int)$id;
			    		        		}
			    		        	}
			    		        	$newBc->actors = implode(',', $persons);
			    		        }else {
			    		            $newBc->actors = '';
			    		        }
			    		        
			    		        $newBc->actors       = is_array( $newBc->actors) ? implode(',', $newBc->actors) : $newBc->actors ;
			    		        $newBc->directors    = is_array( $newBc->directors) ? implode(',', $newBc->directors) : $newBc->directors ;
			    		        $newBc->commentators = is_array( $newBc->commentators) ? implode(',', $newBc->commentators) : '' ;
			    		        $newBc->writers      = is_array( $newBc->writers ) ? implode( ',', $newBc->writers ) : '' ;
			    		        $newBc->operators    = is_array( $newBc->operators ) ? implode( ',', $newBc->operators ) : '' ;
			    		        $newBc->composers    = is_array( $newBc->composers ) ? implode( ',', $newBc->composers ) : '' ;
			    		        $newBc->editors      = is_array( $newBc->editors ) ? implode( ',', $newBc->editors ) : '' ;
			    		        $newBc->presenters   = is_array( $newBc->presenters ) ? implode( ',', $newBc->presenters ) : '' ;
			    		        $newBc->producers    = is_array( $newBc->producers) ? implode( ',', $newBc->producers) : '' ;
			    		        
			    		        if ((int)$newBc->channel==0){
			    		        	var_dump($newBc->toArray());
			    		        	die( "Cannot save row with channel '0'" );
			    		        }
			    		         
			    		        if (APPLICATION_ENV=='development'){
			    		        	//var_dump($newBc);
			    		        	//die(__FILE__.': '.__LINE__);
			    		        }
			    		        
			    		        $newBc->save();
				    		    $log = new Zend_Log( new Zend_Log_Writer_Stream( APPLICATION_PATH . '/../log/grab.log' ));
				    		    $log->log("Saved: ".$newBc->title.':'.$newBc->hash, Zend_Log::INFO);
				    		    
			    		         
			    		    }
			    		}
			    		
			    		if (APPLICATION_ENV=='development'){
			    			//die(__FILE__.': '.__LINE__);
			    		}
			    		
			    	}
			    }
			    if (APPLICATION_ENV=='development'){
			    	//die(__FILE__.': '.__LINE__);
			    }
		    }
		    
		    $yaDayCount+=1;
		    sleep(1);
		    
		} while( $yaDayCount < 7 );
		
		
		if (APPLICATION_ENV=='development'){
			var_dump($broadcasts);
			die(__FILE__.': '.__LINE__);
		}
		
		if (APPLICATION_ENV=='development'){
			var_dump(count($activeChannels));
			var_dump($activeChannels);
			die(__FILE__.': '.__LINE__);
		}
		
		
		foreach ($activeChannels as $active) {
            $allChannels = $this->channelsModel->allChannels();
            $unpub = array();
            foreach ($allChannels as $ch) {
                if (! in_array($ch['id'], $activeChannels)) {
                    $unpub[] = $ch['id'];
                }
            }
            $this->channelsModel->unpublishMulti($unpub);
        }
	    
	}
	
	

    
    public function grabListings()
    {
    	$filters = array( '*'=>'StringTrim', '*'=>'StringToLower' );
    	$validators = array(
    		'module'=>array( new Zend_Validate_Regex('/^[a-z]+$/u') ),
    		'controller'=>array( new Zend_Validate_Regex('/^[a-z]+$/') ),
    		'action'=>array( new Zend_Validate_Regex('/^[a-z-]+$/') ),
    		'target'=>array( new Zend_Validate_Regex('/^[a-z]+$/') ),
    		'format'=>array( new Zend_Validate_Regex('/^html|json$/') ),
    	);
    	$input = new Zend_Filter_Input($filters, $validators, $this->_request->getParams());
    	if (!$input->isValid()) throw new Exception("Неверные данные", 500);
    	
    	if ($this->_debug) $lifetime = 60;
    	else $lifetime = Xmltv_Config::getCacheLifetime();
    	
    	$model   = new Admin_Model_Grab(array('cache_lifetime'=>$lifetime));
    	$request = $this->_getAllParams();
    	$model->setDebug(true);
    	$model->setSite($request['target']);
    	
    	if (Xmltv_Config::getProxyEnabled()===true) {
    		$model->enableProxy(array('host'=>Xmltv_Config::getProxyHost(), 'port'=>Xmltv_Config::getProxyPort()));
    	}
    	$model->enableCookies(APPLICATION_PATH.'/../cookies/'.$request['target'].'.txt', 
            APPLICATION_PATH.'/../cookies/'.$request['target'].'-jar.txt');
    	
    	if ($request['target']=='vsetvcom')
    	$model->setEncoding('windows-1251');
    	
    	if (Xmltv_Config::getCaching()===true)
    	$model->setCaching(true, Xmltv_Config::getCacheLifetime());
    	
    	$model->setConnectTimeout(30);
    	$model->setChannelsInfo($request['target']);
    	
    	var_dump($model);
    	die(__FILE__.': '.__LINE__);
    }
    
    public function grabMovies()
    {
    	$request = $this->_getAllParams();
    	$siteKey = $this->_getParam('site', null);
    	$debug   = $this->_getParam('debug', 0);
    	
    	//$use_proxy = $this->_getParam('proxy', 0);
    	var_dump($request);
    	var_dump($siteKey);
    	
    	//var_dump(get_include_path());
    	die(__FILE__.': '.__LINE__);
    	
    	$site_table = new Admin_Model_DbTable_Site();
    	$site_model = new Admin_Model_Site();
    	$site = $this->_getClass($siteKey);
    	$site_config = new Zend_Config_Xml(APPLICATION_PATH.'/configs/sites.xml', 'movies');
		$site_config = $site_config->$siteKey;
    	$site->setProxy(array(
			'host'=>$site_config->proxy->host,
			'port'=>$site_config->proxy->port,
			'type'=>$site_config->proxy->type
		));
    	$site->setBaseUrl($site_config->baseUrl);
    	
    	//var_dump($site);
    	//die(__METHOD__);
    	
    	try {
    		$site->fetchPage($site_config->startUri, $site->getEncoding());
    		$site->getAlphaLinks();
			$site->getPaginationLinks();
			$site->getMoviesLinks();
    	} catch (Zend_Exception $e) {
    		echo $e->getMessage();
    		die(__METHOD__.': '.__LINE__);
    	}
    	
    	$i=0;
    	$links = $site->moviesLinks;
    	do {
    		if ($info = $site->getMovieInfo($links[$i]))
    		unset($site->moviesLinks[$i]);
    		else 
    		throw new Exception("Cannot get movie info for URL ".$site->getBaseUrl().$links[$i]);
    		//$site->getMovieInfo();
    		$i++;
    	} while(!empty($site->moviesLinks));
    	//var_dump($site);
		
    	
    	
		die(__METHOD__.': '.__LINE__);
    }
        
}

