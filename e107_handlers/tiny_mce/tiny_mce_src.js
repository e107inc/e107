/**
 * $RCSfile: tiny_mce_src.js,v $
 * $Revision: 1.2 $
 * $Date: 2005-03-12 19:04:55 $
 *
 * @author Moxiecode
 * @copyright Copyright © 2004, Moxiecode Systems AB, All rights reserved.
 */

function TinyMCE() {
	this.instances = new Array();
	this.stickyClassesLookup = new Array();
	this.windowArgs = new Array();

	// Browser check
	this.isMSIE = (navigator.appName == "Microsoft Internet Explorer");
	this.isMSIE5 = this.isMSIE && (navigator.userAgent.indexOf('MSIE 5') != -1);
	this.isMSIE5_0 = this.isMSIE && (navigator.userAgent.indexOf('MSIE 5.0') != -1);
	this.isGecko = navigator.userAgent.indexOf('Gecko') != -1;

	// TinyMCE editor id instance counter
	this.idCounter = 0;

	// Editor functions
	this.init = TinyMCE_init;
	this.addMCEControl = TinyMCE_addMCEControl;
	this.triggerSave = TinyMCE_triggerSave;
	this._convertOnClick = TinyMCE__convertOnClick;
	this.resetForm = TinyMCE_resetForm;
	this.execCommand = TinyMCE_execCommand;
	this.execInstanceCommand = TinyMCE_execInstanceCommand;
	this._createIFrame = TinyMCE__createIFrame;
	this.handleEvent = TinyMCE_handleEvent;
	this.setupContent = TinyMCE_setupContent;
	this.switchClass = TinyMCE_switchClass;
	this.restoreAndSwitchClass = TinyMCE_restoreAndSwitchClass;
	this.switchClassSticky = TinyMCE_switchClassSticky;
	this.restoreClass = TinyMCE_restoreClass;
	this.setClassLock = TinyMCE_setClassLock;
	this.addEvent = TinyMCE_addEvent;
	this.onLoad = TinyMCE_onLoad;
	this.removeMCEControl = TinyMCE_removeMCEControl;
	this._initCleanup = TinyMCE__initCleanup;
	this._cleanupHTML = TinyMCE__cleanupHTML;
	this._cleanupAttribute = TinyMCE__cleanupAttribute;
	this._fixInlineStyles = TinyMCE__fixInlineStyles;
	this._cleanupElementName = TinyMCE__cleanupElementName;
	this._verifyClass = TinyMCE__verifyClass;
	this.cleanupNode = TinyMCE_cleanupNode;
	this.convertStringToXML = TinyMCE_convertStringToXML;
	this.insertLink = TinyMCE_insertLink;
	this.insertImage = TinyMCE_insertImage;
	this.getElementByAttributeValue = TinyMCE_getElementByAttributeValue;
	this.getElementsByAttributeValue = TinyMCE_getElementsByAttributeValue;
	this.isBlockElement = TinyMCE_isBlockElement;
	this.getParentBlockElement = TinyMCE_getParentBlockElement;
	this.getNodeTree = TinyMCE_getNodeTree;
	this.getParentElement = TinyMCE_getParentElement;
	this.getParam = TinyMCE_getParam;
	this.getLang = TinyMCE_getLang;
	this.replaceVar = TinyMCE_replaceVar;
	this.replaceVars = TinyMCE_replaceVars;
	this.triggerNodeChange = TinyMCE_triggerNodeChange;
	this.parseURL = TinyMCE_parseURL;
	this.convertAbsoluteURLToRelativeURL = TinyMCE_convertAbsoluteURLToRelativeURL;
	this.convertRelativeToAbsoluteURL = TinyMCE_convertRelativeToAbsoluteURL;
	this.updateContent = TinyMCE_updateContent;
	this._customCleanup = TinyMCE__customCleanup;
	this.getContent = TinyMCE_getContent;
	this.setContent = TinyMCE_setContent;
	this.importThemeLanguagePack = TinyMCE_importThemeLanguagePack;
	this.importPluginLanguagePack = TinyMCE_importPluginLanguagePack;
	this.applyTemplate = TinyMCE_applyTemplate;
	this.openWindow = TinyMCE_openWindow;
	this.handleVisualAid = TinyMCE_handleVisualAid;
	this.setAttrib = TinyMCE_setAttrib;
	this.getAttrib = TinyMCE_getAttrib;
	this._getThemeFunction = TinyMCE__getThemeFunction;
	this._themeExecCommand = TinyMCE__themeExecCommand;
	this.getControlHTML = TinyMCE_getControlHTML;
	this._setHTML = TinyMCE__setHTML;
	this._getElementById = TinyMCE__getElementById;
	this.getInstanceById = TinyMCE_getInstanceById;
	this.getEditorId = TinyMCE_getEditorId;
	this.queryInstanceCommandValue = TinyMCE_queryInstanceCommandValue;
	this.queryInstanceCommandState = TinyMCE_queryInstanceCommandState;
	this.getWindowArg = TinyMCE_getWindowArg;
	this.setWindowArg = TinyMCE_setWindowArg;
	this.getCSSClasses = TinyMCE_getCSSClasses;
	this.regexpReplace = TinyMCE_regexpReplace;
	this.cleanupEventStr = TinyMCE_cleanupEventStr;
	this.getAbsPosition = TinyMCE_getAbsPosition;
	this.openFileBrowser = TinyMCE_openFileBrowser;
}

function TinyMCE_init(settings) {
	var theme, srcMode;

	this.settings = settings;

	function defParam(key, def_val) {
		settings[key] = tinyMCE.getParam(key, def_val);
	}

	// Check if valid browser has execcommand support
	if (typeof(document.execCommand) == 'undefined')
		return;

	// Get script base path
	if (!tinyMCE.baseURL) {
		var elements = document.getElementsByTagName('script');

		for (var i=0; i<elements.length; i++) {
			if (elements[i].src && (elements[i].src.indexOf("tiny_mce.js") != -1 || elements[i].src.indexOf("tiny_mce_src.js") != -1)) {
				var src = elements[i].src;

				srcMode = (src.indexOf('_src') != -1) ? '_src' : '';
				src = src.substring(0, src.lastIndexOf('/'));

				tinyMCE.baseURL = src;
				break;
			}
		}
	}

	// Get document base path
	this.documentBasePath = document.location.href;
	if (this.documentBasePath.indexOf('?') != -1)
		this.documentBasePath = this.documentBasePath.substring(0, this.documentBasePath.indexOf('?'));
	this.documentBasePath = this.documentBasePath.substring(0, this.documentBasePath.lastIndexOf('/'));

	// If not HTTP absolute
	if (tinyMCE.baseURL.indexOf('://') == -1 && tinyMCE.baseURL.charAt(0) != '/') {
		// If site absolute
		tinyMCE.baseURL = this.documentBasePath + "/" + tinyMCE.baseURL;
	}

	// Set default values on settings
	defParam("mode", "none");
	defParam("theme", "default");
	defParam("plugins", "", true);
	defParam("language", "en");
	defParam("docs_language", this.settings['language']);
	defParam("elements", "");
	defParam("textarea_trigger", "mce_editable");
	defParam("valid_elements", "a[name|href|target|title],strong/b[class],em/i[class],strike[class],u[class],p[class|align],ol,ul,li,br,img[class|src|border=0|alt|title|hspace|vspace|width|height|align],sub,sup,blockquote[dir|style],table[border=0|cellspacing|cellpadding|width|height|class|align],tr[class|rowspan|width|height|align|valign],td[class|colspan|rowspan|width|height|align|valign],div[class|align],span[class|align],pre[class|align],address[class|align],h1[class|align],h2[class|align],h3[class|align],h4[class|align],h5[class|align],h6[class|align],hr");
	defParam("extended_valid_elements", "");
	defParam("invalid_elements", "");
	defParam("encoding", "");
	defParam("urlconvertor_callback", "TinyMCE_convertURL");
	defParam("save_callback", "");
	defParam("debug", false);
	defParam("force_br_newlines", false);
	defParam("force_p_newlines", true);
	defParam("add_form_submit_trigger", true);
	defParam("relative_urls", true);
	defParam("remove_script_host", true);
	defParam("focus_alert", true);
	defParam("document_base_url", "" + document.location.href);
	defParam("visual", true);
	defParam("visual_table_style", "border: 1px dashed #BBBBBB");
	defParam("setupcontent_callback", "");
	defParam("fix_content_duplication", true);
	defParam("custom_undo_redo", true);
	defParam("custom_undo_redo_levels", -1);
	defParam("custom_undo_redo_keyboard_shortcuts", true);
	defParam("verify_css_classes", false);
	defParam("trim_span_elements", true);
	defParam("verify_html", true);
	defParam("apply_source_formatting", false);
	defParam("directionality", "ltr");
	defParam("auto_cleanup_word", false);
	defParam("cleanup_on_startup", false);
	defParam("inline_styles", false);
	defParam("convert_newlines_to_brs", false);
	defParam("auto_reset_designmode", false);
	defParam("entities", "160,nbsp,38,amp,34,quot,162,cent,8364,euro,163,pound,165,yen,169,copy,174,reg,8482,trade,8240,permil,181,micro,183,middot,8226,bull,8230,hellip,8242,prime,8243,Prime,167,sect,182,para,223,szlig,8249,lsaquo,8250,rsaquo,171,laquo,187,raquo,8216,lsquo,8217,rsquo,8220,ldquo,8221,rdquo,8218,sbquo,8222,bdquo,60,lt,62,gt,8804,le,8805,ge,8211,ndash,8212,mdash,175,macr,8254,oline,164,curren,166,brvbar,168,uml,161,iexcl,191,iquest,710,circ,732,tilde,176,deg,8722,minus,177,plusmn,247,divide,8260,frasl,215,times,185,sup1,178,sup2,179,sup3,188,frac14,189,frac12,190,frac34,402,fnof,8747,int,8721,sum,8734,infin,8730,radic,8764,sim,8773,cong,8776,asymp,8800,ne,8801,equiv,8712,isin,8713,notin,8715,ni,8719,prod,8743,and,8744,or,172,not,8745,cap,8746,cup,8706,part,8704,forall,8707,exist,8709,empty,8711,nabla,8727,lowast,8733,prop,8736,ang,180,acute,184,cedil,170,ordf,186,ordm,8224,dagger,8225,Dagger,192,Agrave,194,Acirc,195,Atilde,196,Auml,197,Aring,198,AElig,199,Ccedil,200,Egrave,202,Ecirc,203,Euml,204,Igrave,206,Icirc,207,Iuml,208,ETH,209,Ntilde,210,Ograve,212,Ocirc,213,Otilde,214,Ouml,216,Oslash,338,OElig,217,Ugrave,219,Ucirc,220,Uuml,376,Yuml,222,THORN,224,agrave,226,acirc,227,atilde,228,auml,229,aring,230,aelig,231,ccedil,232,egrave,234,ecirc,235,euml,236,igrave,238,icirc,239,iuml,240,eth,241,ntilde,242,ograve,244,ocirc,245,otilde,246,ouml,248,oslash,339,oelig,249,ugrave,251,ucirc,252,uuml,254,thorn,255,yuml,914,Beta,915,Gamma,916,Delta,917,Epsilon,918,Zeta,919,Eta,920,Theta,921,Iota,922,Kappa,923,Lambda,924,Mu,925,Nu,926,Xi,927,Omicron,928,Pi,929,Rho,931,Sigma,932,Tau,933,Upsilon,934,Phi,935,Chi,936,Psi,937,Omega,945,alpha,946,beta,947,gamma,948,delta,949,epsilon,950,zeta,951,eta,952,theta,953,iota,954,kappa,955,lambda,956,mu,957,nu,958,xi,959,omicron,960,pi,961,rho,962,sigmaf,963,sigma,964,tau,965,upsilon,966,phi,967,chi,968,psi,969,omega,8501,alefsym,982,piv,8476,real,977,thetasym,978,upsih,8472,weierp,8465,image,8592,larr,8593,uarr,8594,rarr,8595,darr,8596,harr,8629,crarr,8656,lArr,8657,uArr,8658,rArr,8659,dArr,8660,hArr,8756,there4,8834,sub,8835,sup,8836,nsub,8838,sube,8839,supe,8853,oplus,8855,otimes,8869,perp,8901,sdot,8968,lceil,8969,rceil,8970,lfloor,8971,rfloor,9001,lang,9002,rang,9674,loz,9824,spades,9827,clubs,9829,hearts,9830,diams,8194,ensp,8195,emsp,8201,thinsp,8204,zwnj,8205,zwj,8206,lrm,8207,rlm,173,shy");
	defParam("cleanup_callback", "");
	defParam("add_unload_trigger", true);
	defParam("ask", false);
	defParam("nowrap", false);
	defParam("auto_resize", false);
	defParam("auto_focus", false);

	// Setup baseHREF
	var baseHREF = tinyMCE.settings['document_base_url'];
	if (baseHREF.indexOf('?') != -1)
		baseHREF = baseHREF.substring(0, baseHREF.indexOf('?'));
	this.settings['base_href'] = baseHREF.substring(0, baseHREF.lastIndexOf('/')) + "/";

	theme = this.settings['theme'];

	this.blockRegExp = new RegExp("^(h1|h2|h3|h4|h5|h6|p|div|address|pre|form|table|li|ol|ul|td)$", "i");

	// Theme url
	this.settings['theme_href'] = tinyMCE.baseURL + "/themes/" + theme;

	if (!tinyMCE.isMSIE)
		this.settings['force_br_newlines'] = false;

	if (tinyMCE.getParam("content_css", false)) {
		var cssPath = tinyMCE.getParam("content_css", "");

		// Is relative
		if (cssPath.indexOf('://') == -1 && cssPath.charAt(0) != '/')
			this.settings['content_css'] = this.documentBasePath + "/" + cssPath;
		else
			this.settings['content_css'] = cssPath;
	} else
		this.settings['content_css'] = tinyMCE.baseURL + "/themes/" + theme + "/editor_content.css";

	if (tinyMCE.getParam("popups_css", false)) {
		var cssPath = tinyMCE.getParam("popups_css", "");

		// Is relative
		if (cssPath.indexOf('://') == -1 && cssPath.charAt(0) != '/')
			this.settings['popups_css'] = this.documentBasePath + "/" + cssPath;
		else
			this.settings['popups_css'] = cssPath;
	} else
		this.settings['popups_css'] = tinyMCE.baseURL + "/themes/" + theme + "/editor_popup.css";

	if (tinyMCE.getParam("editor_css", false)) {
		var cssPath = tinyMCE.getParam("editor_css", "");

		// Is relative
		if (cssPath.indexOf('://') == -1 && cssPath.charAt(0) != '/')
			this.settings['editor_css'] = this.documentBasePath + "/" + cssPath;
		else
			this.settings['editor_css'] = cssPath;
	} else
		this.settings['editor_css'] = tinyMCE.baseURL + "/themes/" + theme + "/editor_ui.css";

	if (tinyMCE.settings['debug']) {
		var msg = "Debug: \n";

		msg += "baseURL: " + this.baseURL + "\n";
		msg += "documentBasePath: " + this.documentBasePath + "\n";
		msg += "content_css: " + this.settings['content_css'] + "\n";
		msg += "popups_css: " + this.settings['popups_css'] + "\n";
		msg += "editor_css: " + this.settings['editor_css'] + "\n";

		alert(msg);
	}

	// Init HTML cleanup
	this._initCleanup();
	tinyMCE.addEvent(window, "load", TinyMCE_onLoad);

	document.write('<sc'+'ript language="javascript" type="text/javascript" src="' + tinyMCE.baseURL + '/themes/' + this.settings['theme'] + '/editor_template' + srcMode + '.js"></script>');
	document.write('<sc'+'ript language="javascript" type="text/javascript" src="' + tinyMCE.baseURL + '/langs/' + this.settings['language'] +  '.js"></script>');
	document.write('<link href="' + this.settings['editor_css'] + '" rel="stylesheet" type="text/css">');

	// Add theme plugins
	var themePlugins = tinyMCE.getParam('plugins', '', true, ',');
	if (this.settings['plugins'] != '') {
		for (var i=0; i<themePlugins.length; i++)
			document.write('<sc'+'ript language="javascript" type="text/javascript" src="' + tinyMCE.baseURL + '/plugins/' + themePlugins[i] + '/editor_plugin' + srcMode + '.js"></script>');
	}
}

function TinyMCE_confirmAdd(e) {
	var elm = tinyMCE.isMSIE ? event.srcElement : e.target;
	var elementId = elm.name ? elm.name : elm.id;

	if (!elm.getAttribute('mce_noask') && confirm(tinyMCELang['lang_edit_confirm']))
		tinyMCE.addMCEControl(elm, elementId);

	elm.setAttribute('mce_noask', 'true');
}

function TinyMCE_updateContent(form_element_name) {
	// Find MCE instance linked to given form element and copy it's value
	var formElement = document.getElementById(form_element_name);
	for (var n in tinyMCE.instances) {
		var inst = tinyMCE.instances[n];
		if (inst.formElement == formElement) {
			var doc = inst.getDoc();
	
			tinyMCE._setHTML(doc, inst.formElement.value);

			if (!tinyMCE.isMSIE)
				doc.body.innerHTML = tinyMCE._cleanupHTML(doc, this.settings, doc.body, inst.visualAid);
		}
	}
}

function TinyMCE_addMCEControl(replace_element, form_element_name, target_document) {
	var id = "mce_editor_" + tinyMCE.idCounter++;
	var inst = new TinyMCEControl(tinyMCE.settings);

	inst.editorId = id;
	this.instances[id] = inst;

	inst.onAdd(replace_element, form_element_name, target_document);
}

function TinyMCE_triggerSave(skip_cleanup, skip_callback) {
	// Cleanup and set all form fields
	for (var n in tinyMCE.instances) {
		var inst = tinyMCE.instances[n];
		tinyMCE.settings['preformatted'] = false;

		// Default to false
		if (typeof(skip_cleanup) == "undefined")
			skip_cleanup = false;

		// Default to false
		if (typeof(skip_callback) == "undefined")
			skip_callback = false;

		tinyMCE._setHTML(inst.getDoc(), inst.getBody().innerHTML);

		var htm = skip_cleanup ? inst.getBody().innerHTML : tinyMCE._cleanupHTML(inst.getDoc(), this.settings, inst.getBody(), this.visualAid, true);

		//var htm = tinyMCE._cleanupHTML(inst.getDoc(), tinyMCE.settings, inst.getBody(), false, true);

		if (tinyMCE.settings["encoding"] == "xml" || tinyMCE.settings["encoding"] == "html")
			htm = tinyMCE.convertStringToXML(htm);

		if (!skip_callback && tinyMCE.settings['save_callback'] != "")
			var content = eval(tinyMCE.settings['save_callback'] + "(inst.formTargetElementId,htm,inst.getBody());");

		// Use callback content if available
		if ((typeof(content) != "undefined") && content != null)
			htm = content;

		// Replace some weird entities (Bug: #1056343)
		htm = tinyMCE.regexpReplace(htm, "&#40;", "(", "gi");
		htm = tinyMCE.regexpReplace(htm, "&#41;", ")", "gi");
		htm = tinyMCE.regexpReplace(htm, "&#59;", ";", "gi");
		htm = tinyMCE.regexpReplace(htm, "&#34;", "&quot;", "gi");
		htm = tinyMCE.regexpReplace(htm, "&#94;", "^", "gi");

		inst.formElement.value = htm;
	}
}

