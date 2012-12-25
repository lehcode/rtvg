<?php
class Zend_View_Helper_ThirdPartyFeed extends Zend_View_Helper_Abstract
{
	public function thirdPartyFeed($url='', $config=array()) {
		
		$clinet_config = array(
			'adapter'=>'Zend_Http_Client_Adapter_Curl',
			'curloptions'=>array(
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_PROXY => Xmltv_Config::getProxyHost(),
				CURLOPT_PROXYPORT => Xmltv_Config::getProxyPort(),
			)
		);
		$client = new Zend_Http_Client('', $config);
		$feed   = new Zend_Feed();
		$feed->setHttpClient($client);
		$feedArray = Zend_Feed::findFeeds('http://tvoikinomir.ru/category/novosti/kinonovosti/');
		//var_dump($feedArray);
		die(__FILE__.': '.__LINE__);
		
	}
}