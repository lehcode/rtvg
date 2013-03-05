<?php
/**
 * Login form
 *
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @uses    Zend_Form
 * @version $Id: Login.php,v 1.4 2013-03-05 06:53:19 developer Exp $
 *
 */
class Xmltv_Form_Login extends Zend_Form
{
	
	protected static $submitText;
	
	/**
	 * Base login form
	 * 
	 * @param array $options
	 */
	public function __construct(array $options=null){
	    
	    parent::__construct($options);
	    
	    self::$submitText = isset($options['submit_text']) && !empty($options['submit_text']) ? $options['submit_text'] : 'Войти' ;
	    
	    $this->setAttrib('name', 'userlogin');
	    $this->setAttrib('id', 'userlogin');
	    $this->setMethod('post');
	    $class = isset($options['html_tag_class']) && !empty($options['html_tag_class']) ? $options['html_tag_class'] : null ;
	    
	    $tagProps = array('tag'=>'div');
	    $htmlTagClass = isset($options['class']) && !empty($options['class']) ? $options['class'] : null ;
	    if ($htmlTagClass!==null){
	    	$tagProps['class'] = $htmlTagClass;
	    }
	    
	    $this->setDecorators(array(
	    		'FormElements',
	    		'Form',
	    		array(array('data'=>'HtmlTag'), $tagProps),
	    ));
	    
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Zend_Form::init()
	 */
	public function init() {
		
	    $login = new Zend_Form_Element_Text('openid');
		$login->setAttribs( array(
			'name'=>'openid',
			'size'=>12,
			'style'=>'width:128px',
		))
		->setLabel('E-mail')
		->setDecorators(array(
			'ViewHelper',
			//'Description',
			'Errors',
			array( 'Label', array( 'class'=>'pull-left' ) ),
			array( 'HtmlTag', array( 'tag'=>'div', 'class'=>'pull-left' ) ),
		))
		->setValidators( array( new Zend_Validate_Regex( '/^[\w\d\._-]{2,128}@[\w\d\._-]{2,128}\.[\w]{2,4}$/ui' )) );
		
		$password = new Zend_Form_Element_Password('pw');
		$password->setAttribs(array( 
			'name'=>'passwd',
			'size'=>12,
			'style'=>'width:128px', 
		))
		->setLabel('Пароль')
		->setDecorators(array(
			'ViewHelper',
			'Description',
			'Errors',
				array( 'Label', array( 
					'class'=>'pull-left',
				)),
				array( 'HtmlTag', array( 
					'tag'=>'div',
					'class'=>'pull-left',
				)),
		))
		->setValidators( array( new Zend_Validate_Regex( '/^[\w\d]{6,32}$/ui' )));
		
		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setAttrib('id', 'submitbutton');
		$submit->setAttrib('class', 'btn btn-primary btn-mini');
		$submit->setDecorators(array(
				'ViewHelper',
				'Description',
				'Errors',
				array( 'HtmlTag', array( 'tag'=>'div', 'class'=>'pull-left' ) ),
		));
		 
		$submit->setLabel( self::$submitText );
		
		$this->addElements( array($login, $password, $submit));
		
		
    }
    
}