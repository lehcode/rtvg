<?php
/**
 * Быстрый переход по каналам и поиск канала
 * 
 * @author  Antony Repin
 * @version $Id: TypeaheadForm.php,v 1.3 2013-03-05 06:53:19 developer Exp $
 *
 */
class Xmltv_Form_TypeaheadForm extends Zend_Form
{
	
	protected static $formClass;
	protected static $labelClass;
	protected static $htmlTagClass;
	protected static $submitClass;
	
	/**
	 * Форма быстрого поиска канала
	 * 
	 * @param array $options
	 */
    public function __construct($options = null) {
    	
        parent::__construct($options);
		
        $attribs = array(
        	'method'=>'post',
        	'enctype'=>'application/x-www-form-urlencoded',
        	'name'=>'typeahead',
        );
        $this->setAttribs($attribs);
        
        if ( isset($options['action']) && !empty($options['action'])) {
        	$this->setAttrib('action', $options['action']);
        }
        
        $tagProps = array('tag'=>'div');
        if (isset($options['html_tag_class']) && !empty($options['html_tag_class'])){
            $tagProps['class'] = $options['html_tag_class'];
        }
        
        $this->setDecorators(array(
        	'FormElements',
        	'Form', array(
        		array('data'=>'HtmlTag'), $tagProps ),
        ));
        
        self::$submitClass = isset($options['submit_class']) && !empty($options['submit_class']) ? $options['submit_class'] : null ;
		
    }
    
    
    /**
     * (non-PHPdoc)
     * @see Zend_Form::init()
     */
    public function init(){
    	
        $search = new Zend_Form_Element_Text('searchinput');
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
        
        
        $submit = new Zend_Form_Element_Submit('submit');
        $decorators = array(
        	array('ViewHelper'),
        	array('HtmlTag', array('tag' => 'span')),
        );
        $submit->setLabel('>')
        ->setAttrib('id', '')
        ->setDecorators($decorators)
        ->removeDecorator('HtmlTag');
        
        if (self::$submitClass!==null) {
        	$submit->setAttrib('class', self::$submitClass);
        }
         
        $this->addElements(array($search, $submit));
        
        
        
    }
}