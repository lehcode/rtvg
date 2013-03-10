<?php
/**
 * Search methods
 *
 * @author  Antony Repin
 * @uses    Xmltv_Controller_Action
 * @version $Id: Search.php,v 1.1 2013-03-10 02:45:15 developer Exp $
 *
 */
class Xmltv_Model_Search
{
    
    /**
     * Get Google search results
     * 
     * @param string $search
     */
	public function searchGoogle( $search=null ){
		
	    $url  = 'http://torrent-poisk.com/search.php?q='.urlencode( $channel['title'] ).'&r=0&qsrv='.urlencode( $channel['title'] );
	    $curl = new Xmltv_Parser_Curl();
	    $curl->setOption(CURLOPT_CONNECTTIMEOUT, 4);
	    $curl->setOption(CURLOPT_TIMEOUT, 4);
	    $curl->setUrl($url);
	    //$curl->setUserAgent();
	    $result = $curl->fetch(Xmltv_Parser_Curl::PAGE_HTML);
	}
}