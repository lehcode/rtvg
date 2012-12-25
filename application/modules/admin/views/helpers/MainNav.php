<?php
class Zend_View_Helper_MainNav extends Zend_View_Helper_Abstract
{
	
	protected $content;
	
	public function mainNav($controller=''){
		//var_dump($controller);
		ob_start();
		?>
		<ul class="nav nav-pills">
			<li><a href="/admin">Главная</a></li>
			<li class="dropdown">
				<a class="dropdown-toggle" href="#" data-toggle="dropdown">Программы<b class="caret"></b></a>
				<ul class="dropdown-menu">
					<li><a href="/admin/programs/list">Список</a></li>
					<!-- <li><a href="/admin/programs/premieres">Поиск премьер</a></li> -->
					<li><a href="/admin/programs/processing">Обработка</a></li>
				</ul>
			</li>
			<li><a href="/admin/channels">Каналы</a></li>
			<li><a href="/admin/series">Фильмы</a></li>
			<li><a href="/admin/movies">Сериалы</a></li>
			<li><a href="/admin/comments">Комменты</a></li>
			<li class="dropdown">
				<a class="dropdown-toggle" href="#" data-toggle="dropdown">Актеры<b class="caret"></b></a>
				<ul class="dropdown-menu">
					<li><a href="/admin/actors/list">Список</a></li>
					<li><a href="/admin/actors/duplicates">Дубликаты</a></li>
				</ul>
			</li>
			<li class="dropdown">
				<a class="dropdown-toggle" href="#" data-toggle="dropdown">Режиссеры<b class="caret"></b></a>
				<ul class="dropdown-menu">
					<li><a href="/admin/directors/list">Список</a></li>
					<li><a href="/admin/directors/duplicates">Дубликаты</a></li>
				</ul>
			</li>
			<?php /*
			<li><a href="/admin/duplicates">Дубликаты</a></li>
			*/ ?>
			<li><a href="/admin/import">Импорт</a>
			<li><a href="/admin/archive">Архив</a></li>
			</li>
			<?php if (strtolower($controller)=='grab'): ?>
			<li class="dropdown active">
				<a class="dropdown-toggle" href="#" data-toggle="dropdown">Граббинг<b class="caret"></b></a>
				<ul class="dropdown-menu">
					<li><a href="/admin/grab/actors">Актеры</a></li>
					<li><a href="/admin/grab/directors">Режиссеры</a></li>
					<li><a href="/admin/grab/listings">Расписание</a></li>
					<li><a href="/admin/grab/movies">Описания фильмов</a></li>
					<li><a href="/admin/grab/series">Описания сериалов</a></li>
				</ul>
			</li>
			<?php else: ?>
			<li class="dropdown">
				<a class="dropdown-toggle" href="#" data-toggle="dropdown">Граббинг<b class="caret"></b></a>
				<ul class="dropdown-menu">
					<li><a href="/admin/grab/actors">Актеры</a></li>
					<li><a href="/admin/grab/directors">Режиссеры</a></li>
					<li><a href="/admin/grab/premieres">Премьеры</a></li>
					<li><a href="/admin/grab/listings">Расписание</a></li>
				</ul>
			</li>
			<?php endif; ?>
			<li><a href="/admin/cache">Кэш</a></li>
		</ul>
		<?php 
		return ob_get_clean();
	}
	
}