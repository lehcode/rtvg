<?php

/**
 * Right sidebar class
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: Right.php,v 1.1 2012-03-31 23:01:32 dev Exp $
 *
 */
class Xmltv_Block_Sidebar_Right extends Xmltv_Block
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
		<div id="vk_groups"></div>
		<script type="text/javascript">
		VK.Widgets.Group("vk_groups", {mode: 1, width: "350", height: "290"}, 27716041);
		</script>
		<?php 
		return ob_get_clean();
	}
}