function TinyMCE__convertOnClick(node) {
	// Skip on MSIE < 6+
	if (tinyMCE.isMSIE5)
		return;

	// Convert all onclick to mce_onclick
	var elms = node.getElementsByTagName("a");
	for (var i=0; i<elms.length; i++) {
		var onclick = elms[i].getAttribute('onclick');
		if (onclick && onclick != "") {
			elms[i].removeAttribute("onclick");
			elms[i].setAttribute("mce_onclick", tinyMCE.cleanupEventStr("" + onclick));
			elms[i].onclick = null;
		}
	}
}

function TinyMCE_resetForm(form_index) {
	var formObj = document.forms[form_index];

	for (var n in tinyMCE.instances) {
		var inst = tinyMCE.instances[n];

		for (var i=0; i<formObj.elements.length; i++) {
			if (inst.formTargetElementId == formObj.elements[i].name) {
				inst.getBody().innerHTML = formObj.elements[i].value;
				return;
			}
		}
	}
}

function TinyMCE_execInstanceCommand(editor_id, command, user_interface, value, focus) {
	var mceControl = tinyMCE.getInstanceById(editor_id);
	if (mceControl) {
		if (typeof(focus) == "undefined")
			focus = true;

		if (focus)
			mceControl.contentWindow.focus();

		// Reset design mode if lost
		mceControl.autoResetDesignMode();

		this.selectedElement = mceControl.getFocusElement();
		this.selectedInstance = mceControl;
		tinyMCE.execCommand(command, user_interface, value);
	}
}

function TinyMCE_execCommand(command, user_interface, value) {
	// Default input
	user_interface = user_interface ? user_interface : false;
	value = value ? value : null;

	switch (command) {
		case 'mceHelp':
			window.open(tinyMCE.themeURL + "/docs/" + this.settings['docs_language'] + "/index.htm", "mceHelp", "menubar=yes,toolbar=yes,scrollbars=yes,left=20,top=20,width=550,height=600");
		return;

		case 'mceFocus':
			var mceControl = tinyMCE.getInstanceById(value);
			if (mceControl)
				mceControl.contentWindow.focus();
		return;

		case "mceAddControl":
		case "mceAddEditor":
			tinyMCE.addMCEControl(tinyMCE._getElementById(value), value);
			return;

		case "mceAddFrameControl":
			tinyMCE.addMCEControl(tinyMCE._getElementById(value), value['element'], value['document']);
			return;

		case "mceRemoveControl":
		case "mceRemoveEditor":
			tinyMCE.removeMCEControl(value);
			return;

		case "mceResetDesignMode":
			// Resets the designmode state of the editors in Gecko
			if (!tinyMCE.isMSIE) {
				for (var n in tinyMCE.instances)
					tinyMCE.instances[n].getDoc().designMode = "on";
			}

			return;
	}

	if (this.selectedInstance)
		this.selectedInstance.execCommand(command, user_interface, value);
	else if (tinyMCE.settings['focus_alert'])
		alert(tinyMCELang['lang_focus_alert']);
}

function TinyMCE_addEventHandlers(editor_id) {
	if (tinyMCE.isMSIE) {
		var doc = document.frames[editor_id].document;

		var patchFunc = function() {
			var event = document.frames[editor_id].event;

			event.target = event.srcElement;
			event.target.editor_id = editor_id;

			TinyMCE_handleEvent(event);
		};

		// Event patch
		doc.onkeypress = patchFunc;
		doc.onkeyup = patchFunc;
		doc.onkeydown = patchFunc;

		if (tinyMCE.settings['add_unload_trigger']) {
			tinyMCE.addEvent(window, "unload", function () {tinyMCE.triggerSave(true, true);});
			tinyMCE.addEvent(window, "beforeunload", function () {tinyMCE.triggerSave(true, true);});
		}

		// Due to stange focus bug in MSIE this line is disabled for now
		//document.frames[editor_id].document.onmousedown = patchFunc;
		doc.onmouseup = patchFunc;
		doc.onclick = patchFunc;
	} else {
		var instance = tinyMCE.instances[editor_id];
		var doc = instance.getDoc();

		doc.editor_id = editor_id;
		tinyMCE.addEvent(doc, "keypress", tinyMCE.handleEvent);
		tinyMCE.addEvent(doc, "keypress", tinyMCE.handleEvent);
		tinyMCE.addEvent(doc, "keydown", tinyMCE.handleEvent);
		tinyMCE.addEvent(doc, "keyup", tinyMCE.handleEvent);
		tinyMCE.addEvent(doc, "click", tinyMCE.handleEvent);
		tinyMCE.addEvent(doc, "mouseup", tinyMCE.handleEvent);
		tinyMCE.addEvent(doc, "mousedown", tinyMCE.handleEvent);
		tinyMCE.addEvent(doc, "focus", tinyMCE.handleEvent);

		if (tinyMCE.settings['add_unload_trigger'])
			tinyMCE.addEvent(window, "unload", function () {tinyMCE.triggerSave(true, true);});

		eval('try { doc.designMode = "On"; } catch(e) {}');
	}
}

function TinyMCE__createIFrame(replace_element) {
	var iframe = document.createElement("iframe");
	var id = replace_element.getAttribute("id");

	iframe.setAttribute("id", id);
	iframe.setAttribute("className", "mceEditorArea");
	iframe.setAttribute("border", "0");
	iframe.setAttribute("frameBorder", "0");
	iframe.setAttribute("marginWidth", "0");
	iframe.setAttribute("marginHeight", "0");
	iframe.setAttribute("leftMargin", "0");
	iframe.setAttribute("topMargin", "0");
	iframe.setAttribute("width", tinyMCE.settings['area_width']);
	iframe.setAttribute("height", tinyMCE.settings['area_height']);

	if (tinyMCE.settings["auto_resize"])
		iframe.setAttribute("scrolling", "no");

	// Must have a src element in MSIE HTTPs breaks aswell as absoute URLs
	if (tinyMCE.isMSIE)
		iframe.setAttribute("src", this.settings['default_document']);

	iframe.style.width = tinyMCE.settings['area_width'];
	iframe.style.height = tinyMCE.settings['area_height'];

	// MSIE 5.0 issue
	if (tinyMCE.isMSIE)
		replace_element.outerHTML = iframe.outerHTML;
	else
		replace_element.parentNode.replaceChild(iframe, replace_element);

	if (tinyMCE.isMSIE)
		return window.frames[id];
	else
		return iframe;
}

function TinyMCE_setupContent(editor_id) {
	var inst = tinyMCE.instances[editor_id];
	var doc = inst.getDoc();
	var head = doc.getElementsByTagName('head').item(0);
	var content = inst.startContent;

	// Not loaded correctly hit it again, Mozilla bug #997860
	if (!tinyMCE.isMSIE && doc.title != "blank_page") {
		// This part will remove the designMode status
		doc.location.href = tinyMCE.baseURL + "/blank.htm";
		window.setTimeout("tinyMCE.setupContent('" + editor_id + "');", 1000);
		return;
	}

	if (!head) {
		window.setTimeout("tinyMCE.setupContent('" + editor_id + "');", 10);
		return;
	}

	if (tinyMCE.settings['nowrap'])
		doc.body.style.whiteSpace = "nowrap";

	doc.body.dir = this.settings['directionality'];
	doc.editorId = editor_id;

	// Add on document element in Mozilla
	if (!tinyMCE.isMSIE)
		doc.documentElement.editorId = editor_id;

	// Setup base element
	base = doc.createElement("base");
	base.setAttribute('href', tinyMCE.settings['base_href']);
	head.appendChild(base);

	// Replace new line characters to BRs
	if (tinyMCE.settings['convert_newlines_to_brs']) {
		content = tinyMCE.regexpReplace(content, "\r\n", "<br />", "gi");
		content = tinyMCE.regexpReplace(content, "\r", "<br />", "gi");
		content = tinyMCE.regexpReplace(content, "\n", "<br />", "gi");
	}

	// Call custom cleanup code
	content = tinyMCE._customCleanup("insert_to_editor", content);

	if (tinyMCE.isMSIE) {
		var styleSheet = document.frames[editor_id].document.createStyleSheet(inst.settings['content_css']);

		// Ugly!!!
		window.setInterval('try{tinyMCE.getCSSClasses(document.frames["' + editor_id + '"].document, "' + editor_id + '");}catch(e){}', 500);

		if (tinyMCE.settings["force_br_newlines"])
			document.frames[editor_id].document.styleSheets[0].addRule("p", "margin: 0px;");

		var patchFunc = function() {
			var event = document.frames[editor_id].event;
			event.target = document.frames[editor_id].document;

			TinyMCE_handleEvent(event);
		};

		var body = document.frames[editor_id].document.body;

		body.onbeforepaste = patchFunc;
		body.onbeforecut = patchFunc;
		body.onpaste = patchFunc;
		body.editorId = editor_id;
	} else {
		// Import editor css
		var cssImporter = doc.createElement("link");
		cssImporter.rel = "stylesheet";
		cssImporter.href = inst.settings['content_css'];
		if (headArr = doc.getElementsByTagName("head"));
			headArr[0].appendChild(cssImporter);
	}

	// Fix for bug #958637
	if (!tinyMCE.isMSIE) {
		var contentElement = inst.getDoc().createElement("body");
		var doc = inst.getDoc();

		contentElement.innerHTML = content;

		// Remove weridness!
		if (tinyMCE.settings['force_p_newlines'])
			content = content.replace(new RegExp('&lt;&gt;', 'g'), "");

		if (tinyMCE.settings['cleanup_on_startup'])
			inst.getBody().innerHTML = tinyMCE._cleanupHTML(doc, this.settings, contentElement);
		else {
			// Convert all strong/em to b/i
			content = tinyMCE.regexpReplace(content, "<strong", "<b", "gi");
			content = tinyMCE.regexpReplace(content, "<em", "<i", "gi");
			content = tinyMCE.regexpReplace(content, "</strong>", "</b>", "gi");
			content = tinyMCE.regexpReplace(content, "</em>", "</i>", "gi");
			inst.getBody().innerHTML = contentElement.innerHTML;
		}

		inst.convertAllRelativeURLs();
	} else {
		if (tinyMCE.settings['cleanup_on_startup']) {
			tinyMCE._setHTML(inst.getDoc(), content);
			// Produces permission denied error in MSIE 5.5
			eval('try {inst.getBody().innerHTML = tinyMCE._cleanupHTML(inst.contentDocument, this.settings, inst.getBody());} catch(e) {}');
		} else
			inst.getBody().innerHTML = content;
	}

	tinyMCE._convertOnClick(inst.getBody());

	// Fix for bug #957681
	//inst.getDoc().designMode = inst.getDoc().designMode;

	// Setup element references
	var parentElm = document.getElementById(inst.editorId + '_parent');
	if (parentElm.lastChild.nodeName.toLowerCase() == "input")
		inst.formElement = parentElm.lastChild;
	else
		inst.formElement = parentElm.nextSibling;

	tinyMCE.handleVisualAid(inst.getBody(), true, tinyMCE.settings['visual']);
	inst.executeCallback('setupcontent_callback', '_setupContent', 0, editor_id, inst.getBody(), inst.getDoc());

	// Re-add design mode on mozilla
	if (!tinyMCE.isMSIE)
		TinyMCE_addEventHandlers(editor_id);

	// Trigger node change, this call locks buttons for tables and so forth
	tinyMCE.selectedInstance = inst;
	tinyMCE.selectedElement = inst.contentWindow.document.body;
	tinyMCE.triggerNodeChange(false, true);
}

function TinyMCE_handleEvent(e) {
	//debug(e.type + " " + e.target.nodeName + " " + (e.relatedTarget ? e.relatedTarget.nodeName : ""));

	switch (e.type) {
		case "submit":
			var formObj = tinyMCE.isMSIE ? window.event.srcElement : e.target;

			// Disable all UI form elements that TinyMCE created
			for (var i=0; i<formObj.elements.length; i++) {
				var elementId = formObj.elements[i].name ? formObj.elements[i].name : formObj.elements[i].id;

				if (elementId.indexOf('mce_editor_') == 0)
					formObj.elements[i].disabled = true;
			}

			tinyMCE.triggerSave();
			return;

		case "reset":
			var formObj = tinyMCE.isMSIE ? window.event.srcElement : e.target;

			for (var i=0; i<document.forms.length; i++) {
				if (document.forms[i] == formObj)
					window.setTimeout('TinyMCE_resetForm(' + i + ');', 10);
			}
			return;

		case "paste":
			if (tinyMCE.settings['auto_cleanup_word'])
				window.setTimeout("tinyMCE.execInstanceCommand('" + e.target.editorId + "', 'mceCleanupWord', false, null);", 1);

			break;

		case "beforecut":
		case "beforepaste":
			if (tinyMCE.selectedInstance)
				tinyMCE.selectedInstance.execCommand("mceAddUndoLevel");
			break;

		case "keypress":
			if (e.target.editorId) {
				tinyMCE.selectedInstance = tinyMCE.instances[e.target.editorId];
			} else {
				if (e.target.ownerDocument.editorId)
					tinyMCE.selectedInstance = tinyMCE.instances[e.target.ownerDocument.editorId];
			}

			// Insert space instead of &nbsp;
/*			if (tinyMCE.isGecko && e.charCode == 32) {
				if (tinyMCE.selectedInstance._insertSpace()) {
					// Cancel event
					e.preventDefault();
					return false;
				}
			}*/

			// Insert P element
			if (tinyMCE.isGecko && tinyMCE.settings['force_p_newlines'] && e.keyCode == 13 && !e.shiftKey) {
				// Insert P element instead of BR
				if (tinyMCE.selectedInstance._insertPara(e)) {
					// Cancel event
					e.preventDefault();
					return false;
				}
			}

			// Handle backspace
			if (tinyMCE.isGecko && tinyMCE.settings['force_p_newlines'] && (e.keyCode == 8 || e.keyCode == 46) && !e.shiftKey) {
				// Insert P element instead of BR
				if (tinyMCE.selectedInstance._handleBackSpace(e.type)) {
					// Cancel event
					e.preventDefault();
					return false;
				}
			}

			// Mozilla custom key handling
			if (!tinyMCE.isMSIE && e.ctrlKey && tinyMCE.settings['custom_undo_redo']) {
				if (e.charCode == 120 || e.charCode == 118) { // Ctrl+X, Ctrl+V
					tinyMCE.selectedInstance.execCommand("mceAddUndoLevel");
					return;
				}

				if (tinyMCE.settings['custom_undo_redo_keyboard_shortcuts']) {
					if (e.charCode == 122) { // Ctrl+Z
						tinyMCE.selectedInstance.execCommand("Undo");

						// Cancel event
						e.preventDefault();
						return false;
					}

					if (e.charCode == 121) { // Ctrl+Y
						tinyMCE.selectedInstance.execCommand("Redo");

						// Cancel event
						e.preventDefault();
						return false;
					}
				}

				if (e.charCode == 98) { // Ctrl+B
					tinyMCE.selectedInstance.execCommand("Bold");

					// Cancel event
					e.preventDefault();
					return false;
				}

				if (e.charCode == 105) { // Ctrl+I
					tinyMCE.selectedInstance.execCommand("Italic");

					// Cancel event
					e.preventDefault();
					return false;
				}

				if (e.charCode == 117) { // Ctrl+U
					tinyMCE.selectedInstance.execCommand("Underline");

					// Cancel event
					e.preventDefault();
					return false;
				}
			}

			if (tinyMCE.settings['custom_undo_redo']) {
				// Check if it's a position key press
				var keys = new Array(13,45,36,35,33,34,37,38,39,40);
				var posKey = false;
				for (var i=0; i<keys.length; i++) {
					if (keys[i] == e.keyCode) {
						tinyMCE.selectedInstance.typing = false;
						posKey = true;
						break;
					}
				}

				// Add typing undo level
				if (!tinyMCE.selectedInstance.typing && !posKey) {
					tinyMCE.selectedInstance.execCommand("mceAddUndoLevel");
					tinyMCE.selectedInstance.typing = true;
				}
			}

			//window.status = e.keyCode;
			//window.status = e.type + " " + e.target.nodeName;

			// Return key pressed
			if (tinyMCE.isMSIE && tinyMCE.settings['force_br_newlines'] && e.keyCode == 13) {
				if (e.target.editorId)
					tinyMCE.selectedInstance = tinyMCE.instances[e.target.editorId];

				if (tinyMCE.selectedInstance) {
					var sel = tinyMCE.selectedInstance.getDoc().selection;
					var rng = sel.createRange();

					if (tinyMCE.getParentElement(rng.parentElement(), "li") != null)
						return false;

					// Cancel event
					e.returnValue = false;
					e.cancelBubble = true;

					// Insert BR element
					rng.pasteHTML("<br />");
					rng.collapse(false);
					rng.select();

					tinyMCE.triggerNodeChange(false);
					return false;
				}
			}

			// Backspace or delete
			if (e.keyCode == 8 || e.keyCode == 46) {
				tinyMCE.selectedElement = e.target;
				tinyMCE.linkElement = tinyMCE.getParentElement(e.target, "a");
				tinyMCE.imgElement = tinyMCE.getParentElement(e.target, "img");
				tinyMCE.triggerNodeChange(false);
			}

			return false;
		break;

		case "keyup":
		case "keydown":
			if (e.target.editorId)
				tinyMCE.selectedInstance = tinyMCE.instances[e.target.editorId];
			else
				return;

			// Handle backspace
			if (tinyMCE.isGecko && tinyMCE.settings['force_p_newlines'] && (e.keyCode == 8 || e.keyCode == 46) && !e.shiftKey) {
				// Insert P element instead of BR
				if (tinyMCE.selectedInstance._handleBackSpace(e.type)) {
					// Cancel event
					e.preventDefault();
					return false;
				}
			}

			tinyMCE.selectedElement = null;
			tinyMCE.selectedNode = null;
			var elm = tinyMCE.selectedInstance.getFocusElement();
			tinyMCE.linkElement = tinyMCE.getParentElement(elm, "a");
			tinyMCE.imgElement = tinyMCE.getParentElement(elm, "img");
			tinyMCE.selectedElement = elm;

			// Update visualaids on tabs
			if (tinyMCE.isGecko && e.type == "keyup" && e.keyCode == 9)
				tinyMCE.handleVisualAid(tinyMCE.selectedInstance.getBody(), true, tinyMCE.settings['visual']);

			// Run image/link fix on Gecko if diffrent document base on paste
			if (tinyMCE.isGecko && tinyMCE.settings['document_base_url'] != "" + document.location.href && e.type == "keyup" && e.ctrlKey && e.keyCode == 86)
				tinyMCE.selectedInstance.fixBrokenURLs();

			// Insert space instead of &nbsp;
/*			if (e.type == "keydown" && e.keyCode == 32) {
				if (tinyMCE.selectedInstance._insertSpace()) {
					// Cancel event
					e.returnValue = false;
					e.cancelBubble = true;
					return false;
				}
			}*/

			// MSIE custom key handling
			if (tinyMCE.isMSIE && tinyMCE.settings['custom_undo_redo']) {
				// Check if it's a position key press
				var keys = new Array(13,45,36,35,33,34,37,38,39,40);
				var posKey = false;
				for (var i=0; i<keys.length; i++) {
					if (keys[i] == e.keyCode) {
						tinyMCE.selectedInstance.typing = false;
						posKey = true;
						break;
					}
				}

				// Add typing undo level (unless pos keys or shift, alt, ctrl, capslock)
				if (!tinyMCE.selectedInstance.typing && !posKey && (e.keyCode < 16 || e.keyCode > 18 && e.keyCode != 255)) {
					tinyMCE.selectedInstance.execCommand("mceAddUndoLevel");
					tinyMCE.selectedInstance.typing = true;
					tinyMCE.triggerNodeChange(false);
				}

				if (posKey && e.type == "keyup")
					tinyMCE.triggerNodeChange(false);

				var ctrlKeys = new Array(66,73,85,86,88); // B/I/U/V/X
				var keys = new Array(8,46); // Backspace,Delete
				for (var i=0; i<keys.length; i++) {
					if ((ctrlKeys[i] == e.keyCode && e.ctrlKey) || keys[i] == e.keyCode) {
						tinyMCE.selectedInstance.execCommand("mceAddUndoLevel");
						tinyMCE.triggerNodeChange(false);
						return true;
					}
				}

				if (tinyMCE.settings['custom_undo_redo_keyboard_shortcuts']) {
					if (e.keyCode == 90 && e.ctrlKey && e.type == "keydown") { // Ctrl+Z
						tinyMCE.selectedInstance.execCommand("Undo");
						tinyMCE.triggerNodeChange(false);

						// Cancel event
						e.returnValue = false;
						e.cancelBubble = true;
						return false;
					}

					if (e.keyCode == 89 && e.ctrlKey && e.type == "keydown") { // Ctrl+Y
						tinyMCE.selectedInstance.execCommand("Redo");
						tinyMCE.triggerNodeChange(false);

						// Cancel event
						e.returnValue = false;
						e.cancelBubble = true;
						return false;
					}
				}
			}

			// Check if it's a position key press
			var keys = new Array(13,45,36,35,33,34,37,38,39,40);
			var posKey = false;
			for (var i=0; i<keys.length; i++) {
				if (keys[i] == e.keyCode) {
					posKey = true;
					break;
				}
			}

			// Trigger some nodechange on keyup
			if (posKey && e.type == "keyup")
				tinyMCE.triggerNodeChange(false);
		break;

		case "mousedown":
		case "mouseup":
		case "click":
		case "focus":
			// Check instance event trigged on
			var targetBody = tinyMCE.getParentElement(e.target, "body");
			for (var instanceName in tinyMCE.instances) {
				var inst = tinyMCE.instances[instanceName];

				// Reset design mode if lost (on everything just in case)
				inst.autoResetDesignMode();

				if (inst.getBody() == targetBody) {
					tinyMCE.selectedInstance = inst;
					tinyMCE.selectedElement = e.target;
					tinyMCE.linkElement = tinyMCE.getParentElement(tinyMCE.selectedElement, "a");
					tinyMCE.imgElement = tinyMCE.getParentElement(tinyMCE.selectedElement, "img");

					// Reset typing
					tinyMCE.selectedInstance.typing = false;
					break;
				}
			}

			// Reset selected node
			if (e.type != "focus")
				tinyMCE.selectedNode = null;

			tinyMCE.triggerNodeChange(false);

			// Just in case
			if (!tinyMCE.selectedInstance && e.target.editorId)
				tinyMCE.selectedInstance = tinyMCE.instances[e.target.editorId];

			// Was it alt click on link
			if (e.target.nodeName.toLowerCase() == "a" && e.type == "click" && e.altKey) {
				var evalCode = "" + tinyMCE.cleanupEventStr(e.target.getAttribute("mce_onclick"));

				// Remove any return too
				eval(evalCode.replace('return false;', ''));
			}

			//if (tinyMCE.selectedInstance)
			//	tinyMCE.selectedInstance.fixBrokenURLs();

			// Run image/link fix on Gecko if diffrent document base
			if (tinyMCE.isGecko && tinyMCE.settings['document_base_url'] != "" + document.location.href)
				window.setTimeout('tinyMCE.getInstanceById("' + inst.editorId + '").fixBrokenURLs();', 10);

			return false;
		break;
    } // end switch
} // end function

