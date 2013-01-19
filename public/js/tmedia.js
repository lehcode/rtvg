$(document).ready(function(){
	
	function toggleTMedia(){
		
		console.log("toggleTMedia");
		var images = $('#tmedia img[class^=tm_img]');
		var mainWrap = $('#tmedia');
		console.log(images);
		images.each(function(){
			var linkHref = $(this).parent().parent().attr('href');
			var image = $('<img/>').attr({ src:$(this).attr('src') }).css({ width: '80px' });
			var value = $(this).attr('alt');
			var link  = $('<a>'+value+'</a>').attr({ href:linkHref }).append(image);
			var parag = $('<p/>').attr({ class:'text' }).append(link);
			var linkWrap = $('<div/>').attr({ class:'wrap' }).append(parag);
			//console.log( $(this).parent().parent().parent().parent().parent().next().find('a').val() );
			mainWrap.append(linkWrap);
			$('#tmedia div[id^=tm_]').each( function(){
				$(this).remove();
			} );
			
		});
			
	}
	toggleTMedia();
	
});