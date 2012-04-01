<?php
class Xmltv_Form_FastChannelSearchForm extends Zend_Form
{
    public function __construct($options = null)
    {
		parent::__construct($options);
	  	// setting name, action and encryption type
		$this->setName('document');
		$this->setAction("");
		$this->setAttrib('enctype', 'multipart/form-data');
		
		$search = new Zend_Form_Element_Text('fast_switch');
		$search->setLabel("Быстрый поиск канала")
			->addDecorator(new Zend_Form_Decorator_Label );
		
		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setLabel('Перейти')
			->setAttrib('id', 'submitbutton');

		$this->addElements(array($search, $submit));
		$this->setElementDecorators(array('ViewHelper'));
		
    }
}