function TinyMCE_switchClass(element, class_name, lock_state) {
	var lockChanged = false;

	if (typeof(lock_state) != "undefined" && element != null) {
		element.classLock = lock_state;
		lockChanged = true;
	}

	if (element != null && (lockChanged || !element.classLock)) {
		element.oldClassName = element.className;
		element.className = class_name;
	}
}

function TinyMCE_restoreAndSwitchClass(element, class_name) {
	if (element != null && !element.classLock) {
		this.restoreClass(element);
		this.switchClass(element, class_name);
	}
}

function TinyMCE_switchClassSticky(element_name, class_name, lock_state) {
	var element, lockChanged = false;

	// Performance issue
	if (!this.stickyClassesLookup[element_name])
		this.stickyClassesLookup[element_name] = document.getElementById(element_name);

//	element = document.getElementById(element_name);
	element = this.stickyClassesLookup[element_name];

	if (typeof(lock_state) != "undefined" && element != null) {
		element.classLock = lock_state;
		lockChanged = true;
	}

	if (element != null && (lockChanged || !element.classLock)) {
		element.className = class_name;
		element.oldClassName = class_name;
	}
}

function TinyMCE_restoreClass(element) {
	if (element != null && element.oldClassName && !element.classLock) {
		element.className = element.oldClassName;
		element.oldClassName = null;
	}
}

function TinyMCE_setClassLock(element, lock_state) {
	if (element != null)
		element.classLock = lock_state;
}

function TinyMCE_addEvent(obj, name, handler) {
	if (tinyMCE.isMSIE)
		obj.attachEvent("on" + name, handler);
	else
		obj.addEventListener(name, handler, false);
}

function TinyMCE_onLoad() {
	var elementRefAr = new Array();

	// Add submit triggers
	if (document.forms && tinyMCE.settings['add_form_submit_trigger']) {
		for (var i=0; i<document.forms.length; i++) {
			var form = document.forms[i];

			tinyMCE.addEvent(form, "submit", TinyMCE_handleEvent);
			tinyMCE.addEvent(form, "reset", TinyMCE_handleEvent);
		}
	}

	// Add editor instances based on mode
	switch (tinyMCE.settings['mode']) {
		case "exact":
			var elements = tinyMCE.getParam('elements', '', true, ',');

			for (var i=0; i<elements.length; i++) {
				var element = tinyMCE._getElementById(elements[i]);

				if (tinyMCE.settings['ask'] && element) {
					elementRefAr[elementRefAr.length] = element;
					continue;
				}

				if (element)
					tinyMCE.addMCEControl(element, elements[i]);
				else
					alert("Error: Could not find element by id or name: " + elements[i]);
			}
		break;

		case "specific_textareas":
		case "textareas":
			var nodeList = document.getElementsByTagName("textarea");

			for (var i=0; i<nodeList.length; i++) {
				if (tinyMCE.settings['mode'] != "specific_textareas" || nodeList.item(i).getAttribute(tinyMCE.settings['textarea_trigger']) == "true")
					elementRefAr[elementRefAr.length] = nodeList.item(i);
			}
		break;
	}

	for (var i=0; i<elementRefAr.length; i++) {
		var element = elementRefAr[i];
		var elementId = element.name ? element.name : element.id;

		if (tinyMCE.settings['ask']) {
			// Focus breaks in Mozilla
			if (tinyMCE.isGecko)
				tinyMCE.addEvent(element, "focus", function (e) {window.setTimeout(function() {TinyMCE_confirmAdd(e);}, 10);});
			else
			tinyMCE.addEvent(element, "focus", TinyMCE_confirmAdd);
		} else
			tinyMCE.addMCEControl(element, elementId);
	}

	// Handle auto focus
	if (tinyMCE.settings['auto_focus']) {
		window.setTimeout(function () {
			var inst = tinyMCE.getInstanceById(tinyMCE.settings['auto_focus']);
			inst.selectNode(inst.getBody(), true, true);
			inst.contentWindow.focus();
		}, 10);
	}

	// Call oninit callback
	if (tinyMCE.settings['oninit'])
		eval(tinyMCE.settings['oninit']);
}

function TinyMCE_removeMCEControl(editor_id) {
	var mceControl = tinyMCE.getInstanceById(editor_id);
	if (mceControl) {
		editor_id = mceControl.editorId;
		var html = tinyMCE.getContent(editor_id);

		// Remove editor instance from instances array
		var tmpInstances = new Array();
		for (var instanceName in tinyMCE.instances) {
			var instance = tinyMCE.instances[instanceName];
			if (instanceName != editor_id)
					tmpInstances[instanceName] = instance;
		}
		tinyMCE.instances = tmpInstances;

		tinyMCE.selectedElement = null;
		tinyMCE.selectedInstance = null;

		// Remove element
		var replaceElement = document.getElementById(editor_id + "_parent");
		var oldTargetElement = mceControl.oldTargetElement;
		var targetName = oldTargetElement.nodeName.toLowerCase();

		if (targetName == "textarea" || targetName == "input") {
			// Just show the old text area
			replaceElement.parentNode.removeChild(replaceElement);
			oldTargetElement.style.display = "inline";
			oldTargetElement.value = html;
		} else {
			oldTargetElement.innerHTML = html;

			replaceElement.parentNode.insertBefore(oldTargetElement, replaceElement);
			replaceElement.parentNode.removeChild(replaceElement);
		}
	}
}

function TinyMCE__cleanupElementName(element_name, element) {
	element_name = element_name.toLowerCase();

	// Never include body
	if (element_name == "body")
		return null;

	// If verification mode
	if (tinyMCE.cleanup_verify_html) {
		// Check if invalid element
		for (var i=0; i<tinyMCE.cleanup_invalidElements.length; i++) {
			if (tinyMCE.cleanup_invalidElements[i] == element_name)
				return null;
		}

		// Check if valid element
		var validElement = false;
		var elementAttribs = null;
		for (var i=0; i<tinyMCE.cleanup_validElements.length && !elementAttribs; i++) {
			for (var x=0; x<tinyMCE.cleanup_validElements[i][0].length; x++) {
				var elmMatch = tinyMCE.cleanup_validElements[i][0][x];

				// Handle wildcard/regexp
				if (elmMatch.match(new RegExp('\\*|\\?|\\+', 'g')) != null) {
					elmMatch = elmMatch.replace(new RegExp('\\?', 'g'), '(\\S?)');
					elmMatch = elmMatch.replace(new RegExp('\\+', 'g'), '(\\S+)');
					elmMatch = elmMatch.replace(new RegExp('\\*', 'g'), '(\\S*)');
					elmMatch = "^" + elmMatch + "$";
					if (element_name.match(new RegExp(elmMatch, 'g'))) {
						elementAttribs = tinyMCE.cleanup_validElements[i];
						validElement = true;
						break;
					}
				}

				// Handle non regexp
				if (element_name == elmMatch) {
					elementAttribs = tinyMCE.cleanup_validElements[i];
					validElement = true;
					element_name = elementAttribs[0][0];
					break;
				}
			}
		}

		if (!validElement)
			return null;
	}

	// Special Mozilla stuff
	if (!tinyMCE.isMSIE) {
		// Fix for bug #958498
		if (element_name == "strong" && !tinyMCE.cleanup_on_save)
			element_name = "b";
		else if (element_name == "em" && !tinyMCE.cleanup_on_save)
			element_name = "i";
	}

	// Special MSIE stuff
	if (tinyMCE.isMSIE) {
		if (element_name == "table") {
			var attribValue = element.style.pixelWidth == 0 ? element.getAttribute("width") : element.style.pixelWidth;
			element.setAttribute("width", attribValue);

			attribValue = element.style.pixelHeight == 0 ? element.getAttribute("height") : element.style.pixelHeight;
			element.setAttribute("height", attribValue);
		}
	}

	var elmData = new Object();

	elmData.element_name = element_name;
	elmData.valid_attribs = elementAttribs;

	return elmData;
}

/**
 * Converts some element attributes to inline styles.
 */
function TinyMCE__fixInlineStyles(elm) {
	// Handle non table elements
	if (elm.nodeName.toLowerCase() != "table") {
		var value;

		// Setup width
		value = tinyMCE.isMSIE ? elm.width : elm.getAttribute("width");
		if (value && value != "") {
			if (typeof(value) != "string" || !value.indexOf("%"))
				value += "px";

			elm.style.width = value;
		}

		// Setup height
		value = tinyMCE.isMSIE ? elm.height : elm.getAttribute("height");
		if (value && value != "") {
			if (typeof(value) != "string" || !value.indexOf("%"))
				value += "px";

			elm.style.height = value;
		}

		// Setup border
		value = tinyMCE.isMSIE ? elm.border : elm.getAttribute("border");
		if (value && value != "")
			elm.style.borderWidth = value + "px";
	}

	// Setup align
	value = elm.getAttribute("align");
	if (value && value != "") {
		if (elm.nodeName.toLowerCase() == "img") {
			if (tinyMCE.isMSIE)
				elm.style.styleFloat = value;
			else
				elm.style.cssFloat = value;
		} else
			elm.style.textAlign = value;
	}

	// Setup vspace
	value = elm.getAttribute("vspace");
	if (value && value != "")
		elm.style.marginTop = value + "px";

	// Setup hspace
	value = elm.getAttribute("hspace");
	if (value && value != "")
		elm.style.marginBottom = value + "px";
}

function TinyMCE__cleanupAttribute(valid_attributes, element_name, attribute_node, element_node) {
	var attribName = attribute_node.nodeName.toLowerCase();
	var attribValue = attribute_node.nodeValue;
	var attribMustBeValue = null;
	var verified = false;

	// Inline styling, skip them
	if (tinyMCE.cleanup_inline_styles && element_name != "table" && element_name != "td") {
		if (attribName == "width" || attribName == "height" || attribName == "border" || attribName == "align" || attribName == "valign" || attribName == "hspace" || attribName == "vspace")
			return null;
	}

	// Mozilla attibute, remove them
	if (attribName.indexOf('moz_') != -1)
		return null;

	// Mozilla fix for drag-drop/copy/paste images
	if (!tinyMCE.isMSIE && (attribName == "mce_real_href" || attribName == "mce_real_src")) {
		if (!tinyMCE.cleanup_on_save) {
			var attrib = new Object();

			attrib.name = attribName;
			attrib.value = attribValue;

			return attrib;
		} else
			return null;
	}

	// Auto verify 
	if (attribName == "mce_onclick")
		verified = true;

	// Verify attrib
	if (tinyMCE.cleanup_verify_html && !verified) {
		for (var i=1; i<valid_attributes.length; i++) {
			var attribMatch = valid_attributes[i][0];
			var re = null;

			// Build regexp from wildcard
			if (attribMatch.match(new RegExp('\\*|\\?|\\+', 'g')) != null) {
				attribMatch = attribMatch.replace(new RegExp('\\?', 'g'), '(\\S?)');
				attribMatch = attribMatch.replace(new RegExp('\\+', 'g'), '(\\S+)');
				attribMatch = attribMatch.replace(new RegExp('\\*', 'g'), '(\\S*)');
				attribMatch = "^" + attribMatch + "$";
				re = new RegExp(attribMatch, 'g');
			}

			if ((re && attribName.match(re) != null) || attribName == attribMatch) {
				verified = true;
				attribMustBeValue = valid_attributes[i][3];
				break;
			}
		}

		// Allways pass styles on table and td elements if visual_aid
		if ((element_name == "table" || element_name == "td") && attribName == "style")
			verified = true;

		if (!verified)
			return false;
	} else
		verified = true;

	// Treat some attribs diffrent
	switch (attribName) {
		case "size":
			if (tinyMCE.isMSIE5 && element_name == "font")
				attribValue = element_node.size;
			break;

		case "color":
			if (tinyMCE.isMSIE5 && element_name == "font")
				attribValue = element_node.color;
			break;

		case "width":
			// MSIE 5.5 issue
			if (tinyMCE.isMSIE)
				attribValue = element_node.width;
			break;

		case "height":
			// MSIE 5.5 issue
			if (tinyMCE.isMSIE)
				attribValue = element_node.height;
			break;

		case "border":
			// MSIE 5.5 issue
			if (tinyMCE.isMSIE)
				attribValue = element_node.border;
			break;

//		case "className":
		case "class":
			if (!tinyMCE._verifyClass(element_node))
				return null;

//			if (tinyMCE.isMSIE)
//				attribValue = node.getAttribute('className');

			break;

		case "style":
			if (element_name == "table" || element_name == "td") {
				// Handle visual aid
				if (tinyMCE.cleanup_visual_table_style != "") {
					// Find parent table
					var tableElement = element_node;
					if (element_name == "td")
						tableElement = tinyMCE.getParentElement(tableElement, "table");

					if (tableElement && tableElement.getAttribute("border") == 0) {
						if (tinyMCE.cleanup_visual_aid)
							attribValue = tinyMCE.cleanup_visual_table_style;
						else
							return null;
					}
				}
			} else
				attribValue = element_node.style.cssText.toLowerCase();

			// Compress borders some
			if (tinyMCE.isMSIE) {
				var border = element_node.style.border;
				var bt = element_node.style.borderTop;
				var bl = element_node.style.borderLeft;
				var br = element_node.style.borderRight;
				var bb = element_node.style.borderBottom;

				// All the same
				if (border != "" && (bt == border && bl == border && br == border && bb == border)) {
					attribValue = tinyMCE.regexpReplace(attribValue, 'border-top: ' + border + '?; ?', '');
					attribValue = tinyMCE.regexpReplace(attribValue, 'border-left: ' + border  + '?; ?', '');
					attribValue = tinyMCE.regexpReplace(attribValue, 'border-right: ' + border  + '?; ?', '');
					attribValue = tinyMCE.regexpReplace(attribValue, 'border-bottom: ' + border + '?;( ?)', 'border: ' + border + ';$1');
				}
			}
			break;

		// Handle onclick
		case "onclick":
		case "mce_onclick":
			// Skip on MSIE < 6+
			if (tinyMCE.isMSIE5)
				break;

			// Fix onclick attrib
			if (tinyMCE.cleanup_on_save) {
				if (element_node.getAttribute("mce_onclick")) {
					attribName = "onclick";
					attribValue = "" + element_node.getAttribute("mce_onclick");
				}
			} else {
				if (attribName == "onclick" && !tinyMCE.cleanup_on_save)
					return null;
			}

			break;

		// Convert the URLs of these
		case "href":
		case "src":
			// Fix for dragdrop/copy paste Mozilla issue
			if (!tinyMCE.isMSIE && attribName == "href" && element_node.getAttribute("mce_real_href"))
				attribValue = element_node.getAttribute("mce_real_href");

			// Fix for dragdrop/copy paste Mozilla issue
			if (!tinyMCE.isMSIE && attribName == "src" && element_node.getAttribute("mce_real_src"))
				attribValue = element_node.getAttribute("mce_real_src");

			// Force absolute URLs in Firefox
			if (tinyMCE.isGecko && !tinyMCE.settings['relative_urls'])
				attribValue = tinyMCE.convertRelativeToAbsoluteURL(tinyMCE.settings['base_href'], attribValue);

			attribValue = eval(tinyMCE.cleanup_urlconvertor_callback + "(attribValue, element_node, tinyMCE.cleanup_on_save);");
			break;

		case "colspan":
		case "rowspan":
			// Not needed
			if (attribValue == "1")
				return null;
			break;

		// Skip these
		case "_moz-userdefined":
		case "editorid":
		case "editor_id":
		case "mce_real_href":
		case "mce_real_src":
			return null;
	}

	// Not the must be value
	if (attribMustBeValue != null) {
		var isCorrect = false;
		for (var i=0; i<attribMustBeValue.length; i++) {
			if (attribValue == attribMustBeValue[i]) {
				isCorrect = true;
				break;
			}
		}

		if (!isCorrect)
			return null;
	}

	var attrib = new Object();

	attrib.name = attribName;
	attrib.value = attribValue;

	return attrib;
}

