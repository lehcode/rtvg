<?php
/**
 * Backend import controller
 * 
 * @author  toshihir
 * @package rutvgid
 * @subpackage backend
 * @version $Id: ImportController.php,v 1.17 2013-01-12 09:06:22 developer Exp $
 *
 */

//ini_set( 'error_reporting', 30711 );
//ini_set( 'display_errors', 1 );

class Admin_ImportController extends Zend_Controller_Action
{
	
	private $_progressBar;
	private $_lockFile;
	private $_parseFolder='/uploads/parse/';

	/**
	 * 
	 * Validator
	 * @var Xmltv_Controller_Action_Helper_RequestValidator
	 */
	private $_teleguideUrl='http://www.teleguide.info/download/new3/xmltv.xml.gz';
	private $_xmlFolder;
	
	/**
	 *
	 * Validator
	 * @var Xmltv_Controller_Action_Helper_RequestValidator
	 */
	protected $validator;
	/**
	 *
	 * Input filtering plugin
	 * @var Zend_Filter_Input
	 */
	protected $input;
	
	const ERR_INVALID_INPUT = 'Неверные данные!';
	const DEFAULT_ICON = 'default.gif';
	
	/**
	 * (non-PHPdoc)
	 * @see Zend_Controller_Action::init()
	 */
	public function init() {
	   
		$this->_helper->layout->setLayout('admin');
		$ajaxContext = $this->_helper->getHelper('AjaxContext');
		$ajaxContext->addActionContext('xml-parse-channels', 'html')
			->addActionContext('xml-parse-programs', 'html')
			->addActionContext('parsing-progress', 'json')
			->initContext();
		
		$this->app_config  = Zend_Registry::get('app_config');
		$this->site_config = Zend_Registry::get('site_config');
		$this->_xmlFolder  = ROOT_PATH.'/uploads/parse/';
		$this->validator = $this->_helper->getHelper('requestValidator');
		
	}

	/**
	 * 
	 * Index page
	 */
	public function indexAction()
	{
		$this->_forward('upload');
	}

   	/**
   	 * Handles XML file upload
   	 */
   	public function uploadAction() {
		
		ini_set('max_execution_time', 0);
		ini_set('max_input_time', -1);
		
		$form = new Xmltv_Form_UploadForm();
		$this->view->form = $form;
		if ($this->_request->isPost()) {
			$formData = $this->_request->getPost();
			if ($form->isValid($formData)) {
				$xmltv_file = $this->_uploadXml();
				$this->view->assign('file_info', array(
					'filename'=>$xmltv_file,
					'filesize'=>filesize($xmltv_file))
				);
				$this->view->assign('show_continue', true);
				$this->render('xml');
			} 
		} 
	}
	
