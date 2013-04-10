<?php
class Rtvg_Converter_CountryRuToIso {
    
	/**
	 * Convert russian country name to ISO format
	 * 
	 * @param  string $string
	 * @throws Zend_Exception
	 * @return string
	 */
	public static function convert($country=null){
	    
	    $countriesList = array(
	    		'Австралия'=>'au',
	    		'Аргентина'=>'ar',
	    		'Бельгия'=>'be',
	    		'Великобритания'=>'gb',
	    		'Германия'=>'de',
	    		'Дания'=>'dk',
	    		'Индия'=>'in',
	    		'Индонезия'=>'id',
	    		'Испания'=>'es',
	    		'Ирландия'=>'ie',
	    		'Италия'=>'it',
	    		'Канада'=>'ca',
	    		'Китай'=>'cn',
	    		'Мексика'=>'mx',
	    		'Нидерланды'=>'nl',
	    		'Россия'=>'ru',
	    		'США'=>'us',
	    		'Украина'=>'ua',
	    		'Финляндия'=>'fi',
	    		'Франция'=>'fr',
	    		'Южная Корея'=>'kp',
	    		'Япония'=>'jp',
	    );
	    
	    if (!$country){
	        throw new Zend_Exception( Rtvg_Message::ERR_MISSING_PARAM );
	    }
	    $string = (string)$country;
		if( stristr($string, '-')){
	    	$country = preg_replace('/(\s-\s)/ui', '-', $string);
	    	$ex = explode( '-', $country);
	    	$c=array();
	    	foreach ($ex as $e){
	    		$key = trim($e);
	    		if ( array_key_exists( $key, $countriesList)){
	    			$result['country'][] = $countriesList[$key];
	    		}
	    	}
	    	if ($result && is_array($result)){
	    		$result = implode(',', $result['country']);
	    		return $result;
	    	} 
	    	return null;
	    } elseif (stristr($string, ',')){
	        $country = preg_replace('/(\s-\s)/ui', ',', $string);
	        $ex = explode( ',', $country);
	        $c  = array();
	        foreach ($ex as $e){
	        	$key = trim($e);
	        	if ( array_key_exists( $key, $countriesList)){
	        		$result['country'][] = $countriesList[$key];
	        	}
	        }
	        if ($result && is_array($result)){
	        	$result = implode(',', $result['country']);
	        	return $result;
	        }
	        return null;
	    } else {
	    	$country = $string;
	    	if ( array_key_exists( $country, $countriesList)){
	    		return $countriesList[$country];
	    	}
	    }
	    
	}
    
}