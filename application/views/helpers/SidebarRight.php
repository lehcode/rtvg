<?php
class Zend_View_Helper_SidebarRight extends Zend_View_Helper_Abstract
{
	
	protected $_kidsChannels=array(3,18);
	
	public function sidebarRight($channel_props=null){
		
		ob_start();
		?>
		
		<?php 
		if ($this->view->sidebar_videos === true) {
			
			$query = array();
			
			if (isset($this->view->current_program->title)) {
				$t = explode(' ', $this->view->current_program->title );
				$words = array();
				$q = array();
				foreach ($t as $k=>$w) {
					$t[$k] = Xmltv_String::strtolower( $this->_cleanTitle( trim( $w ) ) );
					if ( Xmltv_String::strlen( $t[$k] ) > 3 && !is_numeric($t[$k]) )
						$q[] = $t[$k];
				}
				$query[] = implode(' ', $q);
			}
			
			//var_dump($query);
			
			
			if (strstr($this->view->channel->title, ' ')) {
				$t = explode(' ', $this->view->channel->title);
				$q = array();
				foreach ($t as $k=>$w) {
					$t[$k] = Xmltv_String::strtolower( $this->_cleanTitle( trim( $w ) ) );
					if ( Xmltv_String::strlen( $t[$k] ) > 2 )
						$q[] = Xmltv_String::strtolower( $t[$k] );
				}
				$query[] = implode(' ', $q);
			} else {
				$query[] = Xmltv_String::strtolower( $this->_cleanTitle( $this->view->channel->title ) );
			}
			
			//var_dump($query);
			
			$ytConfig = array(
				'order'=>'relevance',
				'max_results'=>10,
				'cache_subfolder'=>'SidebarRight',
				'operator'=>' ',
				'debug'=>false,
				'start_index'=>2,
				'operator'=>'|',
			);
			if (!preg_match('/[\p{Cyrillic}]+/', $this->view->channel->title) && preg_match('/[\p{Latin}]+/', $this->view->channel->title))
				$ytConfig['language']='en';

			$yt = new Xmltv_Youtube($ytConfig);
			
			/**
			 * @var Zend_Gdata_YouTube_VideoFeed
			 */
			$result = $yt->fetchVideos($query);
			$escape = new Zend_Filter_HtmlEntities();
			
			if ( is_a($result, 'Zend_Gdata_YouTube_VideoFeed') ) {
				$this->view->headScript()->appendScript("$(function() { 
					$( '#col_r .videos_sidebar' ).accordion({ 
						autoHeight: true,
						navigation: false,
						clearStyle: true,
						header: 'h4',
						active: 0
					});
				});");
				?>
				<div class="module">
					<h3 class="mediumheading">
						Онлайн-видео <?php echo $escape->filter( $this->view->channel->title ) ?>
					</h3>
					
					<div class="videos_sidebar">
					<?php 
					for ($i=1; $i<count($result); $i++) {
						
						/**
						 * @var Zend_Gdata_YouTube_VideoEntry
						 */
						$v = $result->offsetGet($i);
						if (!is_a($v, 'Zend_Gdata_YouTube_VideoEntry'))
							continue;
						
						$videoTitle = $v->getVideoTitle();
						$videoAlias = $this->view->videoAlias( $videoTitle );
						$videoDesc  = $v->getVideoDescription();
						$videoId    = $this->view->videoId( $v->getVideoId() );
						$videoViews = (int)$v->getVideoViewCount();
						
						?>
							
						<h4>
							<i class="icon-plus icon-white"></i><?php echo $videoTitle ?>
						</h4>
						
						<div class="info">
							
							<p class="viewcount">
								Понравилось <?php echo number_format( $v->getVideoViewCount (), 0 ); ?>
							</p>
							
							<?php 
							$videoThumbnails = $v->getVideoThumbnails();
							foreach($videoThumbnails as $videoThumbnail) {
								//var_dump($videoThumbnail);
								if ( $videoThumbnail['width']==120)
								$thumb = $videoThumbnail;
							}
							
							$link_title = empty($videoDesc) ? $videoTitle. $this->view->truncateString( $videoDesc, 40, 'letters') : $videoTitle ; 
							$link_href = "/видео/онлайн/$videoAlias/$videoId";
								
							?>
							<a href="<?php echo $link_href ?>" title="<?php echo $link_title ?>">
								<img src="<?php echo $thumb['url'] ?>" alt="Картинка" />
							</a>
							<a class="btn btn-inverse btn-mini" href="<?php echo $link_href ?>" title="смотреть <?php echo $link_title ?>">
								Смотреть
							</a>
							
							<?php 
							
							$d = new Zend_Date($v->getPublished(), Zend_Date::ISO_8601);
							$d->addHour(3);
							?>
							<div class="date">
								<?php echo Xmltv_String::ucfirst( $d->toString("EEEE, d MMMM YYYY, H:mm", 'ru')) ?>
							</div>
							
							<?php  $d = new Zend_Date($v->getVideoDuration(), Zend_Date::TIMESTAMP); ?>
							<div class="duration">
								<?php echo $d->toString("mm:ss"); ?>
							</div>
							
							<?php  if (!empty($videoDesc)) : ?>
							<p class="desc">
								<?php echo $this->view->truncateString( $videoDesc, 30, 'words') ; ?>
							</p>
							<?php endif; ?>
							
							<?php
							$tags = $v->getVideoTags();
							if (count($tags)) {
								$limit = 5;
								$limit = count($tags)>$limit ? $limit : count($tags) ; 
								?>
								<ul class="tags">
									<?php 
									$added=0;
									foreach($tags as $tag){
										if ($added<$limit) {
											$safe_tag = $this->view->safeTag($tag);
											//var_dump($safe_tag);
											if ( Xmltv_String::strlen($safe_tag)>3 ) {
												?>
											<li>
												<a class="" href="/видео/тема/<?php echo $safe_tag ?>?p=<?php echo urlencode( base64_encode( $this->view->escape( $tag ))); ?>" title="10 новых видео на тему <?php echo $tag; ?>">
													<span class="badge badge-important"><?php echo $tag ?></span>
												</a>
											</li>
												<?php 
												$added++;
											}
										} else break;
									}
									?>
									<li>
										<a class="pull-left" href="<?php echo "/видео/онлайн/$videoAlias/$videoId" ?>" title="<?php echo $this->view->truncateString($videoDesc, 5, 'words'); ?>">
											<span class="badge badge-important">Все темы этого видео</span>
										</a>
									</li>
								</ul>
								<?php 
							} ?>
						</div>	
					<?php 
					
					}
					?>
						
				</div>
			</div>
				<?php 		
			}
			?>
			
		<?php 
		}
		?>
		
		<?php 
		if (@!in_array($channel_props->category, $this->_kidsChannels)) {
			$this->view->headScript()->appendScript("$(document).ready(function(){
				$('#rumor-ads').fadeIn(1000);
			});");
		?>
		<div class="module" id="rumor-ads">
			<?php /* <h4 class="mediumheading">Свежие слухи</h4> */ ?>
			<div class="content" id="DIV_DA_85753"></div>
		</div>
		<?php 
		} ?>
		
		<?php  if (!$this->view->isDev()) : ?>
		<div class="module">
			<h5 class="mediumheading">Телепрограмма vkontakte</h5>
			<div id="vk_groups"></div>
			<script type="text/javascript">
			VK.Widgets.Group("vk_groups", {mode: 1, width: "350", height: "290"}, 27716041);
			</script>
		</div>
		<?php endif; ?>
		
		
		<?php 
		return ob_get_clean();
	}
	
	private function _cleanTitle($input=''){
		
		if (!$input)
			return false;
		
		$result = preg_replace('/[^\d\w\s]+/iu', ' ', $input);
		$escape = new Zend_Filter_HtmlEntities();
		return $escape->filter( trim($result) );
		
	}
}