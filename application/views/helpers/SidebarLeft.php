<?php
class Zend_View_Helper_SidebarLeft extends Zend_View_Helper_Abstract
{
	public function sidebarLeft(){
		
		
		ob_start();
		?>
		
		<?php 
		/*
		 * Prevent output for adult channels
		 */
		if (!$this->view->isDev()) {
			if (isset($this->view->channel->category) && ($this->view->channel->category != 15)) : ?>
		<div class="ad160x90" style="margin: 0 auto; width: 160px;">
			<script type="text/javascript"><!--
			google_ad_client = "ca-pub-1744616629400880";
			/* rutvgid 160x90 */
			google_ad_slot = "7070699068";
			google_ad_width = 160;
			google_ad_height = 90;
			//-->
			</script>
			<script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
			</script>
		</div>
		<?php 
			endif;
		} ?>
		
		<?php echo $this->view->channelsCategoriesList(); ?>
		
		<?php /*
		<div>
			<h3>Популярные программы</h3>
			<ul>
				<li></li>
			</ul>
		</div>
		*/ ?>
		
		
		<?php 
		return ob_get_clean();
	}
}