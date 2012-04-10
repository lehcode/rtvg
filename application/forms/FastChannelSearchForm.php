<?php
class Xmltv_Form_FastChannelSearchForm extends Zend_Form
{
    public function __construct($options = null)
    {
		parent::__construct($options);

		$this->setName('document');
		$this->setAction("");
		$this->setAttrib('enctype', 'multipart/form-data');
		
		$search = new Zend_Form_Element_Text('fast_search');
		$search->setLabel("Быстрый поиск канала")
			->setAttrib('data-provide', 'typeahead')
			->removeDecorator('HtmlTag');
		
		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setLabel('Перейти')
			->setAttrib('id', 'submitbutton')
			->removeDecorator('DtDdWrapper');
		
		$this->addElements(array($search, $submit));
		//$label_decorator = new Zend_Form_Decorator_Label();
		//$label_decorator->setTag('div');
		$this->setElementDecorators(array('Label'), array('fast_switch'));
		$this->setDecorators(  
			array('FormElements', 
				array(
					array('data'=>'HtmlTag'),
					//array('tag'=>'div', 'class'=>'form_wrap')
			)));
		
    }
}