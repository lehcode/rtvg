$(document).ready(function(){
	
	Progress = $('#progress');
	Errors   = $('#parseerrors');
	
	$('#parsechannels').click(function(e){
		e.stopPropagation();
		$.ajax({
			url: '/admin/import/xmlparsechannels/html',
			data: $('form#parse_channels').serialize(),
			dataType: 'html',
			type: 'post',
			beforeSend:function(jqXHR, settings){
				Progress.hide().html('<p>Начинаю парсинг каналов из XML</p>').fadeIn();
			},
			success: function(response, textStatus, jqXHR){
				Progress.fadeOut().html(Progress.html()+'<p>'+response+'</p>').fadeIn();
				return true;
				//$(this).abort();
			},
			error: function(jqXHR, textStatus, errorThrown){
				Progress.hide().html(Progress.html()+'<p>'+errorThrown+'</p>').fadeIn();
				return false;
				//$(this).abort();
			}
		});
		$('#submitbutton').fadeOut();
		return false;
	});
	
	$('#parseprograms').click(function(e){
		e.stopPropagation();
		$.ajax({
			url: '/admin/import/xmlparseprograms/ajax',
			data: $('form#parse_programs').serialize(),
			dataType: 'html',
			type: 'post',
			beforeSend:function(jqXHR, settings){
				Progress.hide().html('<p>Начинаю парсинг программ из XML</p>').fadeIn();
			},
			success: function(response, textStatus, jqXHR){
				Progress.fadeOut().html(Progress.html()+'<p>'+response+'</p>').fadeIn();
				return true;
				//$(this).abort();
			},
			error: function(jqXHR, textStatus, errorThrown){
				Progress.hide().html(Progress.html()+'<p>'+errorThrown+'</p>').fadeIn();
				return false;
				//$(this).abort();
			}
		});
		$('#submitbutton').fadeOut();
		return false;
	});
});