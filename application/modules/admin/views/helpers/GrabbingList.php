<?php
/**
 * Bootstrap
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: GrabbingList.php,v 1.1 2012-04-05 22:48:55 dev Exp $
 *
 */
class Admin_View_Helper_GrabbingList extends Zend_View_Helper_Abstract
{
	public function grabbingList(){
		
		$sites = new Zend_Config_Xml(APPLICATION_PATH.'/configs/sites.xml', 'listings');
		
		if (empty($sites))
		return;
		
		$elements = array();
		$replace = new Zend_Filter_Word_SeparatorToSeparator('.', '');
		$c=0;
		foreach ($sites as $s){
			if ($s->active==1) {
				$alias = $replace->filter( $s->title );
				$elements[$alias]['key']   = $alias;
				$elements[$alias]['value'] = $s->title;
				$c++;
			}
		}
		
		$hidden = new Zend_Form_Element_Hidden('format');
		$hidden->setValue('html')
			->removeDecorator('label')
			->removeDecorator('HtmlTag');
		$form = new Xmltv_Form_RadioGroup('grab_targets', $elements, 'Список сайтов для граббинга');
		$form->addElement($hidden);
		
		$this->view->headScript()->appendScript("
		$(document).ready(function(){
			$('form#grab_targets').after('<div id=\"response\"></div>');
			$('#submitbutton').click(function(){
			$.ajax({
				url: '/admin/grab/grab-listings',
				data : $('form#grab_targets').serialize(),
				dataType: 'html',
				success: function(response){
					$('#response').html(response);
				}
			});
				
			});
		});
		");
		
		return $form;
		
	}
}