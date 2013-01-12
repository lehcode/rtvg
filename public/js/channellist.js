$(function() {
	
	$.getJSON( '/channels/typeahead/format/json', function(response) {
			
		var typeahead_items = new Array();
		$.each(response, function(key, val) {
			$.each(val, function(name, channel){  typeahead_items.push(channel.title); });
		});
		$('input[name=fast_search]').typeahead({ source:typeahead_items });
		$('#fastsearch-wrap').fadeIn(500, function(){
			$('input[name=fast_search]').blur(function(){ 
				if ($(this).val() != '') {
					$('ul.typeahead li').click(function(){
						$('.channeltitle').each(function(){
							var r = new RegExp($('ul.typeahead li.active').attr('data-value'), 'i');
							if (currentTitle = $(this).html().match(r)) {
								$("html:not(:animated),body:not(:animated)").animate({ scrollTop: $(this).offset().top }, 1000);
								return false;
							}
						});
					});
				}
			});
		});
	});
	$( '#channels' ).accordion({ autoHeight:false, navigation:true, clearStyle:true, icons: false });
	
});