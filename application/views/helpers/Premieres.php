<?php
class Zend_View_Helper_Premieres extends Zend_View_Helper_Abstract 
{
	
	public function premieres(){
		
		$config = new Zend_Config_Xml(APPLICATION_PATH . '/configs/view-helpers.xml', 'premieres');
		$programs = new Xmltv_Model_Programs();
		$date = new Zend_Date(null, null, 'ru');
		$today = $date->toString(Zend_Date::WEEKDAY_DIGIT);
		
		$d = new Zend_Date(null, null, 'ru');
		do{
			if ($d->toString(Zend_Date::WEEKDAY_DIGIT, 'ru')>1)
			$d->subDay(1);
		} while ($d->toString(Zend_Date::WEEKDAY_DIGIT, 'ru')>1);
		$weekStart = $d;
		
		$d = new Zend_Date(null, null, 'ru');
		do{
			$d->addDay(1);
		} while ($d->toString(Zend_Date::WEEKDAY_DIGIT, 'ru')>0);
		$weekEnd = $d;
		
		$programs_list = $programs->getPremieres(new Zend_Date(), $weekEnd);
		$heading = "Премьеры сегодня";
		if (!$programs_list) {
			$heading = "Премьеры недели";
			$programs_list = $programs->getPremieres($weekStart, $weekEnd);
		}
		
		//var_dump($programs_list);
		
		$toDash  = new Zend_Filter_Word_SeparatorToDash();
		$toLower = new Zend_Filter_StringToLower();
		
		$this->view->headStyle()->appendStyle('#premieres_carousel .item .title { font-weight: bold; font-size: 14px; }');
		$js = "$(document).ready(function() { $('#premieres_carousel').jcarousel({ scroll:4 }); });";
		$this->view->headScript()->appendScript($js);
		
		if (!empty($programs_list)) {
			ob_start();
			?>
			
		<?php if ((bool)$config->get('display_title')===true): ?>
		<h3><?php echo $heading; ?></h3>
		<?php endif; ?>
		
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
		return;
		
	}
	
}