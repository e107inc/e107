// Get tinyMCE window
var win = window.opener ? window.opener : window.dialogArguments;

var tinyMCE = null;
var tinyMCELang = null;

// Use top window if not defined
if (!win)
	win = top;

var tinyMCE = win.tinyMCE;
var tinyMCELang = win.tinyMCELang;

if (!tinyMCE)
	alert("tinyMCE object reference not found from popup.");

// Setup window openerer
window.opener = win;

// Setup title
var re = new RegExp('{|\\\$|}', 'g');
var title = document.title.replace(re, "");
if (typeof tinyMCELang[title] != "undefined") {
	var divElm = document.createElement("div");
	divElm.innerHTML = tinyMCELang[title];
	document.title = divElm.innerHTML;
}

// Setup dir
if (tinyMCELang['lang_dir'])
	document.dir = tinyMCELang['lang_dir'];

function TinyMCEPlugin_onLoad() {
	if (tinyMCE.getWindowArg('mce_replacevariables', true))
		document.body.innerHTML = tinyMCE.applyTemplate(document.body.innerHTML, tinyMCE.windowArgs);

	// Auto resize window
	if (tinyMCE.getWindowArg('mce_windowresize', true)) {
		var width = tinyMCE.isMSIE ? document.body.offsetWidth : window.innerWidth;
		var height = tinyMCE.isMSIE ? document.body.offsetHeight : window.innerHeight;
		var dx = document.body.scrollWidth - width;
		var dy = document.body.scrollHeight - height;

		if (tinyMCE.isMSIE) {
			window.dialogWidth = (parseInt(window.dialogWidth) + dx) + "px";
			window.dialogHeight = (parseInt(window.dialogHeight) + dy + 3) + "px";
		} else
			window.resizeBy(dx + 15, dy + 15);
	}
}

// Add onload trigger
tinyMCE.addEvent(window, "load", TinyMCEPlugin_onLoad);

// Output Popup CSS class
document.write('<link href="' + tinyMCE.getParam("popups_css") + '" rel="stylesheet" type="text/css">');
