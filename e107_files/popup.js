window.defaultStatus = "";

//get reference object for popup
function getRefToDivMod( divID, oDoc ) {
	if( !oDoc ) { oDoc = document; }
	if( document.layers ) {
		if( oDoc.layers[divID] ) { return oDoc.layers[divID]; } else {
			for( var x = 0, y; !y && x < oDoc.layers.length; x++ ) {
				y = getRefToDivMod(divID,oDoc.layers[x].document);
			}
			return y;
		}
	}
	if( document.getElementById ) { return oDoc.getElementById(divID); }
	if( document.all ) { return oDoc.all[divID]; }
	return document[divID];
}


//resize method for popup window (resize to fit contents)
function resizeWinTo() {
	if( !document.images.length ) { document.images[0] = document.layers[0].images[0]; }
	if( !document.images[0].height || window.doneAlready ) { return; } //in case images are disabled
	var oH = getRefToDivMod( 'myID' ); if( !oH ) { return false; }
	var oW = oH.clip ? oH.clip.width : oH.offsetWidth;
	var oH = oH.clip ? oH.clip.height : oH.offsetHeight; if( !oH ) { return false; }
	if( !oH || window.doneAlready ) { return; } //in case images are disabled
	window.doneAlready = true; //for Safari and Opera
	/*//no idea why this is in here
	if(document.getElementsByTagName) {
		for( var l = document.getElementsByTagName(\'a\'), x = 0; l[x]; x++ ) {
			if(l[x].className==\'makeright\'&&!l[x].style.position){
				l[x].style.position=\'relative\';
				l[x].style.left=(document.images[0].width-(l[x].offsetWidth+l[x].offsetLeft))+\'px\';
	}}}
	*/
	var x = window; x.resizeTo( oW + 200, oH + 200 );
	var myW = 0, myH = 0, d = x.document.documentElement, b = x.document.body;
	if( x.innerWidth ) { myW = x.innerWidth; myH = x.innerHeight; }
	else if( d && d.clientWidth ) { myW = d.clientWidth; myH = d.clientHeight; }
	else if( b && b.clientWidth ) { myW = b.clientWidth; myH = b.clientHeight; }
	if( window.opera && !document.childNodes ) { myW += 16; }
	x.resizeTo( oW = oW + ( ( oW + 200 ) - myW ), oH = oH + ( (oH + 200 ) - myH ) );
	//three lines to center the popup on the screen
	//'var scW = screen.availWidth ? screen.availWidth : screen.width;
	//'var scH = screen.availHeight ? screen.availHeight : screen.height;
	//'if( !window.opera ) { x.moveTo(Math.round((scW-oW)/2),Math.round((scH-oH)/2)); }
}


