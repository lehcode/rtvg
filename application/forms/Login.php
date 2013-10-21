<?php
/**
 * Login form
 *
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @uses    Zend_Form
 * @version $Id: Login.php,v 1.5 2013-03-11 13:55:37 developer Exp $
 *
 */
class Xmltv_Form_Login extends Zend_Form
{
	
	/**
	 * Base login form
	 * 
	 * @param array $options
	 */
	public function __construct(array $options=null){
	    
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
	    
	    parent::__construct($options);
	    	    
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
			'Errors',
			array( 'Label', array( 'class'=>'pull-left' ) ),
			array( 'HtmlTag', array( 'tag'=>'div', 'class'=>'pull-left' ) ),
		))
		->setValidators( array( new Zend_Validate_Regex( Rtvg_Regex::EMAIL_REGEX )) );
		
		$password = new Zend_Form_Element_Password('passwd');
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
		->setValidators( array( new Zend_Validate_Regex( Rtvg_Regex::PASSWORD_REGEX )));
		
		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setAttrib( 'id', 'submitbutton' );
		$submit->setAttrib( 'class', 'btn btn-primary btn-mini' );
		$submit->setLabel( "Войти" );
		$submit->setDecorators(array(
			'ViewHelper',
			'Description',
			'Errors',
			array( 'HtmlTag', array(
				'tag'=>'div',
				'class'=>'pull-left',
			)),
		));
				
		$this->addElements( array($login, $password, $submit));
		
		
    }
    
}