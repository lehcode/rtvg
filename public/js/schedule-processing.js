$(document).ready(function(){
	
	$('#start-search').click(function(){
		$.ajax({
			url: '/admin/programs/premieres-search',
			data: $('#premiere_search').serialize()+'&format=html',
			dataType: 'html',
			beforeSend: function(){
				$('#result').css({ 'background':'lightYellow' }).html("<h3>Происходит удаление программ</h3>");
			},
			success: function(response){
				$('#result').css({ "background-color":"lightGreen" }).html(response);
			},
			error: function(XHR, textStatus, errorThrown){
				$('#result').css({ "background-color":"lightRed" }).html(errorThrown);
			}
		});
	});
	
	/*
	$('#delete-programs').change(function(){ 
		if ($(this).prop('checked')==true && $('input#delete-info').prop('checked')==false) {
			$('input#delete-info').prop('checked', true);
		}
	});
	*/

	$('#titles_parsing').click(function(){ 
		$.ajax({
			url: '/admin/import/parse-saved-programs',
			data: $('#parse_titles').serialize()+'&format=html',
			dataType: 'html',
			beforeSend: function(){
				$('#result').html('').css({ 'background':'lightYellow' }).html("<h3>Происходит обработка программ</h3>");
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