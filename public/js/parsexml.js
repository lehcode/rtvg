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
				Progress.hide(100, function(){ $(this).html('<h3>Парсинг каналов из XML</h3>').fadeIn(); });
			},
			success: function(response, textStatus, jqXHR){
				Progress.hide(100, function(){ $(this).html('<h3>Парсинг каналов завершен</p><p>'+response+'</h3>').fadeIn(); });
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
			cache: false,
			beforeSend:function(jqXHR, settings){
				Progress.hide(100, function(){ Progress.html('<h3>Парсинг программ из XML</h3>').fadeIn(); });
				/*
				var startParsing = new Date();
				startTime = startParsing.getTime();
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
								var elapsedMs = procDate.getTime()-startTime;
								var timeSpent = new Date (elapsedMs);
								var rpms = processedNodes/elapsedMs;
								var etaDate = new Date( startTime+elapsedMs+(Math.floor(((totalNodes-processedNodes)/(rpms*1000))*1000)) );
								var etaHours = String( etaDate.getHours() );
								if (etaHours.length == 1) { etaHours = '0'+etaHours; } 
								var etaMinutes = String( etaDate.getMinutes() );
								if (etaMinutes.length == 1) { etaMinutes = '0'+etaMinutes; }
								var pct = Math.round((processedNodes/(totalNodes/100))*100)/100;
								var responseHtml = '<p>'+response.current+' из '+response.max+' ('+pct+'%)</p>';
								responseHtml += '<p><b>Скорость:</b> '+Math.floor(rpms*1000)+' записей/сек</p>';
								//responseHtml += '<p><b>Прошло времени:</b> '+timeSpent.getHours()+':'+timeSpent.getMinutes()+'</p>';
								responseHtml += '<p><b>Осталось программ:</b> '+(totalNodes-processedNodes)+'</p>';
								responseHtml += '<p><b>Расчетное время завершения:</b> '+etaHours+':'+etaMinutes+'</p>';
								Count.hide(100, function(){ Count.html(responseHtml).fadeIn(); });
								Errors.hide();
							}
						},
						error: function(jqXHR, textStatus, errorThrown){
							Errors.hide(100, function(){ Errors.html(errorThrown).fadeIn(); });
						}
					});
				}, "15000");
				*/
			},
			success: function(response, textStatus, jqXHR){
				Progress.fadeOut(100, function(){ Progress.html('<h3>Завершено!</h3>'+response).fadeIn(); });
				//window.clearInterval(pollParsing);
				return true;
			},
			error: function(jqXHR, textStatus, errorThrown){
				//window.clearInterval(pollParsing);
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
				Progress.hide().html('<h3>Поиск премьер в программе</h3>').fadeIn();
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