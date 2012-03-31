<?php

/**
 * Site header class
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: Footer.php,v 1.1 2012-03-31 23:01:32 dev Exp $
 *
 */
class Xmltv_Block_Footer extends Xmltv_Block
{


	public function __construct () {

		parent::__construct();
	}


	public function getHtml () {

		ob_start();
		?>
		<div class="span12" id="footer">
			<h4><a href="http://www.egeshi.com/" title="Профессионльная разработка сайтов">Egeshi Solutions</a></h4>
			<p>&copy;Copyright 2012 rutvgid.ru</p>
		</div>
		<?php
		return ob_get_clean();
	}
}
