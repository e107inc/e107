function init() {
	var inst = tinyMCE.selectedInstance;
	var trElm = tinyMCE.getParentElement(inst.getFocusElement(), "tr");
	var formObj = document.forms[0];
	var st = tinyMCE.parseStyle(trElm.style.cssText);

	// Get table row data
	var rowtype = trElm.parentNode.nodeName.toLowerCase();
	var align = tinyMCE.getAttrib(trElm, 'align');
	var valign = tinyMCE.getAttrib(trElm, 'valign');
	var height = tinyMCE.getAttrib(trElm, 'height');
	var className = tinyMCE.getVisualAidClass(tinyMCE.getAttrib(trElm, 'class'), false);
	var bordercolor = tinyMCE.getAttrib(trElm, 'bordercolor');
	var bgcolor = tinyMCE.getAttrib(trElm, 'bgcolor');
	var backgroundimage = getStyle(trElm, st, 'background', 'background-image').replace(new RegExp("url\\('?([^']*)'?\\)", 'gi'), "$1");;
	var id = tinyMCE.getAttrib(trElm, 'id');
	var lang = tinyMCE.getAttrib(trElm, 'lang');
	var dir = tinyMCE.getAttrib(trElm, 'dir');

	// Setup form
	addClassesToList('class', 'table_row_styles');
	formObj.bordercolor.value = bordercolor;
	formObj.bgcolor.value = bgcolor;
	formObj.backgroundimage.value = backgroundimage;
	formObj.height.value = height;
	formObj.id.value = id;
	formObj.lang.value = lang;
	formObj.style.value = tinyMCE.serializeStyle(st);
	selectByValue(formObj, 'align', align);
	selectByValue(formObj, 'valign', valign);
	selectByValue(formObj, 'class', className);
	selectByValue(formObj, 'rowtype', rowtype);
	selectByValue(formObj, 'dir', dir);

	// Resize some elements
	if (isVisible('backgroundimagebrowser'))
		document.getElementById('backgroundimage').style.width = '180px';

	updateColor('bordercolor_pick', 'bordercolor');
	updateColor('bgcolor_pick', 'bgcolor');
}

function updateAction() {
	var inst = tinyMCE.selectedInstance;
	var trElm = tinyMCE.getParentElement(inst.getFocusElement(), "tr");
	var tableElm = tinyMCE.getParentElement(inst.getFocusElement(), "table");
	var formObj = document.forms[0];
	var action = getSelectValue(formObj, 'action');

	inst.execCommand('mceBeginUndoLevel');

	switch (action) {
		case "row":
			updateRow(trElm);
			break;

		case "all":
			var rows = tableElm.getElementsByTagName("tr");

			for (var i=0; i<rows.length; i++)
				updateRow(rows[i], true);

			break;

		case "odd":
		case "even":
			var rows = tableElm.getElementsByTagName("tr");

			for (var i=0; i<rows.length; i++) {
				if ((i % 2 == 0 && action == "odd") || (i % 2 != 0 && action == "even"))
					updateRow(rows[i], true, true);
			}

			break;
	}

	tinyMCE.handleVisualAid(inst.getBody(), true, inst.visualAid, inst);
	tinyMCE.triggerNodeChange();
	inst.execCommand('mceEndUndoLevel');
	tinyMCEPopup.close();
}

function updateRow(tr_elm, skip_id, skip_parent) {
	var inst = tinyMCE.selectedInstance;
	var formObj = document.forms[0];
	var curRowType = tr_elm.parentNode.nodeName.toLowerCase();
	var rowtype = getSelectValue(formObj, 'rowtype');
	var doc = inst.getDoc();

	// Update row element
	if (!skip_id)
		tr_elm.setAttribute('id', formObj.id.value);

	tr_elm.setAttribute('align', getSelectValue(formObj, 'align'));
	tr_elm.setAttribute('vAlign', getSelectValue(formObj, 'valign'));
	tr_elm.setAttribute('height', formObj.height.value);
	tr_elm.setAttribute('borderColor', formObj.bordercolor.value);
	tr_elm.setAttribute('bgColor', formObj.bgcolor.value);
	tr_elm.setAttribute('lang', formObj.lang.value);
	tr_elm.setAttribute('dir', getSelectValue(formObj, 'dir'));
	tr_elm.setAttribute('style', tinyMCE.serializeStyle(tinyMCE.parseStyle(formObj.style.value)));
	tinyMCE.setAttrib(tr_elm, 'class', getSelectValue(formObj, 'class'));

	// Setup new rowtype
	if (curRowType != rowtype && !skip_parent) {
		// first, clone the node we are working on
		var newRow = tr_elm.cloneNode(1);

		// next, find the parent of its new destination (creating it if necessary)
		var theTable = tinyMCE.getParentElement(tr_elm, "table");
		var dest = rowtype;
		var newParent = null;
		for (var i = 0; i < theTable.childNodes.length; i++) {
			if (theTable.childNodes[i].nodeName.toLowerCase() == dest)
				newParent = theTable.childNodes[i];
		}

		if (newParent == null) {
			newParent = doc.createElement(dest);

			if (dest == "thead")
				theTable.insertBefore(newParent, theTable.firstChild);
			else
				theTable.appendChild(newParent);
		}

		// append the row to the new parent
		newParent.appendChild(newRow);

		// remove the original
		tr_elm.parentNode.removeChild(tr_elm);

		// set tr_elm to the new node
		tr_elm = newRow;
	}
}

function getStyle(elm, st, attrib, style) {
	var val = tinyMCE.getAttrib(elm, attrib);

	if (typeof(style) == 'undefined')
		style = attrib;

	return val == '' ? (st[style] ? st[style].replace('px', '') : '') : val;
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
