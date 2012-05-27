<?php
/**
 * Список категорий каналов
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: ChannelsCategoriesList.php,v 1.3 2012-05-27 20:05:50 dev Exp $
 *
 */
class Zend_View_Helper_ChannelsCategoriesList extends Zend_View_Helper_Abstract 
{
	
	public function channelsCategoriesList(){
		
		$escape = new Zend_Filter_HtmlEntities();
		$css = "ul#channels_categories { padding:0; margin: 0; }
		ul#channels_categories li { list-style: none; line-height: 26px;  }
		ul#channels_categories li a { width:125px; display:block; text-align: left; padding-left: 34px; line-height: 26px; }";
		$this->view->headStyle()->appendStyle($css);
		
		$table = new Xmltv_Model_DbTable_ChannelsCategories();
		
		try {
		if (Xmltv_Config::getCaching()===true) {
			$subDir = "Channels";
			$cache = new Xmltv_Cache( array('location'=>"/cache/$subDir") );
			$hash  = $cache->getHash(__METHOD__);
			if (!$cats = $cache->load($hash, 'Core', $subDir)) {
				$cats = $table->fetchAll();
				$cache->save($cats, $hash, 'Core', $subDir);
			}
			} else {
				$cats = $table->fetchAll();
			}
		} catch (Exception $e) {
				echo $e->getMessage();
		}
		
		//$requestParams = Zend_Controller_Front::getInstance()->getRequest()->getParams();
				
		$html = '<ul id="channels_categories" class="catlist">';
		foreach ($cats as $cat) {
			$catAlias = $escape->filter( Xmltv_String::strtolower( $cat->alias ) );
			$catTitle = $escape->filter( Xmltv_String::ucfirst( $cat->title ) );
			$html.='<li><a href="/каналы/'.$catAlias.'" class="btn" style="background: url(\'/images/categories/channels/'.$escape->filter( $cat->image ).'\') no-repeat scroll 4px 4px transparent;" title="'.$catTitle.' телеканалы">'.$catTitle.'</a></li>';
		}
		$html .= '</ul>';
		return $html;
		
	}
}