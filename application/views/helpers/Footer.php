<?php

/**
 * Site header class
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: Footer.php,v 1.1 2012-04-07 11:43:08 dev Exp $
 *
 */
class Zend_View_Helper_Footer extends Zend_View_Helper_Abstract
{


	public function footer () {
		ob_start();
		?>
		<div class="span12" id="footer">
			<h4><a href="http://www.egeshi.com/" title="Профессиональная разработка сайтов">Egeshi Solutions</a></h4>
			<p>&copy;Copyright 2012 rutvgid.ru</p>
		</div>
		<?php
		return ob_get_clean();
	}
	
	
    
}
