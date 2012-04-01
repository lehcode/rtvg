<?php
class Xmltv_Block_Premieres extends Xmltv_Block 
{
	
	public function getHtml(){
		
		$programs = new Xmltv_Model_Programs();
		$date = new Zend_Date(null, null, 'ru');
		$today = $date->toString(Zend_Date::WEEKDAY_DIGIT);
		if ($today==0) {
			$date->subDay(6, 'ru');
		} elseif ($today>1) {
			do {
				$date->subDay(1, 'ru');	
			} while( $date->toString(Zend_Date::WEEKDAY_DIGIT)>1 );
		} 
		$week_first = $date;
		
		$date = new Zend_Date(null, null, 'ru');
		if ($today>0) {
			do {
				$date->addDay(1, 'ru');	
			} while( $date->toString(Zend_Date::WEEKDAY_DIGIT)<6 );
			$date->addDay(1, 'ru');
		} 
		$week_last = $date;
		$programs_list = $programs->getPremieres(new Zend_Date(null, null, 'ru'), $week_last);
		
		$toDash  = new Zend_Filter_Word_SeparatorToDash();
		$toLower = new Zend_Filter_StringToLower();
		
		ob_start();
		?>
		
		<ul id="premieres_carousel" class="jcarousel-skin-tango">
			<?php 
			foreach ($programs_list as $p) {
				$category_title = empty($p->category_title) ? '' : $p->category_title ;
				$category_alias = empty($category_title) ? '' : $toLower->filter( $toDash->filter( $p->category_title ) ) ;
				$channel_alias  = $toLower->filter($p->channel_alias);
			?>
			<li class="item">
				
				<p class="title">
					<a href="/премьеры/<?php echo $category_alias ?>" title=""><?php echo $category_title ?> <strong><?php echo $p->title ?></strong></a>
					<span class="sub_title"><?php echo $p->sub_title ?></span>
				</p>
				
				<?php
				$channelLink = '<a href="/телепрограмма/'. $channel_alias .'/'. $p->start->toString('yyyy-MM-dd').'">'. $p->channel_title .'</a>';
				?>
				<p class="start">
					<?php printf("Премьера на канале %s %s в %s", $channelLink, $p->start->toString('d '.Zend_Date::MONTH_NAME), $p->start->toString('HH:MM'));?></p>
			
				<?php  if (!empty($p->sub_title)) : ?>
				<p class="subtitle">
					<?php echo $p->sub_title ?>
				</p>
				<?php endif; ?>
				
				<p>
					<a href="/телепрограмма/<?php echo $toLower->filter( $p->channel_alias ) ?>/<?php echo $p->alias ?>/<?php echo $p->start->toString('yyyy-MM-dd') ?>">Посмотреть в программе</a>
				</p>
				
				
			</li>
			<?php  
			}
			?>
		</ul>
				
		<?php 
		return ob_get_clean();
		
	}
	
	public function getCss(){
		
		$this->css = '#premieres_carousel .item .title { font-weight: bold; font-size: 14px; }
		';
		return $this->css;
		
	}
	
	public function getJs(){
		$this->js = "
		$(document).ready(function() {
		    $('#premieres_carousel').jcarousel({ scroll:4 });
		});
		";
		return $this->js;
	}
	
}