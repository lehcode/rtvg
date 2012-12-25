<?php
class Xmltv_Form_FastChannelSearchForm extends Zend_Form
{
	
	private $_formClass='fastsearch';
	private $_formCss='';
	private $_actionUrl='';
	private $_labelClass='mediumheading';
	
    public function __construct($options = null)
    {
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
		
		$search = new Zend_Form_Element_Text('fast_search');
		$decorators = array(
            array('ViewHelper'),
            array('Errors'),
            array('Label', array('tag' => 'div', 'class' => $this->_labelClass )),
        );
		
		$search->setLabel("Быстрый поиск по телеканалам")
			->setAttrib('data-provide', 'typeahead')
			->addFilter('StripTags')
			->addValidator('NotEmpty')
    		->addFilter('StringTrim')
    		->setDecorators($decorators)
    		->removeDecorator('HtmlTag');
		
    	$submit = new Zend_Form_Element_Submit('fastsearch-submit');
		$decorators = array(
            array('ViewHelper'),
            array('HtmlTag', array('tag' => 'span')),
        );
    	$submit->setLabel('Перейти')
			->setAttrib('class', 'btn btn-mini btn-primary')
			->setDecorators($decorators)
			->removeDecorator('HtmlTag');
			
		
		$this->addElements(array($search, $submit));
		$formAttribs = array(
			'action'=>'/телепрограмма/поиск/канал',
			'method'=>'post',
			'enctype'=>'application/x-www-form-urlencoded',
			'name'=>'channel-search',
			'class'=>$this->_formClass,
		);
		if (!empty($this->actionUrl)) {
			$formAttribs['action'] = $this->actionUrl;
		}
		$this->setAttribs($formAttribs);
		$this->setDecorators(array(
				'FormElements',
				'Form', array(
					array('data'=>'HtmlTag'),
					array('tag'=>'div', 'class'=>'fastsearch')
				),
			));
		
    }
}