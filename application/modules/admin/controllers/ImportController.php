<?php

/**
 * Backend import controller
 * 
 * @author     Antony Repin <egeshisolutions@gmail.com>
 * @package backend
 * @version    $Id: ImportController.php,v 1.27 2013-04-10 01:55:58 developer Exp $
 *
 */
class Admin_ImportController extends Rtvg_Controller_Admin
{

    private $_progressBar;
    private $_lockFile;
    private $_parseFolder = '/uploads/parse/';
    protected $channelsList;
    protected $programsCategoriesList;

    /**
     * 
     * Validator
     * @var Xmltv_Controller_Action_Helper_RequestValidator
     */
    private $_teleguideUrl = 'http://www.teleguide.info/download/new3/xmltv.xml.gz';
    private $_xmlFolder;

    const DEFAULT_ICON = 'default.gif';

    /**
     * 
     * @var Admin_Model_Broadcasts
     */
    private $broadcasts;

    /**
     * @see Zend_Controller_Action::init()
     */
    public function init()
    {

        parent::init();
        $this->broadcasts = new Admin_Model_Broadcasts();
        $this->_xmlFolder = APPLICATION_PATH . '/../uploads/parse/';
    }

    /**
     * 
     * Index page
     */
    public function indexAction()
    {

        parent::validateRequest();

        $form = new Xmltv_Form_UploadForm();
        $this->view->form = $form;
        if ($this->_request->isPost()) {
            $formData = $this->_request->getPost();
            if ($form->isValid($formData)) {
                $xmltv_file = $this->_uploadXml();
                $this->view->assign('file_info', array(
                    'filename' => $xmltv_file,
                    'filesize' => filesize($xmltv_file))
                );
                $this->render('xml');
            }
        }
    }

