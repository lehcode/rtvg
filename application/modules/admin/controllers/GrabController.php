<?php
/**
 * Manage listings grabbing
 * 
 * @author     Antony Repin <egeshisolutions@gmail.com>
 * @subpackage backend
 * @version    $Id: GrabController.php,v 1.6 2013-04-07 21:04:17 developer Exp $
 *
 */
class Admin_GrabController extends Rtvg_Controller_Admin
{
	/**
	 * @var Admin_Model_Channels
	 */
    private $channelsModel;

    /**
	 * @var Admin_Model_Programs
	 */
    private $programsModel;
    
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
        $this->programsModel = new Admin_Model_Programs();
        
        
    }


    /**
     * Index
     */
	public function indexAction () 
	{
		$this->_redirect('listings');
	}
	
	/**
	 * Grab listings index page
	 */
	public function listingsAction()
	{
		$weekEnd   = $this->_helper->getHelper('weekDays')->getEnd();	
		$weekStart = $weekEnd->addDay(1);
		$this->view->assign( 'start', $weekStart );
	}
	
	public function doAction()
	{
	    $this->_helper->getHelper('Layout')->disableLayout();
	    $weekEnd   = $this->_helper->getHelper('weekDays')->getEnd();
	    $weekStart = $weekEnd->addDay(1);
	    $this->view->assign( 'start', $weekStart );
	    $form = new Xmltv_Form_Admin_SitesToGrab(array('start'=>$weekStart));
	    if( parent::validateForm( $form, $this->_getAllParams() ) ){
	        parent::validateRequest();
	        //var_dump($this->_getParam('date'));
	        //die(__FILE__.': '.__LINE__);
	        $this->_forward('grab-tv-yandex', null, null, array('date'=>$this->_getParam('date')));
	    }
	}
	
	public function grabTvYandexAction(){
		
	    parent::validateRequest();
	    
	    set_time_limit(-1);
	    $weekStart  = new Zend_Date($this->_getParam('date'));
	    $yaDayStart = ceil($weekStart->toString('U')/(3600*24));
	    $yaDayCount = 0;
	    do {
	        
	        $yaDay = $yaDayStart+$yaDayCount;
	        $date = new Zend_Date( $yaDay*3600*24, 'U' );
	        var_dump($date->toString('YYYY-MM-dd'));
			$urls = array(
		    	'centr_ru'=>"http://m.tv.yandex.ru/213/?day=$yaDay&when=2&channel=146%2C711%2C649%2C162%2C187%2C515%2C353%2C304%2C18%2C79%2C427%2C405%2C511%2C698%2C291%2C740%2C323%2C557%2C898%2C150",
		    	'centr_ua'=>"http://m.tv.yandex.ru/187/?day=$yaDay&when=2&channel=677%2C620%2C326%2C670%2C709%2C140%2C128%2C773%2C583%2C479%2C453%2C281%2C586%2C940%2C632%2C167%2C689%2C607%2C939%2C937",
		    	'region_ru'=>"http://m.tv.yandex.ru/213?day=$yaDay&when=2&channel=421%2C655%2C335%2C161%2C334",
		    	'region_ua'=>"http://m.tv.yandex.ru/187/?day=$yaDay&when=2&channel=788%2C627%2C650%2C634%2C641%2C561%2C90",
		    	'topics'=>"http://m.tv.yandex.ru/213/?day=$yaDay&when=2&channel=916%2C917%2C918%2C919%2C921%2C922%2C924%2C925%2C926%2C927%2C928%2C929%2C932%2C933%2C934%2C935%2C710%2C579%2C658%2C365%2C516%2C463%2C601%2C495%2C325%2C409%2C437%2C60%2C23%2C850%2C288%2C661%2C429%2C575%2C608%2C102%2C567%2C55%2C127%2C267%2C309%2C589%2C213%2C521%2C277%2C346%2C454%2C669%2C66%2C747%2C834%2C273%2C123%2C798%2C462%2C22%2C71%2C542%2C618%2C675%2C518%2C12%2C485%2C783%2C617%2C566%2C638%2C743%2C53%2C406%2C663%2C447%2C181%2C173%2C163%2C794%2C716%2C180%2C779%2C686%2C61%2C16%2C502%2C410%2C659%2C615%2C810%2C520%2C352%2C19%2C494%2C598%2C646%2C51%2C138%2C741%2C15%2C801%2C145%2C82%2C765%2C223%2C328%2C31%2C644%2C37%2C434%2C384%2C648%2C313%2C119%2C125%2C789%2C547%2C156%2C455%2C333%2C604%2C376%2C769%2C705%2C21%2C626%2C637%2C477%2C275%2C776%2C555%2C308%2C332%2C849%2C388%2C897%2C425%2C774%2C258%2C389%2C680%2C723%2C154%2C367%2C505%2C595%2C6%2C737%2C481%2C726%2C423%2C113%2C713%2C111%2C662%2C201%2C681%2C322%2C377%2C499%2C134%2C664%2C183%2C697%2C358%2C563%2C311%2C217%2C24%2C799%2C821%2C614%2C153%2C415%2C250%2C8%2C401%2C306%2C214%2C851%2C923%2C920%2C931%2C930%2C994%2C911%2C912%2C983%2C984%2C990%2C989%2C986%2C987%2C988%2C756%2C828%2C355%2C312%2C715%2C777%2C284%2C278%2C797%2C319%2C831%2C757%2C393%2C461%2C631%2C59%2C315%2C442%2C804%2C533%2C25%2C642%2C141%2C552%2C247%2C132%2C39%2C591%2C331%2C731%2C491%2C91%2C554%2C531%2C473%2C412%2C430%2C431%2C11%2C121%2C807%2C363%2C685%2C458%2C509%2C464%2C151%2C730%2C560%2C178%2C35%2C382%2C576%2C349%2C270%2C237%2C852%2C165%2C257%2C249",
		    );
		    
		    $parser = new Xmltv_Parser_Listings_Tvyandexru();
		    $listings = array();
		    
		    foreach ($urls as $cat=>$channelUrl){
		        
			    $parser->setUrl( $channelUrl );
			    $parser->setUserAgent( 'Mozilla/5.0 (Windows; U; Windows CE 5.1; rv:1.8.1a3) Gecko/20060610 Minimo/0.016' );
			    //$parser->setCookiePath( APPLICATION_PATH.'/../cookies/m.tv.yandex.ru.jar.txt' );
			    //$parser->setProxy('172.16.78.1', 8118);
			    $parser->setReferrer('http://m.tv.yandex.ru');
			    $parser->setOption( CURLOPT_TIMEOUT, 20 );
			    $parser->setOption( CURLOPT_FOLLOWLOCATION, 1 );
			    $parser->setOption( CURLOPT_MAXREDIRS, 5 );
			    
			    if (APPLICATION_ENV=='development'){
			    	var_dump($parser);
			    }
			    
			    
			    $f = '/Grab';
			    $hash = md5($channelUrl);
			    if ($this->cache->enabled){
				    if (false===($queryResult = $this->cache->load($hash, 'Core', $f))){
				        $queryResult = $parser->fetch();
					    $this->cache->save($queryResult, $hash, 'Core', $f);
			    	} 
			    } else {
			        $queryResult = $parser->fetch();
			    }
			    
			    if (!$queryResult){
			    	throw new Zend_Exception( "No response from server or proxy!".__LINE__ );
			    }
			    $html = $queryResult->getDocument();
			    
			    $dom = new DOMDocument();
			    $dom->loadHTML( $html );
			    $dom->preserveWhiteSpace = false;
			    $tables = $dom->getElementsByTagName('table');
			    foreach ($tables as $table){
			        if($table->getAttribute('class')=='b-schedule__list'){
			            $headers = $table->getElementsByTagName('th');
			            $title = $parser->mapTitle( trim($headers->item(1)->nodeValue) );
			            if (empty($title)){
			                throw new Zend_Exception('Bad channel title!');
			            }
			            
			            $listings[$title] = array(
			            		'alias'=>$this->channelsModel->makeAlias($title),
			            		'date'=>$date->toString('YYYY-MM-dd'));
			            
			            $tableRows = $table->getElementsByTagName('tr');
			            $i=0;
			            foreach ($tableRows as $row){
			                
			                $bc = array(); // single broadcast
			                $tds = $row->childNodes;
			                $ri =0;
			                foreach ($tds as $td){
			                    $mod = ($ri%2);
			                    if ($i==0){
			                        //var_dump( $td->nodeValue );
			                    } else {
			                        
			                        if ($ri==0){
			                            
			                            $bcHref = $td->childNodes->item(0)->getAttribute('href');
			                            $progUrl = 'http://m.tv.yandex.ru/'.ltrim( $bcHref, '/' );
			                            if (APPLICATION_ENV=='development'){
			                        		//var_dump( $progUrl );
			                            }
			                            
			                            if ($this->cache->enabled){
			                                $f = '/Grab';
			                                $hash = md5($progUrl);
			                            	if (false===($broadcastPage = $this->cache->load($hash, 'Core', $f))){
			                            		$broadcastPage = $parser->fetchBroadcastPage( $progUrl, $channelUrl );
			                            		$this->cache->save($broadcastPage, $hash, 'Core', $f);
			                            	}
			                            } else {
			                            	$broadcastPage = $parser->fetchBroadcastPage( $progUrl, $channelUrl );
			                            }
			                            if (!$broadcastPage){
			                            	throw new Zend_Exception( "No response from server or proxy!".__LINE__ );
			                            }
			                            
			                            $pageHtml = $broadcastPage->getDocument();
			                            
			                            if (APPLICATION_ENV=='development'){
			                            	//echo( $pageHtml ) ;
			                            	//die(__FILE__.': '.__LINE__);
			                            }
			                            
			                            $pageDom = new DOMDocument();
			                            $pageDom->loadHTML( $pageHtml );
			                            $pageDom->preserveWhiteSpace = false;
			                            $divs = $pageDom->getElementsByTagName('div');
			                            
			                            foreach ($divs as $div){
			                            	if ($div->getAttribute('class')=='b-content'){
			                            	    $bContent = $div;
			                            	}
			                            }
			                            
			                            $bc['title'] = $this->programsModel->cleanTitle( $bContent->getElementsByTagName('h1')->item(0)->nodeValue );
			                            $parsed = $this->programsModel->parseTitle( $bc['title'] );
			                            //var_dump($parsed);
			                            $bc['alias'] = $this->programsModel->makeAlias( $bc['title'] );
			                            
			                            foreach ($bContent->childNodes as $broadcastNode){
			                                
			                                if ($broadcastNode->getAttribute('class')=='b-broadcast'){
			                            
			                            		if (@$broadcastNode->childNodes->item(0)->getAttribute('src')) {
			                            			$bc['image'] = $broadcastNode->childNodes->item(0)->getAttribute('src');
			                            		}
			                            		 
			                            		if ($broadcastNode->childNodes->item(1)->getAttribute('class')=='b-broadcast__time') {
			                            			$value = trim($broadcastNode->childNodes->item(1)->nodeValue);
			                            			preg_match('/,\s(\d{1,2}:\d{2})—(\d{1,2}:\d{2})$/u', $value, $m);
			                            			$bc['start'] = !empty($m[1]) ? new Zend_Date( $date->toString("YYYY-MM-dd").' '.trim($m[1]), 'YYYY-MM-dd H:mm') : null ;
			                            			$bc['end']   = !empty($m[2]) ? new Zend_Date( $date->toString("YYYY-MM-dd").' '.trim($m[2]), 'YYYY-MM-dd H:mm') : null ;
			                            			$bc['start'] = $bc['start']->toString("YYYY-MM-dd HH:mm").':00';
			                            			$bc['end']   = $bc['end']->toString("YYYY-MM-dd HH:mm").':00';
			                            		}
			                            		
			                            		$infoNode = $broadcastNode->childNodes->item(2);
			                            		if ($infoNode && $infoNode->getAttribute('class')=='b-broadcast__info'){
				                            		foreach ($broadcastNode->childNodes as $propPart){
				                            		    if( $propPart->getAttribute('class')=='b-broadcast__info' ){
				                            		        $bcInfo   = $propPart;
				                            		        
				                            		        if (APPLICATION_ENV=='development'){
				                            		        	//var_dump( $bcInfo->childNodes->length ) ;
				                            		        	//die(__FILE__.': '.__LINE__);
				                            		        }
				                            		        
				                            		        $descNode = @$bcInfo->childNodes->item(0);
				                            		        if (is_a($descNode, 'DOMElement')){
				                            		        	$bc['desc'] = trim($descNode->nodeValue);
				                            		        }
				                            		        
				                            		        for ($c=1;$c<$bcInfo->childNodes->length;$c++){
				                            		        	
				                            		            //var_dump($bcInfo->childNodes->item($c)->nodeValue);
				                            		        	
				                            		            $value = $bcInfo->childNodes->item($c)->nodeValue;
				                            		            
				                            		            if (Xmltv_String::stristr($value, 'Ведущий')) {
				                            		            	if (preg_match('/^Ведущий:\s+([\p{Lu}\p{Ll}\s,]+)+\.?$/ui', $value, $m)){
				                            		            		$bc['commentators'] = $parser->extractCommentators( $this->programsModel->namesRegex, trim($m[1], ' .') );
				                            		            	}
				                            		            }
				                            		             
				                            		            if (Xmltv_String::stristr($value, 'Режиссер:')) {
				                            		            	if (preg_match('/^Режиссер:\s+([\p{Lu}\p{Ll}\s,]+)+\.?$/ui', $value, $m)){
				                            		            		$bc['directors'] = $parser->extractDirectors( $this->programsModel->namesRegex, trim($m[1], ' .') );
				                            		            	}
				                            		            }
				                            		            
				                            		            if (Xmltv_String::stristr($value, 'В ролях:')) {
				                            		            	if (preg_match('/^В ролях:\s+([\p{Lu}\p{Ll}\s,]+)+\.?$/ui', $value, $m)){
				                            		            		$bc['actors'] = $parser->extractActors( $this->programsModel->namesRegex, trim($m[1], ' .') );
				                            		            	}
				                            		            }
				                            		            
				                            		            if (Xmltv_String::stristr($value, 'Возрастные ограничения')) {
				                            		            	if (preg_match('/^Возрастные ограничения:\s+([\p{Lu}\p{Ll}\s,]+)+\.?$/ui', $value, $m)){
				                            		            		$bc['age_rating'] = $parser->extractAgeRating( trim($m[1], ' .') );
				                            		            	}
				                            		            }
				                            		            
				                            		            if (Xmltv_String::stristr($value, 'Производство')) {
				                            		            	if (preg_match('/^Производство:\s+(.+)\.?$/ui', $value, $m)){
				                            		            		$bc['country'] = $this->programsModel->countryRuToIso( trim($m[1], ' .') );
				                            		            	} elseif (preg_match('/^Производство:\s+(.+),\s+\d{4}\s+г\.$/ui', $value, $m)){
				                            		            		$bc['country'] = $this->programsModel->countryRuToIso( trim($m[1], ' .') );
				                            		            		$bc['year'] = (int)trim($m[2], ' .');
				                            		            	}
				                            		            }
				                            		            
				                            		            if (Xmltv_String::stristr($value, 'Автор сценария')) {
				                            		            	if (preg_match('/^Автор сценария:\s+([\p{Lu}\p{Ll}\s,-]+)+\.?$/ui', $value, $m)){
				                            		            		$bc['writers'] = $parser->extractWriters( $this->programsModel->namesRegex, trim($m[1], ' .') );
				                            		            	}
				                            		            }
				                            		            
				                            		            if (Xmltv_String::stristr($value, 'Оператор')) {
				                            		            	if (preg_match('/^Оператор:\s+([\p{Lu}\p{Ll}\s,-]+)+\.?$/ui', $value, $m)){
				                            		            		$bc['operators'] = $parser->extractOperators( $this->programsModel->namesRegex, trim($m[1], ' .') );
				                            		            	}
				                            		            }
				                            		            
				                            		            if (Xmltv_String::stristr($value, 'Композитор')) {
				                            		            	if (preg_match('/^Композитор:\s+([\p{Lu}\p{Ll}\s,-]+)+\.?$/ui', $value, $m)){
				                            		            		$bc['composers'] = $parser->extractComposers( $this->programsModel->namesRegex, trim($m[1], ' .') );
				                            		            	}
				                            		            }
				                            		            
				                            		            if (Xmltv_String::stristr($value, 'Продюсер')) {
				                            		            	if (preg_match('/^Продюсер:\s+([\p{Lu}\p{Ll}\s,-]+)+\.?$/ui', $value, $m)){
				                            		            		$bc['producers'] = $parser->extractProducers( $this->programsModel->namesRegex, trim($m[1], ' .') );
				                            		            	}
				                            		            }
				                            		            
				                            		        }
				                            		        
				                            		    }
				                            		    
				                            		}
			                            		}
			                            		
			                            		
			                            		if (APPLICATION_ENV=='development'){
			                            			//var_dump( $bc ) ;
			                            			//die(__FILE__.': '.__LINE__);
			                            		}
			                            		
			                            	}
			                            	 
			                            	 
			                            }
			                            
			                            
			                            
			                        } elseif ($ri==1){
			                            $bcTitle = $this->programsModel->parseTitle( trim($td->nodeValue, ' .') );
			                        }
			                        
			                        if (is_array($bcTitle) && is_array($bc)){
			                            $bc['title'] = $bcTitle['title'];
			                            $bc['alias'] = $this->programsModel->makeAlias( $bcTitle['alias'] );
			                        	$listings[$title]['broadcasts'][] = array_merge( $bcTitle, $bc );
			                        }
			                        
			                    }
			                    
			                    if (APPLICATION_ENV=='development'){
			                    	//var_dump( $props ) ;
			                    	//var_dump( $bc ) ;
			                    	//die(__FILE__.': '.__LINE__);
			                    }
			                    
			                	$ri++;
			                }
			                
			                $i++;
			                
			            }
			        	if (APPLICATION_ENV=='development'){
			                var_dump($listings[$title]);
			                die(__FILE__.': '.__LINE__);
			            }
			        }
			    }
			    
			    if (APPLICATION_ENV=='development'){
			    	var_dump($listings);
			    	die(__FILE__.': '.__LINE__);
			    }
			    
			    usleep(1*350000); //0.35 secs
		    } 
		    //var_dump($yaDay);
		    //var_dump($yaDayCount);
		    $yaDayCount+=1;
		    
	    } while( $yaDayCount < 7 );
	    
	    if (APPLICATION_ENV=='development'){
	    	var_dump($listings);
	    	die(__FILE__.': '.__LINE__);
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
    	$model->enableCookies(ROOT_PATH.'/cookies/'.$request['target'].'.txt', ROOT_PATH.'/cookies/'.$request['target'].'-jar.txt');
    	
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
    
    public function grabSeriesAction() {
		die(__METHOD__);
    }
    
    
    

    
}

