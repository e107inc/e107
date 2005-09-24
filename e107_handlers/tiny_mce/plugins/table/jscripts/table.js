var action;

function insertTable() {
	var formObj = document.forms[0];
	var inst = tinyMCE.selectedInstance;
	var cols = 2, rows = 2, border = 0, cellpadding = -1, cellspacing = -1, align, width, height, className;
	var html = '';
	var elm = tinyMCE.tableElm;

	// Get form data
	cols = formObj.elements['cols'].value;
	rows = formObj.elements['rows'].value;
	border = formObj.elements['border'].value != "" ? formObj.elements['border'].value  : 0;
	cellpadding = formObj.elements['cellpadding'].value != "" ? formObj.elements['cellpadding'].value : "";
	cellspacing = formObj.elements['cellspacing'].value != "" ? formObj.elements['cellspacing'].value : "";
	align = formObj.elements['align'].options[formObj.elements['align'].selectedIndex].value;
	width = formObj.elements['width'].value;
	height = formObj.elements['height'].value;
	bordercolor = formObj.elements['bordercolor'].value;
	bgcolor = formObj.elements['bgcolor'].value;
	className = formObj.elements['class'].options[formObj.elements['class'].selectedIndex].value;
	id = formObj.elements['id'].value;
	summary = formObj.elements['summary'].value;
	style = formObj.elements['style'].value;
	dir = formObj.elements['dir'].value;
	lang = formObj.elements['lang'].value;
	background = formObj.elements['backgroundimage'].value;

	// Update table
	if (action == "update") {
		inst.execCommand('mceBeginUndoLevel');

		tinyMCE.setAttrib(elm, 'cellPadding', cellpadding, true);
		tinyMCE.setAttrib(elm, 'cellSpacing', cellspacing, true);
		tinyMCE.setAttrib(elm, 'border', border, true);
		tinyMCE.setAttrib(elm, 'width', width, true);
		tinyMCE.setAttrib(elm, 'height', height, true);
		tinyMCE.setAttrib(elm, 'borderColor', bordercolor);
		tinyMCE.setAttrib(elm, 'bgColor', bgcolor);
		tinyMCE.setAttrib(elm, 'align', align);
		tinyMCE.setAttrib(elm, 'class', className);
		tinyMCE.setAttrib(elm, 'style', style);
		tinyMCE.setAttrib(elm, 'id', id);
		tinyMCE.setAttrib(elm, 'summary', summary);
		tinyMCE.setAttrib(elm, 'dir', dir);
		tinyMCE.setAttrib(elm, 'lang', lang);

		if (background != '')
			elm.style.backgroundImage = "url('" + background + "')";

		tinyMCE.handleVisualAid(tinyMCE.tableElm, false, inst.visualAid, inst);

		// Fix for stange MSIE align bug
		tinyMCE.tableElm.outerHTML = tinyMCE.tableElm.outerHTML;

		tinyMCE.handleVisualAid(inst.getBody(), true, inst.visualAid, inst);
		tinyMCE.triggerNodeChange();
		inst.execCommand('mceEndUndoLevel');
		tinyMCEPopup.close();
		return true;
	}

	// Create new table
	html += '<table';

	html += makeAttrib('id', id);
	html += makeAttrib('border', border);
	html += makeAttrib('cellpadding', cellpadding);
	html += makeAttrib('cellspacing', cellspacing);
	html += makeAttrib('width', width);
	html += makeAttrib('height', height);
	html += makeAttrib('bordercolor', bordercolor);
	html += makeAttrib('bgcolor', bgcolor);
	html += makeAttrib('align', align);
	html += makeAttrib('class', tinyMCE.getVisualAidClass(className, border == 0));
	html += makeAttrib('style', style);
	html += makeAttrib('summary', summary);
	html += makeAttrib('dir', dir);
	html += makeAttrib('lang', lang);

	html += '>';

	for (var y=0; y<rows; y++) {
		html += "<tr>";

		for (var x=0; x<cols; x++)
			html += '<td>&nbsp;</td>';

		html += "</tr>";
	}

	html += "</table>";

	inst.execCommand('mceBeginUndoLevel');
	inst.execCommand('mceInsertContent', false, html);
	tinyMCE.handleVisualAid(inst.getBody(), true, tinyMCE.settings['visual']);
	inst.execCommand('mceEndUndoLevel');

	tinyMCEPopup.close();
}


