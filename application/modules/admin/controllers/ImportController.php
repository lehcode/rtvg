<?php
/**
 * Backend import controller
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: ImportController.php,v 1.6 2012-04-07 09:08:30 dev Exp $
 *
 */
class Admin_ImportController extends Zend_Controller_Action
{
	
	protected $config;
	private $_debug = false;
	private $_progressBar;
	
    public function init()
    {
    	$this->_helper->layout->setLayout('admin');
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
		$ajaxContext->addActionContext('xmlparsechannels', 'json')
			->addActionContext('xml-parse-programs', 'html')
			->addActionContext('parsing-progress', 'json')
			->addActionContext('premieres-search', 'html')
			->initContext();
		/*
			$this->view->setScriptPath(APPLICATION_PATH . 
					'/modules/admin/views/scripts/');
		*/
		$this->config = Xmltv_Config::getConfig('site');
		$this->_debug = (int)$this->config->site->debug;
		
    }

    public function indexAction()
    {
        $this->_forward('upload');
    }

   	/**
   	 * Handles XML file upload
   	 */
   	public function uploadAction()
    {
    	
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
    public function xmlparsechannelsAction(){
    	
    	ini_set('max_execution_time', 0);
    	ini_set('max_input_time', -1);
    	
    	//var_dump($this->_request->getParams());
    	//var_dump($input->isValid());
    	//die(__FILE__.": ".__LINE__);
    	
    	if ($this->_parseRequestValid()===true){
    		$xml_file = Xmltv_Filesystem_File::getName($this->_request->get('xml_file_ch'));
    		$path     = Xmltv_Filesystem_File::getPath($this->_request->get('xml_file_ch'));
    		$nodeName = 'channel';
    		$file = new Xmltv_XmlChunk($xml_file, array(
    			'chunkSize'=>2048,
    			'path'=>$path,
    			'element'=>$nodeName));
    		
    		while ( $xml = $file->read () ) {
    			
				preg_match ( '/(<' . $nodeName . ' id="[0-9]+">.+<\/' . $nodeName . '>).*$/imsU', $xml, $m );
				
				if (@isset ( $m [1] )) {
					$xml = new SimpleXMLElement ( $m [1] );
					//$dom = new DOMDocument ( '1.0', 'utf-8' );
	    			//$dom->appendChild ( $dom->importNode ( dom_import_simplexml ( $xml ), true ) );
					//$dom->normalizeDocument ();
					//$xmltext = $dom->saveXML ();
					$info = array ();
					$attrs = $xml->attributes ();
					$info ['ch_id'] = ( int ) $attrs ['id'];
					$node = 'display-name';
					$info ['title'] = ( string ) $xml->$node;
					$filter = new Xmltv_Filter_SeparatorToDash ();
					$info ['alias'] = $filter->filter($info['title']);
					
					//var_dump($ch_info);
					
					$channels = new Xmltv_Model_DbTable_Channels();
					$programs = new Xmltv_Model_DbTable_Programs();
					$by_alias = $channels->fetchRow("`alias`='".$info['alias']."'");
					//var_dump($by_alias);
					//die();
					$new_channels = array();
					$updated_channels = array();
					if ($by_alias===null) {
						$where = "`ch_id`='".$info['ch_id']."'";
						$by_ch_id = $channels->fetchRow($where);
						if ($by_ch_id===null) {
							$info['icon'] = 'default-icon.gif';
							$channels->insert($info);
							$new_channels[]=$info;
						} else {
							$existing = $by_ch_id->toArray();
							$existing['title'] = $info['title'];
							$existing['alias'] = $info['alias'];
							$channels->update($info, $where);
							$updated_channels[]=$info;
						}
					}
    			}	
				$response['added']   = $new_channels;
				$response['updated'] = $updated_channels;
    			$this->view->assign('response', $response);
			}
    	}
		$this->render('xmlparsechannels-ajax');
    }

    
    
	public function xmlParseProgramsAction(){
		
		ini_set('max_execution_time', 0);
    	ini_set('max_input_time', -1);
		
		if ($this->_parseRequestValid()===true){
    		
    		$xml_file = Xmltv_Filesystem_File::getName($this->_request->get('xml_file'));
    		$path     = Xmltv_Filesystem_File::getPath($this->_request->get('xml_file'));
    		$nodeName = 'programme';
    		$file = new Xmltv_XmlChunk($xml_file, array(
    			'chunkSize'=>24000,
    			'path'=>$path,
    			'element'=>$nodeName
    		));
    		/*
    		$total = 0;
    		while ( $xml = $file->read () ) {
    			preg_match ( '/(<'.$nodeName.' start="[0-9]{14} \+[0-9]{4}" stop="[0-9]{14} \+[0-9]{4}" channel="[0-9]+">.+<\/' . $nodeName . '>).*$/imsU', $xml, $m );
				if (@isset( $m[1] ) )
				$total++;
    		}
    		$a = new Zend_ProgressBar_Adapter_JsPull();
			$a->setExitAfterSend(false);
			$progressBar = new Zend_ProgressBar($a, 0, $total, 'parse');
			// re-load file
			$file = new Xmltv_XmlChunk($xml_file, array(
    			'chunkSize'=>24000,
    			'path'=>$path,
    			'element'=>$nodeName
    		));
    		*/
    		$tolower  = new Zend_Filter_StringToLower();
    		$descriptions_table = new Admin_Model_DbTable_ProgramsDescriptions();
			$programs = new Admin_Model_Programs();
    		$cnt=0;
    		while ( $xml = $file->read () ) {
    			
    			preg_match ( '/(<'.$nodeName.' start="[0-9]{14} \+[0-9]{4}" stop="[0-9]{14} \+[0-9]{4}" channel="[0-9]+">.+<\/' . $nodeName . '>).*$/imsU', $xml, $m );
				if (@isset ( $m [1] )) {
					/*
					if ($cnt==100)
					die(__FILE__.": ".__LINE__);
					else 
					$cnt++;
					*/
					//xdebug_start_trace();
					$xml = new SimpleXMLElement ( $m [1] );
					$info = $programs->parseProgramXml($xml);
					/*
					if (!empty($info)) {
						//var_dump($info);
						if ($info['hash']=='9a8298a407354469ac9a2e9a620da9b9') {
							var_dump($info);
							die(__FILE__.": ".__LINE__);
						}
					}
					*/
					$new_hash = $programs->saveProgram($info);
					$found    = $programs->findProgram($new_hash)->toArray();
					/*
					if (!empty($found)) {
						if ($found['hash']=='9a8298a407354469ac9a2e9a620da9b9') {
							var_dump($found);
							die(__FILE__.": ".__LINE__);
						}
					}
					*/
					$new = empty($found)? $info : $found ;
					$desc = trim((string)$xml->desc);
					if (!$descriptions_table->find($new['hash']) && !empty($desc)) {
						$desc = $programs->parseDescription($desc, $new['hash']);
						if (!empty($desc['intro'])) {
							$programs->saveDescription($desc, $new['hash']);
							$credits = $programs->getCredits((string)$xml->desc);
							if ($credits) {
								$programs->saveCredits($credits, $new);
							}
						}
						
					}
					//xdebug_stop_trace();
					//die(__FILE__.": ".__LINE__);
					$cnt++;
					//$progressBar->update($cnt);
				}
				
    		}
    		//$progressBar->finish();
    	}
	}
	
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
	
	
	private function _uploadXml(){
		
		/* Uploading Document File on Server */
        $upload = new Zend_File_Transfer_Adapter_Http();
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
        	
        	$filter = new Zend_Filter_Decompress(array(
        		'adapter'=>$type, 
        		'options'=>array(
        			'target'=>"$xml_dir/"
        	)));
        	$xmlfile = $filter->filter("$uploads/$nn");
        	
		} catch (Exception $e) {
			echo $e->getMessage();
		}
		
		$files = Xmltv_Filesystem_Folder::files($xml_dir, addslashes($fn).'\.xml');
		return"$xml_dir/".$files[0];
	}
	
