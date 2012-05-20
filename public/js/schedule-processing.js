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
	
	$('#delete_start_btn').click(function(){
		$.ajax({
			url: '/admin/programs/delete-programs',
			data: $('#delete_programs').serialize()+'&format=html',
			dataType: 'html',
			beforeSend: function(){
				$('#result').css({ 'background':'lightYellow' }).html("<h3>Происходит удаление программ</h3>");
				$('#result').after('<div id="progress"></div>');
				polling = setInterval( function(){
					var start = $('input#delete_start').val();
					var end = $('input#delete_end').val();
					$.ajax({
						url: '/admin/programs/programs-delete-progress',
						data: 'start_date='+start+'&end_date='+end+'&format=html',
						method: 'get',
						success: function(response){
							console.log(response);
							$('#progress').html(response);
							window.clearInterval(polling);
						},
						error: function(XHR, textStatus, errorThrown){
							$('#result').css({ "background-color":"lightRed" }).html(errorThrown);
							window.clearInterval(polling);
						}
					});
				}, 10000);
			},
			success: function(response){
				$('#result').css({ "background-color":"lightGreen" }).html(response);
				window.clearInterval(polling);
				$('#progress').hide();
			},
			error: function(XHR, textStatus, errorThrown){
				$('#result').css({ "background-color":"lightRed" }).html(errorThrown);
				window.clearInterval(polling);
			}
		});
	});
});