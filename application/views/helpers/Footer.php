<?php

/**
 * Site header class
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: Footer.php,v 1.2 2012-05-30 21:46:59 dev Exp $
 *
 */
class Zend_View_Helper_Footer extends Zend_View_Helper_Abstract
{


	public function footer () {
		
		ob_start();
		?>
		<div class="span12" id="footer">
			<div class="counter_logo">
				<!--LiveInternet logo-->
				<a href="http://www.liveinternet.ru/click" target="_blank"><img src="//counter.yadro.ru/logo?52.3" title="LiveInternet: number of pageviews and visitors for 24 hours is shown" alt="" border="0" width="88" height="31"/></a>
				<!--/LiveInternet-->
			</div>
			<div class="copy">
				<h4><a href="http://www.egeshi.com/" title="Профессиональная разработка сайтов">Egeshi Solutions</a></h4>
				<p>&copy;Copyright 2012 rutvgid.ru</p>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
	
	
    
}
