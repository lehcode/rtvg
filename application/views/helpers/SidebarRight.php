<?php
class Zend_View_Helper_SidebarRight extends Zend_View_Helper_Abstract
{
	public function sidebarRight(){
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