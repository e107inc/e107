/**
 * $RCSfile: tiny_mce_popup.js,v $
 * $Revision: 1.5 $
 * $Date: 2005-09-07 13:32:50 $
 *
 * @author Moxiecode
 * @copyright Copyright © 2004, Moxiecode Systems AB, All rights reserved.
 */

var tinyMCE = null, tinyMCELang = null;

function TinyMCEPopup() {
};

TinyMCEPopup.prototype.init = function() {
	var win = window.opener ? window.opener : window.dialogArguments;

	if (!win)
		win = top;

	window.opener = win;
	this.windowOpener = win;

	// Setup parent references
	tinyMCE = win.tinyMCE;
	tinyMCELang = win.tinyMCELang;

	if (!tinyMCE) {
		alert("tinyMCE object reference not found from popup.");
		return;
	}

	this.isWindow = tinyMCE.getWindowArg('mce_inside_iframe', false) == false;
	this.storeSelection = tinyMCE.isMSIE && !this.isWindow && tinyMCE.getWindowArg('mce_store_selection', true);

	// Store selection
	if (this.storeSelection)
		tinyMCE.selectedInstance.execCommand('mceStoreSelection');

	// Setup dir
	if (tinyMCELang['lang_dir'])
		document.dir = tinyMCELang['lang_dir'];

	// Setup title
	var re = new RegExp('{|\\\$|}', 'g');
	var title = document.title.replace(re, "");
	if (typeof tinyMCELang[title] != "undefined") {
		var divElm = document.createElement("div");
		divElm.innerHTML = tinyMCELang[title];
		document.title = divElm.innerHTML;

		if (tinyMCE.setWindowTitle != null)
			tinyMCE.setWindowTitle(window, divElm.innerHTML);
	}

	// Output Popup CSS class
	document.write('<link href="' + tinyMCE.getParam("popups_css") + '" rel="stylesheet" type="text/css">');

	tinyMCE.addEvent(window, "load", this.onLoad);
};

TinyMCEPopup.prototype.onLoad = function() {
	if (tinyMCE.getWindowArg('mce_replacevariables', true))
		document.body.innerHTML = tinyMCE.applyTemplate(document.body.innerHTML, tinyMCE.windowArgs);

	var dir = tinyMCE.selectedInstance.settings['directionality'];
	if (dir == "rtl") {
		var elms = document.forms[0].elements;
		for (var i=0; i<elms.length; i++) {
			if ((elms[i].type == "text" || elms[i].type == "textarea") && elms[i].getAttribute("dir") != "ltr")
				elms[i].dir = dir;
		}
	}
};

TinyMCEPopup.prototype.resizeToContent = function() {
	var isMSIE = (navigator.appName == "Microsoft Internet Explorer");
	var isOpera = (navigator.userAgent.indexOf("Opera") != -1);

	if (isOpera)
		return;

	if (isMSIE) {
		try { window.resizeTo(10, 10); } catch (e) {}

		var elm = document.body;
		var width = elm.offsetWidth;
		var height = elm.offsetHeight;
		var dx = (elm.scrollWidth - width) + 4;
		var dy = elm.scrollHeight - height;

		try { window.resizeBy(dx, dy); } catch (e) {}
	} else {
		window.scrollBy(1000, 1000);
		if (window.scrollX > 0 || window.scrollY > 0) {
			window.resizeBy(window.innerWidth * 2, window.innerHeight * 2);
			window.sizeToContent();
			window.scrollTo(0, 0);
			var x = parseInt(screen.width / 2.0) - (window.outerWidth / 2.0);
			var y = parseInt(screen.height / 2.0) - (window.outerHeight / 2.0);
			window.moveTo(x, y);
		}
	}
};

TinyMCEPopup.prototype.getWindowArg = function(name, default_value) {
	return tinyMCE.getWindowArg(name, default_value);
};

TinyMCEPopup.prototype.execCommand = function(command, user_interface, value) {
	var inst = tinyMCE.selectedInstance;

	// Restore selection
	if (this.storeSelection) {
		inst.getWin().focus();
		inst.execCommand('mceRestoreSelection');
	}

	inst.execCommand(command, user_interface, value);

	// Store selection
	if (this.storeSelection)
		inst.execCommand('mceStoreSelection');
};

TinyMCEPopup.prototype.close = function() {
	tinyMCE.closeWindow(window);
};

TinyMCEPopup.prototype.pickColor = function(e, element_id) {
	tinyMCE.selectedInstance.execCommand('mceColorPicker', true, {
		element_id : element_id,
		document : document,
		window : window,
		store_selection : false
	});
};

TinyMCEPopup.prototype.openBrowser = function(element_id, type, option) {
	var cb = tinyMCE.getParam(option, tinyMCE.getParam("file_browser_callback"));
	var url = document.getElementById(element_id).value;

	tinyMCE.setWindowArg("window", window);
	tinyMCE.setWindowArg("document", document);

	// Call to external callback
	if (eval('typeof(tinyMCEPopup.windowOpener.' + cb + ')') == "undefined")
		alert("Callback function: " + cb + " could not be found.");
	else
		eval("tinyMCEPopup.windowOpener." + cb + "(element_id, url, type, window);");
};

// Setup global instance
var tinyMCEPopup = new TinyMCEPopup();

tinyMCEPopup.init();
