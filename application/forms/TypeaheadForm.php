<?php
/**
 * Быстрый переход по каналам и поиск канала
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: TypeaheadForm.php,v 1.2 2013-01-12 09:06:22 developer Exp $
 *
 */
class Xmltv_Form_TypeaheadForm extends Zend_Form
{
	
	private $_formClass;
	private $_formCss;
	private $_actionUrl;
	private $_labelClass;
	
    public function __construct($options = null) {
    	
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
		
		if (isset($options['label_class']) && !empty($options['label_class'])) {
			$this->_labelClass = $options['label_class'];
		}
		
		/*
		 * Search input
		 */
		$search = new Zend_Form_Element_Text('searchinput');
		//Decorators
		$decorators = array( 
			array('ViewHelper'),
			array('Errors'),
			array('Label', array( 'tag' => null )));
		
		$search->setLabel("Быстрый поиск по телеканалам")
			->setAttrib('data-provide', 'typeahead')
			->setAttrib('class', 'span4')
			->addFilter('StripTags')
			->addValidator('NotEmpty')
    		->addFilter('StringTrim')
    		->setDecorators($decorators)
    		->removeDecorator('HtmlTag');
		
		/*
		 * Submit button
		 */
    	$submit = new Zend_Form_Element_Submit('submit');
    	//Decorators
		$decorators = array(
            array('ViewHelper'),
            array('HtmlTag', array('tag' => 'span')),
        );
    	$submit->setLabel('>')
			->setAttrib('class', 'btn btn-primary')
			->setAttrib('id', '')
			->setDecorators($decorators)
			->removeDecorator('HtmlTag');

    	/**
    	 * Seach type if defined
    	 * @var string
    	 */
    	$type = new Zend_Form_Element_Hidden('type');
    	$type->setDecorators(array('ViewHelper'))
    		->setAttrib('id', '')
    		->setValue('channel');
    	
		$view = new Zend_View();
		$this->addElements(array($search, $submit, $type));
		$formAttribs = array(
			'action'=>$view->url(array(), 'default_search_search'),
			'method'=>'post',
			'enctype'=>'application/x-www-form-urlencoded',
			'name'=>'typeahead',
		);
		if (!empty($this->actionUrl)) {
			$formAttribs['action'] = $this->actionUrl;
		}
		$this->setAttribs($formAttribs);
		$this->setDecorators(array(
			'FormElements',
			'Form', array(
				//array('data'=>'HtmlTag'),
				//array('tag'=>'div', 'class'=>$this->_formClass)
			),
		));
		
    }
}