<?php

/**
 * Site header helper
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: Header.php,v 1.8 2012-08-13 13:20:28 developer Exp $
 *
 */
class Zend_View_Helper_Header extends Zend_View_Helper_Abstract
{


	public function header () {
		
		ob_start();
		?>
		
		<div class="navbar span12">
			<div class="navbar-inner">
				<div class="container">
					<ul class="nav">
						<li><a href="/телепрограмма">Каналы</a></li>
						<li><a href="/сериалы">Сериалы</a></li>
						<li><a href="/фильмы">Фильмы</a></li>
						<li><a href="/актеры">Актеры</a></li>
						<li><a href="/слухи">Новости</a></li>
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
			</div>
			<?php 
			//$form = new Xmltv_Form_Login(array('form_class'=>'vhodwrap pull-left')); echo $form;
			//$this->view->headStyle()->appendStyle($formCss)
			//	->appendStyle($vkcss); 
			?>
		</div>
		
		<div class="brand span12">
			<h1>Rutvgid.ru :: <a href="/" title="Телепрограмма на все телеканалы">Программа передач телеканалов России, Украины и СНГ.</a><br />
			Онлайн-видео на любой вкус</h1>
		</div>
		
		<?php
		return ob_get_clean();
	}
}