function TinyMCE__verifyClass(node) {
	// Sometimes the class gets set to null, weird Gecko bug?
	if (tinyMCE.isGecko) {
		var className = node.getAttribute('class');
		if (!className)
			return false;
	}

	// Trim CSS class
	if (tinyMCE.isMSIE)
		var className = node.getAttribute('className');

	if (tinyMCE.cleanup_verify_css_classes && tinyMCE.cleanup_on_save) {
		var csses = tinyMCE.getCSSClasses();
		nonDefinedCSS = true;
		for (var c=0; c<csses.length; c++) {
			if (csses[c] == className) {
				nonDefinedCSS = false;
				break;
			}
		}

		if (nonDefinedCSS && className.indexOf('mce_') != 0) {
			node.removeAttribute('className');
			node.removeAttribute('class');
			return false;
		}
	}

	return true;
}

function TinyMCE_cleanupNode(node) {
	var output = "";

	switch (node.nodeType) {
		case 1: // Element
			var elementData = tinyMCE._cleanupElementName(node.nodeName, node);
			var elementName = elementData ? elementData.element_name : null;
			var elementValidAttribs = elementData ? elementData.valid_attribs : null;
			var elementAttribs = "";

			// Checking DOM tree for MSIE weirdness!!
			if (tinyMCE.isMSIE && tinyMCE.settings['fix_content_duplication']) {
				var lookup = tinyMCE.cleanup_elementLookupTable;

				for (var i=0; i<lookup.length; i++) {
					// Found element reference else were, hmm?
					if (lookup[i] == node)
						return output;
				}

				// Add element to lookup table
				lookup[lookup.length] = node;
			}

			// Element not valid (only render children)
			if (!elementName) {
				if (node.hasChildNodes()) {
					for (var i=0; i<node.childNodes.length; i++)
						output += this.cleanupNode(node.childNodes[i]);
				}

				return output;
			}

			// Has mso/microsuck crap or empty attrib
			if (node.style && (node.style.cssText.indexOf('mso-') != -1 && tinyMCE.settings['auto_cleanup_word']) || node.style.cssText == "") {
				node.style.cssText = "";
				node.removeAttribute("style");
			}

			// Handle inline styles
			if (tinyMCE.cleanup_inline_styles)
				tinyMCE._fixInlineStyles(node);

			// Set attrib data
			if (elementValidAttribs) {
				for (var a=1; a<elementValidAttribs.length; a++) {
					var attribName, attribDefaultValue, attribForceValue, attribValue;

					attribName = elementValidAttribs[a][0];
					attribDefaultValue = elementValidAttribs[a][1];
					attribForceValue = elementValidAttribs[a][2];

					if (attribDefaultValue || attribForceValue) {
						var attribValue = node.getAttribute(attribName);
						if (node.getAttribute(attribName) == null || node.getAttribute(attribName) == "")
							attribValue = attribDefaultValue;

						attribValue = attribForceValue ? attribForceValue : attribValue;

						// Is to generate id
						if (attribValue == "{$uid}")
							attribValue = "uid_" + (tinyMCE.cleanup_idCount++);

						node.setAttribute(attribName, attribValue);
						//alert(attribName + "=" + attribValue);
					}
				}
			}

			// Remove non needed span elements
			if (elementName == "span" && tinyMCE.cleanup_trim_span_elements) {
				var re = new RegExp('^[ \t]+', 'g');
				var onlyWhiteSpace = true;
				for (var a=0; a<node.childNodes.length; a++) {
					var tmpNode = node.childNodes[a];
					if ((tmpNode.nodeType == 3 && !tmpNode.nodeValue.match(re)) || tmpNode.nodeName.toLowerCase() != "span") {
						onlyWhiteSpace = false;
						break;
					}
				}

				// Count attributes
				tinyMCE._verifyClass(node);
				var numAttribs = 0;
				for (var i=0; i<node.attributes.length; i++) {
					if (node.attributes[i].specified)
						numAttribs++;
				}

				// Is not a valid span, remove it
				if (onlyWhiteSpace || numAttribs == 0) {
					if (node.hasChildNodes()) {
						for (var i=0; i<node.childNodes.length; i++)
							output += this.cleanupNode(node.childNodes[i]);
					}

					return output;
				}
			}

			// Add some visual aids
			if (elementName == "table" || elementName == "td") {
				// Handle visual aid
				if (tinyMCE.cleanup_visual_table_style != "") {
					// Find parent table
					var tableElement = node;
					if (elementName == "td")
						tableElement = tinyMCE.getParentElement(tableElement, "table");

					if (tableElement && tableElement.getAttribute("border") == 0) {
						if (tinyMCE.cleanup_visual_aid)
							elementAttribs += " style=" + '"' + tinyMCE.cleanup_visual_table_style + '"';
					}
				}
			}

			// Remove empty tables
			if (elementName == "table" && !node.hasChildNodes())
				return "";

			// Handle element attributes
			if (node.attributes.length > 0) {
				for (var i=0; i<node.attributes.length; i++) {
					if (node.attributes[i].specified) {
						var attrib = tinyMCE._cleanupAttribute(elementValidAttribs, elementName, node.attributes[i], node);
						if (attrib)
							elementAttribs += " " + attrib.name + "=" + '"' + attrib.value + '"';
					}
				}

				//alert(elementAttribs);
			}

			// MSIE form element issue
			if (tinyMCE.isMSIE && elementName == "input") {
				if (node.type)
					elementAttribs += " type=" + '"' + node.type + '"';

				if (node.value)
					elementAttribs += " value=" + '"' + node.value + '"';
			}

			// Add nbsp to some elements
			if ((elementName == "p" || elementName == "td") && (node.innerHTML == "" || node.innerHTML == "&nbsp;"))
				return "<" + elementName + elementAttribs + ">&nbsp;</" + elementName + ">";

			// Is MSIE script element
			if (tinyMCE.isMSIE && elementName == "script")
				return "<" + elementName + elementAttribs + ">" + node.text + "</" + elementName + ">";

			// Clean up children
			if (node.hasChildNodes()) {
				// Force BR
				if (elementName == "p" && tinyMCE.cleanup_force_br_newlines)
					output += "<div" + elementAttribs + ">";
				else
					output += "<" + elementName + elementAttribs + ">";

				for (var i=0; i<node.childNodes.length; i++)
					output += this.cleanupNode(node.childNodes[i]);

				// Force BR
				if (elementName == "p" && tinyMCE.cleanup_force_br_newlines)
					output += "</div><br />";
				else
					output += "</" + elementName + ">";
			} else {
				// Allways leave anchor elements open
				if (elementName == "a")
					output += "<" + elementName + elementAttribs + "></" + elementName + ">";
				else {
					// No children
					output += "<" + elementName + elementAttribs + " />";
				}
			}

			return output;

		case 3: // Text
			// Do not convert script elements
			if (node.parentNode.nodeName.toLowerCase() == "script")
				return node.nodeValue;

			return this.convertStringToXML(node.nodeValue);

		case 8: // Comment
			return "<!--" + node.nodeValue + "-->";

		default: // Unknown
			return "[UNKNOWN NODETYPE " + node.nodeType + "]";
	}
}

function TinyMCE_convertStringToXML(html_data) {
    var output = "";

	for (var i=0; i<html_data.length; i++) {
		var chr = html_data.charCodeAt(i);

		// Check if a name exists in lookup table
		if (typeof(tinyMCE.cleanup_entities["c" + chr]) != 'undefined' && tinyMCE.cleanup_entities["c" + chr] != '')
			output += '&' + tinyMCE.cleanup_entities["c" + chr] + ';';
		else
			output += '' + String.fromCharCode(chr);
    }

    return output;
}

function TinyMCE__initCleanup() {
	function getElementName(chunk) {
		var pos;

		if ((pos = chunk.indexOf('/')) != -1)
			chunk = chunk.substring(0, pos);

		if ((pos = chunk.indexOf('[')) != -1)
			chunk = chunk.substring(0, pos);

		return chunk;
	}

	// Parse valid elements and attributes
	var validElements = tinyMCE.settings["valid_elements"];
	validElements = validElements.split(',');

	// Handle extended valid elements
	var extendedValidElements = tinyMCE.settings["extended_valid_elements"];
	extendedValidElements = extendedValidElements.split(',');
	for (var i=0; i<extendedValidElements.length; i++) {
		var elementName = getElementName(extendedValidElements[i]);
		var skipAdd = false;

		// Check if it's defined before, if so override that one
		for (var x=0; x<validElements.length; x++) {
			if (getElementName(validElements[x]) == elementName) {
				validElements[x] = extendedValidElements[i];
				skipAdd = true;
				break;
			}
		}

		if (!skipAdd)
			validElements[validElements.length] = extendedValidElements[i];
	}

	for (var i=0; i<validElements.length; i++) {
		var item = validElements[i];
		item = item.replace('[','|');
		item = item.replace(']','');

		// Split and convert
		var attribs = item.split('|');
		for (var x=0; x<attribs.length; x++)
			attribs[x] = attribs[x].toLowerCase();

		// Handle change elements
		attribs[0] = attribs[0].split('/');

		// Handle default attribute values
		for (var x=1; x<attribs.length; x++) {
			var attribName = attribs[x];
			var attribDefault = null;
			var attribForce = null;
			var attribMustBe = null;

			// Default value
			if ((pos = attribName.indexOf('=')) != -1) {
				attribDefault = attribName.substring(pos+1);
				attribName = attribName.substring(0, pos);
			}

			// Force check
			if ((pos = attribName.indexOf(':')) != -1) {
				attribForce = attribName.substring(pos+1);
				attribName = attribName.substring(0, pos);
			}

			// Force check
			if ((pos = attribName.indexOf('<')) != -1) {
				attribMustBe = attribName.substring(pos+1).split('?');
				attribName = attribName.substring(0, pos);
			}

			attribs[x] = new Array(attribName, attribDefault, attribForce, attribMustBe);
		}

		validElements[i] = attribs;
	}

	var invalidElements = tinyMCE.settings['invalid_elements'].split(',');
	for (var i=0; i<invalidElements.length; i++)
		invalidElements[i] = invalidElements[i].toLowerCase();

	// Set these for performance
	tinyMCE.cleanup_validElements = validElements;
	tinyMCE.cleanup_invalidElements = invalidElements;
	tinyMCE.cleanup_verify_html = tinyMCE.settings['verify_html'];
	tinyMCE.cleanup_force_br_newlines = tinyMCE.settings['force_br_newlines'];
	tinyMCE.cleanup_urlconvertor_callback = tinyMCE.settings['urlconvertor_callback'];
	tinyMCE.cleanup_verify_css_classes = tinyMCE.settings['verify_css_classes'];
	tinyMCE.cleanup_visual_table_style = tinyMCE.settings['visual_table_style'];
	tinyMCE.cleanup_apply_source_formatting = tinyMCE.settings['apply_source_formatting'];
	tinyMCE.cleanup_urlconvertor_callback = tinyMCE.settings['urlconvertor_callback'];
	tinyMCE.cleanup_trim_span_elements = tinyMCE.settings['trim_span_elements'];
	tinyMCE.cleanup_inline_styles = tinyMCE.settings['inline_styles'];

	// Setup entities
	tinyMCE.cleanup_entities = new Array();
	var entities = tinyMCE.getParam('entities', '', true, ',');
	for (var i=0; i<entities.length; i+=2)
		tinyMCE.cleanup_entities['c' + entities[i]] = entities[i+1];
}

function TinyMCE__cleanupHTML(doc, config, element, visual, on_save) {
	// Set these for performance
	tinyMCE.cleanup_visual_aid = visual;
	tinyMCE.cleanup_on_save = on_save;
	tinyMCE.cleanup_idCount = 0;
	tinyMCE.cleanup_elementLookupTable = new Array();

	var startTime = new Date().getTime();

	tinyMCE._convertOnClick(element);

	// Cleanup madness that breaks the editor in MSIE
	if (tinyMCE.isMSIE) {
		element.innerHTML = tinyMCE.regexpReplace(element.innerHTML, '<p>[ \n\r]*<hr id=null>[ \n\r]*</p>', '<hr />', 'gi');
		element.innerHTML = tinyMCE.regexpReplace(element.innerHTML, '<!([^-(DOCTYPE)]* )|<!/[^-]*>', '', 'gi');
	}

	var html = this.cleanupNode(element);

	if (tinyMCE.settings['debug'])
		alert("Cleanup process executed in: " + (new Date().getTime()-startTime) + " ms.");

	// Remove pesky HR paragraphs
	html = tinyMCE.regexpReplace(html, '<p><hr /></p>', '<hr />');
	html = tinyMCE.regexpReplace(html, '<p>&nbsp;</p><hr /><p>&nbsp;</p>', '<hr />');

	// Remove some mozilla crap
	if (!tinyMCE.isMSIE)
		html = html.replace(new RegExp('<o:p _moz-userdefined="" />', 'g'), "");

	if (tinyMCE.settings['apply_source_formatting']) {
		html = html.replace(new RegExp('<(p|div)([^>]*)>', 'g'), "\n<$1$2>\n");
		html = html.replace(new RegExp('<\/(p|div)([^>]*)>', 'g'), "\n</$1$2>\n");
		html = html.replace(new RegExp('<br />', 'g'), "<br />\n");
	}

	if (tinyMCE.settings['force_br_newlines']) {
		var re = new RegExp('<p>&nbsp;</p>', 'g');
		html = html.replace(re, "<br />");
	}

	if (tinyMCE.settings['force_p_newlines']) {
		// Remove weridness!
		var re = new RegExp('&lt;&gt;', 'g');
		html = html.replace(re, "");
	}

	// Call custom cleanup code
	html = tinyMCE._customCleanup(on_save ? "get_from_editor" : "insert_to_editor", html);

	// Emtpy node, return empty
	if (html == "<br />" || html == "<br />\n" || html == "<p>&nbsp;</p>")
		html = "";

	if (tinyMCE.settings["preformatted"])
		return "<pre>" + html + "</pre>";

	return html;
}

function TinyMCE_insertLink(href, target, title, onclick) {
	function setAttrib(element, name, value) {
		if (value != null && value != "")
			element.setAttribute(name, value);
		else
			element.removeAttribute(name);
	}

	this.execCommand("mceAddUndoLevel");

	if (this.selectedInstance && this.selectedElement && this.selectedElement.nodeName.toLowerCase() == "img") {
		var doc = this.selectedInstance.getDoc();

		var linkElement = doc.createElement("a");

		href = eval(tinyMCE.settings['urlconvertor_callback'] + "(href, linkElement);");
		setAttrib(linkElement, 'href', href);
		setAttrib(linkElement, 'target', target);
		setAttrib(linkElement, 'title', title);
        setAttrib(linkElement, 'mce_onclick', onclick);

		linkElement.appendChild(this.selectedElement.cloneNode(true));

		this.selectedElement.parentNode.replaceChild(linkElement, this.selectedElement);

		return;
	}

	if (!this.linkElement && this.selectedInstance) {
		this.selectedInstance.contentDocument.execCommand("createlink", false, "#mce_temp_url#");
		tinyMCE.linkElement = this.getElementByAttributeValue(this.selectedInstance.contentDocument.body, "a", "href", "#mce_temp_url#");

		var elementArray = this.getElementsByAttributeValue(this.selectedInstance.contentDocument.body, "a", "href", "#mce_temp_url#");

		for (var i=0; i<elementArray.length; i++) {
			href = eval(tinyMCE.settings['urlconvertor_callback'] + "(href, elementArray[i]);");
			setAttrib(elementArray[i], 'href', href);
			setAttrib(elementArray[i], 'mce_real_href', href);
			setAttrib(elementArray[i], 'target', target);
			setAttrib(elementArray[i], 'title', title);
            setAttrib(elementArray[i], 'mce_onclick', onclick);
		}

		tinyMCE.linkElement = elementArray[0];
	}

	if (this.linkElement) {
		href = eval(tinyMCE.settings['urlconvertor_callback'] + "(href, this.linkElement);");
		setAttrib(this.linkElement, 'href', href);
		setAttrib(this.linkElement, 'mce_real_href', href);
		setAttrib(this.linkElement, 'target', target);
		setAttrib(this.linkElement, 'title', title);
        setAttrib(this.linkElement, 'mce_onclick', onclick);
	}
}

