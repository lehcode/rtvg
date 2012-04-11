$(document).ready(function(){
	
	Progress = $('#progress');
	Errors   = $('#parseerrors');
	Count    = $('#parsecounter');
	
	$('#parsechannels').click(function(e){
		e.stopPropagation();
		Errors.hide(100, function(){ $(this).html(''); });
		Count.hide(100, function(){ $(this).html(''); });
		Progress.hide(100, function(){ $(this).html(''); });
		$.ajax({
			url: '/admin/import/xml-parse-channels',
			data: $('form#parse_channels').serialize()+'&format=html',
			dataType: 'html',
			type: 'post',
			beforeSend:function(jqXHR, settings){
				Progress.hide(100, function(){ $(this).html('<p>Начинаю парсинг каналов из XML</p>').fadeIn(); });
			},
			success: function(response, textStatus, jqXHR){
				Progress.hide(100, function(){ $(this).html('<p>Парсинг каналов завершен</p><p>'+response+'</p>').fadeIn(); });
				return true;
			},
			error: function(jqXHR, textStatus, errorThrown){
				Errors.hide(100, function(){ $(this).html(Progress.html()+'<p>'+errorThrown+'</p>').fadeIn(); });
				return false;
			}
		});
		//$('#parsechannels').fadeOut();
		return false;
	});
	
	$('#parseprograms').click(function(e){
		
		e.stopPropagation();
		Errors.hide(100, function(){ $(this).html(''); });
		Count.hide(100, function(){ $(this).html(''); });
		Progress.hide(100, function(){ $(this).html(''); });
		$.ajax({
			url: '/admin/import/xml-parse-programs',
			data: $('form#parse_programs').serialize()+'&format=html',
			dataType: 'html',
			type: 'post',
			beforeSend:function(jqXHR, settings){
				Progress.hide(100, function(){ Progress.html('<p>Начинаю парсинг программ из XML</p>').fadeIn(); });
				var startParsing = new Date();
				timer = startParsing.getTime();
				pollParsing = setInterval( function(){
					var xmlFile = $('#parse_programs input[name=xml_file]').val();
					$.ajax({
						url: '/admin/import/parsing-progress',
				    	data: 'format=json&parse=programs&xml='+xmlFile,
				    	timeout: 30000,
				    	type: 'get',
				    	dataType: 'json',
				    	success: function(response, responseText, JqXHR){
							if (response!=null) {
								var totalNodes = parseInt( response.max );
								var processedNodes = parseInt( response.current );
								var procDate = new Date();
								var newMs = procDate.getTime();
								//var elapsedSecs = (newMs-timer)/1000;
								var elapsedMs = newMs-timer;
								var rpms = processedNodes/elapsedMs;
								//var etaTimer = timer+((response.max-response.current)/timer)*1000;
								var etaTimer = (totalNodes-processedNodes)/rpms;
								var etaDate = new Date(etaTimer);
								var etaMinutes = String( etaDate.getMinutes() );
								if (etaMinutes.length == 1) { etaMinutes = '0'+etaMinutes; } 
								var etaHours = String( etaDate.getHours() );
								if (etaHours.length == 1) { etaHours = '0'+etaHours; } 
								var pct = Math.round((response.current/(response.max/100))*100)/100;
								var responseHtml = '<p>'+response.current+' из '+response.max+' ('+pct+'%)</p>';
								responseHtml += '<p><b>Скорость:</b> '+Math.floor(rpms*1000)+' записей/сек</p>';
								responseHtml += '<p><b>Расчетное время парсинга:</b> '+etaHours+':'+etaMinutes+'</p>';
								Count.hide(100, function(){ Count.html(responseHtml).fadeIn(); });
								Errors.hide();
							}
						},
						error: function(jqXHR, textStatus, errorThrown){
							Errors.hide(100, function(){ Errors.html(errorThrown).fadeIn(); });
						}
					});
				}, "15000");
			},
			success: function(response, textStatus, jqXHR){
				Progress.fadeOut(100, function(){ Progress.html('<p>Завершено</p>'+response).fadeIn(); })
				window.clearInterval(pollParsing);
				return true;
			},
			error: function(jqXHR, textStatus, errorThrown){
				window.clearInterval(pollParsing);
				Errors.hide(100, function(){ Errors.html('<p>'+errorThrown+'</p>').fadeIn(); });
				return false;
			}
		});
		$('#submitbutton').fadeOut();
		return false;
	});
	
	$('#premieres_start_search').click(function(e){
		Errors.hide(100, function(){ $(this).html(''); });
		Count.hide(100, function(){ $(this).html(''); });
		Progress.hide(100, function(){ $(this).html(''); });
		$.ajax({
			url: '/admin/import/premieres-search',
			data: $('#premieres_search').serialize()+'&format=html',
			dataType: 'html',
			type: 'post',
			beforeSend:function(jqXHR, settings){
				Progress.hide().html('<p>Поиск премьер в программе</p>').fadeIn();
			},
			success: function(response){
				Progress.fadeOut().html(response).fadeIn();
			},
			error: function(jqXHR, textStatus, errorThrown){
				Progress.hide().html(errorThrown).fadeIn();
				return false;
			}
		});
	});
	
});