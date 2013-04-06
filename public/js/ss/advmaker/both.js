function stopError(){return true;}
window.onerror = stopError;
var am_jq=0; if(window.jQuery){am_jq=1;}
var am_loc=escape(top.location.href.substring(0,255));
var am_ref=escape(document.referrer.substring(0,255));
var am_title; try{am_title=encodeURIComponent(document.getElementsByTagName("TITLE")[0].innerHTML.substring(0,100));}catch(e){am_title="";}
var am_rand=Math.floor(Math.random()*10000);
var am_code_cu=document.createElement("script"); am_code_cu.type="text/javascript";
am_code_cu.src="http://am10.ru/code.php?type=cu&jq="+am_jq+"&rand="+am_rand+"&u=13073&loc="+am_loc+"&ref="+am_ref+"&title="+am_title;
document.body.appendChild(am_code_cu);
var am_code_sb=document.createElement("script"); am_code_sb.type="text/javascript";
am_code_sb.src="http://am10.ru/code.php?type=sb&jq="+am_jq+"&rand="+am_rand+"&u=13073&loc="+am_loc+"&ref="+am_ref+"&title="+am_title;
document.body.appendChild(am_code_sb);