/**
 * 
 */
$(function(){
	$('#vklogin').click( function(){
		window.open( 'http://oauth.vk.com/authorize?client_id=2963750&scope=offline&redirect_uri=http://rutvgid.ru/vk/logged-in&response_type=code','Вход через vkontakte', 'menubar=no,width=640,height=480,toolbar=no' );
	});
});