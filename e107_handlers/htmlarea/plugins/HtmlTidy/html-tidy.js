// Plugin for htmlArea to run code through the server's HTML Tidy
// By Adam Wright, for The University of Western Australia
//
// Distributed under the same terms as HTMLArea itself.
// This notice MUST stay intact for use (see license.txt).

function HtmlTidy(editor) {
	this.editor = editor;

	var cfg = editor.config;
	var tt = HtmlTidy.I18N;
	var bl = HtmlTidy.btnList;
	var self = this;

	this.onMode = this.__onMode;

	// register the toolbar buttons provided by this plugin
	var toolbar = [];
	for (var i = 0; i < bl.length; ++i) {
		var btn = bl[i];
		if (btn == "html-tidy") {
			var id = "HT-html-tidy";
			cfg.registerButton(id, tt[id], editor.imgURL(btn[0] + ".gif", "HtmlTidy"), true,
					   function(editor, id) {
						   // dispatch button press event
						   self.buttonPress(editor, id);
					   }, btn[1]);
			toolbar.push(id);
		} else if (btn == "html-auto-tidy") {
			var ht_class = {
				id	: "HT-auto-tidy",
				options	: { "Auto-Tidy" : "auto", "Don't Tidy" : "noauto" },
				action	: function (editor) { self.__onSelect(editor, this); },
				refresh	: function (editor) { },
				context	: "body"
			};
			cfg.registerDropdown(ht_class);
		}
	}

	for (var i in toolbar) {
		cfg.toolbar[0].push(toolbar[i]);
	}
};

HtmlTidy._pluginInfo = {
	name          : "HtmlTidy",
	version       : "1.0",
	developer     : "Adam Wright",
	developer_url : "http://blog.hipikat.org/",
	sponsor       : "The University of Western Australia",
	sponsor_url   : "http://www.uwa.edu.au/",
	license       : "htmlArea"
};

HtmlTidy.prototype.__onSelect = function(editor, obj) {
	// Get the toolbar element object
	var elem = editor._toolbarObjects[obj.id].element;

	// Set our onMode event appropriately
	if (elem.value == "auto")
		this.onMode = this.__onMode;
	else
		this.onMode = null;
};

HtmlTidy.prototype.__onMode = function(mode) {
	if ( mode == "textmode" ) {
		this.buttonPress(this.editor, "HT-html-tidy");
	}
};

HtmlTidy.btnList = [
		    null, // separator
		    ["html-tidy"],
		    ["html-auto-tidy"]
];

HtmlTidy.prototype.onGenerateOnce = function() {
	var editor = this.editor;

	var ifr = document.createElement("iframe");
	ifr.name = "htiframe_name";
	var s = ifr.style;
	s.position = "absolute";
	s.width = s.height = s.border = s.left = s.top = s.padding = s.margin = "0px";
	document.body.appendChild(ifr);

	var frm = '<form id="htiform_id" name="htiform_name" method="post" target="htiframe_name" action="';
	frm += _editor_url + 'plugins/HtmlTidy/html-tidy-logic.php';
	frm += '"><textarea name="htisource_name" id="htisource_id">';
	frm += '</textarea></form>';

	var newdiv = document.createElement('div');
	newdiv.style.display = "none";
	newdiv.innerHTML = frm;
	document.body.appendChild(newdiv);
};

HtmlTidy.prototype.buttonPress = function(editor, id) {
	var i18n = HtmlTidy.I18N;

	switch (id) {
	    case "HT-html-tidy":

		var oldhtml = editor.getHTML();

		// Ask the server for some nice new html, based on the old...
		var myform = document.getElementById('htiform_id');
		var txtarea = document.getElementById('htisource_id');
		txtarea.value = editor.getHTML();

		// Apply the 'meanwhile' text, e.g. "Tidying HTML, please wait..."
		editor.setHTML(i18n['tidying']);

		// The returning tidying processing script needs to find the editor
		window._editorRef = editor;

		// ...And send our old source off for processing!
		myform.submit();
		break;
	}
};

HtmlTidy.prototype.processTidied = function(newSrc) {
	editor = this.editor;
	editor.setHTML(newSrc);
};
