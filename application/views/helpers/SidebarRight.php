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
		/*
		 * Prevent output for adult channels
		 */
		if ($this->view->channel->category!=15) : ?>
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
		<?php endif; ?>
		
		<?php 
		if ($this->view->sidebar_videos === true) {
			$videos = $this->view->youtubeVideos( array('телеканал', $this->view->channel->title, $this->view->current_program->title), array(
				'start_index'=>2,
				'max_results'=>6,
				'show_duration'=>true,
				'show_date'=>true,
				'thumb_width'=>120,
				'collapse'=>true,
				'debug'=>false,
				'safe_search'=>'strict',
				'order'=>'published',
				'truncate_description'=>30), 'sidebar' );
			if (!empty($videos) && ($videos!='<div class="videos_sidebar"></div>')) {
				echo '<h3>Новые видео "'.$this->view->channel->title.'" онлайн</h3>';
				echo $videos;
			}
		}	
		?>
		
		
		
		<?php 
		return ob_get_clean();
	}
}