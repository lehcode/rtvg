$(document).ready(function(){
	
	function scrollScreen(element) {
		var targetPostion = $(element).offset().top;
		var speed = 1000;
		var selector = "html:not(:animated),body:not(:animated)"; 
		$(selector).animate({ scrollTop: targetPostion }, speed);
	}
	
	$('#programslist').carousel('cycle').carousel('pause');
	$('a#slideshow_onoff').click(function(){
		$(this).fadeOut(1000);
		$('#programslist').fadeOut( 1000, function(){
			if ($(this).hasClass('carousel')) {
				$(this).toggleClass('carousel');
				$('#programs-carousel').removeClass('carousel-inner');
				$('#slides-nav a').hide();
				$('#programs-carousel div.item').each( function(){
					if ($(this).hasClass('programcontainer')) {
						$(this).toggleClass('programcontainer');
						$(this).toggleClass('programdiv');
					}	
				});
				$('#programslist').css({ 'border':'none' });
				$('#programslist p').each(function(){ $(this).hide(); });
				$(this).show(300, function(){ scrollScreen('#programs-carousel .active'); });
				$('#programslist .program-video').remove();
			} else {
				$(this).addClass('carousel');
				$('#programs-carousel').addClass('carousel-inner');
				$('#slides-nav a').show();
				$('#programs-carousel div.item').each( function(){
					$(this).toggleClass('programdiv');
					$(this).toggleClass('programcontainer');
				});
			}
		});
		
	});
	
	$.getJSON( '/channels/typeahead/format/json', function(response) {
		var typeahead_items = new Array();
		$.each(response, function(key, val) { $.each(val, function(name, channel){  typeahead_items.push(channel.title); }); });
		$('input[name=fs]').typeahead({ source:typeahead_items });
		$('#fastsearch-wrap').fadeIn(500, function(){
			$('input[name=fs]').blur(function(){ 
				if ($(this).val() != '') {
					$('ul.typeahead li').click(function(){
						$('.channellink').each(function(){
							var r = new RegExp($('ul.typeahead li.active').attr('data-value'), 'i');
							if (currentTitle = $(this).html().match(r)) {
								$("html:not(:animated),body:not(:animated)").animate({ scrollTop: $(this).offset().top }, 1000);
							}
							
						});
					});
				}
			});
		});
	});
	
});