    /**
     * Ajax action which handles channels parsing
     */
    public function xmlParseChannels($xml_file = null)
    {

        ini_set('max_execution_time', 0);
        ini_set('max_input_time', -1);

        parent::validateRequest();

        /*
         * Check if XML file exists
         */
        if (!$xml_file) {
            $xml_file = Xmltv_Filesystem_File::getName($this->_getParam('xml_file'));
            $path = Xmltv_Filesystem_File::getPath($this->_getParam('xml_file'));
        } else {
            $xml_file = Xmltv_Filesystem_File::getName($xml_file);
            $path = APPLICATION_PATH . '/..' . $this->_parseFolder;
        }

        if (!is_file($xml_file = $path . $xml_file)) {
            throw new Zend_Exception("XML file not found!");
        }

        /*
         * Load and process XML data
         */
        $xml = new DOMDocument();
        $xml->preserveWhiteSpace = false;
        $xml->formatOutput = true;
        if (!$xml->load($xml_file)) {
            throw new Zend_Exception("Cannot load XML from " . $xml_file . '!');
        }

        $channelsTable = new Admin_Model_DbTable_Channels();
        $newChannels = array();
        $updatedChannels = array();
        $channelsModel = new Admin_Model_Channels();
        $channelsRows = $channelsTable->fetchAll()->toArray();
        $channelsMap = $channelsModel->getChannelMap();
        foreach ($xml->getElementsByTagName('channel') as $item) {

            $channel = array();
            $channel['id'] = (int) $item->getAttribute('id');
            $name = $item->getElementsByTagName('display-name');
            $channel['lang'] = $name->item(0)->getAttribute('lang');

            // Title
            $channel['title'] = $name->item(0)->nodeValue;
            if (Xmltv_String::stristr(Xmltv_String::strtolower($channel['title']), 'канал')) {
                $channel['title'] = Xmltv_String::str_ireplace(array('канал. ', 'канал.', 'канал ', 'канал'), '', $channel['title']);
            }
            $channel['title'] = trim($channel['title']);

            if ((bool) $item->getElementsByTagName('icon')->item(0) !== false) {
                $iconOriginal = $item->getElementsByTagName('icon')->item(0)->getAttribute('src');
                $icon = $iconOriginal;
                $icon = Xmltv_String::substr_replace($icon, '', 0, Xmltv_String::strrpos($icon, '/') + 1);
                $icon = Xmltv_String::substr_replace($icon, '.png', Xmltv_String::strrpos($icon, '.'));
                $channel['icon'] = $icon;
            } else {
                $channel['icon'] = 'default.png';
            }

            $channelsTitles = array();
            foreach ($channelsRows as $row) {
                $channelsTitles[] = $row['title'];
            }

            if (($mapped = $this->_mapChannel($channel['title'], $channelsTitles, $channelsMap)) !== false) {
                $channel['title'] = $mapped;
            }

            //Generate channel alias
            $toDash = new Xmltv_Filter_SeparatorToDash();
            $channel['alias'] = $toDash->filter($channel['title']);
            $plusToPlus = new Zend_Filter_Word_SeparatorToSeparator('+', '-плюс-');
            $channel['alias'] = $plusToPlus->filter($channel['alias']);
            $channel['alias'] = str_replace('--', '-', trim($channel ['alias'], ' -'));

            if ($channelsTable->find($channel['id'])->count()==0) {

                if ((bool)$channelsTable->fetchRow("`alias` = '" . $channel['alias'] . "' OR `title` LIKE '" . $channel['title'] . "'")===false) {
                    //Save if new
                    try {
                        $channelsTable->insert($channel);
                        $newChannels[] = $channel;
                        $this->sendEmail("Added new channel", "Channel data: ". print_r($channel));
                    } catch (Zend_Db_Statement_Exception $e) {
                        if ($e->getCode() != 1062) {
                            throw new Zend_Exception($e->getMessage(), $e->getCode(), $e);
                        }
                    }
                }

                $allChannels[] = $channel; //for debugging
            } else {

                $pngFile = APPLICATION_PATH . '/../public/images/channel_logo/' . $channel['icon'];
                $bigPngFile = APPLICATION_PATH . '/../public/images/channel_logo/100/' . $channel['icon'];

                if (!file_exists($pngFile) || !file_exists($bigPngFile)) {

                    $gifIcon = Xmltv_String::substr_replace($iconOriginal, '', 0, Xmltv_String::strrpos($iconOriginal, '/') + 1);
                    $gifFile = realpath(APPLICATION_PATH . '/../tmp/') . $gifIcon;
                    $curl = new Zend_Http_Client_Adapter_Curl();
                    $curl->setCurlOption(CURLOPT_HEADER, false);
                    $curl->setCurlOption(CURLOPT_RETURNTRANSFER, 1);
                    $curl->setCurlOption(CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
                    $curl->setCurlOption(CURLOPT_HTTPHEADER, array('Content-type: image/gif'));
                    $client = new Zend_Http_Client($iconOriginal);
                    $client->setAdapter($curl);

                    if (!file_exists($bigPngFile)) {
                        file_put_contents($gifFile, $client->request("GET")->getBody());
                        file_put_contents($bigPngFile, $this->_helper->getHelper('imageToPng')->imageToPng($gifFile, array(
                                'tmp_folder' => APPLICATION_PATH . '/../tmp',
                                'max_size' => 100)));
                    }

                    if (!file_exists($pngFile)) {
                        file_put_contents($gifFile, $client->request("GET")->getBody());
                        file_put_contents($pngFile, $this->_helper->getHelper('imageToPng')->imageToPng($gifFile, array(
                                'tmp_folder' => APPLICATION_PATH . '/../tmp',
                                'max_size' => 45)));
                    }

                    $channelsTable->update($channel, "`id`='" . $channel['id'] . "'");

                    unlink($gifFile);
                    $allChannels[] = $channel;
                }
            }
        }
    }

    /**
     * 
     * Ajax action which handles programs parsing
     */
    public function xmlParsePrograms($xml_file = null)
    {

        ini_set('max_execution_time', 0);
        ini_set('max_input_time', -1);

        /*
         * Check if XML file exists
         */
        if (!$xml_file) {
            $file = Xmltv_Filesystem_File::getName($this->_request->get('xml_file'));
            $path = Xmltv_Filesystem_File::getPath($this->_request->get('xml_file'));
        } else {
            $file = Xmltv_Filesystem_File::getName($xml_file);
            $path = APPLICATION_PATH . '/..' . $this->_parseFolder;
        }
        if (!is_file($file = $path . $file)) {
            throw new Zend_Exception("XML file not found!");
        }

        /*
         * Load and process XML data
         */
        $xml = new DOMDocument();
        if (!$xml->load($file)) {
            throw new Exception("Cannot load XML!");
        }

        $programs = $xml->getElementsByTagName('programme');
        $broadcasts = new Admin_Model_Broadcasts();
        $eventsModel = new Xmltv_Model_Event();
        $c = 0;
        foreach ($programs as $node) {

            $bc = new stdClass();
            $evt = $eventsModel->create();

            //Process program title and detect some properties
            $category = $node->getElementsByTagName('category')->item(0)->nodeValue;
            if ($category) {
                switch (Xmltv_String::strtolower($category)) {
                    case 'спорт':
                        $parsed = $broadcasts->parseSportsTitle(array('title' => trim($node->getElementsByTagName('title')->item(0)->nodeValue, '. ')));
                        break;
                    default:
                        $parsed = $broadcasts->parseTitle(trim($node->getElementsByTagName('title')->item(0)->nodeValue, '. '));
                        break;
                }
            }

            //var_dump($parsed);
            //die(__FILE__ . ': ' . __LINE__);

            $bc->title = $parsed['title'];
            $bc->sub_title = $parsed['sub_title'];
            $bc->age_rating = (@$parsed['rating'] > 0) ? $parsed['rating'] : 0;
            $evt->premiere = (@$parsed['premiere'] == 1) ? '1' : null;
            $evt->live = (@$parsed['live'] === true) ? '1' : null;
            $bc->episode_num = (@$parsed['episode'] > 0) ? (int) $parsed['episode'] : null;

            // Detect category ID
            $bc->category = (@$parsed['category'] > 0) ? $parsed['category'] : $node->getElementsByTagName('category')->item(0)->nodeValue;
            if (!is_numeric($bc->category)) {
                $bc->category = $broadcasts->catIdFromTitle($bc->category);
            }

            //Parse description
            if (@$node->getElementsByTagName('desc')->item(0)) {

                $parseDesc = $broadcasts->parseDescription($node->getElementsByTagName('desc')->item(0)->nodeValue);

                $bc->title = isset($parseDesc['title']) && !empty($parseDesc['title']) ? $bc->title . ' ' . $parseDesc['title'] : $bc->title;
                $bc->desc = isset($parseDesc['text']) ? $parseDesc['text'] : '';

                if (!empty($parseDesc['actors'])) {
                    if (is_array($parseDesc['actors'])) {
                        $bc->actors = implode(',', $parseDesc['actors']);
                    } elseif (is_numeric($parseDesc['actors'])) {
                        $bc->actors = $parseDesc['actors'];
                    } elseif (stristr($parseDesc['actors'], ',')) {
                        $bc->actors = $parseDesc['actors'];
                    } else {
                        Zend_Debug::dump($parseDesc['actors']);
                        die(__FILE__ . ': ' . __LINE__);
                    }
                }

                if (!empty($parseDesc['directors'])) {
                    if (is_array($parseDesc['directors'])) {
                        $bc->directors = implode(',', $parseDesc['directors']);
                    } elseif (is_numeric($parseDesc['directors'])) {
                        $bc->directors = $parseDesc['directors'];
                    } elseif (stristr($parseDesc['directors'], ',')) {
                        $bc->directors = $parseDesc['directors'];
                    } else {
                        Zend_Debug::dump($parseDesc['directors']);
                        die(__FILE__ . ': ' . __LINE__);
                    }
                }

                $bc->writers = (isset($parseDesc['writers']) && (bool) $parseDesc['writers'] !== false ) ? implode(',', $parseDesc['writers']) : '';
                $bc->country = (isset($parseDesc['country']) && (bool) $parseDesc['country'] !== false) ? $parseDesc['country'] : 'na';
                $bc->date = isset($parseDesc['year']) ? $parseDesc['year'] : null;
                $bc->episode_num = isset($parseDesc['episode']) && (int) $bc->episode_num == 0 ? (int) $parseDesc['episode'] : $bc->episode_num;
                $bc->category = isset($parseDesc['category']) && (bool) $bc->category === false ? $parseDesc['category'] : $bc->category;
            } else {
                $bc->country = 'na';
            }

            // Alias
            $bc->alias = $broadcasts->makeAlias($bc->title);

            //Channel
            $evt->channel = (int) $node->getAttribute('channel');
            // MGM HD has duplicate channel ID in XML
            if ($evt->channel == 1422) {
                $evt->channel = 400018;
            }

            /*
             * Fix split title for particular channels mostly movies
             */
            $splitTitles = array(100037);
            if (in_array($evt->channel, $splitTitles) && Xmltv_String::strlen($bc->sub_title)) {
                $bc->title .= ' ' . $bc->sub_title;
                $bc->sub_title = '';
            }

            // Start and end datetime
            $start = $broadcasts->rfcToZendDate($node->getAttribute('start'));
            $end = $broadcasts->rfcToZendDate($node->getAttribute('stop'));
            $evt->start = $start->toString("YYYY-MM-dd HH:mm") . ':00';
            $evt->end = $end->toString("YYYY-MM-dd HH:mm") . ':00';

            // Calculate hash
            $bc->hash = $this->broadcasts->getBroadcastHash($bc);
            $evt->hash = $bc->hash;


            /* if ($c<50){
              Zend_Debug::dump($bc);
              $c++;
              } else {
              die(__FILE__ . ': ' . __LINE__);
              } */


            // Save records
            try {
                $broadcasts->create($bc);
            } catch (Zend_Db_Table_Row_Exception $e) {
                echo $e->getMessage() . '<br />';
                Zend_Debug::dump($e->getPrevious());
                Zend_Debug::dump($bc->toArray());
                die("Broadcast save failed!");
            }


            if (isset($evt->channel) && !empty($evt->channel)) {
                try {
                    $evt->save();
                } catch (Zend_Exception $e) {
                    echo $e->getMessage() . '<br />';
                    Zend_Debug::dump($e->getPrevious());
                    Zend_Debug::dump($bc);
                    Zend_Debug::dump($evt->toArray());
                    die("Event save failed!");
                }
            }
        }

        $response['success'] = true;
        $this->view->assign('response', $response);

        $last_file = APPLICATION_PATH . '/../uploads/parse/listings.xml.last';
        system('mv ' . $last_file . ' ' . $xml_file . '.old');
        system('mv ' . $xml_file . ' ' . $xml_file . '.last');
    }
    
    /**
     * 
     * @param type $msg
     */
    protected function sendEmail($subj=null, $msg = null)
    {

        $senderEmail = 'dev@egeshi.com';
        $mail = new Zend_Mail('UTF-8');
        $mail->setBodyText($msg);
        $mail->setFrom($senderEmail, 'Rutvgid Error');
        $mail->addTo('egeshisolutions@gmail.com', 'Admin');
        $mail->setSubject($subj);

        if (APPLICATION_ENV == 'production') {
            $t = new Zend_Mail_Transport_Smtp('smtp.gmail.com', array(
                'auth' => 'login',
                'ssl' => 'ssl',
                'port' => 465,
                'username' => $senderEmail,
                'password' => '3k2mzE9bE2iheEMi9RqcVu5t'
            ));
        } else {
            $t = new Zend_Mail_Transport_File(array('path' => APPLICATION_PATH . '/../mail'));
        }

        //Send
        try {
            $mail->send($t);
        } catch (Zend_Mail_Exception $e) {
            $this->logMessage($e->getMessage(), Zend_log::CRIT);
        }
    }

    
    /**
     * 
     * @return type
     * @throws Exception
     */
    private function _uploadXml()
    {

        /* Uploading Document File on Server */
        $upload = new Zend_File_Transfer_Adapter_Http();

        $path = APPLICATION_PATH . "/../uploads/xmltv/";
        $upload->setDestination($path);
        try {
            $upload->receive();
        } catch (Zend_File_Transfer_Exception $e) {
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
        $size = (int) $upload->getFileSize();
        $mimeType = $upload->getMimeType();
        $fn = Xmltv_Parser_FilenameParser::getXmlFileName($name);
        $ext = Xmltv_Parser_FilenameParser::getExt($name);
        preg_match('/^application\/(.+)$/', $mimeType, $m);
        $type = $m[1];

        try {

            $uploads = $upload->getDestination();
            $nn = md5($fn . time()) . '.' . $type;
            if (copy($name, "$uploads/$nn") === false)
                throw new Exception("Cannot copy file");

            $path = APPLICATION_PATH . "/../uploads/parse/";
            $xml_dir = $path . $fn;
            if (!is_dir($xml_dir)) {
                if (!mkdir($xml_dir))
                    throw new Exception("Cannot create directory");
            }

            $decompress = new Zend_Filter_Decompress(array(
                'adapter' => $type,
                'options' => array(
                    'target' => "$xml_dir/"
            )));
            $xmlfile = $decompress->filter("$uploads/$nn");
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        $files = Xmltv_Filesystem_Folder::files($xml_dir, addslashes($fn) . '\.xml');
        return"$xml_dir/" . $files[0];
    }

    private function _getDateString($input = null)
    {
        if (!$input)
            return;
        $date['year'] = substr($input, 0, 4);
        $date['month'] = substr($input, 4, 2);
        $date['day'] = substr($input, 6, 2);
        $date['hours'] = substr($input, 8, 2);
        $date['minutes'] = substr($input, 10, 2);
        $date['seconds'] = substr($input, 12, 2);
        $date['gmt_diff'] = substr($input, 16, 4);
        return $date['year'] . '-' . $date['month'] . '-' . $date['day'] . ' ' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'] . ' ' . $date['gmt_diff'];
    }

    /**
     * 
     * Progress of parsing
     */
    public function parsingProgressAction()
    {
        $funcName = '_' . $this->_getParam('parse') . 'ParseProgress';
        $this->$funcName();
    }

    /**
     * 
     * Progress of parsing programs
     */
    private function _programsParseProgress()
    {

        die(__FILE__ . ': ' . __LINE__);

        $xml_file = Xmltv_Filesystem_File::getName($this->_request->get('xml'));
        $path = Xmltv_Filesystem_File::getPath($this->_request->get('xml'));

        $this->_lockFile = APPLICATION_PATH . '/../cache/' . $cache->getHash($xml_file) . '.lock';

        $hash = $cache->getHash(__METHOD__ . $xml_file);
        if (!($locked = @fopen($this->_lockFile, 'r'))) {

            $fh = fopen($this->_lockFile, 'w');
            fwrite($fh, time());
            fclose($fh);

            if (!$tc = $cache->load($hash)) {

                $nodeName = 'programme';
                $file = new Xmltv_XmlChunk($xml_file, array(
                    'chunkSize' => 24000,
                    'path' => $path,
                    'element' => $nodeName));

                while ($xml = $file->read()) {
                    preg_match('/(<' . $nodeName . ' start="[0-9]{14} \+[0-9]{4}" stop="[0-9]{14} \+[0-9]{4}" channel="[0-9]+">.+<\/' . $nodeName . '>).*$/imsU', $xml, $m);
                    if (isset($m [1]) && !empty($m[1]))
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

        //dates
        $parts = explode('.', $xml_file);
        $parts = explode('-', $parts[0]);
        $start = substr($parts[0], 4, 4) . '-' . substr($parts[0], 2, 2) . '-' . substr($parts[0], 0, 2);
        $end = substr($parts[1], 4, 4) . '-' . substr($parts[1], 2, 2) . '-' . substr($parts[1], 0, 2);
        $weekStart = new Zend_Date($start);
        $weekEnd = new Zend_Date($end);

        //Get programs count
        $programs = new Admin_Model_Broadcasts();
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
    public function listingsAction()
    {

        $site = $this->_getParam('site', 'teleguide');
        switch ($site) {
            case 'teleguide':

                $gzFile = $this->_xmlFolder . 'current.xml.gz';
                $xmlFile = Xmltv_String::substr_replace($gzFile, '', Xmltv_String::strlen($gzFile) - 3);

                if (!file_exists($gzFile) && !file_exists($xmlFile)) {

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

                if (file_exists($gzFile) && !file_exists($xmlFile)) {
                    system("gzip -d $gzFile");
                }

                if (!file_exists($xmlFile)) {
                    throw new Exception("Error downloading XML from $site!");
                }

                $this->xmlParseChannels($xmlFile);
                $this->xmlParsePrograms($xmlFile);

                system("mv $xmlFile $xmlFile.last");

                break;

            default:
                break;
        }

        echo "Готово!";
        die();
    }

    /**
     * Map some changed titles to existing ones
     * 
     * @param array $siteChannels
     * @throws Zend_Exception
     * @return string|boolean
     */
    private function _mapChannel($search, array $titles = array(), array $map = array())
    {

        if (array_key_exists($search, $map)) {
            return $map[$search];
        } else {
            foreach ($titles as $ch) {
                if (Xmltv_String::strtolower($ch) == Xmltv_String::strtolower($search)) {
                    return $ch;
                }
            }
        }
        return false;
    }

}
