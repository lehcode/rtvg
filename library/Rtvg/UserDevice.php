<?php

require_once APPLICATION_PATH . '/../library/WURFL/WURFL/Application.php';

class Rtvg_UserDevice{
    
    /**
     * @var WURFL_WURFLManager
     */
    private $manager;
    
    /**
     * @var WURFL_CustomDevice
     */
    public $userDevice;
    
    /**
     * @var string 
     */
    public $agentString;
    
    public function __construct()
    {
        $resourcesDir = realpath( APPLICATION_PATH.'/../library/WURFL/resources' );
        $persistenceDir = $resourcesDir.'/persistence';
        $cacheDir = $resourcesDir.'/cache';
        
        // Create WURFL Configuration
        $wurflConfig = new WURFL_Configuration_InMemoryConfig();
        
        // Set location of the WURFL File
        $wurflConfig->wurflFile($resourcesDir.'/wurfl-2.3.5.zip');
        
        // Set the match mode for the API ('performance' or 'accuracy')
        $wurflConfig->matchMode('accuracy');
        
        // Automatically reload the WURFL data if it changes
        $wurflConfig->allowReload(true);
        
        // Optionally specify which capabilities should be loaded
        $wurflConfig->capabilityFilter(array(
            "ajax_support_javascript",
            "ajax_support_getelementbyid",
            "ajax_xhr_type",
            "can_assign_phone_number",
            "device_os",
            "device_os_version",
            "is_tablet",
            "is_wireless_device",
            "mobile_browser",
            "mobile_browser_version",
            "pointing_method",
            "preferred_markup",
            "resolution_height",
            "resolution_width",
            "ux_full_desktop",
            "xhtml_support_level",
        ));
        
        // Setup WURFL Persistence
        $wurflConfig->persistence('file', array('dir' => $persistenceDir));
        
        // Setup Caching
        $wurflConfig->cache('file', array('dir' => $cacheDir, 'expiration' => 36000));

        // Create a WURFL Manager Factory from the WURFL Configuration
        $wurflManagerFactory = new WURFL_WURFLManagerFactory($wurflConfig);
        
        // Create a WURFL Manager
        $this->manager = $wurflManagerFactory->create();
        $agentString = $this->getUserAgentString();
        $this->userDevice = $this->manager->getDeviceForUserAgent($agentString);
        
    }
    
    public function getUserAgentString(){
        
        $httpAgent = new Zend_Http_UserAgent();
        $agentString = $httpAgent->getUserAgent();
        return $agentString;
        
    }
    
    public function getDeviceCapabilities(){
        
        return $this->userDevice->getAllCapabilities();
    }
    
}
?>
