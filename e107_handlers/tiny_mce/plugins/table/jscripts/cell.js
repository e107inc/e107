function init() {
	var inst = tinyMCE.selectedInstance;
	var tdElm = tinyMCE.getParentElement(inst.getFocusElement(), "td,th");
	var formObj = document.forms[0];
	var st = tinyMCE.parseStyle(tdElm.style.cssText);

	// Get table cell data
	var celltype = tdElm.nodeName.toLowerCase();
	var align = tinyMCE.getAttrib(tdElm, 'align');
	var valign = tinyMCE.getAttrib(tdElm, 'valign');
	var width = tinyMCE.getAttrib(tdElm, 'width');
	var height = tinyMCE.getAttrib(tdElm, 'height');
	var className = tinyMCE.getVisualAidClass(tinyMCE.getAttrib(tdElm, 'class'), false);
	var bordercolor = tinyMCE.getAttrib(tdElm, 'bordercolor');
	var bgcolor = tinyMCE.getAttrib(tdElm, 'bgcolor');
	var backgroundimage = getStyle(tdElm, st, 'background', 'background-image').replace(new RegExp("url\\('?([^']*)'?\\)", 'gi'), "$1");;
	var id = tinyMCE.getAttrib(tdElm, 'id');
	var lang = tinyMCE.getAttrib(tdElm, 'lang');
	var dir = tinyMCE.getAttrib(tdElm, 'dir');

	// Setup form
	addClassesToList('class', 'table_cell_styles');
	formObj.bordercolor.value = bordercolor;
	formObj.bgcolor.value = bgcolor;
	formObj.backgroundimage.value = backgroundimage;
	formObj.width.value = width;
	formObj.height.value = height;
	formObj.id.value = id;
	formObj.lang.value = lang;
	formObj.style.value = tinyMCE.serializeStyle(st);
	selectByValue(formObj, 'align', align);
	selectByValue(formObj, 'valign', valign);
	selectByValue(formObj, 'class', className);
	selectByValue(formObj, 'celltype', celltype);
	selectByValue(formObj, 'dir', dir);

	// Resize some elements
	if (isVisible('backgroundimagebrowser'))
		document.getElementById('backgroundimage').style.width = '180px';

	updateColor('bordercolor_pick', 'bordercolor');
	updateColor('bgcolor_pick', 'bgcolor');
}

function updateAction() {
	var inst = tinyMCE.selectedInstance;
	var tdElm = tinyMCE.getParentElement(inst.getFocusElement(), "td,th");
	var trElm = tinyMCE.getParentElement(inst.getFocusElement(), "tr");
	var tableElm = tinyMCE.getParentElement(inst.getFocusElement(), "table");
	var formObj = document.forms[0];

	inst.execCommand('mceBeginUndoLevel');

	switch (getSelectValue(formObj, 'action')) {
		case "cell":
			updateCell(tdElm);
			break;

		case "row":
			var cell = trElm.firstChild;

			do {
				cell = updateCell(cell, true);
			} while ((cell = nextCell(cell)));

			break;

		case "all":
			var rows = tableElm.getElementsByTagName("tr");

			for (var i=0; i<rows.length; i++) {
				var cell = rows[i].firstChild;

				do {
					cell = updateCell(cell, true);
				} while ((cell = nextCell(cell)));
			}

			break;
	}

	tinyMCE.handleVisualAid(inst.getBody(), true, inst.visualAid, inst);
	tinyMCE.triggerNodeChange();
	inst.execCommand('mceEndUndoLevel');
	tinyMCEPopup.close();
}

function nextCell(elm) {
	while ((elm = elm.nextSibling)) {
		if (elm.nodeName == "TD" || elm.nodeName == "TH")
			return elm;
	}

	return null;
}

function updateCell(td, skip_id) {
	var inst = tinyMCE.selectedInstance;
	var formObj = document.forms[0];
	var curCellType = td.nodeName.toLowerCase();
	var celltype = getSelectValue(formObj, 'celltype');
	var doc = inst.getDoc();

	if (!skip_id)
		td.setAttribute('id', formObj.id.value);

	td.setAttribute('align', formObj.align.value);
	td.setAttribute('vAlign', formObj.valign.value);
	td.setAttribute('width', formObj.width.value);
	td.setAttribute('height', formObj.height.value);
	td.setAttribute('borderColor', formObj.bordercolor.value);
	td.setAttribute('bgColor', formObj.bgcolor.value);
	td.setAttribute('lang', formObj.lang.value);
	td.setAttribute('dir', getSelectValue(formObj, 'dir'));
	td.setAttribute('style', tinyMCE.serializeStyle(tinyMCE.parseStyle(formObj.style.value)));
	tinyMCE.setAttrib(td, 'class', getSelectValue(formObj, 'class'));

	if (curCellType != celltype) {
		// changing to a different node type
		var newCell = doc.createElement(celltype);

		for (var c=0; c<td.childNodes.length; c++) {
			newCell.appendChild(td.childNodes[c].cloneNode(1));
		}

		for (var a=0; a<td.attributes.length; a++) {
			var attr = td.attributes[a];
			newCell.setAttribute(attr.name, attr.value);
		}

		td.parentNode.replaceChild(newCell, td);
		td = newCell;

		return newCell;
	}

	return td;
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
