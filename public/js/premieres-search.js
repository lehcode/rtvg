$(document).ready(function(){
	$('#submitbutton').click(function(){
		$.ajax({
			url: '/admin/programs/premieres-search',
			data: 'format=html',
			dataType: 'html',
			success: function(response){
				$('#result').html(response);
			}
		});
	});
});