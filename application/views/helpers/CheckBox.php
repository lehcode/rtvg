<?php
/**
 * 
 * @author Antony Repin <egeshisolutions@gmail.com>
 * @version $Id: CheckBox.php,v 1.1 2013-03-31 18:57:01 developer Exp $
 *
 */
class Rtvg_View_Helper_CheckBox extends Zend_View_Helper_Abstract
{
    
	public function checkBox($id=null, $value=0, $options=array(), $attribs=array()){
		
		if (!$id) {
		    $cb = new Zend_Form_Element_Checkbox( 'cb' );
		} else {
		    $cb = new Zend_Form_Element_Checkbox( $id );
		}

		(isset($options['tag']) && !empty($options['tag']))     ? 
			$tagProps = (array)$options['tag'] : array() ;
				
		(isset($options['label']) && !empty($options['label'])) ? 
			$labelProps = (array)$options['label'] : array() ;
		
		$cb->setLabel( $labelProps['text'] );
		unset($labelProps['text']);
		$cb->setAttribs( $attribs )
			->setDecorators( array(
				'ViewHelper',
				'Errors',
				array( 'HtmlTag', $tagProps ),
				array( 'Label', $labelProps ),
			));
		
		return $cb;
		
	}
	
}