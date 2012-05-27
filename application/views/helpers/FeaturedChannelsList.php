<?php
class Zend_View_Helper_FeaturedChannelsList extends Zend_View_Helper_Abstract
{
	function featuredChannelsList(){
		
		$config = new Zend_Config_Xml(APPLICATION_PATH . '/configs/view-helpers.xml', 'featuredChannels');
		$table  = new Xmltv_Model_DbTable_Channels();
		$order  = $config->get('order')=='ch_id' ? 'ch_id' : 'title' ;
		$cols   = (int)$config->get('cols', 4);
		$total  = (int)$config->get('amount', 20);
		$colW   = 100/$cols;
		$list   = $table->getFeatured($order, $total);
		
		//var_dump($list);
		//die(__FILE__.': '.__LINE__);
		
		$css = "ul#featured_channels { }
		ul#featured_channels li { list-style:none; display: block; float: left; width: ".$colW."%; }";
		$this->view->headStyle()->appendStyle($css);
		$html="";
		ob_start();
		?>
		<ul id="featured_channels">
		<?php  
		for ( $i=0; $i<count($list); $i++ ) {
			$linkText = $this->view->truncateString( $list[$i]->title, 25 ).' сегодня';
			if (($i%$cols==$cols-1) || $i==(count($list)-1)) {
				$html .= '<li class="lastcol"><a href="/телепрограмма/'.Xmltv_String::strtolower($list[$i]->alias).'">'.$linkText.'</a></li>';
			} else {
				$html .= '<li><a href="/телепрограмма/'.Xmltv_String::strtolower($list[$i]->alias).'">'.$linkText.'</a></li>';
			}
		}
		echo $html;
		?>
		</ul>
		<?php 
		return ob_get_clean();
		
	}
}