//open popup with image and text
function openPerfectPopup(oSrc, oWidth, oTitle, oText){

	//the first two should be small for Opera's sake
	PositionX = 20;
	PositionY = 20;
	defaultWidth  = 600;
	defaultHeight = 400;
	var AutoClose = '';
	var oW1 = oWidth+30;
	var oContent

	var buttonclose = "<input class='button' type='button' value='close' onClick='window.close();' />";

	oContent  = "<table border='0' cellspacing='10' cellpadding='0' style='text-align:center; width:"+oWidth+"px; height:100px;'>\n";
	oContent  += "<tr><td style='white-space:nowrap; width:"+oWidth+"px;'>";
	oContent  += "<img src='"+oSrc+"' alt='' style='width:"+oWidth+"px;'  />";
	oContent  += "</td></tr>\n";
	oContent  += "<tr><td class='poptext' style='width:"+oWidth+"px; text-align:left;'>"+oText+"</td></tr>\n";
	oContent  += "<tr><td colspan='2' style='white-space:nowrap; width:"+oWidth+"px; text-align:right;'>"+buttonclose+"</td></tr>\n";
	oContent  += "</table>\n";

	var imgWin = window.open('','name','scrollbars=no,resizable=1,width='+defaultWidth+',height='+defaultHeight+',left='+PositionX+',top='+PositionY);
	if( !imgWin ) { return true; } //popup blockers should not cause errors
	imgWin.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd"><html>\n'+
		'<head>\n'+
		'<title>'+oTitle+'<\/title>\n'+
		'<script type="text/javaScript">\n'+
		'//get reference object for popup\n'+
		'function getRefToDivMod( divID, oDoc ) {\n'+
		'	if( !oDoc ) { oDoc = document; }\n'+
		'	if( document.layers ) {\n'+
		'		if( oDoc.layers[divID] ) { return oDoc.layers[divID]; } else {\n'+
		'			for( var x = 0, y; !y && x < oDoc.layers.length; x++ ) {\n'+
		'				y = getRefToDivMod(divID,oDoc.layers[x].document);\n'+
		'			}\n'+
		'			return y;\n'+
		'		}\n'+
		'	}\n'+
		'	if( document.getElementById ) { return oDoc.getElementById(divID); }\n'+
		'	if( document.all ) { return oDoc.all[divID]; }\n'+
		'	return document[divID];\n'+
		'}\n'+
		'\n'+
		'//resize method for popup window (resize to fit contents)\n'+
		'function resizeWinTo() {\n'+
		'	if( !document.images.length ) { document.images[0] = document.layers[0].images[0]; }\n'+
		'	if( !document.images[0].height || window.doneAlready ) { return; } //in case images are disabled\n'+
		'	var oH = getRefToDivMod( "myID" ); if( !oH ) { return false; }\n'+
		'	var oW = oH.clip ? oH.clip.width : oH.offsetWidth;\n'+
		'	var oH = oH.clip ? oH.clip.height : oH.offsetHeight; if( !oH ) { return false; }\n'+
		'	if( !oH || window.doneAlready ) { return; } //in case images are disabled\n'+
		'	window.doneAlready = true; //for Safari and Opera\n'+
		'	var x = window; x.resizeTo( oW + 200, oH + 200 );\n'+
		'	var myW = 0, myH = 0, d = x.document.documentElement, b = x.document.body;\n'+
		'	if( x.innerWidth ) { myW = x.innerWidth; myH = x.innerHeight; }\n'+
		'	else if( d && d.clientWidth ) { myW = d.clientWidth; myH = d.clientHeight; }\n'+
		'	else if( b && b.clientWidth ) { myW = b.clientWidth; myH = b.clientHeight; }\n'+
		'	if( window.opera && !document.childNodes ) { myW += 16; }\n'+
		'	x.resizeTo( oW = oW + ( ( oW + 200 ) - myW ), oH = oH + ( (oH + 200 ) - myH ) );\n'+
		'}\n'+
		'<\/script>\n'+
		'<style type="text/css">\n'+
		'html,body{\n'+
		'	text-align:center;\n'+
		'	font-family: arial, verdana, helvetica, tahoma, sans-serif;\n'+
		'	font-size: 11px;\n'+
		'	color: #444;\n'+
		'	margin-left: auto;\n'+
		'  	margin-right: auto;	\n'+
		'  	margin-top:0px;\n'+
		'	margin-bottom:0px;\n'+
		'	padding: 0px;\n'+
		'	background-color:#FFF;\n'+
		'	height:100%;\n'+
		'	cursor:default;\n'+
		'}\n'+
		'.poptext{\n'+
		'	font-size: 11px;\n'+
		'	text-align:left;\n'+
		'	color:#444;\n'+
		'	line-height:140%;\n'+
		'	vertical-align:top;\n'+
		'	text-align:left;\n'+
		'}\n'+
		'.button{\n'+
		'	border:1px solid #444;\n'+
		'	color: #444;\n'+
		'	background-color:#FFF;\n'+
		'	font-size: 11px;\n'+
		'	padding:2px;\n'+
		'	cursor:pointer;\n'+
		'	width:50px;\n'+
		'}\n'+
		'<\/style>\n'+

		'<\/head>\n'+
		'<body onload="resizeWinTo();">\n'+
		(document.layers?('<layer left="0" top="0" id="myID">\n'):('<div style="width:'+oW1+'px; position:absolute;left:0px;top:0px;" id="myID" >\n'))+
		oContent+
		(document.layers?'<\/layer>\n':'<\/div>\n')+
		'<\/body>\n'+
		'<\/html>\n');

	imgWin.document.close();
	if( imgWin.focus ) { imgWin.focus(); }
}
