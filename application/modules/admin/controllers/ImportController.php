<?php
class Admin_ImportController extends Zend_Controller_Action
{
	
	private $_debug = false;
	
    public function init()
    {
        $this->_helper->layout->setLayout('admin');
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
		$ajaxContext->addActionContext('xmlparsechannels', 'json')
			->addActionContext('xmlparseprograms', 'json')
			->initContext();
		$this->view->setScriptPath(APPLICATION_PATH . 
					'/modules/admin/views/scripts/');
		
		$config = $this->_getConfig('site');
		$this->_debug = (int)$config->site->debug;
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

    
    
	public function xmlparseprogramsAction(){
		
		ini_set('max_execution_time', 0);
    	ini_set('max_input_time', -1);
		
		//var_dump($this->_debug);
    	//die();
    	
    	if ($this->_parseRequestValid()===true){
    		
    		$xml_file = Xmltv_Filesystem_File::getName($this->_request->get('xml_file_pr'));
    		$path     = Xmltv_Filesystem_File::getPath($this->_request->get('xml_file_pr'));
    		$nodeName = 'programme';
    		$file = new Xmltv_XmlChunk($xml_file, array(
    			'chunkSize'=>24000,
    			'path'=>$path,
    			'element'=>$nodeName));
    		$programs     = new Xmltv_Model_DbTable_Programs();
    		$descriptions = new Xmltv_Model_DbTable_ProgramsDescriptions();
    		$programs_model = new Admin_Model_Programs();
    		//die(__FILE__.": ".__LINE__);
    		
    		$cnt=0;
    		while ( $xml = $file->read () ) {
    			
    			preg_match ( '/(<'.$nodeName.' start="[0-9]{14} \+[0-9]{4}" stop="[0-9]{14} \+[0-9]{4}" channel="[0-9]+">.+<\/' . $nodeName . '>).*$/imsU', $xml, $m );
				if (@isset ( $m [1] )) {
					
					//if ($cnt==500)
					//die(__FILE__.": ".__LINE__);
					//else 
					//$cnt++;
					
					$xml   = new SimpleXMLElement ( $m [1] );
					$info  = array();
					$attrs = $xml->attributes ();
					$d = (string)$attrs->start;
					$f = Zend_Date::YEAR."-".Zend_Date::MONTH."-".Zend_Date::DAY." ".
						Zend_Date::HOUR.':'.Zend_Date::MINUTE.':'.Zend_Date::SECOND.' '.Zend_Date::GMT_DIFF;
					$date_str = $this->_getDateString($d);
					$dates['start'] = new Zend_Date($date_str, $f, 'ru');
					$d = (string)$attrs->stop;
					$date_str = $this->_getDateString($d);
					$dates['end'] = new Zend_Date($date_str, $f, 'ru');
					$date_mysql  = "yyyy-MM-dd HH:mm:ss";
					$info ['start'] = $dates['start']->toString($date_mysql);
					$info ['end']   = $dates['end']->toString($date_mysql);
					
					$info ['ch_id'] = (int)$attrs->channel;
					
					$cat_title = @isset($xml->category) ? (string)$xml->category : 0 ;
					$info = $programs_model->setProgramCategory($info, $cat_title);
					
					$info ['hash']  = $programs_model->getHash ( (int)$attrs->channel, $info ['start'], $info ['end'] );
					
					$info ['title'] = ( string ) $xml->title;
					$info = $programs_model->makeTitles ( $info );
					
					$info ['alias'] = $programs_model->makeProgramAlias ( $info ['title'] );
					
					try {
						$info['alias'] = str_replace('--', '-', $info['alias']);
						$new_hash = $programs->insert($info);
					} catch(Exception $e) {
						if ($e->getCode()==1062) {
							continue;
						} else {
							echo $e->getMessage();
							die(__FILE__.": ".__LINE__);
						}
					}
					$new = $programs->find($new_hash)->current()->toArray();
					
					/*
					 * описание
					 */
					$desc = @isset($xml->desc) ? $programs_model->getDescription((string)$xml->desc) : null ;
					if ($desc && !empty($desc)) {
						$credits = $programs_model->getCredits((string)$xml->desc);
						$programs_model->saveCredits($credits, $new);
						$programs_model->saveDescription((string)$xml->desc, $new['alias']);
					}
					
					
					$tolower = new Zend_Filter_StringToLower();
					if (Xmltv_String::stristr($tolower->filter($info['title']), 'премьера')) {
						$programs_model->savePremiere($new);
					}
					
				}
    		}
    		
    		//die(__FILE__.": ".__LINE__);
    	}
    	
		die(__FILE__.": ".__LINE__);
		
	}
	
	private function _parseRequestValid(){
		$filters = array(
    		'module'=>'StringTrim',
    		'controller'=>'StringTrim',
    		'action'=>'StringTrim',
    		'xml_file_ch'=>'StringTrim',
    		'xml_file_pr'=>'StringTrim'
    	);
    	$validators = array(
    		'module'=>array(
    			new Zend_Validate_Regex('/^[a-z]+$/u')),
    		'controller'=>array(
    			new Zend_Validate_Regex('/^[a-z]+$/')),
    		'action'=>array(
    			new Zend_Validate_Regex('/^[a-z]+$/')),
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
	
	private function _getConfig($type='application', $mode='development'){
		
		if ($type=='site')
		return new Zend_Config_Ini(APPLICATION_PATH.'/configs/site.ini', $mode);
		
		return new Zend_Config_Ini(APPLICATION_PATH.'/configs/application.ini', $mode);
	
	}
	
	
	
}