	private function _getDateString($input=null){
		if(!$input) return;
		$date['year']      = substr($input, 0, 4);
		$date['month']     = substr($input, 4,2);
		$date['day']       = substr($input, 6,2);
		$date['hours']     = substr($input, 8,2);
		$date['minutes']   = substr($input, 10,2);
		$date['seconds']   = substr($input, 12,2);
		$date['gmt_diff']  = substr($input, 16,4);
		return $date['year'].'-'.$date['month'].'-'.$date['day'].' '.$date['hours'].':'.$date['minutes'].':'.$date['seconds'].' '.$date['gmt_diff'];
		
	}
	
	public function premieresSearchAction(){
    	
		ini_set('max_execution_time', 0);
    	ini_set('max_input_time', -1);
		
    	$response['success']=false;
    	$response['data']=null;
    	if ($this->_parseRequestValid()===true){
    		
    		$request = $this->_getAllParams();
	    	$xml_file = Xmltv_Filesystem_File::getName($this->_request->get('xml_file'));
    		$path     = Xmltv_Filesystem_File::getPath($this->_request->get('xml_file'));
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
    
    public function parsingProgressAction(){
    	$funcName = '_'.$this->_getParam('parse').'ParseProgress';
    	$this->$funcName();
    }
    
    private function _programsParseProgress(){
    	
    	$xml_file = Xmltv_Filesystem_File::getName($this->_request->get('xml'));
    	$path     = Xmltv_Filesystem_File::getPath($this->_request->get('xml'));
    	$total_count=0;
    	$cache = new Xmltv_Cache(array('cache_lifetime'=>7200));
    	$lock_path = ROOT_PATH.'/cache/'.$cache->getHash($xml_file).'.lock';
    	$hash = $cache->getHash(__METHOD__.$xml_file);
    	if (!$lock_file = @fopen($lock_path, 'r')){
    		
    		$fh = fopen($lock_path, 'w');
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
    		$total_count = $cache->load($hash);
    	}
    	/*
    	 * dates
    	 */
    	$parts = explode('.', $xml_file);
    	$parts = explode('-', $parts[0]);
    	$start = substr($parts[0], 4, 4) . '-' . substr($parts[0], 2, 2) . '-' . substr($parts[0], 0, 2);
    	$end   = substr($parts[1], 4, 4) . '-' . substr($parts[1], 2, 2) . '-' . substr($parts[1], 0, 2);
    	$weekStart = new Zend_Date($start, null, 'ru');
    	$weekEnd   = new Zend_Date($end, null, 'ru');
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
	
}





