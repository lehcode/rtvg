<?php
class Zend_View_Helper_FpMenu extends Zend_View_Helper_Abstract
{
	public function fpMenu(){
		ob_start();
		
		//$this->view->headStyle()->appendStyle("#fpnav li { list-style: none; }");
		
		?>
		
		<ul id="fpnav" class="span12">
			<li class="span4"><a href="/телепрограмма"><span>Каналы</span></a></li>
			<li class="span4"><a href="/фильмы"><span>Фильмы на этой неделе</span></a></li>
			<li class="span4 lastcol"><a href="/сериалы"><span>Сериалы на этой неделе</span></a></li>
			<li class="span4"><a href="/премьеры"><span>Премьеры недели</span></a></li>
			<li class="span4"><a href="/актеры"><span>Актеры</span></a></li>
			<li class="span4 lastcol"><a href="/режиссеры"><span>Режиссеры</span></a></li>
		</ul>
		
		<div style="width: 976px; margin: 0 auto;">
			
			<div class="pull-left ad468x60">
				<script type="text/javascript">
					<!--
					google_ad_client = "ca-pub-1744616629400880";
					/* Rutvgid Frontpage */
					google_ad_slot = "3801803939";
					google_ad_width = 468;
					google_ad_height = 60;
					//-->
					</script>
					<script type="text/javascript"
					src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
				</script>
			</div>
			
			<div class="pull-left ad468x60">
				<script type="text/javascript">
					<!--
					google_ad_client = "ca-pub-1744616629400880";
					/* Rutvgid Frontpage */
					google_ad_slot = "3801803939";
					google_ad_width = 468;
					google_ad_height = 60;
					//-->
					</script>
					<script type="text/javascript"
					src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
				</script>
			</div>
			
		</div>
		<?php 
		return ob_get_clean();
	}
}