<?php
/**
 * Быстрый переход по каналам и поиск канала
 * 
 * @author  Antony Repin
 * @version $Id: TypeaheadForm.php,v 1.6 2013-04-06 22:35:03 developer Exp $
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
        
        ob_start();
        ?>
        $(function(){
        	$.getJSON( '<?php echo $view->baseUrl( 'channels/typeahead/format/json') ?>', function(data) {
			var typeaheadItems = new Array();
			$.each(data.result, function( key, val ) {
				var newItem = new Array(); 
				newItem['label'] = val.title;
				newItem['value'] = val.title;
				typeaheadItems.push( newItem ); 
			});
			$('input#<?php echo $inputId; ?>').autocomplete({ 
				appendTo: '<?php echo $appendTo ?>',
				source: typeaheadItems,
				delay: 0,
				select: function( event, ui ){
		        <?php 
		        if ( $options['scroll']===true ){
		            ?>
		            $('.channeltitle').each(function( ){
						var r = new RegExp(ui.item.label, 'i');
						if (currentTitle = $(this).html().match(r)) {
							$(\"html:not(:animated),body:not(:animated)\").animate({ scrollTop: $(this).offset().top }, 1000);
							return false;
						}
					});
				<?php  
		        } else { ?>
		            $.ajax({
		            	url: '<?php echo $view->baseUrl( 'channels/alias/format/json'); ?>', 
		            	data: 't='+ui.item.label,
		            	dataType: 'json',
		            	success: function(r) {
		            		$('._FlashDiv_wb_').remove();
		            		<?php if(APPLICATION_ENV=='production') :?>
		            		_gaq.push( ['_trackPageview', url] );
		            		<?php endif; ?>
		            		var url = '<?php echo $view->url(array('module'=>'default', 'controller'=>'channels', 'action'=>'list'), 'default_channels_list'); ?>'+r.alias;
		            		var bgStyle = { 'visibility':'visible', 'position':'absolute', 'left':0, 'top':0, 'width':'100%', 'height':'100%', 'text-align':'center', 'z-index': 1000, 'background-color':'#000', 'opacity':0.5 };
		            		var img = $('<img/>')
		            			.attr({ 'src':'/images/icons/loading.gif', 'width':128, 'height': 128 })
		            			.css({ 'background-color':'#fff', 'opacity':'1', 'padding':10 });
		            		var innerDiv = $('<div/>').css({
		            			'width':300,
							    'margin':'100px auto',
							    'padding':15,
							    'text-align':'center',
							    'font-size':32,
							    'color':'#fff'
		            		}).addClass('modal').append(img);
		            		var overlay = $('<div/>').css(bgStyle);
		            		overlay.appendTo($('body')).show(200);
		            		
		            	}
		            });
		        	<?php 
		        } ?>
        
	    	}});
			$('#<?php echo $inputId; ?>').show(500);
		});
		
    	});
    	<?php 
        $js = (APPLICATION_ENV=='production') ? Rtvg_Compressor_JSMin::minify( ob_get_clean() ) : ob_get_clean() ;
        $view->inlineScript()->appendScript($js);
        
        $search = new Zend_Form_Element_Text( $inputId );
        $tipText = "Начните вводить название канала и выберите его из списка";
        $htmlTag = null;
        if (isset($input['html_tag']) && !empty($input['html_tag'])){
            $htmlTag['tag']   = (string)$input['html_tag']['tag'];
            $htmlTag['class'] = (isset($input['html_tag']['class']) && !empty($input['html_tag']['class'])) ? (string)$input['html_tag']['class'] : null ;
        }
        
        $htmlTagClass = (isset($input['html_tag']) && !empty($input['html_tag'])) ? (string)$input['html_tag'] : null ;
        $search->setLabel( $input['label'] )
        	->setAttrib( 'title', $tipText )
        	->addFilter( 'StripTags' )
        	->addValidator( 'NotEmpty' )
       		->addFilter( 'StringTrim' )
        	->setDecorators( array(
        		array( 'ViewHelper' ),
        		array( 'Errors' ),
        		array( 'Label', $htmlTag ),
        	));
        (isset($input['class']) && !empty($input['class'])) ? $search->setAttrib( 'class', $input['class'] ) : null;
        
        $formHtmlTag = (isset($tagProps) && !empty($tagProps)) ? $tagProps['tag'] : null ;
        $formHtmlTagClass = (isset($tagProps) && !empty($tagProps)) ? $tagProps['class'] : null ;
        $this->setDecorators(array(
        	'FormElements',
        	'Form', array(
        		array('data'=>'HtmlTag'),
        		array(
        			'tag'   => $formHtmlTag,
        			'class' => $formHtmlTagClass
        	)),
        ));
        $this->addElement($search);
        
    }
    
}