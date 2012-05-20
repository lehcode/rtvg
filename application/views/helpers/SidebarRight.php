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
		<div class="ad336x280">
			<script type="text/javascript"><!--
			google_ad_client = "ca-pub-1744616629400880";
			/* rutvgid 336x280 */
			google_ad_slot = "3286652462";
			google_ad_width = 336;
			google_ad_height = 280;
			//-->
			</script>
			<script type="text/javascript"
			src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
			</script>
		</div>
		<?php 
		return ob_get_clean();
	}
}