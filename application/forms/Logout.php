<?php
/**
 *
 * Logout form class
 *
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @package rutvgid
 * @version $Id: Logout.php,v 1.3 2013-03-17 17:19:11 developer Exp $
 */
class Xmltv_Form_Logout extends Zend_Form
{
    
    /**
     * Form class
     * @var string
     */
    protected $formClass;
    
    /**
     * Button text
     * @var string
     */
    protected $submitLabel;

    /**
     * Button text
     * @var string
     */
    protected $htmlTagClass;

    /**
     * Base logout form
     *
     * @param array $options
     */
    public function __construct(array $options=null) {
        
    	$this->setAttrib('id', 'logoutform');
    	$this->submitLabel = isset($options['submit_text']) && !empty($options['submit_text']) ? $options['submit_text'] : 'Выйти' ;
        $this->formClass = isset($options['form_class']) && !empty($options['form_class']) ? $options['form_class'] : null ;
        $this->htmlTagClass = isset($options['html_tag_class']) && !empty($options['html_tag_class']) ? $options['html_tag_class'] : null ;
        parent::__construct($options);
        
    }
    
    /**
     * (non-PHPdoc)
     * @see Zend_Form::init()
     */
	public function init() {
		
		$submit = new Zend_Form_Element_Submit('submit');
        $submit->setAttribs( array('class'=>'btn'))
        	->setAttrib( 'type', 'submit' )
        	->setDecorators( array(
        		array('ViewHelper') ))
        	->setLabel( $this->submitLabel );
        
        $this->addElement( $submit );
	    
        // Wrapper properties
        $props = array('tag'=>'div');
        if ((bool)$this->htmlTagClass!==false){
        	$props['class'] = $this->htmlTagClass;
        }
        $this->setDecorators(array(
        	'FormElements',
        	'Form', array( array('data'=>'HtmlTag'), $props),
        ));
        	
	}
}