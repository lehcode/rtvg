<?php
/**
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @version $Id: AdCode.php,v 1.4 2013-04-11 05:21:23 developer Exp $
 *
 */
class Rtvg_View_Helper_AdCode extends Zend_View_Helper_Abstract
{
    
    private $codes=array();
    private $width = 468;
    private $height = 60;
    private $type = 'cpa';
    private $output = 'random';
    private $amt=2;
    private $moduleclass='extras';
    
    /**
     * Get ad code
     * 
     * @param array $options
     * @return string
     */
	public function adCode($options = array()){
		
	    $this->width = (isset($options['width']) && !empty($options['width']) && is_int($options['width'])) ? $options['width'] : $this->width ;
	    $this->height = (isset($options['height']) && !empty($options['height']) && is_int($options['height'])) ? $options['height'] : $this->height ;
	    $this->type = (isset($options['type']) && !empty($options['type'])) ? $options['type'] : $this->type ;
	    $this->output = (isset($options['output']) && !empty($options['output'])) ? $options['output'] : $this->output ;
	    $this->amt = (isset($options['amt']) && !empty($options['amt']) && is_int($options['amt'])) ? $options['amt'] : null ;
	    $this->moduleclass = (isset($options['moduleclass']) && !empty($options['moduleclass']) && is_int($options['moduleclass'])) ? $options['moduleclass'] : $this->moduleclass ;
	    
	    $codes['wizards-world']['code']='<!-- admitad.banner: 8473ea334743631d2075c3fe9ec496 Wizards World -->
		<a target="_blank" rel="nofollow" href="http://ad.admitad.com/goto/8473ea334743631d2075c3fe9ec496/">
		<img width="300" height="250" border="0" src="http://ad.admitad.com/b/8473ea334743631d2075c3fe9ec496/" alt="Wizards World" />
		</a><!-- /admitad.banner -->';
		$codes['mts-class-smartphones']['code']='<!-- admitad.banner: 79544c6f7643631d20751ebfd6fcfa MTC  -->
		<a target="_blank" rel="nofollow" href="http://ad.admitad.com/goto/79544c6f7643631d20751ebfd6fcfa/">
		<img width="300" height="250" border="0" src="http://ad.admitad.com/b/79544c6f7643631d20751ebfd6fcfa/" alt="MTC " />
		</a><!-- /admitad.banner -->';
		$codes['urban-rivals-ru-1']['code'] = '<!-- admitad.banner: e71e4f518243631d2075e35220f679 Urban Rivals RU -->
		<a target="_blank" rel="nofollow" href="http://ad.admitad.com/goto/e71e4f518243631d2075e35220f679/">
		<img width="300" height="250" border="0" src="http://ad.admitad.com/b/e71e4f518243631d2075e35220f679/" alt="Urban Rivals RU" />
		</a><!-- /admitad.banner -->';
		$codes['urban-rivals-ru-2']['code'] = '<!-- admitad.banner: eadcad53b343631d2075e35220f679 Urban Rivals RU -->
		<a target="_blank" rel="nofollow" href="http://ad.admitad.com/goto/eadcad53b343631d2075e35220f679/">
		<img width="300" height="250" border="0" src="http://ad.admitad.com/b/eadcad53b343631d2075e35220f679/" alt="Urban Rivals RU" />
		</a><!-- /admitad.banner -->';
		$codes['bs-ru-1']['code'] = '<!-- admitad.banner: df1f1510ae43631d2075baff29a610 BS.ru -->
		<script type="text/javascript"> 
		try{(function(d,ad,s,ulp,subID,injectTo){ 
			/* Optional settings (these lines can be removed): */ 
		  ulp = "";  // - custom goto link;
		  subID = "";  // - local banner key; 
		  injectTo = "";  // - #id of html element (ex., "top-banner").
		var dInject="admitad"+ad+subID+Math.round(Math.random()*100000000);
		injectTo=="" && d.write(\'<div id="\'+dInject+\'"></div>\');
		s=s.replace("$",ad);s+="?inject="+(injectTo==""||!injectTo?dInject:injectTo);
		if(subID!="")s+="&subid="+subID;if(ulp!="")s+="&ulp="+escape(encodeURI(ulp)); 
		s=(("https:"==d.location.protocol)?"https":"http")+"://"+s;var j=d.createElement("script");
		j.type="text/javascript";j.src=s;(d.getElementsByTagName("head")[0]).appendChild(j);
		})(window.document,"df1f1510ae43631d2075baff29a610","ad.admitad.com/j/$/","","","");}catch(err){}
		</script>
		<noscript>
		<embed wmode="opaque" width="300" height="250" src="http://ad.admitad.com/f/df1f1510ae43631d2075baff29a610/" type="application/x-shockwave-flash">
		<noembed>
		<a target="_blank" rel="nofollow" href="http://ad.admitad.com/goto/df1f1510ae43631d2075baff29a610/">
		<img width="300" height="250" border="0" src="http://ad.admitad.com/b/df1f1510ae43631d2075baff29a610/" alt="BS.ru" />
		</a>
		</noembed>
		</noscript><!-- /admitad.banner -->';
		$codes['bs-ru-2']['code'] = '<!-- admitad.banner: e2c2d87c5043631d2075baff29a610 BS.ru -->
		<script type="text/javascript"> 
		try{(function(d,ad,s,ulp,subID,injectTo){ 
		  /* Optional settings (these lines can be removed): */ 
		  ulp = "";  // - custom goto link;
		  subID = "";  // - local banner key; 
		  injectTo = "";  // - #id of html element (ex., "top-banner").
		var dInject="admitad"+ad+subID+Math.round(Math.random()*100000000);
		injectTo=="" && d.write(\'<div id="\'+dInject+\'"></div>\');
		s=s.replace("$",ad);s+="?inject="+(injectTo==""||!injectTo?dInject:injectTo);
		if(subID!="")s+="&subid="+subID;if(ulp!="")s+="&ulp="+escape(encodeURI(ulp)); 
		s=(("https:"==d.location.protocol)?"https":"http")+"://"+s;var j=d.createElement("script");
		j.type="text/javascript";j.src=s;(d.getElementsByTagName("head")[0]).appendChild(j);
		})(window.document,"e2c2d87c5043631d2075baff29a610","ad.admitad.com/j/$/","","","");}catch(err){}
		</script>
		<noscript>
		<embed wmode="opaque" width="300" height="250" src="http://ad.admitad.com/f/e2c2d87c5043631d2075baff29a610/" type="application/x-shockwave-flash">
		<noembed>
		<a target="_blank" rel="nofollow" href="http://ad.admitad.com/goto/e2c2d87c5043631d2075baff29a610/">
		<img width="300" height="250" border="0" src="http://ad.admitad.com/b/e2c2d87c5043631d2075baff29a610/" alt="BS.ru" />
		</a>
		</noembed>
		</noscript><!-- /admitad.banner -->';
		$codes['bs-ru-3']['code'] = '<!-- admitad.banner: 1a68d1606143631d2075baff29a610 BS.ru -->
		<script type="text/javascript"> 
		try{(function(d,ad,s,ulp,subID,injectTo){ 
		  /* Optional settings (these lines can be removed): */ 
		  ulp = "";  // - custom goto link;
		  subID = "";  // - local banner key; 
		  injectTo = "";  // - #id of html element (ex., "top-banner").
		var dInject="admitad"+ad+subID+Math.round(Math.random()*100000000);
		injectTo=="" && d.write(\'<div id="\'+dInject+\'"></div>\');
		s=s.replace("$",ad);s+="?inject="+(injectTo==""||!injectTo?dInject:injectTo);
		if(subID!="")s+="&subid="+subID;if(ulp!="")s+="&ulp="+escape(encodeURI(ulp)); 
		s=(("https:"==d.location.protocol)?"https":"http")+"://"+s;var j=d.createElement("script");
		j.type="text/javascript";j.src=s;(d.getElementsByTagName("head")[0]).appendChild(j);
		})(window.document,"1a68d1606143631d2075baff29a610","ad.admitad.com/j/$/","","","");}catch(err){}
		</script>
		<noscript>
		<embed wmode="opaque" width="300" height="250" src="http://ad.admitad.com/f/1a68d1606143631d2075baff29a610/" type="application/x-shockwave-flash">
		<noembed>
		<a target="_blank" rel="nofollow" href="http://ad.admitad.com/goto/1a68d1606143631d2075baff29a610/">
		<img width="300" height="250" border="0" src="http://ad.admitad.com/b/1a68d1606143631d2075baff29a610/" alt="BS.ru" />
		</a>
		</noembed>
		</noscript>
		<!-- /admitad.banner -->';
		
		
		switch ($this->output){
			default:
			case 'random':
			    $keys = array_keys($codes);
			    $rand = mt_rand(0, count($keys)-1);
			    $idx  = $keys[$rand];
			    $html = '<div class="module '.$this->moduleclass.'">'.$codes[$idx]['code'].'</div>';
			break;
			
			case 'stack';
				$amt = $this->amt;
				if ($amt>0){
					$html='';
					do {
					    $keys = array_keys($codes);
					    $rand = mt_rand( 0, count($keys)-1 );
					    $idx  = $keys[$rand];
					    $html .= '<div class="module '.$this->moduleclass.'">'.$codes[$idx]['code'].'</div>'.PHP_EOL;
					    $amt-=1;
					} while ($amt > 0);
					
					return $html;
				}
			break;
		}
		
		
	}
	
}