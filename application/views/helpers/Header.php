<?php

/**
 * Site header helper
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: Header.php,v 1.7 2012-08-03 00:18:19 developer Exp $
 *
 */
class Zend_View_Helper_Header extends Zend_View_Helper_Abstract
{


	public function header () {
		
		ob_start();
		?>
		
		<div class="navbar">
			<div class="navbar-inner">
				<div class="container">
					<ul class="nav">
						<?php /* <li><a href="/">Главная</a></li> */ ?>
						<li><a href="/телепрограмма">Каналы</a></li>
						<li><a href="/сериалы">Сериалы</a></li>
						<li><a href="/фильмы">Фильмы</a></li>
						<li><a href="/актеры">Актеры</a></li>
						<?php /* <li><a href="/слухи">Дневник папарацци</a></li> */ ?>
						<li class="divider-vertical"></li>
						<li>
							<div id="vk_like"></div>
							<script type="text/javascript">VK.Widgets.Like("vk_like", {type: "mini", height: 24});</script>
						</li>
						<li>
							<a href="javascript:void(0);" id="vklogin" class="vklogin" title="Нажмите чтобы войти через vkontakte"><img src="/images/forms/vklogin.png" alt="Войти vkontakte" /></a>
						</li>
					</ul>
				</div>
				<h1>
					<a class="brand" href="/" title="Телепрограмма на все телеканалы">Rutvgid.ru <span>Программа передач телеканалов России, Украины и СНГ. Онлайн-видео на любой вкус</span></a>
				</h1>
			</div>
			<?php 
			//$form = new Xmltv_Form_Login(array('form_class'=>'vhodwrap pull-left')); echo $form;
			//$this->view->headStyle()->appendStyle($formCss)
			//	->appendStyle($vkcss); 
			?>
		</div>
		
		<?php
		return ob_get_clean();
	}
}

