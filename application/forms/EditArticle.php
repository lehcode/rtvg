<?php
/**
 * Редактирование статьи
 * 
 * @author	 Antony Repin
 * @subpackage backend
 * @version	$Id: EditArticle.php,v 1.3 2013-03-22 17:51:44 developer Exp $
 *
 */
class Xmltv_Form_EditArticle extends ZendX_JQuery_Form
{
	
	/**
	 * @var array
	 */
	private $options;

	/**
	 * @var array
	 */
	private $data;
	
	/**
	 * @var array
	 */
	private $categories = array();

	/**
	 * @var array
	 */
	private $authors = array();

	/**
	 * @var Xmltv_User
	 */
	private $user;
	
	
	/**
	 * Edit article form
	 * @param array $options
	 */
	public function __construct($options=array(), $data=array())
	{
		if (isset($data['categories']) && !empty($data['categories'])) {
			$this->categories = $data['categories'];
		}

		if (isset($data['authors']) && !empty($data['authors'])) {
			$this->authors = $data['authors'];
		}

		if (isset($data['user']) && !empty($data['user'])) {
			$this->user = $data['user']->toArray();
		}
		
		if (isset($data) && !empty($data)) {
			unset($data['categories']);
			$this->data = $data['article'];
		}
		
		$this->options = $options;
		
		$this->init();
		
	}
	
	
	
