<?php
class Zend_View_Helper_SidebarLeft extends Zend_View_Helper_Abstract
{
	public function sidebarLeft(){
		ob_start();
		?>
		<div>
			<h3>Популярные программы</h3>
			<ul>
				<li></li>
			</ul>
		</div>
		<div>
			<h3>Каналы по темам</h3>
			<ul>
				<li></li>
			</ul>
		</div>
		<?php 
		return ob_get_clean();
	}
}