	/**
   	 * Ajax action which handles channels parsing
   	 */
	public function xmlParseChannelsAction($xml_file=null){
		
		ini_set('max_execution_time', 0);
		ini_set('max_input_time', -1);
		
		//var_dump($this->_getAllParams());
		//var_dump($xml_file);
		//var_dump($this->requestParamsValid());
		//die(__FILE__.': '.__LINE__);
		
		if ($this->requestParamsValid()){
		    
			/*
			 * Check if XML file exists
			 */
			if (!$xml_file) {
				$xml_file = Xmltv_Filesystem_File::getName($this->_getParam('xml_file'));
				$path	  = Xmltv_Filesystem_File::getPath($this->_getParam('xml_file'));
			} else {
			    //var_dump(Xmltv_Filesystem_File::getName($xml_file));
			    //var_dump(ROOT_PATH.$this->_parseFolder);
			    //die(__FILE__.': '.__LINE__);
				$xml_file = Xmltv_Filesystem_File::getName($xml_file);
				$path	 = ROOT_PATH.$this->_parseFolder;
			}
			
			//var_dump(is_file($xml_file = $path.$xml_file));
			//die(__FILE__.': '.__LINE__);
			
			if (!is_file($xml_file = $path.$xml_file)){
				throw new Zend_Exception("XML file not found!");
			}
			/*
			 * Load and process XML data
			 */
			$xml = new DOMDocument();
			$xml->preserveWhiteSpace = false;
			$xml->formatOutput = true;
			if (!$xml->load($xml_file)){
				throw new Zend_Exception("Cannot load XML from ".$xml_file.'!');
			}
			
			$channelsTable   = new Admin_Model_DbTable_Channels();
			$newChannels	 = array();
			$updatedChannels = array();
			
			//var_dump($xml->getElementsByTagName('channel')->length);
			//die(__FILE__.': '.__LINE__);
			
			foreach ($xml->getElementsByTagName('channel') as $item){
			    
				$info = array();
				$info['ch_id'] = (int)$item->getAttribute('id');
				$name = $item->getElementsByTagName('display-name');
				$info['lang']  = $name->item(0)->getAttribute('lang');
				
				// Title
				$info['title'] = $name->item(0)->nodeValue;
				if ( Xmltv_String::stristr( Xmltv_String::strtolower( $info['title']), 'канал')){
					$info['title'] = Xmltv_String::str_ireplace( array( 'канал. ', 'канал.', 'канал ', 'канал'), '', $info['title']);
				}
				$info['title'] = trim( $info['title']);
				
				if ((bool)$item->getElementsByTagName( 'icon')->item(0)!==false) {
					$iconOriginal = $item->getElementsByTagName( 'icon')->item(0)->getAttribute('src');
					$icon = $iconOriginal;
					$icon = Xmltv_String::substr_replace( $icon, '', 0, Xmltv_String::strrpos($icon, '/')+1);
					$icon = Xmltv_String::substr_replace( $icon, '.png', Xmltv_String::strrpos($icon, '.'));
					$info['icon'] = $icon;
				} else {
					$info['icon'] = 'default.png';
				}
				
				//var_dump($info);
				//die(__FILE__.': '.__LINE__);
				
				//Generate channel alias
				$toDash        = new Xmltv_Filter_SeparatorToDash();
				$info['alias'] = $toDash->filter($info['title']);
				$plusToPlus	   = new Zend_Filter_Word_SeparatorToSeparator('+', '-плюс-');
				$info['alias'] = $plusToPlus->filter($info['alias']);
				$info['alias'] = str_replace('--', '-', trim($info ['alias'], ' -'));
				
				//var_dump( $channelsTable->fetchRow( array("`alias`='".$info['alias']."' OR `title` LIKE '%".$info['title']."%'")));
				//die(__FILE__.': '.__LINE__);
				
				if ( !$present = $channelsTable->fetchRow( array("`alias`='".$info['alias']."' OR `title` LIKE '%".$info['title']."%'"))) {
				    
				    //die(__FILE__.': '.__LINE__);
					//Save if new
					try {
					    $channelsTable->insert($info);
						$newChannels[] = $info;
					} catch (Exception $e) {
						if ($e->getCode()!=1062) {
						    die($e->getMessage());
						}
					}
					$allChannels[]=$info; //for debugging
					
				} else {
				    
				    $pngFile    = ROOT_PATH.'/public/images/channel_logo/'.$info['icon'];
				    $bigPngFile = ROOT_PATH.'/public/images/channel_logo/100/'.$info['icon'];
				    
				    if ( !file_exists($pngFile) || !file_exists($bigPngFile)){
				        
					    $gifIcon = Xmltv_String::substr_replace($iconOriginal, '', 0, Xmltv_String::strrpos($iconOriginal, '/')+1);
					    $gifFile = ROOT_PATH.'/tmp/'.$gifIcon;
					    $curl    = new Zend_Http_Client_Adapter_Curl();
						$curl->setCurlOption( CURLOPT_HEADER, false);
						$curl->setCurlOption( CURLOPT_RETURNTRANSFER, 1);
						$curl->setCurlOption( CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
						$curl->setCurlOption( CURLOPT_HTTPHEADER, array('Content-type: image/gif'));
						$client = new Zend_Http_Client( $iconOriginal);
						$client->setAdapter($curl);
						
						if (!file_exists($bigPngFile)){
						    file_put_contents($gifFile, $client->request("GET")->getBody());
							file_put_contents($bigPngFile, $this->_helper->getHelper('imageToPng')->imageToPng($gifFile, array(
								'tmp_folder'=>ROOT_PATH.'/tmp',
								'max_size'=>100)));
						}
				
						if (!file_exists($pngFile)){
						    file_put_contents($gifFile, $client->request("GET")->getBody());
							file_put_contents($pngFile, $this->_helper->getHelper('imageToPng')->imageToPng($gifFile, array(
								'tmp_folder'=>ROOT_PATH.'/tmp',
								'max_size'=>45)));
						}
						//var_dump($info);
						$channelsTable->update($info, "`ch_id`='".$info['ch_id']."'");
						
						unlink($gifFile);
						$allChannels[]=$info;
							
						
				    }
				}
			}
			
			//var_dump( $allChannels);
			//var_dump( $newChannels);
			//var_dump( $newIcons);
			//die(__FILE__.': '.__LINE__);
			
			$response['added'] = $newChannels;
			$this->view->assign('response', $response);
			
		}
		
	}

	
	/**
	 * 
	 * Ajax action which handles programs parsing
	 */
	public function xmlParseProgramsAction($xml_file=null){
		
		ini_set('max_execution_time', 0);
		ini_set('max_input_time', -1);	
		
		//$input = $this->_validator->direct( array('isValidRequest', 'vars'=>$this->_getAllParams()) );
		//if ($input->isValid('xml_file')){
		/*
		 * Check if XML file exists
		 */
		if (!$xml_file) {
			$file = Xmltv_Filesystem_File::getName($this->_request->get('xml_file'));
			$path	 = Xmltv_Filesystem_File::getPath($this->_request->get('xml_file'));
		} else {
			$file = Xmltv_Filesystem_File::getName($xml_file);
			$path	 = ROOT_PATH.$this->_parseFolder;			
		}
		if (!is_file($file = $path.$file)){
			throw new Zend_Exception("XML file not found!");
		}
		
		//var_dump($file);
		//die(__FILE__.': '.__LINE__);
		
		/*
		 * Load and process XML data
		 */
		$xml = new DOMDocument();
		if (!$xml->load($file)){
			throw new Exception("Cannot load XML!");
		}
		
		$programs   = $xml->getElementsByTagName('programme');
		$model	  = new Admin_Model_Programs();
		$i=0; //for debug
		foreach ($programs as $node){
			
			$prog  = array();
			$desc  = array();
			$props = array();
			
			//Process program title and detect some properties
			$title = $node->getElementsByTagName('title')
				->item(0)->nodeValue;
			$titles = $model->makeTitles( trim( $title, '. '));
			//var_dump($title);
			$prog['title'] = $titles['title'];
			$prog['sub_title'] = $titles['sub_title'];
			if (isset($titles['rating'])){
				$prog['rating']=$titles['rating'];
			}
			if (isset($titles['category'])){
				$prog['category']=$titles['category'];
			}
			if (isset($titles['premiere'])){
				$props['premiere']=$titles['premiere'];
			}
			if (isset($titles['live'])){
				$prog['live']=$titles['live'];
			}
			if (isset($titles['episode'])){
				$props['episode_num']=$titles['episode'];
			}
			
			//Parse description
			if (@$node->getElementsByTagName('desc')->item(0)){
				$d = $node->getElementsByTagName('desc')->item(0)->nodeValue;
				//var_dump($d);
				$parseDesc = $model->parseDescription($d);
				if (isset($parseDesc['title'])){
					$prog['title'] .= ' '.$parseDesc['title'];
				}
				if (isset($parseDesc['actors']) && !empty($parseDesc['actors'])){
					$props['actors'] = $parseDesc['actors'];
				}
				if (isset($parseDesc['directors']) && !empty($parseDesc['directors'])){
					$props['directors'] = $parseDesc['directors'];
				}
				if (isset($parseDesc['rating'])){
					$prog['rating'] = $parseDesc['rating'];
				}
				if (isset($parseDesc['writer'])){
					$props['writers'] = $parseDesc['writer'];
				}
				
				if (isset($parseDesc['category'])){
					$prog['category'] = $parseDesc['category'];
				}
				if (isset($parseDesc['country'])){
					$props['country'] = $parseDesc['country'];
				}
				if (isset($parseDesc['year'])){
					$props['date'] = $parseDesc['year'];
				}
				if (isset($parseDesc['episode'])){
					$props['episode_num'] = (int)$parseDesc['episode'];
				}
				if (isset($parseDesc['country'])){
					$props['country'] = $parseDesc['country'];
				}
				$desc['intro'] = $parseDesc['text'];
			}
			//die(__FILE__.': '.__LINE__);
			//Channel
			$prog['ch_id'] = (int)$node->getAttribute('channel');
			
			/*
			 * Fix split title for particular channels
			 * mostly movies
			 */
			$splitTitles = array(100037);
			if (in_array($prog['ch_id'], $splitTitles) && Xmltv_String::strlen($prog['sub_title'])){
				$prog['title'] .= ' '.$prog['sub_title'];
				$prog['sub_title'] = '';
			}
			
			//Start and end datetime
			$start = $model->startDateFromAttr( $node->getAttribute('start') );
			$end   = $model->endDateFromAttr( $node->getAttribute('stop') );
			$prog['start'] = $start->toString("yyyy-MM-dd HH:mm:ss");
			$prog['end'] = $end->toString("yyyy-MM-dd HH:mm:ss");
			//Set hash
			$prog['hash'] = $desc['hash'] = $props['hash'] = md5($prog['ch_id'] . $start->toString('U') . $end->toString('U'));
			//Detect category
			$category = $node->getElementsByTagName('category')
				->item(0)->nodeValue;
			$fixCats = array(
				300006=>'религия',
				300037=>'музыка',
			);
			
			if (!isset($prog['category']) && $category) {
				if (array_key_exists($prog['ch_id'], $fixCats)){
					$prog['category'] = $model->getProgramCategory( $fixCats[$prog['ch_id']]);
				} else {
					$prog['category'] = $model->getProgramCategory( $category, $desc['intro']);
				}
				
			}
			
			
			$e = explode('. ', $prog['title']);
			if (count($e)>2){
				$prog['title'] = trim($e[0]).'. '.trim($e[1]).'.';
				unset($e[0]);
				unset($e[1]);
				if (isset($prog['sub_title']))
					$prog['sub_title'] .= implode('. ', $e);
				else
					$prog['sub_title'] = implode('. ', $e);
			}
			
			$prog['alias'] = $desc['alias'] = $props['alias'] = $model->makeAlias($prog['title']);
			
			//debug breakpoint
			 //if ($i<50){
			//	var_dump($prog);
			//	var_dump($props);
			//	var_dump($desc);
			//} else {
			//	die(__FILE__.': '.__LINE__);
			//} 
			
			// Fix wrong age rating
			$model->saveProgram($prog);
			if ($desc['intro'] && Xmltv_String::strlen($desc['intro']))
				$model->saveDescription($desc);
			if ($props) {
			    
				//var_dump($props);
				//die(__FILE__.': '.__LINE__);
				
				if (isset($props['actors']) && is_array($props['actors'])) {
					$props['actors'] = implode(',', $props['actors']);
				} 
				if (isset($props['directors']) && is_array($props['directors'])) {
					$props['directors'] = implode(',', $props['directors']);
				}
				$model->saveProps($props);
			}
			//die(__FILE__.': '.__LINE__);
			//die(__FILE__.': '.__LINE__);
			$i++;
			
		}		
		
		$response['success'] = true;
		$this->view->assign( 'response', $response );
		system( 'mv '.$xml_file.' '.$xml_file.'.processed' );
			
		
	}
	
	
	/*
	private function _parseRequestValid(){
		$filters = array( '*'=>'StringTrim' );
		$validators = array(
			'module'=>array(
				new Zend_Validate_Regex('/^[a-z]+$/')),
			'controller'=>array(
				new Zend_Validate_Regex('/^[a-z]+$/')),
			'action'=>array(
				new Zend_Validate_Regex('/^[a-z-]+$/')),
			'xml_file_ch'=>array(
				new Zend_Validate_Regex('/\/.+\/[0-9]{8}-[0-9]{8}.+\.xml$/')),
			'xml_file_pr'=>array(
				new Zend_Validate_Regex('/\/.+\/[0-9]{8}-[0-9]{8}.+\.xml$/')),
				
		);
		$input = new Zend_Filter_Input($filters, $validators, $this->_request->getParams());
		if ($input->isValid())
		return true;
		
		return false;
	}
	*/
	
	
	private function _uploadXml(){
		
		/* Uploading Document File on Server */
		$upload = new Zend_File_Transfer_Adapter_Http();
		
		//var_dump(ROOT_PATH);
		//die(__FILE__.': '.__LINE__);
		
		$path = ROOT_PATH."/uploads/xmltv/";
		$upload->setDestination($path);
		try {
			$upload->receive();
		} catch (Zend_File_Transfer_Exception $e){
			$e->getMessage();
		}
		
		/*
		if (APPLICATION_ENV=='development') {
			$uploadedData = $form->getValues();
			Zend_Debug::dump($uploadedData, 'Данные формы:');
		}
		*/
		$name = $upload->getFileName();
		$upload->setOptions(array('useByteString' => false));
		$size = (int)$upload->getFileSize();
		$mimeType = $upload->getMimeType();
		$fn  = Xmltv_Parser_FilenameParser::getXmlFileName($name);
		$ext = Xmltv_Parser_FilenameParser::getExt($name);
		preg_match('/^application\/(.+)$/', $mimeType, $m);
		$type = $m[1];
		//var_dump($fn);
		//die(__METHOD__);
		try {
			
			$uploads = $upload->getDestination();
			$nn=md5($fn.time()).'.'.$type;
			if (copy($name, "$uploads/$nn")===false)
			throw new Exception("Cannot copy file");
			
			$path = ROOT_PATH."/uploads/parse/";
			$xml_dir = $path.$fn;
			if (!is_dir($xml_dir)) {
				if (!mkdir($xml_dir))
				throw new Exception("Cannot create directory");
			}
			
			$decompress = new Zend_Filter_Decompress(array(
				'adapter'=>$type, 
				'options'=>array(
					'target'=>"$xml_dir/"
			)));
			$xmlfile = $decompress->filter("$uploads/$nn");
			
		} catch (Exception $e) {
			echo $e->getMessage();
		}
		
		$files = Xmltv_Filesystem_Folder::files($xml_dir, addslashes($fn).'\.xml');
		return"$xml_dir/".$files[0];
	}
	
	private function _getDateString($input=null){
		if(!$input) return;
		$date['year']	  = substr($input, 0, 4);
		$date['month']	 = substr($input, 4,2);
		$date['day']	   = substr($input, 6,2);
		$date['hours']	 = substr($input, 8,2);
		$date['minutes']   = substr($input, 10,2);
		$date['seconds']   = substr($input, 12,2);
		$date['gmt_diff']  = substr($input, 16,4);
		return $date['year'].'-'.$date['month'].'-'.$date['day'].' '.$date['hours'].':'.$date['minutes'].':'.$date['seconds'].' '.$date['gmt_diff'];
		
	}
	
	/*
	public function premieresSearchAction(){
		
		ini_set('max_execution_time', 0);
		ini_set('max_input_time', -1);
		
		$response['success']=false;
		$response['data']=null;
		if ($this->_parseRequestValid()===true){
			
			$request = $this->_getAllParams();
			$xml_file = Xmltv_Filesystem_File::getName($this->_request->get('xml_file'));
			$path	 = Xmltv_Filesystem_File::getPath($this->_request->get('xml_file'));
			$nodeName = 'programme';
			$file = new Xmltv_XmlChunk($xml_file, array(
				'chunkSize'=>24000,
				'path'=>$path,
				'element'=>$nodeName));
			
			$model = new Admin_Model_Import();
			$processed = array();
			while ( $xml = $file->read () ) {
				preg_match ( '/(<'.$nodeName.' start="[0-9]{14} \+[0-9]{4}" stop="[0-9]{14} \+[0-9]{4}" channel="[0-9]+">.+<\/' . $nodeName . '>).*$/imsU', $xml, $m );
				if (@isset ( $m [1] )) {
					$xml = new SimpleXMLElement ( $m [1] );
					if ($model->isPremiere((string)$xml->title)){
						$model->savePremiere($xml);
						$p = $model->getProgramInfo();
						$processed[]=$p;
					}
				}
			}
			
			$response['success']=true;
			$response['data']=$processed;
			$this->view->assign('response', $response);
			
		} else {
			$response['error']='Ошибка параметров';
		}
		$this->view->assign('response', $response);
		
	}
	*/
	
	
	/**
	 * 
	 * Progress of parsing
	 */
	public function parsingProgressAction(){
		$funcName = '_'.$this->_getParam('parse').'ParseProgress';
		$this->$funcName();
	}
	
	
	/**
	 * 
	 * Progress of parsing programs
	 */
	private function _programsParseProgress(){
		
		var_dump($this->_getAllParams());
		die(__FILE__.': '.__LINE__);
		
		$xml_file = Xmltv_Filesystem_File::getName($this->_request->get('xml'));
		$path = Xmltv_Filesystem_File::getPath($this->_request->get('xml'));
		//var_dump($xml_file);
		//$total_count=0;
		//$cache = new Xmltv_Cache(array('cache_lifetime'=>7200));
		$this->_lockFile = ROOT_PATH.'/cache/'.$cache->getHash($xml_file).'.lock';
		//var_dump($this->_lockFile);
		$hash = $cache->getHash(__METHOD__.$xml_file);
		if (!($locked = @fopen($this->_lockFile, 'r'))){
			
			$fh = fopen($this->_lockFile, 'w');
			fwrite($fh, time());
			fclose($fh);
			
			if (!$tc = $cache->load($hash)){
				
				$nodeName = 'programme';
				$file = new Xmltv_XmlChunk($xml_file, array(
					'chunkSize'=>24000,
					'path'=>$path,
					'element'=>$nodeName));
				
				while ( $xml = $file->read () ) {
					preg_match ( '/(<'.$nodeName.' start="[0-9]{14} \+[0-9]{4}" stop="[0-9]{14} \+[0-9]{4}" channel="[0-9]+">.+<\/' . $nodeName . '>).*$/imsU', $xml, $m );
					if (isset ( $m [1] ) && !empty($m[1]))
					$total_count++;
				}
				$cache->save($total_count, $hash);
				
			} else {
				$total_count = $tc;
			} 		
		} else {
			if (is_file($this->_lockFile))
			$total_count = $cache->load($hash);
			else
			unlink($this->_lockFile);
			
		}
		//var_dump($this->_lockFile);
		//var_dump($locked);
		//die(__FILE__.': '.__LINE__);
		/*
		 * dates
		 */
		$parts = explode('.', $xml_file);
		$parts = explode('-', $parts[0]);
		$start = substr($parts[0], 4, 4) . '-' . substr($parts[0], 2, 2) . '-' . substr($parts[0], 0, 2);
		$end   = substr($parts[1], 4, 4) . '-' . substr($parts[1], 2, 2) . '-' . substr($parts[1], 0, 2);
		$weekStart = new Zend_Date($start);
		$weekEnd   = new Zend_Date($end);
		/*
		 * Get programs count
		 */
		$programs = new Admin_Model_Programs();
		$current = $programs->getProgramsCountForWeek($weekStart, $weekEnd);
		
		$adapter = new Zend_ProgressBar_Adapter_JsPull();
		$adapter->setExitAfterSend(false);
		$this->_progressBar = new Zend_ProgressBar($adapter, $current, $total_count, 'parse');
		$this->_progressBar->update($current);
		exit();
	}
	
	
	/**
	 * 
	 * Download from remote source and parse 
	 * gzipped listings XMLTV file
	 */
	public function remoteAction(){
		
		//var_dump($this->_getAllParams());
		//die(__FILE__.': '.__LINE__);
		
		$site = $this->_getParam('site', 'teleguide');
		switch ($site){
			case 'teleguide':
				
				$gzFile  = $this->_xmlFolder.'current.xml.gz';
				$xmlFile = Xmltv_String::substr_replace( $gzFile, '', Xmltv_String::strlen($gzFile)-3);
				if ( !file_exists($gzFile) && !file_exists($xmlFile)) {
					
					$curl = new Zend_Http_Client_Adapter_Curl();
					$curl->setCurlOption(CURLOPT_HEADER, false);
					$curl->setCurlOption(CURLOPT_RETURNTRANSFER, 1);
					$curl->setCurlOption(CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
					$curl->setCurlOption(CURLOPT_HTTPHEADER, array('Content-type: application/gzip'));
					$client = new Zend_Http_Client($this->_teleguideUrl);
					$client->setAdapter($curl);
					file_put_contents($gzFile, $client->request("GET")->getBody());
					system("gzip -d $gzFile");
					
				} 
				if(file_exists($gzFile) && !file_exists($xmlFile)) {
					system("gzip -d $gzFile");
				} 
				
				if (!file_exists($xmlFile))
					throw new Exception("Error downloading XML from $site!");
				
				
				//var_dump($xmlFile);
				//die(__FILE__.': '.__LINE__);
				
				$this->xmlParseChannelsAction($xmlFile);
				$this->xmlParseProgramsAction($xmlFile);
				//die(__FILE__.': '.__LINE__);
				
				
				//unlink($xmlFile);
				system("mv $xmlFile $xmlFile.last");
				
				break;
				
			default:
				break;
		}
		
		echo "Готово!";
		die();
		
	}
	
	/**
	 * Validate nad filter request parameters
	 *
	 * @throws Zend_Exception
	 * @throws Zend_Controller_Action_Exception
	 * @return boolean
	 */
	protected function requestParamsValid(){
	
		// Validation routines
		$this->input = $this->validator->direct(array('isvalidrequest', 'vars'=>$this->_getAllParams()));
		//var_dump($this->input->isValid('site'));
		//var_dump($this->input->isValid('format'));
		//die(__FILE__.': '.__LINE__);
		if ($this->input===false) {
		    if (APPLICATION_ENV=='development') {
				var_dump($this->_getAllParams());
				die(__FILE__.': '.__LINE__);
		    }
		} else {
		    
			foreach ($this->_getAllParams() as $k=>$v){
				if ($this->input->isValid($k)!==true) {
					echo("Invalid ".$k.'!');
				}
			}
			//die(__FILE__.': '.__LINE__);
			return true;
	
		}
	
	}
	
}





