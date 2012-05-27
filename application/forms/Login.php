<?php
class Xmltv_Form_Login extends Zend_Form
{
	
	protected $formClass='';
	protected $formCss='';
	
	public function __construct($options=array()) {
		
		parent::__construct($options);
		
		$formClass='';
		if (isset($options['form_class']) && !empty($options['form_class'])) {
			$this->formClass = 	$options['form_class'];
		}
			
		
		$login = new Zend_Form_Element_Text('addr');
		$login->setAttribs(array(
				'name'=>'addr',
				'size'=>12,
				'style'=>'width:128px',
			))
			->setLabel('E-mail')
			->setDecorators(array(
				'ViewHelper',
				'Description',
				'Errors',
				array( 'Label', array( 'class'=>'pull-left' ) ),
				array( 'HtmlTag', array( 'tag'=>'div', 'class'=>'pull-left' ) ),
			));
			//->removeDecorator('HtmlTag');
		
		$password = new Zend_Form_Element_Password('psw');
		$password->setAttribs(array( 
				'name'=>'psw',
				'size'=>12,
				'style'=>'width:128px',
			))
			->setLabel('Пароль')
			->setDecorators(array(
				'ViewHelper',
				'Description',
				'Errors',
				array( 'Label', array( 'class'=>'pull-left' ) ),
				array( 'HtmlTag', array( 'tag'=>'div', 'class'=>'pull-left' ) ),
			));

		$button = new Zend_Form_Element_Submit('submit');
		$button->setLabel('Войти')
			->setAttribs(array(
				'id'=>'submitbutton',
				'class'=>'btn btn-primary btn-mini',
			))
			->setDecorators(array(
				'ViewHelper',
				'Description',
				'Errors',
				array( 'HtmlTag', array( 'tag'=>'div', 'class'=>'pull-left' ) ),
			));
			
		$this->addElements(array($login, $password, $button ))
			->setAttribs(array(
				'name'=>"userlogin",
				'action'=>"/user/login",
				'id'=>"userlogin",
				'class'=>"form-horizontal",
			))
			->setMethod('post')
			->setDecorators(array(
				'FormElements',
				'Form',
				array(array('data'=>'HtmlTag'), array('tag'=>'div', 'class'=>$this->formClass)),
			));
					
		return $this;
		
    }
    
}