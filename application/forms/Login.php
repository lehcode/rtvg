<?php
/**
 * Login form
 *
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @uses    Zend_Form
 * @version $Id: Login.php,v 1.3 2013-03-03 23:34:13 developer Exp $
 *
 */
class Xmltv_Form_Login extends Zend_Form
{
	
	protected $class;
	
	/**
	 * Constructor
	 * @param array $options
	 */
	public function __construct(array $options=null){
	    
	    parent::__construct($options);
	    
	    //var_dump($this);
	    //die(__FILE__.': '.__LINE__);
	    
	    $submit = new Zend_Form_Element_Submit('submit');
	    $submit->setAttrib('id', 'submitbutton');
	    $submit->setAttrib('class', 'btn btn-primary btn-mini');
		$submit->setDecorators(array(
			'ViewHelper',
			'Description',
			'Errors',
			array( 'HtmlTag', array( 'tag'=>'div', 'class'=>'pull-left' ) ),
		));
	    $label = isset($options['submit_text']) && !empty($options['submit_text']) ? $options['submit_text'] : 'Войти' ;
	    $submit->setLabel( $label );
	    $this->addElement( $submit );
	    
	    $this->class = isset($options['class']) && !empty($options['class']) ? $options['class'] : null ;
	    
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Zend_Form::init()
	 */
	public function init($options=array()) {
		
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
		
		$this->addElement($login);
		$this->addElement($password);
		
		$this->setAttrib('name', 'userlogin');
		$this->setAttrib('id', 'userlogin');
		$this->setMethod('post');
		
		$htmlTagProps = array('tag'=>'div');
		if ($this->class!==null){
			$htmlTagProps['class'] = $this->class;
		}
		$this->setDecorators(array(
			'FormElements',
			'Form',
			array(array('data'=>'HtmlTag'), $htmlTagProps),
		));
		
    }
    
}