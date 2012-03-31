$(document).ready(function(){
	
	function scrollScreen(element) {
		// It neater if we get the target from the href of the link
		//var targetHref = $(element).attr("href");
		// The location of the target div layer in relation to the browser window
		var targetPostion = $(element).offset().top;
		// The speed of the animation in millisenconds
		var speed = 1000;
		var selector = "html:not(:animated),body:not(:animated)"; 
		// We then use the animation function to scroll the main window
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
				
				var divs = $('#programs-carousel div.item');
				var dc = divs.length;
				$('#programs-carousel div.item').each( function(){
					if ($(this).hasClass('programcontainer')) {
						$(this).toggleClass('programcontainer');
						$(this).toggleClass('programdiv');
						dc--;
					}	
				});
				$(this).show(300, function(){ 
					scrollScreen('#programs-carousel .active');
				});
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
});