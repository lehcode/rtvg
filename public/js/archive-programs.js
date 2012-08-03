/**
 * 
 */
jQuery(function($){
	$('#archive_start').click(function(){
		$.ajax({
			url: '/admin/archive/store',
			data: $('#archive_programs').serialize()+'&format=html',
			type: $('#archive_programs').attr('method'),
			dataType: 'html',
			beforeSend: function(){
				$('#result').css({ 'background':'lightYellow' }).html("<h3>Происходит архивация программ</h3>");
			},
			success: function(response){
				$('#result').css({ "background-color":"lightGreen" }).html(response);
			},
			error: function(XHR, textStatus, errorThrown){
				$('#result').css({ "background-color":"lightRed" }).html(errorThrown);
			}
		});
	});
});