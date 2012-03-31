<?php

/**
 * Site header class
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: Header.php,v 1.1 2012-03-31 23:01:32 dev Exp $
 *
 */
class Xmltv_Block_Header extends Xmltv_Block
{


	public function __construct () {

		parent::__construct();
		$this->css = "a.brand, a.brand span { font-size: 11px; }";
	}
	
	public function getJs(){
		
		return 'VK.init({ apiId: 2369516, onlyWidgets: true });';
		
	}


	public function getHtml () {

		ob_start();
		?>
		<div class="span12" id="header">
			<div class="span12 navbar">
				<div class="navbar-inner">
					<div class="container">
						<ul class="nav">
							<li><a href="/телепрограмма">Каналы</a></li>
							<li><a href="/сериалы">Сериалы</a></li>
							<li><a href="/фильмы">Фильмы</a></li>
							<li class="divider-vertical"></li>
							<li>
								<div id="vk_like" class="pull-right"></div>
								<script type="text/javascript">VK.Widgets.Like("vk_like", {type: "mini", height: 24});</script>
							</li>
						</ul>
						<a class="brand" href="/" title="Телепрограмма на все телеканалы">Rutvgid.ru <span>Телепрограмма для 300+ телеканалов России, Украины и СНГ</span></a>
					</div>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}

