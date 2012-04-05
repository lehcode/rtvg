<?php

class Xmltv_Form_RadioGroup extends Zend_Form
{


	public function __construct ($name = null, $input = array(), $label=null, $options = null) {

		if(  !$name ) throw new Exception( "Не указано имя формы" );
		if(  !$input ) throw new Exception( "Форме не переданы данные" );
		
		parent::__construct( $options );
		
		// setting name, action and encryption type
		$this->setName( $name )
			->setAttribs(array(
				'class'=>'form',
				'enctype'=>'multipart/form-data',
				'action'=>'',
				'id'=>$name
			));
		
		$radio = new Zend_Form_Element_Radio( 'target' );
		$radio->addMultiOptions( $input )
			->setLabel($label );
			
		$submit = new Zend_Form_Element_Button( 'submit' );
		$submit->setLabel( 'Грабить' )
			->setAttribs(array(
				'id'=>'submitbutton',
				'class'=>'btn btn-primary'
			))
			->removeDecorator('DtDdWrapper')
			->removeDecorator('Tooltip');
		
		
		$this->addElements( array($radio, $submit) );
		$this->removeDecorator('HtmlTag');
		
	}
}