function TinyMCE_insertImage(src, alt, border, hspace, vspace, width, height, align, title, onmouseover, onmouseout) {
	this.execCommand("mceAddUndoLevel");

	function setAttrib(element, name, value, no_fix_value) {
		if (!no_fix_value && value != null) {
			var re = new RegExp('[^0-9%]', 'g');
			value = value.replace(re, '');
		}

		if (value != null && value != "")
			element.setAttribute(name, value);
		else
			element.removeAttribute(name);
	}

	if (!this.imgElement && this.selectedInstance) {
		this.selectedInstance.contentDocument.execCommand("insertimage", false, "#mce_temp_url#");
		tinyMCE.imgElement = this.getElementByAttributeValue(this.selectedInstance.contentDocument.body, "img", "src", "#mce_temp_url#");
	}

	if (this.imgElement) {
		src = eval(tinyMCE.settings['urlconvertor_callback'] + "(src, tinyMCE.imgElement);");

		// Use alt as title if it's undefined
		if (typeof(title) == "undefined")
			title = alt;

		tinyMCE.setAttrib(this.imgElement, 'src', src, true);
		tinyMCE.setAttrib(this.imgElement, 'mce_real_src', src, true);
		tinyMCE.setAttrib(this.imgElement, 'alt', alt, true);
		tinyMCE.setAttrib(this.imgElement, 'title', title, true);
		tinyMCE.setAttrib(this.imgElement, 'align', align, true);
		tinyMCE.setAttrib(this.imgElement, 'border', border);
		tinyMCE.setAttrib(this.imgElement, 'hspace', hspace);
		tinyMCE.setAttrib(this.imgElement, 'vspace', vspace);
		tinyMCE.setAttrib(this.imgElement, 'width', width);
		tinyMCE.setAttrib(this.imgElement, 'height', height);
		tinyMCE.setAttrib(this.imgElement, 'border', border);
        tinyMCE.setAttrib(this.imgElement, 'onmouseover', onmouseover, true);
        tinyMCE.setAttrib(this.imgElement, 'onmouseout', onmouseout, true);

		// Fix for bug #989846 - Image resize bug
		if (width && width != "")
			this.imgElement.style.pixelWidth = width;

		if (height && height != "")
			this.imgElement.style.pixelHeight = height;
	}
}

function TinyMCE_getElementByAttributeValue(node, element_name, attrib, value) {
	var elements = this.getElementsByAttributeValue(node, element_name, attrib, value);
	if (elements.length == 0)
		return null;

	return elements[0];
}

function TinyMCE_getElementsByAttributeValue(node, element_name, attrib, value) {
	var elements = new Array();

	if (node && node.nodeName.toLowerCase() == element_name) {
		if (node.getAttribute(attrib) && node.getAttribute(attrib).indexOf(value) != -1)
			elements[elements.length] = node;
	}

	if (node.hasChildNodes) {
		for (var x=0; x<node.childNodes.length; x++) {
			var childElements = this.getElementsByAttributeValue(node.childNodes[x], element_name, attrib, value);
			for (var i=0; i<childElements.length; i++)
				elements[elements.length] = childElements[i];
		}
	}

	return elements;
}

function TinyMCE_isBlockElement(node) {
	return node != null && node.nodeType == 1 && this.blockRegExp.test(node.nodeName);
}

function TinyMCE_getParentBlockElement(node) {
	// Search up the tree for block element
	while (node) {
		if (this.blockRegExp.test(node.nodeName))
			return node;

		node = node.parentNode;
	}

	return null;
}

function TinyMCE_getNodeTree(node, node_array, type, node_name) {
	if (typeof(type) == "undefined" || node.nodeType == type && (typeof(node_name) == "undefined" || node.nodeName.toLowerCase() == node_name.toLowerCase()))
		node_array[node_array.length] = node;

	if (node.hasChildNodes()) {
		for (var i=0; i<node.childNodes.length; i++)
			tinyMCE.getNodeTree(node.childNodes[i], node_array, type, node_name);
	}

	return node_array;
}

function TinyMCE_getParentElement(node, names, attrib_name, attrib_value) {
	if (typeof(names) == "undefined") {
		if (node.nodeType == 1)
			return node;

		// Find parent node that is a element
		while ((node = node.parentNode) != null && node.nodeType != 1) ;

		return node;
	}

	var namesAr = names.split(',');

	if (node == null)
		return null;

	do {
		for (var i=0; i<namesAr.length; i++) {
			if (node.nodeName.toLowerCase() == namesAr[i].toLowerCase() || names == "*") {
				if (typeof(attrib_name) == "undefined")
					return node;
				else if (node.getAttribute(attrib_name)) {
					if (typeof(attrib_value) == "undefined") {
						if (node.getAttribute(attrib_name) != "")
							return node;
					} else if (node.getAttribute(attrib_name) == attrib_value)
						return node;
				}
			}
		}
	} while (node = node.parentNode);

	return null;
}

function TinyMCE_convertURL(url, node, on_save) {
	var fileProto = (document.location.protocol == "file:");

	// Something is wrong, remove weirdness
	url = tinyMCE.regexpReplace(url, '(http|https):///', '/');

	// Mailto link or anchor (Pass through)
	if (url.indexOf('mailto:') != -1 || url.indexOf('javascript:') != -1 || tinyMCE.regexpReplace(url,'[ \t\r\n\+]|%20','').charAt(0) == "#")
		return url;

	// Fix relative/Mozilla
	if (!tinyMCE.isMSIE && !on_save && url.indexOf("://") == -1 && url.charAt(0) != '/')
		return tinyMCE.settings['base_href'] + url;

	// Handle absolute url anchors
	if (!tinyMCE.settings['relative_urls']) {
		var urlParts = tinyMCE.parseURL(url);
		var baseUrlParts = tinyMCE.parseURL(tinyMCE.settings['base_href']);

		// If anchor and path is the same page
		if (urlParts['anchor'] && urlParts['path'] == baseUrlParts['path'])
			return "#" + urlParts['anchor'];
	}

	// Convert to relative urls
	if (on_save && tinyMCE.settings['relative_urls']) {
		var urlParts = tinyMCE.parseURL(url);

		// If not absolute url, do nothing (Mozilla)
		if (!urlParts['protocol'] && !tinyMCE.isMSIE) {
			var urlPrefix = "http://";
			urlPrefix += document.location.hostname;
			if (document.location.port != "")
				urlPrefix += ":" + document.location.port;

			url = urlPrefix + url;
			urlParts = tinyMCE.parseURL(url);
		}

		var tmpUrlParts = tinyMCE.parseURL(tinyMCE.settings['document_base_url']);

		// Link is within this site
		if (urlParts['host'] == tmpUrlParts['host'] && (!urlParts['port'] || urlParts['port'] == tmpUrlParts['port']))
			return tinyMCE.convertAbsoluteURLToRelativeURL(tinyMCE.settings['document_base_url'], url);
	}

	// Remove current domain
	if (!fileProto && tinyMCE.settings['remove_script_host']) {
		var start = document.location.protocol + "//" + document.location.hostname + "/";

		if (url.indexOf(start) == 0)
			url = url.substring(start.length-1);

		// Add first slash if missing on a absolute URL
		if (!tinyMCE.settings['relative_urls'] && url.indexOf('://') == -1 && url.charAt(0) != '/')
			url = '/' + url;
	}

	return url;
}

/**
 * Parses a URL in to its diffrent components.
 */
function TinyMCE_parseURL(url_str) {
	var urlParts = new Array();

	if (url_str) {
		var pos, lastPos;

		// Parse protocol part
		pos = url_str.indexOf('://');
		if (pos != -1) {
			urlParts['protocol'] = url_str.substring(0, pos);
			lastPos = pos + 3;
		}

		// Find port or path start
		for (var i=lastPos; i<url_str.length; i++) {
			var chr = url_str.charAt(i);

			if (chr == ':')
				break;

			if (chr == '/')
				break;
		}
		pos = i;

		// Get host
		urlParts['host'] = url_str.substring(lastPos, pos);

		// Get port
		lastPos = pos;
		if (url_str.charAt(pos) == ':') {
			pos = url_str.indexOf('/', lastPos);
			urlParts['port'] = url_str.substring(lastPos+1, pos);
		}

		// Get path
		lastPos = pos;
		pos = url_str.indexOf('?', lastPos);

		if (pos == -1)
			pos = url_str.indexOf('#', lastPos);

		if (pos == -1)
			pos = url_str.length;

		urlParts['path'] = url_str.substring(lastPos, pos);

		// Get query
		lastPos = pos;
		if (url_str.charAt(pos) == '?') {
			pos = url_str.indexOf('#');
			pos = (pos == -1) ? url_str.length : pos;
			urlParts['query'] = url_str.substring(lastPos+1, pos);
		}

		// Get anchor
		lastPos = pos;
		if (url_str.charAt(pos) == '#') {
			pos = url_str.length;
			urlParts['anchor'] = url_str.substring(lastPos+1, pos);
		}
	}

	return urlParts;
}

/**
 * Converts an absolute path to relative path.
 */
function TinyMCE_convertAbsoluteURLToRelativeURL(base_url, url_to_relative) {
	var strTok1;
	var strTok2;
	var breakPoint = 0;
	var outputString = "";

	// Crop away last path part
	base_url = base_url.substring(0, base_url.lastIndexOf('/'));
	strTok1 = base_url.split('/');
	strTok2 = url_to_relative.split('/');

	if (strTok1.length >= strTok2.length) {
		for (var i=0; i<strTok1.length; i++) {
			if (i >= strTok2.length || strTok1[i] != strTok2[i]) {
				breakPoint = i + 1;
				break;
			}
		}
	}

	if (strTok1.length < strTok2.length) {
		for (var i=0; i<strTok2.length; i++) {
			if (i >= strTok1.length || strTok1[i] != strTok2[i]) {
				breakPoint = i + 1;
				break;
			}
		}
	}

	if (breakPoint == 1)
		return url_to_relative;

	for (var i=0; i<(strTok1.length-(breakPoint-1)); i++)
		outputString += "../";

	for (var i=breakPoint-1; i<strTok2.length; i++) {
		if (i != (breakPoint-1))
			outputString += "/" + strTok2[i];
		else
			outputString += strTok2[i];
	}

	return outputString;
}

function TinyMCE_convertRelativeToAbsoluteURL(base_url, relative_url) {
	var baseURL = TinyMCE_parseURL(base_url);
	var relURL = TinyMCE_parseURL(relative_url);

	if (relative_url == "" || relative_url.indexOf('://') != -1 || relative_url.indexOf('mailto:') != -1 || relative_url.indexOf('javascript:') != -1 || tinyMCE.regexpReplace(relative_url,'[ \t\r\n\+]|%20','').charAt(0) == "#")
		return relative_url;

	// Split parts
	baseURLParts = baseURL['path'].split('/');
	relURLParts = relURL['path'].split('/');

	// Remove empty chunks
	var newBaseURLParts = new Array();
	for (var i=baseURLParts.length-1; i>=0; i--) {
		if (baseURLParts[i].length == 0)
			continue;

		newBaseURLParts[newBaseURLParts.length] = baseURLParts[i];
	}
	baseURLParts = newBaseURLParts.reverse();

	// Merge relURLParts chunks
	var newRelURLParts = new Array();
	var numBack = 0;
	for (var i=relURLParts.length-1; i>=0; i--) {
		if (relURLParts[i].length == 0 || relURLParts[i] == ".")
			continue;

		if (relURLParts[i] == '..') {
			numBack++;
			continue;
		}

		if (numBack > 0) {
			numBack--;
			continue;
		}

		newRelURLParts[newRelURLParts.length] = relURLParts[i];
	}

	relURLParts = newRelURLParts.reverse();

	// Remove end from absolute path
	var len = baseURLParts.length-numBack;
	var absPath = (len <= 0 ? "" : "/") + baseURLParts.slice(0, len).join('/') + "/" + relURLParts.join('/');
	var start = "", end = "";

	// Build start part
	if (baseURL['protocol'])
		start += baseURL['protocol'] + "://";

	if (baseURL['host'])
		start += baseURL['host'];

	if (baseURL['port'])
		start += ":" + baseURL['port'];

	// Build end part
	if (relURL['query'])
		end += "?" + relURL['query'];

	if (relURL['anchor'])
		end += "#" + relURL['anchor'];

	return start + absPath + end;
}

function TinyMCE_getParam(name, default_value, strip_whitespace, split_chr) {
	var value = (typeof(this.settings[name]) == "undefined") ? default_value : this.settings[name];

	// Fix bool values
	if (value == "true" || value == "false")
		return (value == "true");

	if (strip_whitespace)
		value = tinyMCE.regexpReplace(value, "[ \t\r\n]", "");

	if (typeof(split_chr) != "undefined" && split_chr != null) {
		value = value.split(split_chr);
		var outArray = new Array();

		for (var i=0; i<value.length; i++) {
			if (value[i] && value[i] != "")
				outArray[outArray.length] = value[i];
		}

		value = outArray;
	}

	return value;
}

function TinyMCE_getLang(name, default_value, parse_entities) {
	var value = (typeof(tinyMCELang[name]) == "undefined") ? default_value : tinyMCELang[name];

	if (parse_entities) {
		var el = document.createElement("div");
		el.innerHTML = value;
		value = el.innerHTML;
	}

	return value;
}

function TinyMCE_replaceVar(replace_haystack, replace_var, replace_str) {
	var re = new RegExp('{\\\$' + replace_var + '}', 'g');
	return replace_haystack.replace(re, replace_str);
}

function TinyMCE_replaceVars(replace_haystack, replace_vars) {
	for (var key in replace_vars) {
		var value = replace_vars[key];
		replace_haystack = tinyMCE.replaceVar(replace_haystack, key, value);
	}

	return replace_haystack;
}

function TinyMCE_triggerNodeChange(focus, setup_content) {
	if (tinyMCE.settings['handleNodeChangeCallback']) {
		if (tinyMCE.selectedInstance) {
			var inst = tinyMCE.selectedInstance;
			var editorId = inst.editorId;
			var elm = (typeof(setup_content) != "undefined" && setup_content) ? tinyMCE.selectedElement : inst.getFocusElement();
			var undoIndex = -1;
			var undoLevels = -1;
			var anySelection = false;
			var selectedText = "";

			if (tinyMCE.settings["auto_resize"]) {
				var doc = inst.getDoc();

				inst.iframeElement.style.width = doc.body.offsetWidth + "px";
				inst.iframeElement.style.height = doc.body.offsetHeight + "px";
			}

			if (tinyMCE.isMSIE) {
				var doc = inst.getDoc();
				var rng = doc.selection.createRange();
				selectedText = rng.text;
			} else {
				var sel = inst.contentWindow.getSelection();
				if (sel && sel.toString)
					selectedText = sel.toString();
				else
					selectedText = '';
			}

			if (tinyMCE.selectedElement)
				anySelection = (tinyMCE.selectedElement.nodeName.toLowerCase() == "img") || (selectedText && selectedText.length > 0);

			if (tinyMCE.settings['custom_undo_redo']) {
				undoIndex = inst.undoIndex;
				undoLevels = inst.undoLevels.length;
			}

			inst.executeCallback('handleNodeChangeCallback', '_handleNodeChange', 0, editorId, elm, undoIndex, undoLevels, inst.visualAid, anySelection);
		}
	}

	if (this.selectedInstance && (typeof(focus) == "undefined" || focus))
		this.selectedInstance.contentWindow.focus();
}

function TinyMCE__customCleanup(type, content) {
	// Call custom cleanup
	var customCleanup = tinyMCE.settings['cleanup_callback'];
	if (customCleanup != "" && eval("typeof(" + customCleanup + ")") != "undefined")
		content = eval(customCleanup + "(type, content);");

	// Trigger plugin cleanups
	var plugins = tinyMCE.getParam('plugins', '', true, ',');
	for (var i=0; i<plugins.length; i++) {
		if (eval("typeof(TinyMCE_" + plugins[i] +  "_cleanup)") != "undefined")
			content = eval("TinyMCE_" + plugins[i] +  "_cleanup(type, content);");
	}

	return content;
}

function TinyMCE_getContent(editor_id) {
	if (typeof(editor_id) != "undefined")
		tinyMCE.selectedInstance = tinyMCE.getInstanceById(editor_id);

	if (tinyMCE.selectedInstance)
		return tinyMCE._cleanupHTML(this.selectedInstance.getDoc(), tinyMCE.settings, this.selectedInstance.getBody(), false, true);

	return null;
}

function TinyMCE_setContent(html_content) {
	if (tinyMCE.selectedInstance) {
		var doc = this.selectedInstance.getDoc();

		// Call custom cleanup code
		html_content = tinyMCE._customCleanup("insert_to_editor", html_content);

		tinyMCE._setHTML(doc, html_content);
		doc.body.innerHTML = tinyMCE._cleanupHTML(doc, tinyMCE.settings, doc.body);
		tinyMCE.handleVisualAid(doc.body, true, tinyMCE.selectedInstance.visualAid);
	}
}

function TinyMCE_importThemeLanguagePack(name) {
	if (typeof(name) == "undefined")
		name = tinyMCE.settings['theme'];

	document.write('<script language="javascript" type="text/javascript" src="' + tinyMCE.baseURL + '/themes/' + name + '/langs/' + tinyMCE.settings['language'] +  '.js"></script>');	
}

function TinyMCE_importPluginLanguagePack(name, valid_languages) {
	var lang = "en";

	valid_languages = valid_languages.split(',');
	for (var i=0; i<valid_languages.length; i++) {
		if (tinyMCE.settings['language'] == valid_languages[i])
			lang = tinyMCE.settings['language'];
	}

	document.write('<script language="javascript" type="text/javascript" src="' + tinyMCE.baseURL + '/plugins/' + name + '/langs/' + lang +  '.js"></script>');	
}

/**
 * Adds themeurl, settings and lang to HTML code.
 */
function TinyMCE_applyTemplate(html, args) {
	html = tinyMCE.replaceVar(html, "themeurl", tinyMCE.themeURL);

	if (typeof(args) != "undefined")
		html = tinyMCE.replaceVars(html, args);

	html = tinyMCE.replaceVars(html, tinyMCE.settings);
	html = tinyMCE.replaceVars(html, tinyMCELang);

	return html;
}

function TinyMCE_openWindow(template, args) {
	var html, width, height, x, y, resizable, scrollbars, url;

	args['mce_template_file'] = template['file'];
	tinyMCE.windowArgs = args;

	html = template['html'];
	if (!(width = template['width']))
		width = 320;

	if (!(height = template['height']))
		height = 200;

	// Add to height in M$ due to SP2 WHY DON'T YOU GUYS IMPLEMENT innerWidth of windows!!
	if (tinyMCE.isMSIE)
		height += 30;

	x = parseInt(screen.width / 2.0) - (width / 2.0);
	y = parseInt(screen.height / 2.0) - (height / 2.0);

	resizable = (args && args['resizable']) ? args['resizable'] : "no";
	scrollbars = (args && args['scrollbars']) ? args['scrollbars'] : "no";
	url = tinyMCE.baseURL + "/themes/" + tinyMCE.getParam("theme") + "/" + template['file'];

	// Replace all args as variables in URL
	for (var name in args)
		url = tinyMCE.replaceVar(url, name, escape(args[name]));

	if (html) {
		html = tinyMCE.replaceVar(html, "css", this.settings['popups_css']);
		html = tinyMCE.applyTemplate(html, args);

		var win = window.open("", "mcePopup", "top=" + y + ",left=" + x + ",scrollbars=" + scrollbars + ",dialog=yes,minimizable=" + resizable + ",modal=yes,width=" + width + ",height=" + height + ",resizable=" + resizable);
		win.document.write(html);
		win.document.close();
		win.resizeTo(width, height);
		win.focus();
	} else {
		if (tinyMCE.isMSIE && resizable != 'yes') {
            var features = "resizable:" + resizable 
                + ";scroll:"
                + scrollbars + ";status:yes;center:yes;help:no;dialogWidth:"
                + width + "px;dialogHeight:" + height + "px;";

			window.showModalDialog(url, window, features);
		} else {
			var win = window.open(url, "mcePopup", "top=" + y + ",left=" + x + ",scrollbars=" + scrollbars + ",dialog=yes,minimizable=" + resizable + ",modal=yes,width=" + width + ",height=" + height + ",resizable=" + resizable);
			eval('try { win.resizeTo(width, height); } catch(e) { }');
			win.focus();
		}
	}
}

