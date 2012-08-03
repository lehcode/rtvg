<?php
/**
 * Список категорий каналов
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: ChannelsCategoriesList.php,v 1.4 2012-08-03 00:18:19 developer Exp $
 *
 */
class Zend_View_Helper_ChannelsCategoriesList extends Zend_View_Helper_Abstract 
{
	
	public function channelsCategoriesList(){
		
		$escape = new Zend_Filter_HtmlEntities();
		//$css = "";
		//$this->view->headStyle()->appendStyle($css);
		
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
				
		$html = '<ul id="channels_categories" class="nav nav-list">';
		foreach ($cats as $cat) {
			$catAlias = $escape->filter( Xmltv_String::strtolower( $cat->alias ) );
			$catTitle = $escape->filter( Xmltv_String::ucfirst( $cat->title ) );
			$html.='<li><a href="/каналы/'.$catAlias.'" title="'.$catTitle.' телеканалы"><img class="icon" src="'.'/images/categories/channels/'.$escape->filter( $cat->image ).'" width="18" />'.$catTitle.'</a></li>';
		}
		$html .= '</ul>';
		return $html;
		
	}
}