<?php
/**
 * Список категорий каналов
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: ChannelsCategoriesList.php,v 1.2 2012-05-20 09:01:36 dev Exp $
 *
 */
class Zend_View_Helper_ChannelsCategoriesList extends Zend_View_Helper_Abstract 
{
	
	public function channelsCategoriesList(){
		
		$css = "ul#channels_categories { padding:0; margin: 0; }
		ul#channels_categories li { list-style: none; line-height: 26px;  }
		ul#channels_categories li a { width:125px; display:block; text-align: left; padding-left: 34px; line-height: 26px; }";
		$this->view->headStyle()->appendStyle($css);
		
		$table = new Xmltv_Model_DbTable_ChannelsCategories();
		$cache = new Xmltv_Cache();
		$hash  = $cache->getHash(__METHOD__);
		if (!$cats = $cache->load($hash, 'Function')) {
			$cats = $table->fetchAll();
			$cache->save($cats, $hash, 'Function');
		}
		
		$requestParams = Zend_Controller_Front::getInstance()->getRequest()->getParams();
				
		$html = '<ul id="channels_categories" class="catlist">';
		foreach ($cats as $cat) {
			$alias = Xmltv_String::strtolower( $cat->alias );
			$html.='<li><a class="btn" style="background: url(\'/images/categories/channels/'.$cat->image.'\') no-repeat scroll 4px 4px transparent;" href="/каналы/'.$alias.'">'.$cat->title.'</a></li>';
		}
		$html .= '</ul>';
		return $html;
		
	}
}