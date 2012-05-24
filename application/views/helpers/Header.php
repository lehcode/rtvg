<?php

/**
 * Site header class
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: Header.php,v 1.4 2012-05-24 20:49:35 dev Exp $
 *
 */
class Zend_View_Helper_Header extends Zend_View_Helper_Abstract
{


	public function header () {
		$this->view->headStyle()->appendStyle('a.brand, a.brand span { font-size: 11px; }');
		$this->view->headScript()->appendScript('VK.init({apiId: 2369516, onlyWidgets: true});');
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
					<a class="brand" href="/" title="Телепрограмма на все телеканалы">Rutvgid.ru <span>Телепрограмма для 300+ телеканалов России, Украины и СНГ</span></a>
					<form class="form-horizontal pull-left" action="/user/login">
						<input type="text" name="email" value="" size="12" />
						<input type="password" name="pass" value="" size="12" />
						<button class="btn btn-primary">Вход</button>
					</form>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}

