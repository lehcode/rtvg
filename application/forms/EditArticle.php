<?php
/**
 * Редактирование статьи
 * 
 * @author	 Antony Repin
 * @subpackage backend
 * @version	$Id: EditArticle.php,v 1.5 2013-04-11 05:21:11 developer Exp $
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
	 * @var Zend_View
	 */
	private $view;

	/**
	 * @var array
	 */
	private $acTags;
	
	
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

		if (isset($data['ac_tags']) && !empty($data['ac_tags'])) {
			$this->acTags = $data['ac_tags'];
		}
		
		if (isset($data) && !empty($data)) {
			unset($data['categories']);
			$this->data = $data['article'];
		}
		
		$this->options = $options;
		
		$this->view = $this->getView();
		
		$this->view->headScript()->appendScript("function toggleForm( doAction ){
			var form = $('#".$this->options['id']."');
			$(form).attr('action','".$this->view->baseUrl('admin/content/edit')."?do='+doAction+'&idx[]=".$this->data['id']."');
			$('input#do').val(doAction);
			$(form).submit();
		}
		$(document).ready(function() {
			$('button#new').click(function(e){ e.stopPropagation(); toggleForm($(this).attr('id'));  });
			$('button#toggle').click(function(e){ e.stopPropagation(); toggleForm($(this).attr('id')); });
			$('button#delete').click(function(e){ e.stopPropagation(); toggleForm($(this).attr('id')); });
			$('button#apply').click(function(e){ e.stopPropagation(); toggleForm($(this).attr('id')); });
			$('button#save').click(function(e){ e.stopPropagation(); toggleForm($(this).attr('id')); });
			$('button#save-plus').click(function(e){ e.stopPropagation(); toggleForm($(this).attr('id')); });
			var form = $('#".$this->options['id']."');
			$(form).validate({
				rules: {
					title:{
						required: true,
						minlength: 25
					},
					prog_cat: 'required',
					channel_cat: 'required',
					intro: {
						required: true,
						minlength: 128
					},
					body: {
						required: true,
						minlength: 380
					},
					metadesc: {
						required: false,
						minlength: 24
					},
					tags: {
						required: false,
						minlength: 5
					},
				}
			});
		});
		;");
		
		$this->init();
		
	}
	
	
	
	/**
	 * (non-PHPdoc)
	 * @see Zend_Form::init()
	 */
	public function init()
	{
		parent::init();
		
				
		/*
		 * #########################################
		 * Title
		 * #########################################
		 */ 
		$title = new Zend_Form_Element_Text( 'title' );
		$regex = new Zend_Validate_Regex( '/[\p{Common}\p{Cyrillic}\p{Latin}]+/ui' );
		$regex->setMessage( "Не указано название статьи.[форма]" );
		$title->setLabel( "Название" )
			->setDecorators(array(
				array('ViewHelper'),
				array('Errors'),
				array('Label', array()),
				array('HtmlTag', array('class'=>'label label-important span8', 'style'=>'margin: 16px 16px 16px 0;' ))
			))
			->setValidators( array( $regex ))
			->setValue( $this->data['title'] )
			->setOptions( array(
				'class'=>'span12'
			))
			->setRequired(true);
		/* 
		$this->view->jQuery()
			->addJavascript("$(document).ready(function() {
		    $('input#title').blur(function(){
				$('input#alias').val( '".$this->data['alias']."' );
			});
		});");
		 */
		/*
		 * #########################################
		 * Alias
		 * #########################################
		 */ 
		$alias = new Zend_Form_Element_Hidden('alias');
		$alias->setValue($this->data['alias'])
			->setDecorators(array('ViewHelper'))
			->setAllowEmpty(true);
		
		/*
		 * #########################################
		 * Categories dropdowns
		 * #########################################
		 */ 
		$categoryDropdown = array();
		$dropdownDecorators = array(
			array('ViewHelper'),
			array('Errors'),
			array('Label', array()),
			array('HtmlTag', array('class'=>'label label-important', 'style'=>'margin: 16px 16px 16px 0;' )));
		
		// Content category dropdown
		$categoryDropdown['content'] = new Zend_Form_Element_Select('content_cat');
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
			->setDecorators($dropdownDecorators)
			->setAllowEmpty(true);
		
		// Channel category dropdown
		$categoryDropdown['channel'] = new Zend_Form_Element_Select('channel_cat');
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
			->setDecorators($dropdownDecorators)
			->setRequired(true);

		$textareaDecorators = array(
			'ViewHelper',
			'Errors',
			array('Label', array('class'=>'label label-important')),
			array('HtmlTag', array('class'=>'span6' )),
		);
		
		// Programs category dropdown
		$categoryDropdown['program'] = new Zend_Form_Element_Select('prog_cat');
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

		/*
		 * #########################################
		 * Article intro
		 * #########################################
		 */ 
		$intro = new Zend_Form_Element_Textarea('intro');
		$intro->setValue( $this->data['intro'] )
			->setLabel( 'Лид статьи ['.Xmltv_String::strlen($this->data['intro']).']' )
			->setOptions(array(
				'rows'=>24,
				'class'=>'span12',
			))
			->setDecorators($textareaDecorators)
			->setRequired(true);
		
		/*
		 * #########################################
		 * Article body
		 * #########################################
		 */ 
		$body = new Zend_Form_Element_Textarea('body');
		$body->setValue( $this->data['body'] )
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
			array('Label', array()),
			array('HtmlTag', array( 'class'=>'row-fluid span12', 'style'=>'', 'id'=>'article-tags' )));
		
		/*
		 * #########################################
		 * Tags
		 * #########################################
		 */ 
		$tags = new ZendX_JQuery_Form_Element_AutoComplete( 'tags' );
		$tags->setValue($this->data['tags'])
			->setLabel('Темы')
			->setOptions(array(
				'class'=>'span10',
				'style'=>'',
			))
			->setDecorators(array(
				'UiWidgetElement',
				'Errors',
				array('Label', array('class'=>'label label-important')),
				array('HtmlTag', array('tag'=>'fieldset', 'class'=>'container-fluid' )),
			))
			->setAllowEmpty(true)
			->addErrorMessage("Не указаны теги")
			->setJQueryParam( 'source', $this->acTags );
		
		/*
		 * #########################################
		 * Author
		 * #########################################
		 */ 
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
			->setDecorators(array(
				'ViewHelper',
				'Errors',
				array('Label', array('class'=>'label label-info')),
				array('HtmlTag', array('class'=>'span3')),
			));
		
		/*
		 * #########################################
		 * Dates
		 * #########################################
		 */
		// Creation date 
		$dateCreated = new ZendX_JQuery_Form_Element_DatePicker( 'added' );
		$date = new Zend_Date($this->data['added'], 'YYYY-MM-dd');
		$dateCreated->setLabel('Создана')
			->setValue( $date->toString('dd.MM.YYYY') )
			->addValidator( new Zend_Validate_Date(array('format' => 'dd.MM.YYYY')) )
			->setDecorators(array(
				'UiWidgetElement',
				'Errors',
				array('Label', array( 'class'=>'label label-info' )),
				array('HtmlTag', array( 'class'=>'span3', 'style'=>'' ))
			))
			->setOptions(array('style'=>'margin:0.5em;'))
			->setRequired(true);
		
		// Start publish
		$publishUp = new ZendX_JQuery_Form_Element_DatePicker( 'publish_up');
		if (isset($this->data['publish_up']) && $this->data['publish_up']!==null && $this->data['publish_up']!='0000-00-00') {
			$upDate = new Zend_Date($this->data['publish_up'], 'YYYY-MM-dd');
			$publishUp->setValue( $upDate->toString('dd.MM.YYYY') );
		} else {
			$publishUp->setValue( Zend_Date::now()->toString('dd.MM.YYYY'));
		}
		$publishUp->setLabel('Начало публикации')
			->addValidator(new Zend_Validate_Date(array('format' => 'dd.MM.YYYY')))
			->setDecorators(array(
				'UiWidgetElement',
				'Errors',
				array('Label', array( 'class'=>'label label-info' )),
				array('HtmlTag', array( 'class'=>'span3', 'style'=>'' ))
			))
			->setOptions(array('style'=>'margin:0.5em;'))
			->setRequired(true);
		
		// End publish
		$publishDown = new ZendX_JQuery_Form_Element_DatePicker( 'publish_down');
		$publishDown->setLabel('Снять с публикации');
		$publishDown->setValue( Zend_Date::now()->subDay(1)->toString( 'dd.MM.YYYY' ) );
		if (isset($this->data['publish_down']) && $this->data['publish_down']!==null && $this->data['publish_down']!='0000-00-00'){
		    $downDate = new Zend_Date( $this->data['publish_down'], 'YYYY-MM-dd' );
			$publishDown->setValue( $downDate->toString('dd.MM.YYYY') );
		} else {
		    if (isset($upDate)){
		    	$publishDown->setValue( $upDate->subDay(1)->toString('dd.MM.YYYY') );
		    } else {
		        $publishDown->setValue( Zend_Date::now()->subDay(1)->toString('dd.MM.YYYY') );
		    }
		}
		$publishDown->addValidator(new Zend_Validate_Date(array('format' => 'dd.MM.YYYY')))
			->setDecorators(array(
				'UiWidgetElement',
				'Errors',
				array('Label', array( 'class'=>'label label-info' )),
				array('HtmlTag', array( 'class'=>'span3', 'style'=>'' ))
			))
			->setOptions(array('style'=>'margin:0.5em;'))
			->setAllowEmpty(true);
		
		
		/*
		 * #########################################
		 * Published checkbox
		 * #########################################
		 */
		$published = new Zend_Form_Element_Checkbox('published');
		$published->setDecorators(array(
			array('ViewHelper'),
			array('Label', array('class'=>'label label-important')),
			array('HtmlTag', array('class'=>'span1'))
		));
		$published->setLabel("Публ.")
			->setValue((int)$this->data['published']);
		
		/*
		 * #########################################
		 * Hit count (disabled textarea)
		 * #########################################
		 */
		$hits = new Zend_Form_Element_Text('hittext');
		$hits->setLabel('Просмотры')
			->setDecorators(array(
				'ViewHelper',
				'Errors',
				array('Label', array('class'=>'label label-important')),
				array('HtmlTag', array('class'=>'span2'))
			))
			->setAttrib('class', 'span4')
			->setAttrib('disabled', 'disabled')
			->setValue((int)$this->data['hits']);
		$hitsHidden = new Zend_Form_Element_Hidden('hits');
		$hitsHidden->setDecorators(array( 'ViewHelper' ))
			->setValue((int)$this->data['hits']);
		/*
		 * #########################################
		 * Add elements and display groups
		 * #########################################
		 */ 
		$this->addDisplayGroup( array(
			$title,
			$published,
			$hits,
			$hitsHidden,
			$author,
		), 'titlegrp' );
		$titleGroup = $this->getDisplayGroup('titlegrp');
		$titleGroup->setDecorators(array(
			'FormElements',
			array('Fieldset', array('id'=>'title-set', 'class'=>'container-fluid')),
		));
		
		$this->addDisplayGroup( array(
			$categoryDropdown['content'],
			$categoryDropdown['channel'],
			$categoryDropdown['program']), 'categoriesgrp' );
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
			array('Fieldset', array('id'=>'text-set', 'class'=>'container-fluid')),
		));
		
		$this->addDisplayGroup( array( 
			$dateCreated,
			$publishUp,
			$publishDown ), 'propsgrp'
		);
		$propsGroup = $this->getDisplayGroup('propsgrp');
		$propsGroup->setDecorators(array(
			'FormElements',
			array('Fieldset', array('id'=>'props-set', 'class'=>'container-fluid')),
		));
		
		/*
		 * #########################################
		 * Income type checkboxes 
		 * #########################################
		 */
		$incomeTypes = array(
			'is_ref'=>'Реф.',
			'is_cpa'=>'CPA',
			'is_paid'=>'Плат.',
		);
		
		if (APPLICATION_ENV=='development'){
			//var_dump($this->data);
			//die(__FILE__.': '.__LINE__);
		}
		
		if ($this->view->isAllowed('admin:content.article', 'income')){
			
		    $income = new Zend_Form_Element_Radio( 'income' );
			$income->setMultiOptions( $incomeTypes )
				->setRequired(true)
				->setDecorators(array(
					array( 'ViewHelper' ),
					array( 'Label', array( 'class'=>'label label-info' )),
					array( 'HtmlTag', array( 'tag'=>'fieldset', 'class'=>'container-fluid', 'id'=>'income' )),
				))
				->setSeparator( '&nbsp;' )
				->setLabel("Доход от статьи");
			$income->setValue( 'is_cpa' );
			foreach ($incomeTypes as $type=>$rus){
			    if (isset($this->data[$type]) && (int)$this->data[$type]==1){
			        $income->setValue( $type );
			    }
			}
			$income->setLabel( "Доход" );
			$this->addElement( $income );
			
			
		} else {
			
		    foreach ($incomeTypes as $type=>$rus){
		        $hidden = new Zend_Form_Element_Hidden($type);
		        $hidden->setDecorators( array( array('ViewHelper')) )
					->setValue($this->data[$type]);
		        $this->addElement($hidden);
		    }
		}
		
		$this->view->headStyle()->setStyle("fieldset#income label { float: left; margin: 16px 16px 16px 0; }
		label.error{ background-color: red; color: white; font-weight: bold; font-size: 11px; padding: 0.5em; }");
		
		/*
		 * #########################################
		 * Article META data
		 * #########################################
		 */
		$metadesc = new Zend_Form_Element_Textarea( 'metadesc' );
		$metadesc->setDecorators(array(
			'ViewHelper',
			'Errors',
			array('Label', array('class'=>'label label-important')),
			array('HtmlTag', array('class'=>'span12')),
			))
			->setAttrib('rows', 2)
			->setAttrib('class', 'span12')
			->setLabel("META описание")
			->setValue( html_entity_decode($this->data['metadesc']) )
			->setRequired(false);
		$metakeys = new Zend_Form_Element_Textarea( 'metakeys' );
		
		$this->addDisplayGroup( array($metadesc), 'metadatagrp' );
		$metadatagrpGroup = $this->getDisplayGroup('metadatagrp');
		$metadatagrpGroup->setDecorators(array(
			'FormElements',
			array('Fieldset', array('id'=>'metadata-set', 'class'=>'container-fluid')),
		));
		
		
		$do = new Zend_Form_Element_Hidden('do');
		$do->setDecorators(array('ViewHelper'))
			->setRequired(true);

		$id = new Zend_Form_Element_Hidden('id');
		$id->setDecorators(array('ViewHelper'))
			->setValue((int)$this->data['id'])
			->setName('idx[]');
		
		$this->addElements(array($alias, $do, $id));
		
		$this->setDecorators(array(
			'FormElements',
			'Form', array(
				array('data'=>'HtmlTag'), array(
					'tag'=>'div',
					'class'=>'row-fluid')),
		))
		->setAttrib('class', $this->options['class'])
		->setAttrib('id', $this->options['id'])
		->setAttrib('action', $this->view->baseUrl('admin/content/edit'))
		->setMethod('post');
		
	}
}