function TinyMCE_handleVisualAid(element, deep, state) {
	function getAttrib(elm, name) {
		return elm.getAttribute(name) ? elm.getAttribute(name) : "";
	}

	var tableElement = null;

	switch (element.nodeName.toLowerCase()) {
		case "table":
			var cssText = element.getAttribute("border") == 0 ? tinyMCE.settings['visual_table_style'] : "";

			var oldW = element.style.width;
			var oldH = element.style.height;
			element.style.cssText = state ? cssText : "";
			element.style.width = oldW;
			element.style.height = oldH;

			for (var y=0; y<element.rows.length; y++) {
				for (var x=0; x<element.rows[y].cells.length; x++)
					element.rows[y].cells[x].style.cssText = state ? cssText : "";
			}

			break;

/*		case "a":
			var name = element.getAttribute("name");
			if (name && name != "" && state) {
				//element.innerHTML += '<img mceVisualAid="true" src="' + (tinyMCE.themeURL + "/images/anchor.gif") + '" />';
				return;
			}

			break;*/
	}

	if (deep && element.hasChildNodes()) {
		for (var i=0; i<element.childNodes.length; i++)
			tinyMCE.handleVisualAid(element.childNodes[i], deep, state);
	}
}

function TinyMCE_getAttrib(elm, name, default_value) {
	var v = elm.getAttribute(name);
	return (v && v != "") ? v : default_value;
}

function TinyMCE_setAttrib(element, name, value, no_fix_value) {
	if (typeof(value) == "number")
		value = "" + value;

	if (!no_fix_value && value != null && value != -1) {
		var re = new RegExp('[^0-9%]', 'g');
		value = value.replace(re, '');
	}

	if (value != null && value != "" && value != -1)
		element.setAttribute(name, value);
	else
		element.removeAttribute(name);
}

function TinyMCE__setHTML(doc, html_content) {
	// Weird MSIE bug, <p><hr /></p> breaks runtime?
	if (tinyMCE.isMSIE) {
		var re = new RegExp('<p><hr /></p>', 'g');
		html_content = html_content.replace(re, "<hr>");
	}

	doc.body.innerHTML = html_content;

	// Content duplication bug fix
	if (tinyMCE.isMSIE && tinyMCE.settings['fix_content_duplication']) {
		// Remove P elements in P elements
		var paras = doc.getElementsByTagName("P");
		for (var i=0; i<paras.length; i++) {
			var node = paras[i];
			while ((node = node.parentNode) != null) {
				if (node.nodeName.toLowerCase() == "p")
					node.outerHTML = node.innerHTML;
			}
		}

		// Content duplication bug fix (Seems to be word crap)
		var html = doc.body.createTextRange().htmlText;

		if (html.indexOf('="mso') != -1) {
			for (var i=0; i<doc.body.all.length; i++) {
				var el = doc.body.all[i];
				el.removeAttribute("className","",0);
				el.removeAttribute("style","",0);
			}

			html = doc.body.createTextRange().htmlText;

			html = tinyMCE.regexpReplace(html, "<o:p><\/o:p>", "<br />");
			html = tinyMCE.regexpReplace(html, "<o:p>&nbsp;<\/o:p>", "");
			html = tinyMCE.regexpReplace(html, "<st1:.*?>", "");
			html = tinyMCE.regexpReplace(html, "<p><\/p>", "");
			html = tinyMCE.regexpReplace(html, "<p><\/p>\r\n<p><\/p>", "");
			html = tinyMCE.regexpReplace(html, "<p>&nbsp;<\/p>", "<br />");
			html = tinyMCE.regexpReplace(html, "<p>\s*(<p>\s*)?", "<p>");
			html = tinyMCE.regexpReplace(html, "<\/p>\s*(<\/p>\s*)?", "</p>");
		}

		// Always set the htmlText output
		doc.body.innerHTML = html;
	}
}

function TinyMCE__getElementById(element_id) {
	var elm = document.getElementById(element_id);
	if (!elm) {
		// Check for element in forms
		for (var j=0; j<document.forms.length; j++) {
			for (var k=0; k<document.forms[j].elements.length; k++) {
				if (document.forms[j].elements[k].name == element_id) {
					elm = document.forms[j].elements[k];
					break;
				}
			}
		}
	}

	return elm;
}

function TinyMCE_getEditorId(form_element) {
	var mceControl = this.getInstanceById(form_element);
	if (!mceControl)
		return null;

	return mceControl.editorId;
}

function TinyMCE_getInstanceById(editor_id) {
	var mceControl = this.instances[editor_id];
	if (!mceControl) {
		for (var instanceName in tinyMCE.instances) {
			var instance = tinyMCE.instances[instanceName];
			if (instance.formTargetElementId == editor_id) {
				mceControl = instance;
				break;
			}
		}
	}

	return mceControl;
}

function TinyMCE_queryInstanceCommandValue(editor_id, command) {
	var mceControl = tinyMCE.getInstanceById(editor_id);
	if (mceControl)
		return mceControl.queryCommandValue(command);

	return false;
}

function TinyMCE_queryInstanceCommandState(editor_id, command) {
	var mceControl = tinyMCE.getInstanceById(editor_id);
	if (mceControl)
		return mceControl.queryCommandState(command);

	return null;
}

function TinyMCE_setWindowArg(name, value) {
	this.windowArgs[name] = value;
}

function TinyMCE_getWindowArg(name, default_value) {
	return (typeof(this.windowArgs[name]) == "undefined") ? default_value : this.windowArgs[name];
}

function TinyMCE_getCSSClasses(editor_id, doc) {
	var output = new Array();

	// Is cached, use that
	if (typeof(tinyMCE.cssClasses) != "undefined")
		return tinyMCE.cssClasses;

	if (typeof(editor_id) == "undefined" && typeof(doc) == "undefined") {
		var instance;

		for (var instanceName in tinyMCE.instances) {
			instance = tinyMCE.instances[instanceName];
			break;
		}

		doc = instance.getDoc();
	}

	if (typeof(doc) == "undefined") {
		var instance = tinyMCE.getInstanceById(editor_id);
		doc = instance.getDoc();
	}

	if (doc) {
		var styles = tinyMCE.isMSIE ? doc.styleSheets : doc.styleSheets;

		if (styles && styles.length > 0) {
			var csses = null;

			// Just ignore any errors
			eval("try {var csses = tinyMCE.isMSIE ? doc.styleSheets(0).rules : doc.styleSheets[0].cssRules;} catch(e) {}");
			if (!csses)
				return new Array();

			for (var i=0; i<csses.length; i++) {
				var selectorText = csses[i].selectorText;

				// Can be multiple rules per selector
				var rules = selectorText.split(',');
				for (var c=0; c<rules.length; c++) {
					// Invalid rule
					if (rules[c].indexOf(' ') != -1 || rules[c].indexOf(':') != -1 || rules[c].indexOf('mce_') == 1)
						continue;

					// Is class rule
					if (rules[c].indexOf('.') != -1) {
						//alert(rules[c].substring(rules[c].indexOf('.')));
						output[output.length] = rules[c].substring(rules[c].indexOf('.')+1);
					}
				}
			}
		}
	}

	// Cache em
	if (output.length > 0)
		tinyMCE.cssClasses = output;

	return output;
}

function TinyMCE_regexpReplace(in_str, reg_exp, replace_str, opts) {
	if (typeof(opts) == "undefined")
		opts = 'g';

	var re = new RegExp(reg_exp, opts);
	return in_str.replace(re, replace_str);
}

function TinyMCE_cleanupEventStr(str) {
	str = "" + str;
	str = str.replace('function anonymous()\n{\n', '');
	str = str.replace('\n}', '');

	return str;
}

function TinyMCE_getAbsPosition(node) {
	var pos = new Object();

	pos.absLeft = pos.absTop = 0;

	var parentNode = node;
	while (parentNode) {
		pos.absLeft += parentNode.offsetLeft;
		pos.absTop += parentNode.offsetTop;

		parentNode = parentNode.offsetParent;
	}

	return pos;
}

function TinyMCE_openFileBrowser(field_name, url, type, win) {
	var cb = tinyMCE.getParam("file_browser_callback");

	this.setWindowArg("window", win);

	// Call to external callback
	if(eval('typeof('+cb+')') == "undefined")
		alert("Callback function: " + cb + " could not be found.");
	else
		eval(cb + "(field_name, url, type, win);");
}

function TinyMCE_getControlHTML(control_name) {
	var themePlugins = tinyMCE.getParam('plugins', '', true, ',');
	var templateFunction;

	// Is it defined in any plugins
	for (var i=themePlugins.length; i>=0; i--) {
		templateFunction = 'TinyMCE_' + themePlugins[i] + "_getControlHTML";
		if (eval("typeof(" + templateFunction + ")") != 'undefined') {
			var html = eval(templateFunction + "('" + control_name + "');");
			if (html != "")
				return tinyMCE.replaceVar(html, "pluginurl", tinyMCE.baseURL + "/plugins/" + themePlugins[i]);
		}
	}

	return eval('TinyMCE_' + tinyMCE.settings['theme'] + "_getControlHTML" + "('" + control_name + "');");
}

function TinyMCE__themeExecCommand(editor_id, element, command, user_interface, value) {
	var themePlugins = tinyMCE.getParam('plugins', '', true, ',');
	var templateFunction;

	// Is it defined in any plugins
	for (var i=themePlugins.length; i>=0; i--) {
		templateFunction = 'TinyMCE_' + themePlugins[i] + "_execCommand";
		if (eval("typeof(" + templateFunction + ")") != 'undefined') {
			if (eval(templateFunction + "(editor_id, element, command, user_interface, value);"))
				return true;
		}
	}

	// Theme funtion
	templateFunction = 'TinyMCE_' + tinyMCE.settings['theme'] + "_execCommand";
	if (eval("typeof(" + templateFunction + ")") != 'undefined')
		return eval(templateFunction + "(editor_id, element, command, user_interface, value);");

	// Pass to normal
	return false;
}

function TinyMCE__getThemeFunction(suffix, skip_plugins) {
	if (skip_plugins)
		return 'TinyMCE_' + tinyMCE.settings['theme'] + suffix;

	var themePlugins = tinyMCE.getParam('plugins', '', true, ',');
	var templateFunction;

	// Is it defined in any plugins
	for (var i=themePlugins.length; i>=0; i--) {
		templateFunction = 'TinyMCE_' + themePlugins[i] + suffix;
		if (eval("typeof(" + templateFunction + ")") != 'undefined')
			return templateFunction;
	}

	return 'TinyMCE_' + tinyMCE.settings['theme'] + suffix;
}

// TinyMCEControl
function TinyMCEControl(settings) {
	// Undo levels
	this.undoLevels = new Array();
	this.undoIndex = 0;

	// Default settings
	this.settings = settings;
	this.settings['theme'] = tinyMCE.getParam("theme", "default");
	this.settings['width'] = tinyMCE.getParam("width", -1);
	this.settings['height'] = tinyMCE.getParam("height", -1);

	// Functions
	this.executeCallback = TinyMCEControl_executeCallback;
	this.fixBrokenURLs = TinyMCEControl_fixBrokenURLs;
	this.convertAllRelativeURLs = TinyMCEControl_convertAllRelativeURLs;
	this.execCommand = TinyMCEControl_execCommand;
	this.queryCommandValue = TinyMCEControl_queryCommandValue;
	this.queryCommandState = TinyMCEControl_queryCommandState;
	this.onAdd = TinyMCEControl_onAdd;
	this.getFocusElement = TinyMCEControl_getFocusElement;
	this.autoResetDesignMode = TinyMCEControl_autoResetDesignMode;
	this._insertPara = TinyMCEControl__insertPara;
	this._insertSpace = TinyMCEControl__insertSpace;
	this._handleBackSpace = TinyMCEControl__handleBackSpace;
	this.selectNode = TinyMCEControl_selectNode;
	this.getBody = TinyMCEControl_getBody;
	this.getDoc = TinyMCEControl_getDoc;
	this.getWin = TinyMCEControl_getWin;
	this.getSel = TinyMCEControl_getSel;
	this.getRng = TinyMCEControl_getRng;
}

function TinyMCEControl_executeCallback(param, suffix, mode) {
	function isFunc(func_name) {
		if (func_name == null || func_name == "")
			return false;

		return eval("typeof(" + func_name + ")") != "undefined";
	}

	function exec(func_name, args) {
		var str = func_name + '(';

		// Add all arguments
		for (var i=3; i<args.length; i++) {
			str += 'args[' + i + ']';

			if (i < args.length-1)
				str += ',';
		}

		str += ');';

		return eval(str);
	}

	switch (mode) {
		// No chain
		case 0:
			var state = false;

			// Execute each plugin callback
			var plugins = tinyMCE.getParam('plugins', '', true, ',');
			for (var i=0; i<plugins.length; i++) {
				var func = "TinyMCE_" + plugins[i] + suffix;
				if (isFunc(func)) {
					exec(func, this.executeCallback.arguments);
					state = true;
				}
			}

			// Execute theme callback
			var func = 'TinyMCE_' + tinyMCE.settings['theme'] + suffix;
			if (isFunc(func)) {
				exec(func, this.executeCallback.arguments);
				state = true;
			}

			// Execute settings callback
			var func = tinyMCE.getParam(param, '');
			if (isFunc(func)) {
				exec(func, this.executeCallback.arguments);
				state = true;
			}

			return state;

		// Chain mode
		case 1:
			// Execute each plugin callback
			var plugins = tinyMCE.getParam('plugins', '', true, ',');
			for (var i=0; i<plugins.length; i++) {
				var func = "TinyMCE_" + plugins[i] + suffix;
				if (isFunc(func)) {
					if (exec(func, this.executeCallback.arguments))
						return true;
				}
			}

			// Execute theme callback
			var func = 'TinyMCE_' + tinyMCE.settings['theme'] + suffix;
			if (isFunc(func)) {
				if (exec(func, this.executeCallback.arguments))
					return true;
			}

			// Execute settings callback
			var func = tinyMCE.getParam(param, '');
			if (isFunc(func)) {
				if (exec(func, this.executeCallback.arguments))
					return true;
			}

			return false;
	}
}

function TinyMCEControl_fixBrokenURLs() {
	var body = this.getBody();

	var elms = body.getElementsByTagName("img");
	for (var i=0; i<elms.length; i++) {
		var src = elms[i].getAttribute('mce_real_src');
		if (src && src != "")
			elms[i].setAttribute("src", src);
	}

	var elms = body.getElementsByTagName("a");
	for (var i=0; i<elms.length; i++) {
		var href = elms[i].getAttribute('mce_real_href');
		if (href && href != "")
			elms[i].setAttribute("href", href);
	}
}

function TinyMCEControl_convertAllRelativeURLs() {
	var body = this.getBody();

	// Convert all image URL:s to absolute URL
	var elms = body.getElementsByTagName("img");
	for (var i=0; i<elms.length; i++) {
		var src = elms[i].getAttribute('src');
		if (src && src != "") {
			src = tinyMCE.convertRelativeToAbsoluteURL(tinyMCE.settings['base_href'], src);
			elms[i].setAttribute("src", src);
			elms[i].setAttribute("mce_real_src", src);
		}
	}

	// Convert all link URL:s to absolute URL
	var elms = body.getElementsByTagName("a");
	for (var i=0; i<elms.length; i++) {
		var href = elms[i].getAttribute('href');
		if (href && href != "") {
			href = tinyMCE.convertRelativeToAbsoluteURL(tinyMCE.settings['base_href'], href);
			elms[i].setAttribute("href", href);
			elms[i].setAttribute("mce_real_href", href);
		}
	}
}

function TinyMCEControl_selectNode(node, collapse, select_text_node, to_start) {
	if (typeof(collapse) == "undefined")
		collapse = true;

	if (typeof(select_text_node) == "undefined")
		select_text_node = false;

	if (typeof(to_start) == "undefined")
		to_start = true;

	if (tinyMCE.isMSIE) {
		var rng = this.getBody().createTextRange();

		rng.moveToElementText(node);
		if (collapse)
			rng.collapse(to_start);

		rng.select();
	} else {
		var rng = this.getDoc().createRange();
		var sel = this.contentWindow.getSelection();

		if (!sel)
			return;

		if (select_text_node) {
			// Find first textnode in tree
			var nodes = tinyMCE.getNodeTree(node, new Array(), 3);
			if (nodes.length > 0)
				rng.selectNodeContents(nodes[0]);
			else
				rng.selectNodeContents(node);
		} else
			rng.selectNode(node);

		if (collapse) {
			// Special treatment of textnode collapse
			if (!to_start && node.nodeType == 3) {
				rng.setStart(node, node.nodeValue.length);
				rng.setEnd(node, node.nodeValue.length);
			} else
				rng.collapse(to_start);
		}

		sel.removeAllRanges();
		sel.addRange(rng);
	}

	// Scroll to node position
	var pos = tinyMCE.getAbsPosition(node);
	var doc = this.getDoc();
	var scrollX = doc.body.scrollLeft + doc.documentElement.scrollLeft;
	var scrollY = doc.body.scrollTop + doc.documentElement.scrollTop;
	var height = tinyMCE.isMSIE ? document.getElementById(this.editorId).style.pixelHeight : parseInt(this.targetElement.style.height);

	// Only scroll if out of visible area
	if (!tinyMCE.settings['auto_resize'] && !(node.absTop > scrollY && node.absTop < (scrollY - 25 + height)))
		this.contentWindow.scrollTo(pos.absLeft, pos.absTop - height + 25);

	// Set selected element
	tinyMCE.selectedElement = null;
	if (node.nodeType == 1)
		tinyMCE.selectedElement = node;
}

function TinyMCEControl_getBody() {
	return this.getDoc().body;
}

function TinyMCEControl_getDoc() {
	return this.contentWindow.document;
}

function TinyMCEControl_getWin() {
	return this.contentWindow;
}

function TinyMCEControl_getSel() {
	if (tinyMCE.isMSIE)
		return this.getDoc().selection;

	return this.contentWindow.getSelection();
}

function TinyMCEControl_getRng() {
	var sel = this.getSel();
	if (sel == null)
		return null;

	if (tinyMCE.isMSIE)
		return sel.createRange();

	return this.getSel().getRangeAt(0);
}

