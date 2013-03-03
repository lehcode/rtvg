<?php
/**
 * 
 * Logout from class
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @package sosedionline
 * @filesource $Source: /home/developer/cvs/rutvgid.ru/application/forms/Logout.php,v $
 * @version $Id: Logout.php,v 1.1 2013-03-03 23:33:51 developer Exp $
 */
class Xmltv_Form_Logout extends Zend_Form
{
    
    /**
     * Form class
     * @var string
     */
    private $class;

    /**
     * Constructor
     * 
     * @param array $options
     */
    public function __construct(array $options=null) {
        
        parent::__construct($options);
        
        $submit = new Zend_Form_Element_Submit('submit');
        $decorators = array( 
        	array('ViewHelper'));
        $submit->setAttrib( 'class', 'btn' );
        $submit->setAttrib( 'type', 'submit' );
        $submit->setDecorators( $decorators );
        $label = isset($options['submit_text']) && !empty($options['submit_text']) ? $options['submit_text'] : 'Выйти' ;
        $submit->setLabel( $label );
        $this->addElement( $submit );
        
    }
    
    /**
     * (non-PHPdoc)
     * @see Zend_Form::init()
     */
	public function init() {
		
	    $this->setAttrib('id', 'logoutform');
        $this->setDecorators(array( 'FormElements', 'Form' ));
        
        $props = array('tag'=>'div');
        if ($this->class!==null){
        	$props['class'] = $this->class;
        }
        $this->setDecorators(array(
        		'FormElements',
        		'Form',
        		array(array('data'=>'HtmlTag'), $props),
        ));
		
	}
}