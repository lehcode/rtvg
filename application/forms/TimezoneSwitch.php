<?php
/**
 * Изменяет часовой пояс при просмотре программы
 *
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: TimezoneSwitch.php,v 1.3 2013-03-06 04:54:51 developer Exp $
 *
 */
class Xmltv_Form_TimezoneSwitch extends Zend_Form
{
	
	private $_formClass='timezone';
	private $_formCss='';
	private $_actionUrl='';
	private $_timeShift=0;
	private $_labelClass=0;
	private $_selectedValue=0;
	
	public function __construct($options=array()) {
		
		parent::__construct($options);
		
		if (isset($options['form_class']) && !empty($options['form_class'])) {
			$this->_formClass = $options['form_class'];
		}
		if (isset($options['form_css']) && !empty($options['form_css'])) {
			$this->_formCss = $options['form_css'];
		}
		if (isset($options['action_url']) && !empty($options['action_url'])) {
			$this->_actionUrl = $options['action_url'];
		}
		if (isset($options['time_shift']) && !empty($options['time_shift'])) {
			$this->_timeShift = $options['time_shift'];
		}
		if (isset($options['label_class']) && !empty($options['label_class'])) {
			$this->_labelClass = $options['label_class'];
		}
			
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
		$timezone = new Zend_Form_Element_Select('timezone');
		$timezone->addMultiOptions($options);
		$active = $this->_timeShift==0 ? 'msk' : $this->_timeShift ; 
		$timezone->setValue( $active );
		$labelText = $this->_timeShift==0 ? 'Часовой пояс (MSK)' : 'Часовой пояс (MSK '.$this->_timeShift.')' ;
		$timezone->setAttribs(array(
			'name'=>'tz',
			'size'=>1,
			'onchange'=>'document.forms.timezone.submit();',
		))
		->setLabel($labelText)
		->setDecorators(array(
			'ViewHelper',
			'Description',
			'Errors',
			array( 'Label', array( 'tag'=>'div', 'class'=>$this->_labelClass ) ),
			//array( 'HtmlTag', array( 'tag'=>'div', 'class'=>'mediumheading' ) ),
		))
		->removeDecorator('HtmlTag');
		
			
		$formAttribs = array(
			'action'=>$this->_actionUrl,
			'name'=>'timezone',
			'class'=>$this->_formClass,
			'method'=>'get',
			'id'=>'timezone',
			'enctype'=>'application/x-www-form-urlencoded'
		);
		$this->addElements( array( $timezone ) )
			->setAttribs($formAttribs)
			->setDecorators(array(
				'FormElements',
				'Form', array(
					array('data'=>'HtmlTag'),
					array('tag'=>'div', 'class'=>'timeswitch')
				),
			));
					
		return $this;
		
    }
    
}