function TinyMCEControl__insertPara(e) {
	function isEmpty(para) {
		function isEmptyHTML(html) {
			return html.replace(new RegExp('[ \t\r\n]+', 'g'), '').toLowerCase() == "";
}

		// Check for images
		if (para.getElementsByTagName("img").length > 0)
			return false;

		// Check for tables
		if (para.getElementsByTagName("table").length > 0)
			return false;

		// Check for HRs
		if (para.getElementsByTagName("hr").length > 0)
			return false;

		// Check all textnodes
		var nodes = tinyMCE.getNodeTree(para, new Array(), 3);
		for (var i=0; i<nodes.length; i++) {
			if (!isEmptyHTML(nodes[i].nodeValue))
				return false;
		}

		// No images, no tables, no hrs, no text content then it's empty
		return true;
	}

	var doc = this.getDoc();
	var sel = this.contentWindow.getSelection();
	var win = this.contentWindow;
	var rng = sel.getRangeAt(0);
	var body = doc.body;
	var rootElm = doc.documentElement;
	var self = this;

//	debug(e.target, sel.anchorNode.nodeName, sel.focusNode.nodeName, rng.startContainer, rng.endContainer, rng.commonAncestorContainer, sel.anchorOffset, sel.focusOffset, rng.toString());

	// Setup before range
	var rngBefore = doc.createRange();
	rngBefore.setStart(sel.anchorNode, sel.anchorOffset);
	rngBefore.collapse(true);

	// Setup after range
	var rngAfter = doc.createRange();
	rngAfter.setStart(sel.focusNode, sel.focusOffset);
	rngAfter.collapse(true);

	// Setup start/end points
	var direct = rngBefore.compareBoundaryPoints(rngBefore.START_TO_END, rngAfter) < 0;
	var startNode = direct ? sel.anchorNode : sel.focusNode;
	var startOffset = direct ? sel.anchorOffset : sel.focusOffset;
	var endNode = direct ? sel.focusNode : sel.anchorNode;
	var endOffset = direct ? sel.focusOffset : sel.anchorOffset;

	// Get block elements
	var startBlock = tinyMCE.getParentBlockElement(startNode);
	var endBlock = tinyMCE.getParentBlockElement(endNode);

	// Within a list item (use normal behavior)
	if ((startBlock != null && startBlock.nodeName.toLowerCase() == "li") || (endBlock != null && endBlock.nodeName.toLowerCase() == "li"))
		return false;

	// Within a table create new paragraphs
	if ((startBlock != null && startBlock.nodeName.toLowerCase() == "table") || (endBlock != null && endBlock.nodeName.toLowerCase() == "table"))
		startBlock = endBlock = null;

	// Setup new paragraphs
	var paraBefore = (startBlock != null && startBlock.nodeName.toLowerCase() == "p") ? startBlock.cloneNode(false) : doc.createElement("p");
	var paraAfter = (endBlock != null && endBlock.nodeName.toLowerCase() == "p") ? endBlock.cloneNode(false) : doc.createElement("p");

	// Setup chop nodes
	var startChop = startNode;
	var endChop = endNode;

	// Get startChop node
	node = startChop;
	do {
		if (node == body || node.nodeType == 9 || tinyMCE.isBlockElement(node))
			break;

		startChop = node;
	} while ((node = node.previousSibling ? node.previousSibling : node.parentNode));

	// Get endChop node
	node = endChop;
	do {
		if (node == body || node.nodeType == 9 || tinyMCE.isBlockElement(node))
			break;

		endChop = node;
	} while ((node = node.nextSibling ? node.nextSibling : node.parentNode));

	// If not in a block element
	if (startBlock == null) {
		// Delete selection
		rng.deleteContents();
		sel.removeAllRanges();

		if (startChop != rootElm && endChop != rootElm) {
			// Insert paragraph before
			rngBefore = rng.cloneRange();

			if (startChop == body)
				rngBefore.setStart(startChop, 0);
			else
			rngBefore.setStartBefore(startChop);

			paraBefore.appendChild(rngBefore.cloneContents());

			// Insert paragraph after
			if (endChop.parentNode.nodeName == "P")
				endChop = endChop.parentNode;

			rng.setEndAfter(endChop);

			var contents = rng.cloneContents();
			if (contents.firstChild && (contents.firstChild.nodeName == "P" || contents.firstChild.nodeName.toLowerCase() == "body")) {
				var nodes = contents.firstChild.childNodes;
				for (var i=0; i<nodes.length; i++) {
					if (nodes[i].nodeName.toLowerCase() != "body")
						paraAfter.appendChild(nodes[i]);
				}
			} else
				paraAfter.appendChild(contents);

			// Check if it's a empty paragraph
			if (isEmpty(paraBefore))
				paraBefore.innerHTML = "&nbsp;";

			// Check if it's a empty paragraph
			if (isEmpty(paraAfter))
				paraAfter.innerHTML = "&nbsp;";

			// Delete old contents
			rng.deleteContents();
			rngAfter.deleteContents();
			rngBefore.deleteContents();

			// Insert new paragraphs
			paraAfter.normalize();
			rngBefore.insertNode(paraAfter);
			paraBefore.normalize();
			rngBefore.insertNode(paraBefore);

			// debug("1: ", paraBefore.innerHTML, paraAfter.innerHTML);
		} else {
			body.innerHTML = "<p>&nbsp;</p><p>&nbsp;</p>";
			paraAfter = body.childNodes[1];
		}

		this.selectNode(paraAfter, true, true);

		return true;
	}

	// Place first part within new paragraph
	if (startChop.nodeName == "P")
		rngBefore.setStart(startChop, 0);
	else
	rngBefore.setStartBefore(startChop);
	rngBefore.setEnd(startNode, startOffset);
	paraBefore.appendChild(rngBefore.cloneContents());

	// Place secound part within new paragraph
	rngAfter.setEndAfter(endChop);
	rngAfter.setStart(endNode, endOffset);
	var contents = rngAfter.cloneContents();
	if (contents.firstChild && contents.firstChild.nodeName == "P") {
		var nodes = contents.firstChild.childNodes;
		for (var i=0; i<nodes.length; i++) {
			if (nodes[i].nodeName.toLowerCase() != "body")
				paraAfter.appendChild(nodes[i]);
		}
	} else
		paraAfter.appendChild(contents);

	// Check if it's a empty paragraph
	if (isEmpty(paraBefore))
		paraBefore.innerHTML = "&nbsp;";

	// Check if it's a empty paragraph
	if (isEmpty(paraAfter))
		paraAfter.innerHTML = "&nbsp;";

	// Create a range around everything
	var rng = doc.createRange();

	if (!startChop.previousSibling && startChop.parentNode.nodeName.toLowerCase() == 'p') {
		rng.setStartBefore(startChop.parentNode);
	} else {
		if (rngBefore.startContainer.nodeName.toLowerCase() == 'p' && rngBefore.startOffset == 0)
			rng.setStartBefore(rngBefore.startContainer);
		else
			rng.setStart(rngBefore.startContainer, rngBefore.startOffset);
	}

	if (!endChop.nextSibling && endChop.parentNode.nodeName.toLowerCase() == 'p')
		rng.setEndAfter(endChop.parentNode);
	else
		rng.setEnd(rngAfter.endContainer, rngAfter.endOffset);

	// Delete all contents and insert new paragraphs
	rng.deleteContents();
	rng.insertNode(paraAfter);
	rng.insertNode(paraBefore);
	// debug("2", paraBefore.innerHTML, paraAfter.innerHTML);

	// Normalize
	paraAfter.normalize();
	paraBefore.normalize();

	this.selectNode(paraAfter, true, true);

	return true;
}

function TinyMCEControl__handleBackSpace(evt_type) {
	var doc = this.getDoc();
	var sel = this.contentWindow.getSelection();
	if (sel == null)
		return false;

	var rng = sel.getRangeAt(0);
	var node = rng.startContainer;
	var elm = node.nodeType == 3 ? node.parentNode : node;

	if (node == null)
		return;

	// Empty node, wrap contents in paragraph
	if (elm && elm.nodeName == "") {
		var para = doc.createElement("p");

		while (elm.firstChild)
			para.appendChild(elm.firstChild);

		elm.parentNode.insertBefore(para, elm);
		elm.parentNode.removeChild(elm);

		var rng = rng.cloneRange();
		rng.setStartBefore(node.nextSibling);
		rng.setEndAfter(node.nextSibling);
		rng.extractContents();

		this.selectNode(node.nextSibling, true, true);
	}

	// Remove empty paragraphs
	var para = tinyMCE.getParentBlockElement(node);
	if (para != null && para.nodeName.toLowerCase() == 'p' && evt_type == "keypress") {
		var htm = para.innerHTML;
		var block = tinyMCE.getParentBlockElement(node);

		// Empty node, we do the killing!!
		if (htm == "" || htm == "&nbsp;" || block.nodeName.toLowerCase() == "li") {
			var prevElm = para.previousSibling;

			while (prevElm != null && prevElm.nodeType != 1)
				prevElm = prevElm.previousSibling;

			if (prevElm == null)
				return false;

			// Get previous elements last text node
			var nodes = tinyMCE.getNodeTree(prevElm, new Array(), 3);
			var lastTextNode = nodes.length == 0 ? null : nodes[nodes.length-1];

			// Select the last text node and move curstor to end
			if (lastTextNode != null)
				this.selectNode(lastTextNode, true, false, false);

			// Remove the empty paragrapsh
			para.parentNode.removeChild(para);

			//debug("within p element" + para.innerHTML);
			//showHTML(this.getBody().innerHTML);
			return true;
		}
	}

	// Remove BR elements
/*	while (node != null && (node = node.nextSibling) != null) {
		if (node.nodeName.toLowerCase() == 'br')
			node.parentNode.removeChild(node);
		else if (node.nodeType == 1) // Break at other element
			break;
	}*/

	//showHTML(this.getBody().innerHTML);

	return false;
}

function TinyMCEControl__insertSpace() {
	return true;
}

function TinyMCEControl_autoResetDesignMode() {
	// Add fix for tab/style.display none/block problems in Gecko
	if (!tinyMCE.isMSIE && tinyMCE.settings['auto_reset_designmode']) {
		var sel = this.contentWindow.getSelection();

		// Weird, wheres that cursor selection?
		if (!sel || !sel.rangeCount || sel.rangeCount == 0)
			eval('try { this.getDoc().designMode = "On"; } catch(e) {}');
	}
}

