<?php
/**
 * Список категорий каналов
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: ChannelsCategoriesList.php,v 1.5 2012-08-13 13:20:28 developer Exp $
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
				$cats = $table->fetchAll()->toArray();
			}
		} catch (Exception $e) {
				echo $e->getMessage();
		}
		
		$allChannels = array(
			'id'=>'',
			'title'=>'Все каналы',
			'alias'=>'',
			'image'=>'all-channels.gif',
		);
		array_push( $cats, $allChannels );
		
		//var_dump($cats);		
		//$requestParams = Zend_Controller_Front::getInstance()->getRequest()->getParams();
				
		$html = '<ul id="channels_categories" class="nav nav-list">';
		foreach ($cats as $cat) {
			$catAlias = $escape->filter( Xmltv_String::strtolower( $cat['alias'] ) );
			$catTitle = $escape->filter( Xmltv_String::ucfirst( $cat['title'] ) );
			$li = '<li><a href="/каналы/'.$catAlias.'" title="'.$catTitle.' телеканалы"><img class="icon" src="'.'/images/categories/channels/'.$escape->filter( $cat['image'] ).'" />'.$catTitle.'</a></li>';
			if ( Xmltv_String::strtolower( $catTitle ) == 'все каналы') {
				$li = '<li><a href="/телепрограмма" title="Телепрограмма всех каналов"><img class="icon" src="'.'/images/categories/channels/all-channels.gif" />'.$catTitle.'</a></li>';
			}
			
			$html .= $li;
		}
		$html .= '</ul>';
		return $html;
		
	}
}