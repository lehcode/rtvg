<?php
/**
 * Быстрый переход по каналам и поиск канала
 * 
 * @author  Antony Repin
 * @version $Id: TypeaheadForm.php,v 1.5 2013-04-03 18:18:05 developer Exp $
 *
 */
class Xmltv_Form_TypeaheadForm extends Zend_Form
{
    /**
	 * Форма быстрого поиска канала
	 * со скроллом к положению канала на странице
	 * или без
	 * 
	 * @param array $options
	 */
    public function __construct( $options=null ) {
    	
        (isset($options['html_tag']) && !empty($options['html_tag'])) ? $tagProps = $options['html_tag'] : null ;
        unset($options['html_tag']);
        (isset($options['append']) && !empty($options['append'])) ? $appendTo = $options['append'] : '#maincontent' ;
        unset($options['append']);
        (isset($options['input']) && !empty($options['input'])) ? $input = (array)$options['input'] : null ;
        unset($options['input']);
        (isset($options['ajax_tracking']) && !empty($options['ga_js'])) ? $gaJs = (array)$options['ga_js'] : null ;
        unset($options['input']);
        
        parent::__construct( $options );
		
        $view = $this->getView();
        
        $inputId = (isset($input['id']) && !empty($input['id'])) ? (string)$input['id'] : 'searchinput';
        $js = "$(function(){
        $.getJSON( '".$view->baseUrl( 'channels/typeahead/format/json')."', function(data) {
			var typeaheadItems = new Array();
			$.each(data.result, function( key, val ) {
				var newItem = new Array(); 
				newItem['label'] = val.title;
				newItem['value'] = val.title;
				typeaheadItems.push( newItem ); 
			});
			$('input#$inputId').autocomplete({ 
				appendTo: '$appendTo',
				source: typeaheadItems,
				delay: 0,
				select: function( event, ui ){ " . PHP_EOL;
        
        if ( $options['scroll']===true ){
            $js .= "$('.channeltitle').each(function( ){
				var r = new RegExp(ui.item.label, 'i');
				if (currentTitle = $(this).html().match(r)) {
					$(\"html:not(:animated),body:not(:animated)\").animate({ scrollTop: $(this).offset().top }, 1000);
					return false;
				}
			});".PHP_EOL;
        } else {
            $js .= "$.ajax({
            	url: '".$view->baseUrl( 'channels/alias/format/json')."', 
            	data: 't='+ui.item.label,
            	dataType: 'json',
            	success: function(r) {
            		var url = '".$view->baseUrl('телепрограмма/')."'+r.alias;
            		_gaq.push( ['_trackPageview', url] );
            		window.location = url;
            	}
            });".PHP_EOL;
        }
        
	    $js .= " }
			});
			$('#".$options['id']."').show(500);
		});
		
    	});".PHP_EOL;
        $js = (APPLICATION_ENV=='production') ? Rtvg_Compressor_JSMin::minify($js) : $js ;
        
        $view->inlineScript()->appendScript($js);
        
        $search = new Zend_Form_Element_Text( $input['id'] );
        $tipText = "Начните вводить название канала и выберите его из списка";
        $htmlTag = null;
        if (isset($input['html_tag']) && !empty($input['html_tag'])){
            $htmlTag['tag']   = (string)$input['html_tag']['tag'];
            $htmlTag['class'] = (isset($input['html_tag']['class']) && !empty($input['html_tag']['class'])) ? (string)$input['html_tag']['class'] : null ;
        }
        
        $htmlTagClass = (isset($input['html_tag']) && !empty($input['html_tag'])) ? (string)$input['html_tag'] : null ;
        $search->setLabel( $input['label'] )
        	->setAttrib( 'class', $input['class'] )
        	->setAttrib( 'title', $tipText )
        	->addFilter( 'StripTags' )
        	->addValidator( 'NotEmpty' )
       		->addFilter( 'StringTrim' )
        	->setDecorators( array(
        		array( 'ViewHelper' ),
        		array( 'Errors' ),
        		array( 'Label', $htmlTag ),
        	));
        
        $htmlTag = (isset($tagProps) && !empty($tagProps)) ? $tagProps['tag'] : null ;
        $htmlTagClass = (isset($tagProps) && !empty($tagProps)) ? $tagProps['class'] : null ;
        $this->setDecorators(array(
        	'FormElements',
        	'Form', array(
        		array('data'=>'HtmlTag'),
        		array(
        			'tag'   => $htmlTag,
        			'class' => $htmlTagClass
        	)),
        ));
        $this->addElement($search);
        
    }
    
}