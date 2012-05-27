<?php

/**
 * Site header helper
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: Header.php,v 1.5 2012-05-27 20:05:50 dev Exp $
 *
 */
class Zend_View_Helper_Header extends Zend_View_Helper_Abstract
{


	public function header () {
		
		$css = '.navbar .brand { color: #0066cc; }
		a.brand, a.brand span { font-size: 11px; }';
		$this->view->headStyle()->appendStyle( $css );
		$this->view->headScript()->appendScript('VK.init({ apiId: 2369516, onlyWidgets: true}); ');
		ob_start();
		?>
		
		<div class="navbar">
			<div class="navbar-inner">
				<div class="container">
				
					<ul class="nav">
						<li><a href="/">Главная</a></li>
						<li><a href="/телепрограмма">Каналы</a></li>
						<li><a href="/сериалы">Сериалы</a></li>
						<li><a href="/фильмы">Фильмы</a></li>
						<li><a href="/актеры">Актеры</a></li>
						<?php /* <li><a href="/слухи">Дневник папарацци</a></li> */ ?>
						<li class="divider-vertical"></li>
						<li>
							<div id="vk_like" class="pull-right"></div>
							<script type="text/javascript">VK.Widgets.Like("vk_like", {type: "mini"});</script>
						</li>
					</ul>
					
					<h1>
						<a class="brand" href="/" title="Телепрограмма на все телеканалы">Rutvgid.ru <span>Программа передач телеканалов России, Украины и СНГ</span></a>
					</h1>
					
				</div>
			</div>
			<?php 
			$formCss = '.vhodwrap { margin: 9px 0 0; }';
			$form = new Xmltv_Form_Login(array('form_class'=>'vhodwrap pull-left')); echo $form;
			$vkcss = '#vklogin{  display: block; float: left; height: 21px; line-height: 21px; margin: 13px 0 0 12px; }
			#vklogin img { border-radius: 3px; }'; 
			$this->view->headStyle()->appendStyle($formCss)
				->appendStyle($vkcss); 
			$this->view->headScript()->appendFile('/js/forms/vklogin.js');
			?>
			<a href="javascript:void(0);" id="vklogin" class="vklogin" title="Нажмите чтобы войти через vkontakte"><img src="/images/forms/vklogin.png" alt="Войти vkontakte" /></a>
		</div>
		
		<?php
		return ob_get_clean();
	}
}

