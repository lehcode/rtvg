<?php
class Zend_View_Helper_SidebarRight extends Zend_View_Helper_Abstract
{
	
	protected $_kindsChannels=array(3,18);
	
	public function sidebarRight($channel_props=null){
		
		
		ob_start();
		?>
		
		
		<?php 
		/*
		 * Prevent output for adult channels
		 */
		/*if (isset($this->view->channel->category) && ($this->view->channel->category != 15)) : ?>
		<div class="ad336x280">
			<script type="text/javascript"><!--
			google_ad_client = "ca-pub-1744616629400880";
			google_ad_slot = "3286652462";
			google_ad_width = 336;
			google_ad_height = 280;
			//-->
			</script>
			<script type="text/javascript"
			src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
			</script>
		</div>
		<?php endif; */ 
		?>
		<?php 
		/**
		 * Non-collapsible advmaker 
		 */
		/*
		?>
		<div id="DIV_DA_78318"></div>
		*/ 
		/*
		 * Collapsible advmaker block
		 */
		?>
		<?php 
		if (@!in_array($channel_props->category, $this->_kindsChannels)) {
			$this->view->headScript()->appendScript("$(document).ready(function(){
				$('#rumor-ads').fadeIn(1000);
			});");
		?>
		<div class="module" id="rumor-ads">
			<h4 class="heading">Свежие слухи</h4>
			<div class="content" id="DIV_DA_85753"></div>
		</div>
		<?php 
		} ?>
		
		<?php  if (!$this->view->isDev()) : ?>
		<div class="module">
			<h5 class="heading">Телепрограмма vkontakte</h5>
			<div id="vk_groups"></div>
			<script type="text/javascript">
			VK.Widgets.Group("vk_groups", {mode: 1, width: "350", height: "290"}, 27716041);
			</script>
		</div>
		<?php endif; ?>
		
		
		<?php 
		//var_dump($this->view->sidebar_videos);
		if ($this->view->sidebar_videos === true) {
			$query = array('"'.$this->view->channel->title.'"');
			if (isset($this->view->current_program->title))
				$query[]='"'.$this->_cleanProgramTitle($this->view->current_program->title).'"';
			//var_dump($query);
			$videos = $this->view->youtubeVideos( $query, array(
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
				$this->view->headScript()->appendScript("$(function() { 
						$( '#col_r .videos_sidebar' ).accordion({ autoHeight:true, navigation:false, clearStyle:true }); ;
						
				});");
				echo '<div class="module">
				<h3>Новые видео «'.$this->view->channel->title.'» онлайн</h3>';
				echo $videos.'</div>';
			} ?>
			
		<?php 
		}
		?>
		
		
		
		<?php 
		return ob_get_clean();
	}
	
	private function _cleanProgramTitle($input=''){
		if (!$input)
			return '';
		$result = preg_replace('/\(.+\)/', '', $input);
		return trim($result);
	}
}