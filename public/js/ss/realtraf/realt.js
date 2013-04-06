var initRT = document.createElement('script');
initRT.src = 'http://www.rt-ns.ru/initRTv2.php?id=9154&f=c';
initRT.type  = "text/javascript";document.getElementsByTagName('head')[0].appendChild( initRT );
function runRT(){
	if(window.rtSu===undefined){
		setTimeout(runRT,1e3);return
	}
	for(i=0;i<rtArr.length;i++){
		var a=document.createElement("script");
		a.src="http://www."+rtDo+"/"+rtFo+"/"+rtArr[i]+".php?id="+rtId;
		a.type="text/javascript";
		document.getElementsByTagName("head")[0].appendChild(a)
	}
}
runRT();