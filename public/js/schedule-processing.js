$(document).ready(function(){
	$('#submitbutton').click(function(){
		$.ajax({
			url: '/admin/programs/premieres-search',
			data: $('#premiere_search').serialize()+'&format=html',
			dataType: 'html',
			success: function(response){
				$('#result').html(response);
			}
		});
	});
});