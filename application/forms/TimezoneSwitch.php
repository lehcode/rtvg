<?php
/**
 * Изменяет часовой пояс при просмотре программы
 *
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: TimezoneSwitch.php,v 1.4 2013-04-03 04:08:15 developer Exp $
 *
 */
class Xmltv_Form_TimezoneSwitch extends Zend_Form
{
	
	public function __construct( $options=array() ) {
		
		(isset($options['label']) && !empty($options['label'])) ? $labelProps = $options['label'] : null ;
		unset($options['label']);

	    (isset($options['html_tag']) && !empty($options['html_tag'])) ? $htmlTagProps = $options['html_tag'] : null ;
	    unset($options['html_tag']);
	    
	    (isset($options['diff']) && !empty($options['diff'])) ? $diff = $options['diff'] : null ;
	    unset($options['diff']);
	    
	    parent::__construct( $options );
		
	    $options = array(
			array('key'=>'-12', 'value'=>'-12'),
			array('key'=>'-11', 'value'=>'-11'),
			array('key'=>'-10', 'value'=>'-10'),
			array('key'=>'-9', 'value'=>'-9'),
			array('key'=>'-8', 'value'=>'-8'),
			array('key'=>'-7', 'value'=>'-7'),
			array('key'=>'-6', 'value'=>'-6'),
			array('key'=>'-5', 'value'=>'-5'),
			array('key'=>'-4', 'value'=>'-4'),
			array('key'=>'-3', 'value'=>'-3'),
			array('key'=>'-2', 'value'=>'-2'),
			array('key'=>'-1', 'value'=>'-1'),
			array('key'=>'msk', 'value'=>'0'),
			array('key'=>'1', 'value'=>'+1'),
			array('key'=>'2', 'value'=>'+2'),
			array('key'=>'3', 'value'=>'+3'),
			array('key'=>'4', 'value'=>'+4'),
			array('key'=>'5', 'value'=>'+5'),
			array('key'=>'6', 'value'=>'+6'),
			array('key'=>'7', 'value'=>'+7'),
			array('key'=>'8', 'value'=>'+8'),
			array('key'=>'9', 'value'=>'+9'),
			array('key'=>'10', 'value'=>'+10'),
			array('key'=>'11', 'value'=>'+11'),
			array('key'=>'12', 'value'=>'+12'),
		);
	    
		$timezone = new Zend_Form_Element_Select( 'timezone' );
		$timezone->addMultiOptions( $options );
        if (!@$diff){
            $diff=0;
        }
		$active = $diff==0 ? 'msk' : $diff ; 
		$timezone->setValue( $active );
		$labelText    = $diff==0 ? 'Время(MSK)' : 'Сдвиг(MSK '.$diff.')' ;
		$labelClass   = isset( $labelProps['class']) && !empty( $labelProps['class'] ) ? $labelProps['class'] : null ;
		$labelTag     = isset( $labelProps['tag']) && !empty( $labelProps['tag'] ) ? $labelProps['tag'] : 'div' ;
		$timezone->setAttribs(array(
			'name'=>'tz',
			'size'=>1,
			'onchange'=>'document.forms.timezone.submit();',
			'style'=>'width:auto;',
			))
			->setLabel($labelText)
			->setDecorators(array(
				'ViewHelper',
				array( 'Label', array( 'tag'=>null ) ),
			));
		
		$htmlTag      = isset($htmlTagProps['tag']) && !empty( $htmlTagProps['tag'] ) ? $htmlTagProps['tag'] : 'div' ;
		$htmlTagClass = isset($htmlTagProps['class']) && !empty( $htmlTagProps['class'] ) ? $htmlTagProps['class'] : 'div' ;
		$this->addElements( array( $timezone ) )
			->setDecorators(array(
				'FormElements',
				'Form', array(
					array('data'=>'HtmlTag'),
					array('tag'=>$htmlTag, 'class'=>$htmlTagClass)
				),
			));
		
    }
    
}