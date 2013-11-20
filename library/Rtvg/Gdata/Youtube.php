<?php
class Rtvg_Gdata_Youtube extends Zend_Gdata_YouTube
{
    /**
     * Create Zend_Gdata_YouTube object
     *
     * @param Zend_Http_Client $client (optional) The HTTP client to use when
     *          when communicating with the Google servers.
     * @param string $applicationId The identity of the app in the form of
     *        Company-AppName-Version
     * @param string $clientId The clientId issued by the YouTube dashboard
     * @param string $developerKey The developerKey issued by the YouTube dashboard
     */
    public function __construct($client = null,
        $applicationId = 'Egeshi-RTVG-1.0', $clientId = null,
        $developerKey = null) {
        parent::__construct($client, $applicationId);
    }
    
    /**
     * Retrieves a specific video entry.
     *
     * @param mixed $videoId The ID of the video to retrieve.
     * @param mixed $location (optional) The URL to query or a
     *         Zend_Gdata_Query object from which a URL can be determined.
     * @param boolean $fullEntry (optional) Retrieve the full metadata for the
     *         entry. Only possible if entry belongs to currently authenticated
     *         user. An exception will be thrown otherwise.
     * @throws Zend_Gdata_App_HttpException
     * @return Zend_Gdata_YouTube_VideoEntry The video entry found at the
     *         specified URL.
     */
    public function getVideoEntry($videoId = null, $location = null,
        $fullEntry = false)
    {
        
        if (!$videoId) {
			throw new Zend_Exception("YT ID is required");
		}
        if (is_numeric($videoId)){
            throw new Zend_Exception("YT ID cannot be digit");
        }
        
        if ($videoId !== null) {
            if ($fullEntry) {
                return $this->getFullVideoEntry($videoId);
            } else {
                $uri = self::VIDEO_URI . "/" . $videoId;
            }
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl($this->getMajorProtocolVersion());
        } else {
            $uri = $location;
        }
        
        try{
            $r = parent::getEntry($uri, 'Zend_Gdata_YouTube_VideoEntry');
        } catch (Exception $e){
            if (stristr($e->getMessage(), 'Private video')){
                return 403;
            }
            if (stristr($e->getMessage(), 'Video not found')){
                return 404;
            }
            throw new Zend_Exception($e->getMessage(), 500);
        }
        
        return $r;
        
    }
    
}
?>