function TinyMCEControl_execCommand(command, user_interface, value) {
	var doc = this.getDoc();

	function getAttrib(elm, name) {
		return elm.getAttribute(name) ? elm.getAttribute(name) : "";
	}

	// Mozilla issue
	if (!tinyMCE.isMSIE && !this.useCSS) {
		doc.execCommand("useCSS", false, true);
		this.useCSS = true;
	}

	//debug("command: " + command + ", user_interface: " + user_interface + ", value: " + value);
	this.contentDocument = doc; // <-- Strange, unless this is applied Mozilla 1.3 breaks

	// Call theme execcommand
	if (tinyMCE._themeExecCommand(this.editorId, this.contentDocument.body, command, user_interface, value))
		return;

	// Add undo level of operation
	if (command != "mceAddUndoLevel" && command != "Undo" && command != "Redo" && command != "mceImage" && command != "mceLink" && command != "mceToggleVisualAid" && (command != "mceInsertTable" && !user_interface))
		this.execCommand("mceAddUndoLevel");

	// Fix align on images
	if (this.getFocusElement() && this.getFocusElement().nodeName.toLowerCase() == "img") {
		var align = this.getFocusElement().getAttribute('align');

		switch (command) {
			case "JustifyLeft":
				if (align == 'left')
					this.getFocusElement().removeAttribute('align');
				else
					this.getFocusElement().setAttribute('align', 'left');

				tinyMCE.triggerNodeChange();
				return;

			case "JustifyCenter":
				if (align == 'middle')
					this.getFocusElement().removeAttribute('align');
				else
					this.getFocusElement().setAttribute('align', 'middle');

				tinyMCE.triggerNodeChange();
				return;

			case "JustifyRight":
				if (align == 'right')
					this.getFocusElement().removeAttribute('align');
				else
					this.getFocusElement().setAttribute('align', 'right');

				tinyMCE.triggerNodeChange();
				return;
		}
	}

	if (tinyMCE.settings['force_br_newlines']) {
		var alignValue = "";

		if (doc.selection.type != "Control") {
			switch (command) {
					case "JustifyLeft":
						alignValue = "left";
						break;

					case "JustifyCenter":
						alignValue = "center";
						break;

					case "JustifyFull":
						alignValue = "justify";
						break;

					case "JustifyRight":
						alignValue = "right";
						break;
			}

			if (alignValue != "") {
				var rng = doc.selection.createRange();

				if ((divElm = tinyMCE.getParentElement(rng.parentElement(), "div")) != null)
					divElm.setAttribute("align", alignValue);
				else if (rng.pasteHTML && rng.htmlText.length > 0)
					rng.pasteHTML('<div align="' + alignValue + '">' + rng.htmlText + "</div>");

				tinyMCE.triggerNodeChange();
				return;
			}
		}
	}

	switch (command) {
		case "mceSelectNode":
			this.selectNode(value);
			tinyMCE.triggerNodeChange();
			tinyMCE.selectedNode = value;
			break;

		case "mceSelectNodeDepth":
			var parentNode = this.getFocusElement();
			for (var i=0; parentNode; i++) {
				if (parentNode.nodeName.toLowerCase() == "body")
					break;

				if (parentNode.nodeName.toLowerCase() == "#text") {
					i--;
					parentNode = parentNode.parentNode;
					continue;
				}

				if (i == value) {
					this.selectNode(parentNode, false);
					tinyMCE.triggerNodeChange();
					tinyMCE.selectedNode = parentNode;
					return;
				}

				parentNode = parentNode.parentNode;
			}

			break;

		case "HiliteColor":
			if (tinyMCE.isGecko) {
				this.contentDocument.execCommand("useCSS", false, false);
				this.contentDocument.execCommand('hilitecolor', false, value);
				this.contentDocument.execCommand("useCSS", false, true);
			} else
				this.contentDocument.execCommand('backcolor', false, value);
			break;

		case "Cut":
		case "Copy":
		case "Paste":
			var cmdFailed = false;

			// Try executing command
			eval('try {this.contentDocument.execCommand(command, user_interface, value);} catch (e) {cmdFailed = true;}');

			// Alert error in gecko if command failed
			if (tinyMCE.isGecko && cmdFailed) {
				// Confirm more info
				if (confirm(tinyMCE.getLang('lang_clipboard_msg')))
					window.open('http://www.mozilla.org/editor/midasdemo/securityprefs.html', 'mceExternal');

				return;
			} else
				tinyMCE.triggerNodeChange();
		break;

		case "mceLink":
			var selectedText = "";

			if (tinyMCE.isMSIE) {
				var rng = doc.selection.createRange();
				selectedText = rng.text;
			} else
				selectedText = this.contentWindow.getSelection().toString();

			if (!tinyMCE.linkElement) {
				if ((tinyMCE.selectedElement.nodeName.toLowerCase() != "img") && (selectedText.length <= 0))
					return;
			}

			var href = "", target = "", title = "", onclick = "", action = "insert";

			if (tinyMCE.selectedElement.nodeName.toLowerCase() == "a")
				tinyMCE.linkElement = tinyMCE.selectedElement;

			// Is anchor not a link
			if (tinyMCE.linkElement != null && getAttrib(tinyMCE.linkElement, 'href') == "")
				tinyMCE.linkElement = null;

			if (tinyMCE.linkElement) {
				href = getAttrib(tinyMCE.linkElement, 'href');
				target = getAttrib(tinyMCE.linkElement, 'target');
				title = getAttrib(tinyMCE.linkElement, 'title');
                onclick = getAttrib(tinyMCE.linkElement, 'mce_onclick');

				// Try old onclick to if copy/pasted content
				if (onclick == "")
					onclick = getAttrib(tinyMCE.linkElement, 'onclick');

				onclick = tinyMCE.cleanupEventStr(onclick);

				// Fix for drag-drop/copy paste bug in Mozilla
				mceRealHref = getAttrib(tinyMCE.linkElement, 'mce_real_href');
				if (mceRealHref != "")
					href = mceRealHref;

				href = eval(tinyMCE.settings['urlconvertor_callback'] + "(href, tinyMCE.linkElement, true);");
				action = "update";
			}

			if (this.settings['insertlink_callback']) {
				var returnVal = eval(this.settings['insertlink_callback'] + "(href, target, title, onclick, action);");
				if (returnVal && returnVal['href'])
					tinyMCE.insertLink(returnVal['href'], returnVal['target'], returnVal['title'], returnVal['onclick']);
			} else {
				tinyMCE.openWindow(this.insertLinkTemplate, {href : href, target : target, title : title, onclick : onclick, action : action});
			}
		break;

		case "mceImage":
			var src = "", alt = "", border = "", hspace = "", vspace = "", width = "", height = "", align = "";
			var title = "", onmouseover = "", onmouseout = "", action = "insert";

			if (tinyMCE.selectedElement != null && tinyMCE.selectedElement.nodeName.toLowerCase() == "img")
				tinyMCE.imgElement = tinyMCE.selectedElement;

			if (tinyMCE.imgElement) {
				// Is it a internal MCE visual aid image, then skip this one.
				var imgName = getAttrib(tinyMCE.imgElement, 'name');
				if (imgName.substring(0, 4)=='mce_')
					return;

				src = getAttrib(tinyMCE.imgElement, 'src');
				alt = getAttrib(tinyMCE.imgElement, 'alt');

				// Try polling out the title
				if (alt == "")
					alt = getAttrib(tinyMCE.imgElement, 'title');

				border = getAttrib(tinyMCE.imgElement, 'border');
				hspace = getAttrib(tinyMCE.imgElement, 'hspace');
				vspace = getAttrib(tinyMCE.imgElement, 'vspace');
				width = getAttrib(tinyMCE.imgElement, 'width');
				height = getAttrib(tinyMCE.imgElement, 'height');
				align = getAttrib(tinyMCE.imgElement, 'align');
                onmouseover = getAttrib(tinyMCE.imgElement, 'onmouseover');
                onmouseout = getAttrib(tinyMCE.imgElement, 'onmouseout');
                title = getAttrib(tinyMCE.imgElement, 'title');

				onmouseover = tinyMCE.cleanupEventStr(onmouseover);
				onmouseout = tinyMCE.cleanupEventStr(onmouseout);

				// Fix for drag-drop/copy paste bug in Mozilla
				mceRealSrc = getAttrib(tinyMCE.imgElement, 'mce_real_src');
				if (mceRealSrc != "")
					src = mceRealSrc;

				src = eval(tinyMCE.settings['urlconvertor_callback'] + "(src, tinyMCE.imgElement, true);");
				action = "update";
			}

			if (this.settings['insertimage_callback']) {
				var returnVal = eval(this.settings['insertimage_callback'] + "(src, alt, border, hspace, vspace, width, height, align, title, onmouseover, onmouseout, action);");
				if (returnVal && returnVal['src'])
					tinyMCE.insertImage(returnVal['src'], returnVal['alt'], returnVal['border'], returnVal['hspace'], returnVal['vspace'], returnVal['width'], returnVal['height'], returnVal['align'], returnVal['title'], returnVal['onmouseover'], returnVal['onmouseout']);
			} else
				tinyMCE.openWindow(this.insertImageTemplate, {src : src, alt : alt, border : border, hspace : hspace, vspace : vspace, width : width, height : height, align : align, title : title, onmouseover : onmouseover, onmouseout : onmouseout, action : action});
		break;

		case "mceCleanupWord":
			if (tinyMCE.isMSIE) {
				var html = this.contentDocument.body.createTextRange().htmlText;

				if (html.indexOf('="mso') != -1) {
					tinyMCE._setHTML(this.contentDocument, this.contentDocument.body.innerHTML);
					html = tinyMCE._cleanupHTML(this.contentDocument, this.settings, this.contentDocument.body, this.visualAid);
				}

				this.contentDocument.body.innerHTML = html;
			}
		break;

		case "mceCleanup":
			tinyMCE._setHTML(this.contentDocument, this.contentDocument.body.innerHTML);
			this.contentDocument.body.innerHTML = tinyMCE._cleanupHTML(this.contentDocument, this.settings, this.contentDocument.body, this.visualAid);
			tinyMCE.triggerNodeChange();
		break;

		case "mceAnchor":
			if (!user_interface) {
				var aElm = tinyMCE.getParentElement(this.getFocusElement(), "a", "name");
				if (aElm) {
					if (value == null || value == "") {
						if (tinyMCE.isMSIE) {
							aElm.outerHTML = aElm.innerHTML;
						} else {
							var rng = aElm.ownerDocument.createRange();
							rng.setStartBefore(aElm);
							rng.setEndAfter(aElm);
							rng.deleteContents();
							rng.insertNode(rng.createContextualFragment(aElm.innerHTML));
						}
					} else
						aElm.setAttribute('name', value);
				} else {
					this.contentDocument.execCommand("fontname", false, "#mce_temp_font#");
					var elementArray = tinyMCE.getElementsByAttributeValue(this.contentDocument.body, "font", "face", "#mce_temp_font#");
					for (var x=0; x<elementArray.length; x++) {
						elm = elementArray[x];

						var aElm = this.contentDocument.createElement("a");
						aElm.setAttribute('name', value);

						if (elm.hasChildNodes()) {
							for (var i=0; i<elm.childNodes.length; i++)
								aElm.appendChild(elm.childNodes[i].cloneNode(true));
						}

						elm.parentNode.replaceChild(aElm, elm);
					}
				}

				tinyMCE.triggerNodeChange();
			}
			break;

		case "mceReplaceContent":
			var selectedText = "";

			if (tinyMCE.isMSIE) {
				var rng = doc.selection.createRange();
				selectedText = rng.text;
			} else
				selectedText = this.contentWindow.getSelection().toString();

			if (selectedText.length > 0) {
				value = tinyMCE.replaceVar(value, "selection", selectedText);
				tinyMCE.execCommand('mceInsertContent',false,value);
			}

			tinyMCE.triggerNodeChange();
		break;

		case "mceSetAttribute":
			if (typeof(value) == 'object') {
				var targetElms = (typeof(value['targets']) == "undefined") ? "p,img,span,div,td,h1,h2,h3,h4,h5,h6,pre,address" : value['targets'];
				var targetNode = tinyMCE.getParentElement(this.getFocusElement(), targetElms);

				if (targetNode) {
					targetNode.setAttribute(value['name'], value['value']);
					tinyMCE.triggerNodeChange();
				}
			}
		break;

		case "mceSetCSSClass":
			var selectedText = false;

			if (tinyMCE.isMSIE) {
				var rng = doc.selection.createRange();
				selectedText = (rng.text && rng.text.length > 0);
			} else
				selectedText = (this.contentWindow.getSelection().toString().length > 0);

			// Use selectedNode instead if defined
			if (tinyMCE.selectedNode)
				tinyMCE.selectedElement = tinyMCE.selectedNode;

			if (selectedText && !tinyMCE.selectedNode) {
				this.contentDocument.execCommand("removeformat", false, null);
				this.contentDocument.execCommand("fontname", false, "#mce_temp_font#");
				var elementArray = tinyMCE.getElementsByAttributeValue(this.contentDocument.body, "font", "face", "#mce_temp_font#");

				// Change them all
				for (var x=0; x<elementArray.length; x++) {
					elm = elementArray[x];
					if (elm) {
						var spanElm = this.contentDocument.createElement("span");
						spanElm.className = value;
						if (elm.hasChildNodes()) {
							for (var i=0; i<elm.childNodes.length; i++)
								spanElm.appendChild(elm.childNodes[i].cloneNode(true));
						}

						elm.parentNode.replaceChild(spanElm, elm);
					}
				}
			} else {
				var targetElm = this.getFocusElement();

				// Select element
				if (tinyMCE.selectedElement.nodeName.toLowerCase() == "img" || tinyMCE.selectedElement.nodeName.toLowerCase() == "table")
					targetElm = tinyMCE.selectedElement;

				var targetNode = tinyMCE.getParentElement(targetElm, "p,img,span,div,td,h1,h2,h3,h4,h5,h6,pre,address");

				// Selected element
				if (tinyMCE.selectedElement.nodeType == 1)
					targetNode = tinyMCE.selectedElement;

				// Mozilla img patch
				if (!tinyMCE.isMSIE && !targetNode)
					targetNode = tinyMCE.imgElement;

				if (targetNode) {
					if (targetNode.nodeName.toLowerCase() == "span" && (!value || value == "")) {
						if (targetNode.hasChildNodes()) {
							for (var i=0; i<targetNode.childNodes.length; i++)
								targetNode.parentNode.insertBefore(targetNode.childNodes[i].cloneNode(true), targetNode);
						}

						targetNode.parentNode.removeChild(targetNode);
					} else {
						if (value != null && value != "")
							targetNode.className = value;
						else {
							targetNode.removeAttribute("className");
							targetNode.removeAttribute("class");
						}
					}
				}
			}

			tinyMCE.triggerNodeChange();
		break;

		case "mceInsertContent":
			if (!tinyMCE.isMSIE) {
				var sel = this.contentWindow.getSelection();
				var rng = sel.getRangeAt(0);
				var isHTML = value.indexOf('<') != -1;

				if (isHTML)
				value = rng.createContextualFragment(value);
				else {
					// Setup text node
					var el = document.createElement("div");
					el.innerHTML = value;
					value = el.innerHTML;
					value = doc.createTextNode(value);
				}

				rng.deleteContents();

				// If target node is text do special treatment, (Mozilla 1.3 fix)
				if (rng.startContainer.nodeType == 3) {
					var node = rng.startContainer.splitText(rng.startOffset);
					node.parentNode.insertBefore(value, node); 
				} else
					rng.insertNode(value);

				if (!isHTML) {
					// Removes weird selection trails
					sel.selectAllChildren(doc.body);
					sel.removeAllRanges();

					// Move cursor to end of content
					var rng = doc.createRange();

					rng.selectNode(value);
				rng.collapse(false);

					sel.addRange(rng);
				} else
					rng.collapse(false);
			} else {
				var rng = doc.selection.createRange();

				if (rng.item)
					rng.item(0).outerHTML = value;
				else
					rng.pasteHTML(value);
			}

			tinyMCE.triggerNodeChange();
		break;

		case "mceAddUndoLevel":
			if (tinyMCE.settings['custom_undo_redo']) {
				var customUndoLevels = tinyMCE.settings['custom_undo_redo_levels'];

				var newHTML = this.getBody().innerHTML;
				if (newHTML != this.undoLevels[this.undoLevels.length-1]) {
					// Time to compress
					if (customUndoLevels != -1 && this.undoLevels.length > customUndoLevels) {
						for (var i=0; i<this.undoLevels.length-1; i++) {
							//alert(this.undoLevels[i] + "=" + this.undoLevels[i+1]);
							this.undoLevels[i] = this.undoLevels[i+1];
						}

						this.undoLevels.length--;
						this.undoIndex--;
					}

					//alert(newHTML + "=" + this.undoLevels[this.undoIndex]);
					// Add new level
					this.undoLevels[this.undoIndex++] = newHTML;
					this.undoLevels.length = this.undoIndex;
					//window.status = "mceAddUndoLevel - undo levels:" + this.undoLevels.length + ", undo index: " + this.undoIndex;
				}

				tinyMCE.triggerNodeChange(false);
			}
			break;

		case "Undo":
			if (tinyMCE.settings['custom_undo_redo']) {
				// Is first level
				if (this.undoIndex == this.undoLevels.length) {
					this.execCommand("mceAddUndoLevel");
					this.undoIndex--;
				}

				// Do undo
				if (this.undoIndex > 0) {
					this.undoIndex--;
					this.getBody().innerHTML = this.undoLevels[this.undoIndex];
				}

				//debug("Undo - undo levels:" + this.undoLevels.length + ", undo index: " + this.undoIndex);
				tinyMCE.triggerNodeChange();
			} else
				this.contentDocument.execCommand(command, user_interface, value);
			break;

		case "Redo":
			if (tinyMCE.settings['custom_undo_redo']) {
				if (this.undoIndex < (this.undoLevels.length-1)) {
					this.undoIndex++;
					this.getBody().innerHTML = this.undoLevels[this.undoIndex];
					//debug("Redo - undo levels:" + this.undoLevels.length + ", undo index: " + this.undoIndex);
				}

				tinyMCE.triggerNodeChange();
			} else
				this.contentDocument.execCommand(command, user_interface, value);
			break;

		case "mceToggleVisualAid":
			this.visualAid = !this.visualAid;
			tinyMCE.handleVisualAid(this.getBody(), true, this.visualAid);
			tinyMCE.triggerNodeChange();
			break;

		case "removeformat":
			if (tinyMCE.isMSIE) {
				try {
			var rng = doc.selection.createRange();
					rng.pasteHTML(rng.text);
				} catch (e) {
					// Do nothing
				}
			} else
				this.contentDocument.execCommand(command, user_interface, value);

			tinyMCE.triggerNodeChange();
			break;

		default:
			this.contentDocument.execCommand(command, user_interface, value);
			tinyMCE.triggerNodeChange();
	}
}

function TinyMCEControl_queryCommandValue(command) {
	return this.getDoc().queryCommandValue(command);
}

function TinyMCEControl_queryCommandState(command) {
	return this.getDoc().queryCommandState(command);
}

function TinyMCEControl_onAdd(replace_element, form_element_name, target_document) {
	var targetDoc = target_document ? target_document : document;

	this.targetDoc = targetDoc;

	tinyMCE.themeURL = tinyMCE.baseURL + "/themes/" + this.settings['theme'];
	this.settings['themeurl'] = tinyMCE.themeURL;

	if (!replace_element) {
		alert("Error: Could not find the target element.");
		return false;
	}

	var templateFunction = tinyMCE._getThemeFunction('_getInsertLinkTemplate');
	if (eval("typeof(" + templateFunction + ")") != 'undefined')
		this.insertLinkTemplate = eval(templateFunction + '(this.settings);');

	var templateFunction = tinyMCE._getThemeFunction('_getInsertImageTemplate');
	if (eval("typeof(" + templateFunction + ")") != 'undefined')
		this.insertImageTemplate = eval(templateFunction + '(this.settings);');

	var templateFunction = tinyMCE._getThemeFunction('_getEditorTemplate');
	if (eval("typeof(" + templateFunction + ")") == 'undefined') {
		alert("Error: Could not find the template function: " + templateFunction);
		return false;
	}

	var editorTemplate = eval(templateFunction + '(this.settings, this.editorId);');

	var deltaWidth = editorTemplate['delta_width'] ? editorTemplate['delta_width'] : 0;
	var deltaHeight = editorTemplate['delta_height'] ? editorTemplate['delta_height'] : 0;
	var html = '<span id="' + this.editorId + '_parent">' + editorTemplate['html'];

	var templateFunction = tinyMCE._getThemeFunction('_handleNodeChange', true);
	if (eval("typeof(" + templateFunction + ")") != 'undefined')
		this.settings['handleNodeChangeCallback'] = templateFunction;

	html = tinyMCE.replaceVar(html, "editor_id", this.editorId);
	html = tinyMCE.replaceVar(html, "default_document", tinyMCE.baseURL + "/blank.htm");
	this.settings['default_document'] = tinyMCE.baseURL + "/blank.htm";

	this.settings['old_width'] = this.settings['width'];
	this.settings['old_height'] = this.settings['height'];

	// Set default width, height
	if (this.settings['width'] == -1)
		this.settings['width'] = replace_element.offsetWidth;

	if (this.settings['height'] == -1)
		this.settings['height'] = replace_element.offsetHeight;

	// If no width/height then default to 320x240, better than nothing
	if (replace_element.offsetWidth == 0)
		this.settings['width'] = 320;

	if (replace_element.offsetHeight == 0)
		this.settings['height'] = 240;

	this.settings['area_width'] = parseInt(this.settings['width']);
	this.settings['area_height'] = parseInt(this.settings['height']);
	this.settings['area_width'] += deltaWidth;
	this.settings['area_height'] += deltaHeight;

	// Special % handling
	if (("" + this.settings['width']).indexOf('%') != -1)
		this.settings['area_width'] = "100%";

	if (("" + this.settings['height']).indexOf('%') != -1)
		this.settings['area_height'] = "100%";

	if (("" + replace_element.style.width).indexOf('%') != -1) {
		this.settings['width'] = replace_element.style.width;
		this.settings['area_width'] = "100%";
	}

	if (("" + replace_element.style.height).indexOf('%') != -1) {
		this.settings['height'] = replace_element.style.height;
		this.settings['area_height'] = "100%";
	}

	html = tinyMCE.applyTemplate(html);

	this.settings['width'] = this.settings['old_width'];
	this.settings['height'] = this.settings['old_height'];

	this.visualAid = this.settings['visual'];
	this.formTargetElementId = form_element_name;

	// Get replace_element contents
	if (replace_element.nodeName.toLowerCase() == "textarea")
		this.startContent = replace_element.value;
	else
		this.startContent = replace_element.innerHTML;

	// If not text area
	if (replace_element.nodeName.toLowerCase() != "textarea") {
		this.oldTargetElement = replace_element.cloneNode(true);

		// Debug mode
		if (tinyMCE.settings['debug'])
			html += '<textarea wrap="off" id="' + form_element_name + '" name="' + form_element_name + '" cols="100" rows="15"></textarea>';
		else
			html += '<input type="hidden" type="text" id="' + form_element_name + '" name="' + form_element_name + '" />';

		html += '</span>';

		// Output HTML and set editable
		if (!tinyMCE.isMSIE) {
			var rng = replace_element.ownerDocument.createRange();
			rng.setStartBefore(replace_element);

			var fragment = rng.createContextualFragment(html);
			replace_element.parentNode.replaceChild(fragment, replace_element);
		} else
			replace_element.outerHTML = html;
	} else {
		html += '</span>';

		// Just hide the textarea element
		this.oldTargetElement = replace_element;

		if (!tinyMCE.settings['debug'])
			this.oldTargetElement.style.display = "none";

		// Output HTML and set editable
		if (!tinyMCE.isMSIE) {
			var rng = replace_element.ownerDocument.createRange();
			rng.setStartBefore(replace_element);

			var fragment = rng.createContextualFragment(html);
			replace_element.parentNode.insertBefore(fragment, replace_element);
		} else
			replace_element.insertAdjacentHTML("beforeBegin", html);
	}

	// Setup iframe
	var dynamicIFrame = false;
	var tElm = targetDoc.getElementById(this.editorId);

	if (!tinyMCE.isMSIE) {
		if (tElm && tElm.nodeName.toLowerCase() == "span") {
			tElm = tinyMCE._createIFrame(tElm);
			dynamicIFrame = true;
		}

		this.targetElement = tElm;
		this.iframeElement = tElm;
		this.contentDocument = tElm.contentDocument;
		this.contentWindow = tElm.contentWindow;

		//this.getDoc().designMode = "on";
	} else {
		if (tElm && tElm.nodeName.toLowerCase() == "span")
			tElm = tinyMCE._createIFrame(tElm);
		else
			tElm = targetDoc.frames[this.editorId];

		this.targetElement = tElm;
		this.iframeElement = targetDoc.getElementById(this.editorId);
		this.contentDocument = tElm.window.document;
		this.contentWindow = tElm.window;
		this.contentDocument.designMode = "on";
	}

	// Setup base HTML
	var doc = this.contentDocument;
	if (dynamicIFrame) {
        var html = ""
            + '<!doctype html public "-//w3c//dtd html 4.0 transitional//en">'
            + '<html>'
            + '<head>'
			+ '<base href="' + tinyMCE.settings['base_href'] + '" />'
            + '<title>blank_page</title>'
            + '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">'
            + '</head>'
            + '<body>'
            + '</body>'
            + '</html>';

		try {
			this.getDoc().designMode = "on";
			doc.open();
			doc.write(html);
			doc.close();
		} catch (e) {
			// Failed Mozilla 1.3
			this.getDoc().location.href = tinyMCE.baseURL + "/blank.htm";
		}
	}

	// This timeout is needed in MSIE 5.5 for some odd reason
	// it seems that the document.frames isn't initialized yet?
	if (tinyMCE.isMSIE)
		window.setTimeout("TinyMCE_addEventHandlers('" + this.editorId + "');", 1);

	//window.setTimeout("tinyMCE.setupContent('" + this.editorId + "');", (tinyMCE.isMSIE ? 1 : 1000));
	tinyMCE.setupContent(this.editorId, true);

	return true;
}

function TinyMCEControl_getFocusElement() {
	if (tinyMCE.isMSIE) {
		var doc = this.getDoc();
		var rng = doc.selection.createRange();
		var elm = rng.item ? rng.item(0) : rng.parentElement();
	} else {
		var sel = this.contentWindow.getSelection();
		var elm = (sel && sel.anchorNode) ? sel.anchorNode : null;

		if (tinyMCE.selectedElement != null && tinyMCE.selectedElement.nodeName.toLowerCase() == "img")
			elm = tinyMCE.selectedElement;
	}

	return elm;
}

// Global instances
var tinyMCE = new TinyMCE();
var tinyMCELang = new Array();

/*
function debug() {
	var msg = "";

	var elm = document.getElementById("tinymce_debug");
	if (!elm) {
		var debugDiv = document.createElement("div");
		debugDiv.setAttribute("className", "debugger");
		debugDiv.className = "debugger";
		debugDiv.innerHTML = '\
			Debug output:\
			<textarea id="tinymce_debug" style="width: 100%; height: 300px">\
			</textarea>';

		document.body.appendChild(debugDiv);
		elm = document.getElementById("tinymce_debug");
	}

	var args = this.debug.arguments;
	for (var i=0; i<args.length; i++) {
		msg += args[i];
		if (i<args.length-1)
			msg += ', ';
	}

	elm.value += msg + "\n";
};
*/