	/**
	 * (non-PHPdoc)
	 * @see Zend_Form::init()
	 */
	public function init()
	{
		parent::init();
		
		if (APPLICATION_ENV=='development'){
			//var_dump($this->data);
			//var_dump($this->categories);
			//die(__FILE__.': '.__LINE__);
		}
		
		$textDecorators = array(
			array('ViewHelper'),
			array('Errors'),
			array('Label', array( 'tag' => 'label' )),
			array('HtmlTag', array( 'tag' => 'div', 'class'=>'row-fluid span9', 'style'=>'' )));
		
		// Title
		$title = new Zend_Form_Element_Text('title');
		$title->setLabel( "Название" )
			->setDecorators($textDecorators)
			->setValue( $this->data['title'] )
			->setOptions( array(
				'class'=>'span12'
			))
			->setRequired(true);
		
		// Alias
		$alias = new Zend_Form_Element_Hidden('alias');
		$alias->setValue($this->data['alias'])
			->setDecorators(array('ViewHelper'));
		
		// Dropdowns
		$categoryDropdown = array();
		$dropdownDecorators = array(
			array('ViewHelper'),
			array('Errors'),
			array('Label', array( 'tag' => 'div' )),
			array('HtmlTag', array( 'tag' => 'div', 'class'=>'label', 'style'=>'margin: 0.5em;background-color: darkRed' )));
		
		// Programs category dropdown
		$categoryDropdown['program'] = new Zend_Form_Element_Select('progcat');
		$options = array(''=>Rtvg_Message::FORM_SELECT_ONE);
		$selected = 0;
		foreach ($this->categories['programs'] as $cat){
			$options[$cat['id']] = $cat['title'];
			if ((int)$this->data['prog_cat'] == (int)$cat['id']){
				$selected = (int)$cat['id'];
			}
		}
		$categoryDropdown['program']->setMultiOptions($options)
			->setValue($selected)
			->setLabel("Передачи")
			->setDecorators($dropdownDecorators);
		
		// Content category dropdown
		$categoryDropdown['content'] = new Zend_Form_Element_Select('contcat');
		$options = array(''=>Rtvg_Message::FORM_SELECT_ONE);
		$selected = 0;
		foreach ($this->categories['content'] as $cat){
			$options[$cat['id']] = $cat['title'];
			if ((int)$this->data['content_cat'] == (int)$cat['id']){
				$selected = (int)$cat['id'];
			}
		}
		
		$categoryDropdown['content']->setMultiOptions($options)
			->setValue($selected)
			->setLabel("Контент")
			->setDecorators($dropdownDecorators);
		
		// Channel category dropdown
		$categoryDropdown['channel'] = new Zend_Form_Element_Select('chcat');
		$options = array(''=>Rtvg_Message::FORM_SELECT_ONE);
		$selected = 0;
		foreach ($this->categories['channel'] as $cat){
			$options[$cat['id']] = $cat['title'];
			if ((int)$this->data['channel_cat'] == (int)$cat['id']){
				$selected = (int)$cat['id'];
			}
		}
		$categoryDropdown['channel']->setMultiOptions($options)
			->setValue($selected)
			->setLabel("Каналы")
			->setDecorators($dropdownDecorators);

		$textareaDecorators = array(
			array('ViewHelper'),
			array('Errors'),
			array('Label', array( 'tag' => 'div' )),
			array('HtmlTag', array( 'tag' => 'div', 'class'=>'span6', 'style'=>'' )));

		// Article intro
		$intro = new Zend_Form_Element_Textarea('intro');
		$intro->setValue( html_entity_decode($this->data['intro']) )
			->setLabel( 'Лид статьи ['.Xmltv_String::strlen($this->data['intro']).']' )
			->setOptions(array(
				'rows'=>24,
				'class'=>'span12',
			))
			->setDecorators($textareaDecorators)
			->setRequired(true);
		
		// Article body
		$body = new Zend_Form_Element_Textarea('body');
		$body->setValue( html_entity_decode($this->data['body']) )
			->setLabel( 'Тело статьи ['.Xmltv_String::strlen($this->data['body']).']' )
			->setOptions( array(
				'rows'=>24,
				'class'=>'span12',
			))
			->setDecorators($textareaDecorators)
			->setRequired(true)
			->setAllowEmpty(true);
		
		$textfieldDecorators = array(
			array('ViewHelper'),
			array('Errors'),
			array('Label', array( 'tag' => 'div' )),
			array('HtmlTag', array( 'tag' => 'div', 'class'=>'row-fluid span12', 'style'=>'', 'id'=>'article-tags' )));
		
		// Tags
		$tags = new Zend_Form_Element_Text('tags');
		$tags->setValue($this->data['tags'])
			->setLabel('Темы')
			->setOptions(array(
				'class'=>'span10',
				'style'=>'',
			))
			->setDecorators($textfieldDecorators)
			->setAllowEmpty(true);
		
		// Author
		$author = new Zend_Form_Element_Select('author');
		$options = array(''=>Rtvg_Message::FORM_SELECT_ONE);
		$selected = 0;
		foreach ($this->authors as $person){
			$options[$person['id']] = $person['display_name'];
			if ((int)$this->data['author'] == (int)$person['id']){
				$selected = (int)$person['id'];
			}
		}
		$author->setMultiOptions($options)
			->setValue($selected)
			->setLabel("Автор")
			->setDecorators($dropdownDecorators);
		
		// Dates decorators
		$dropdownDecorators = array(
			array('UiWidgetElement'),
			array('Errors'),
			array('Label', array( 'tag' => 'div' )),
			array('HtmlTag', array( 'tag' => 'div', 'class'=>'label label-info', 'style'=>'margin: 0.5em;' ))
		);
		
		// Dates
		$dateAdded = new ZendX_JQuery_Form_Element_DatePicker( 'added');
		$date = new Zend_Date($this->data['added'], 'YYYY-MM-dd');
		$dateAdded->setLabel('Создана')
			->setValue($date->toString('dd.MM.YYYY'))
			->addValidator(new Zend_Validate_Date(array('format' => 'dd.MM.YYYY')))
			->setDecorators($dropdownDecorators)
			->setOptions(array('style'=>'margin:0.5em;'))
			->setRequired(true);
		
		$publishUp = new ZendX_JQuery_Form_Element_DatePicker( 'publish_up');
		if ($this->data['publish_up']!='0000-00-00 00:00:00') {
			$date = new Zend_Date($this->data['publish_up'], 'YYYY-MM-dd HH:mm:ss');
			$publishUp->setValue($date->toString('dd.MM.YYYY'));
		} else {
			$publishUp->setValue( Zend_Date::now()->toString('dd.MM.YYYY'));
		}
		$publishUp->setLabel('Начало публикации')
			->addValidator(new Zend_Validate_Date(array('format' => 'dd.MM.YYYY')))
			->setDecorators($dropdownDecorators)
			->setOptions(array('style'=>'margin:0.5em;'))
			->setRequired(true);
		
		$publishDown = new ZendX_JQuery_Form_Element_DatePicker( 'publish_down');
		$publishDown->setLabel('Снять с публикации');
		$date = new Zend_Date($this->data['publish_down'], 'YYYY-MM-dd HH:mm:ss');
		$publishDown->setValue( Zend_Date::now()->subDay(1)->toString( 'dd.MM.YYYY' ) );
		if ($this->data['publish_down']!='0000-00-00 00:00:00'){
			$publishDown->setValue($date->toString('dd.MM.YYYY'));
		}
		$publishDown->addValidator(new Zend_Validate_Date(array('format' => 'dd.MM.YYYY')))
			->setDecorators($dropdownDecorators)
			->setOptions(array('style'=>'margin:0.5em;'))
			->setAllowEmpty(true);
		
		
		// Published checkbox
		$published = new Zend_Form_Element_Checkbox('published');
		$published->setDecorators(array(
			array('ViewHelper'),
			array('Label', array( 'tag' => 'label', 'for'=>'published' )),
			array('HtmlTag', array( 'tag' => 'div', 'class'=>'span1' ))
		));
		$published->setLabel("Публ.")
			->setValue((int)$this->data['published']);
		
		// Add elements and display groups
		$this->addDisplayGroup( array(
			$title,
			$published), 'titlegrp' );
		$titleGroup = $this->getDisplayGroup('titlegrp');
		$titleGroup->setDecorators(array(
			'FormElements',
			array('Fieldset', array('id'=>'title-set', 'class'=>'container-fluid')),
		));
		
		$this->addDisplayGroup( array(
			$categoryDropdown['program'],
			$categoryDropdown['content'],
			$categoryDropdown['channel']), 'categoriesgrp' );
		$categoriesGroup = $this->getDisplayGroup('categoriesgrp');
		$categoriesGroup->setDecorators(array(
			'FormElements',
			array('Fieldset', array('id'=>'categories-set', 'class'=>'container-fluid')),
		));
		$categoriesGroup->setLegend("Категории статьи");
		
		$this->addElement($tags);
		
		$this->addDisplayGroup( array( 
			$intro,
			$body ), 'textgrp' );
		$textGroup = $this->getDisplayGroup('textgrp');
		$textGroup->setDecorators(array(
			'FormElements',
			array('Fieldset', array('id'=>'text-set', 'class'=>'row-fluid span9')),
		));
		
		$this->addDisplayGroup( array( 
			$author, 
			$dateAdded,
			$publishUp,
			$publishDown ), 'propsgrp'
		);
		$propsGroup = $this->getDisplayGroup('propsgrp');
		$propsGroup->setDecorators(array(
			'FormElements',
			array('Fieldset', array('id'=>'props-set', 'class'=>'row-fluid span1')),
		));
		
		if ($this->getView()->isAllowed('admin:content.article', 'income')){
			
		    $incomeTypes = array(
				'is_ref'=> 'Реф.',
				'is_cpa'=>'CPA',
				'is_paid'=>'Плат.',
			);
			$income = new Zend_Form_Element_Radio('income');
			$income->setMultiOptions($incomeTypes)
				->setRequired(true)
				->setDecorators(array(
					array( 'ViewHelper' ),
					array( 'Errors' ),
					array( 'HtmlTag', array( 'tag'=>'div', 'class'=>'row-fluid span12', 'id'=>'income' )),
				));
			$selected=0;
			foreach ($incomeTypes as $type=>$rus){
			    if ((int)$this->data[$type]==1){
			        $income->setValue($type);
			    }
			}
			$income->setLabel("Доход");
			$this->addElement($income);
			
			
		} else {
			
			// Article has referral link
			$isRef = new Zend_Form_Element_Hidden('is_ref');
			$isRef->setDecorators(array( array('ViewHelper')))
				->setValue((int)$this->data['is_ref']);
			
			// Article is paid
			$isPaid = new Zend_Form_Element_Hidden('is_paid');
			$isPaid->setDecorators(array( array('ViewHelper')))
				->setValue((int)$this->data['is_paid']);
			
			// Article has CPA ad (default)
			$isCpa = new Zend_Form_Element_Hidden('is_cpa');
			$isCpa->setDecorators(array(array('ViewHelper')))
				->setValue((int)$this->data['is_cpa']);
			
			$this->addElements(array($isRef, $isPaid, $isCpa));
		}
		
		$do = new Zend_Form_Element_Hidden('do');
		$do->setDecorators(array('ViewHelper'));

		$id = new Zend_Form_Element_Hidden('id');
		$id->setDecorators(array('ViewHelper'))
			->setValue((int)$this->data['id'])
			->setName('idx[]');

		$hits = new Zend_Form_Element_Hidden('hits');
		$hits->setDecorators(array('ViewHelper'))
			->setValue((int)$this->data['hits']);
		
		$this->addElements(array($alias, $do, $id, $hits));
		
		$this->setDecorators(array(
			'FormElements',
			'Form', array(
				array('data'=>'HtmlTag'), array(
					'tag'=>'div',
					'class'=>'row-fluid')),
		))
			->setAttrib('class', $this->options['class'])
			->setAttrib('id', $this->options['id'])
			->setAttrib('action', $this->getView()->baseUrl('admin/content/edit'))
			->setMethod('post');
		
	}
}