<?php

/**
 * Left sidebar class
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: Left.php,v 1.1 2012-03-31 23:01:32 dev Exp $
 *
 */
class Xmltv_Block_Sidebar_Left extends Xmltv_Block
{


	public function __construct () {

		parent::__construct();
		$this->html = '&nbsp;';
	}


	/**
	 * Get sidebar content
	 */
	public function getHtml () {
		ob_start();
		?>
		<div>
			<h3>Популярные программы</h3>
			<ul>
				<li></li>
			</ul>
		</div>
		<div>
			<h3>Каналы по темам</h3>
			<ul>
				<li></li>
			</ul>
		</div>
		<?php 
		return ob_get_clean();
	}
}