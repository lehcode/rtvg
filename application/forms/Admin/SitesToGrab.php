<?php
/**
 * 
 * @author	     Antony Repin
 * @subpackage   backend
 * @version	$Id: SitesToGrab.php,v 1.1 2013-04-11 05:21:11 developer Exp $
 *
 */
class Xmltv_Form_Admin_SitesToGrab extends Zend_Form
{
    
    private $formClass;
    private $formId;
    
    public function __construct( $options=array() ){
        $this->formClass = (isset($options['form_class']) && !empty($options['form_class'])) ? $options['form_class'] : null ;
        $this->formId    = (isset($options['form_id']) && !empty($options['form_id'])) ? $options['form_id'] : null ;
        parent::__construct(array());
    }
    
    public function init( )
    {
        $view = new Zend_View();
        
        $actionUrl = $view->url(array(
			'site' => null,
			'weekstart' => null,
		), 'admin_grab_do');
        $this->setDecorators(array(
        	'Description',
			'FormElements',
			'Form', array(
				array('data'=>'HtmlTag'), array(
					'tag'=>'div',
					'class'=>'row-fluid')),
		))
		->setAttrib( 'class', $this->formClass )
		->setAttrib( 'id', $this->formId )
		->setAttrib( 'action', $actionUrl )
		->setMethod( 'post');
		
		$parsers = array(
			'vsetvcom'=>'vsetv.com',
			'tvyandexru'=>'m.tv.yandex.ru',
		);
		
		$parser = new Zend_Form_Element_Radio( 'site' );
		$parser->setMultiOptions( $parsers )
			->setRequired(true)
			->setDecorators(array(
				array( 'ViewHelper' ),
				array( 'Label', array( 'class'=>'label label-info' )),
				array( 'HtmlTag', array( 'tag'=>'fieldset', 'class'=>'container-fluid', 'id'=>'income' )),
			))
			->setSeparator( '&nbsp;' )
			->setLabel("Сайт");
		/* 
		$cb = new Zend_Form_Element_Checkbox('thisweek');
		$cb->setDecorators(array(
				'ViewHelper',
				'Label',
		))
		->setLabel('Эта неделя?');
		 */
		
		$dateStart = new ZendX_JQuery_Form_Element_DatePicker( 'weekstart');
		$dateStart->setLabel('Начальная дата');
		
		/*
		 * #########################################
		* Add elements and display groups
		* #########################################
		*/
		$this->addDisplayGroup( array($parser, $dateStart), 'parsersGrp' );
		$titleGroup = $this->getDisplayGroup('parsersGrp');
		$titleGroup->setDecorators(array(
				'FormElements',
				array('Fieldset', array('class'=>'')),
		));
		/*
		$date = new Zend_Form_Element_Hidden('date');
		$date->setValue( $this->start->toString('YYYY-MM-dd') )
			->setDecorators(array('ViewHelper'));
		*/
		
		$button = new Zend_Form_Element_Submit('submit');
		$button->setLabel('Submit')
			->setDecorators( array('ViewHelper') )
			->setAttrib( 'class', 'btn' );
		
		
		
		$this->addElements(array($dateStart, $button));
        
    }
}