function makeAttrib(attrib, value) {
	var formObj = document.forms[0];
	var valueElm = formObj.elements[attrib];

	if (typeof(value) == "undefined" || value == null) {
		value = "";

		if (valueElm)
			value = valueElm.value;
	}

	if (value == "")
		return "";

	// XML encode it
	value = value.replace(/&/g, '&amp;');
	value = value.replace(/\"/g, '&quot;');
	value = value.replace(/</g, '&lt;');
	value = value.replace(/>/g, '&gr;');

	return ' ' + attrib + '="' + value + '"';
}

function getStyle(elm, st, attrib, style) {
	var val = tinyMCE.getAttrib(elm, attrib);

	if (typeof(style) == 'undefined')
		style = attrib;

	return val == '' ? (st[style] ? st[style].replace('px', '') : '') : val;
}

function init() {
	var cols = 2, rows = 2, border = 0, cellpadding = "", cellspacing = "";
	var align = "", width = "", height = "", bordercolor = "", bgcolor = "", className = "";
	var id = "", summary = "", style = "", dir = "", lang = "", background = "", bgcolor = "", bordercolor = "";
	var inst = tinyMCE.selectedInstance;
	var formObj = document.forms[0];
	var elm = tinyMCE.getParentElement(inst.getFocusElement(), "table");

	tinyMCE.tableElm = elm;
	action = tinyMCE.getWindowArg('action');

	if (tinyMCE.tableElm && action != "insert") {
		var rowsAr = tinyMCE.tableElm.rows;
		var cols = 0;
		for (var i=0; i<rowsAr.length; i++)
			if (rowsAr[i].cells.length > cols)
				cols = rowsAr[i].cells.length;

		cols = cols;
		rows = rowsAr.length;

		st = tinyMCE.parseStyle(tinyMCE.tableElm.style.cssText);
		border = getStyle(elm, st, 'border', 'border-width');
		cellpadding = tinyMCE.getAttrib(tinyMCE.tableElm, 'cellpadding', "");
		cellspacing = tinyMCE.getAttrib(tinyMCE.tableElm, 'cellspacing', "");
		width = getStyle(elm, st, 'width');
		height = getStyle(elm, st, 'height');
		bordercolor = getStyle(elm, st, 'bordercolor', 'border-color');
		bgcolor = getStyle(elm, st, 'bgcolor', 'background-color');
		align = tinyMCE.getAttrib(tinyMCE.tableElm, 'align', align);
		className = tinyMCE.getVisualAidClass(tinyMCE.getAttrib(tinyMCE.tableElm, 'class'), false);
		id = tinyMCE.getAttrib(tinyMCE.tableElm, 'id');
		summary = tinyMCE.getAttrib(tinyMCE.tableElm, 'summary');
		style = tinyMCE.serializeStyle(st);
		dir = tinyMCE.getAttrib(tinyMCE.tableElm, 'dir');
		lang = tinyMCE.getAttrib(tinyMCE.tableElm, 'lang');
		background = getStyle(elm, st, 'background', 'background-image').replace(new RegExp("url\\('?([^']*)'?\\)", 'gi'), "$1");

		action = "update";
	}

	addClassesToList('class', "table_styles");

	// Update form
	selectByValue(formObj, 'align', align);
	selectByValue(formObj, 'class', className);
	formObj.cols.value = cols;
	formObj.rows.value = rows;
	formObj.border.value = border;
	formObj.cellpadding.value = cellpadding;
	formObj.cellspacing.value = cellspacing;
	formObj.width.value = width;
	formObj.height.value = height;
	formObj.bordercolor.value = bordercolor;
	formObj.bgcolor.value = bgcolor;
	formObj.id.value = id;
	formObj.summary.value = summary;
	formObj.style.value = style;
	formObj.dir.value = dir;
	formObj.lang.value = lang;
	formObj.backgroundimage.value = background;
	formObj.insert.value = tinyMCE.getLang('lang_' + action, 'Insert', true); 

	updateColor('bordercolor_pick', 'bordercolor');
	updateColor('bgcolor_pick', 'bgcolor');

	// Resize some elements
	if (isVisible('backgroundimagebrowser'))
		document.getElementById('backgroundimage').style.width = '180px';

	// Disable some fields in update mode
	if (action == "update") {
		formObj.cols.disabled = true;
		formObj.rows.disabled = true;
	}
}

function changedBackgroundImage() {
	var formObj = document.forms[0];
	var st = tinyMCE.parseStyle(formObj.style.value);

	st['background-image'] = "url('" + formObj.backgroundimage.value + "')";

	formObj.style.value = tinyMCE.serializeStyle(st);
}

function changedStyle() {
	var formObj = document.forms[0];
	var st = tinyMCE.parseStyle(formObj.style.value);

	if (st['background-image'])
		formObj.backgroundimage.value = st['background-image'].replace(new RegExp("url\\('?([^']*)'?\\)", 'gi'), "$1");
	else
		formObj.backgroundimage.value = '';
}
