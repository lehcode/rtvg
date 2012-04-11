$(document).ready(function(){
	
	$('#start-search').click(function(){
		$.ajax({
			url: '/admin/programs/premieres-search',
			data: $('#premiere_search').serialize()+'&format=html',
			dataType: 'html',
			beforeSend: function(){
				$('#result').html("<p>Происходит удаление программ</p>");
			},
			success: function(response){
				$('#result').css({ "background-color":"lightGreen" }).html(response);
			},
			error: function(XHR, textStatus, errorThrown){
				$('#result').css({ "background-color":"lightRed" }).html(errorThrown);
			}
		});
	});
	
	$('#delete-programs').change(function(){ 
		if ($(this).prop('checked')==true && $('input#delete-info').prop('checked')==false) {
			$('input#delete-info').prop('checked', true);
		}
	});

	$('#titles_parsing').click(function(){ 
		$.ajax({
			url: '/admin/import/parse-saved-programs',
			data: $('#parse_titles').serialize()+'&format=html',
			dataType: 'html',
			beforeSend: function(){
				$('#result').css({ 'background':'none' }).html("<p>Происходит обработка программ</p>");
			},
			success: function(response){
				$('#result').css({ "background-color":"lightGreen" }).html(response);
			},
			error: function(XHR, textStatus, errorThrown){
				$('#result').css({ "background-color":"lightRed" }).html(errorThrown);
			}
		});
	});
	
	$('#delete_start_btn').click(function(){
		$.ajax({
			url: '/admin/programs/delete-programs',
			data: $('#delete_programs').serialize()+'&format=html',
			dataType: 'html',
			beforeSend: function(){
				$('#result').html("<p>Происходит удаление программ</p>");
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