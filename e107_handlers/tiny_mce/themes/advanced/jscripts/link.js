var url = tinyMCE.getParam("external_link_list_url");
if (url != null) {
	// Fix relative
	if (url.charAt(0) != '/')
		url = tinyMCE.documentBasePath + "/" + url;

	document.write('<sc'+'ript language="javascript" type="text/javascript" src="' + url + '"></sc'+'ript>');
}


function init() {
	for (var i=0; i<document.forms[0].target.options.length; i++) {
		var option = document.forms[0].target.options[i];

		if (option.value == tinyMCE.getWindowArg('target'))
			option.selected = true;
	}

	document.forms[0].href.value = tinyMCE.getWindowArg('href');
	document.forms[0].linktitle.value = tinyMCE.getWindowArg('title');
	document.forms[0].insert.value = tinyMCE.getLang('lang_' + tinyMCE.getWindowArg('action'), 'Insert', true); 

	var className = tinyMCE.getWindowArg('className');
	var styleSelectElm = document.forms[0].styleSelect;
	var stylesAr = tinyMCE.getParam('theme_advanced_styles', false);
	if (stylesAr) {
		stylesAr = stylesAr.split(';');

		for (var i=0; i<stylesAr.length; i++) {
			var key, value;

			key = stylesAr[i].split('=')[0];
			value = stylesAr[i].split('=')[1];

			styleSelectElm.options[styleSelectElm.length] = new Option(key, value);
			if (value == className)
				styleSelectElm.options.selectedIndex = styleSelectElm.options.length-1;
		}
	} else {
		var csses = tinyMCE.getCSSClasses(tinyMCE.getWindowArg('editor_id'));
		for (var i=0; i<csses.length; i++) {
			styleSelectElm.options[styleSelectElm.length] = new Option(csses[i], csses[i]);
			if (csses[i] == className)
				styleSelectElm.options.selectedIndex = styleSelectElm.options.length-1;
		}
	}

	// Hide it if there is no styles
	if (styleSelectElm.options.length == 1) {
		document.getElementById('styleSelectRow').style.display = "none";
		document.getElementById('styleSelectRow').style.overflow = "hidden";
		document.getElementById('styleSelectRow').style.height = "0px";
	}

	// Handle file browser
	if (tinyMCE.getParam("file_browser_callback") != null) {
		document.getElementById('href').style.width = '180px';

		var html = '';

		html += '<img id="browserBtn" src="images/browse.gif"';
		html += ' onmouseover="tinyMCE.switchClass(this,\'mceButtonOver\');"';
		html += ' onmouseout="tinyMCE.restoreClass(this);"';
		html += ' onmousedown="tinyMCE.restoreAndSwitchClass(this,\'mceButtonDown\');"';
		html += ' onclick="javascript:tinyMCE.openFileBrowser(\'href\',document.forms[0].href.value,\'file\',window);"';
		html += ' width="20" height="18" border="0" title="' + tinyMCE.getLang('lang_browse') + '"';
		html += ' class="mceButtonNormal" alt="' + tinyMCE.getLang('lang_browse') + '" />';

		document.getElementById('browser').innerHTML = html;
	}

	// Auto select link in list
	if (typeof(tinyMCELinkList) != "undefined" && tinyMCELinkList.length > 0) {
		var formObj = document.forms[0];

		for (var i=0; i<formObj.link_list.length; i++) {
			if (formObj.link_list.options[i].value == tinyMCE.getWindowArg('href'))
				formObj.link_list.options[i].selected = true;
		}
	}
}

function insertLink() {
	if (window.opener) {
		var href = document.forms[0].href.value;
		var target = document.forms[0].target.options[document.forms[0].target.selectedIndex].value;
		var title = document.forms[0].linktitle.value;
		var style_class = document.forms[0].styleSelect.value;
		var dummy;

		window.opener.tinyMCE.insertLink(href, target, title, dummy, style_class);
		tinyMCEPopup.close();
	}
}
