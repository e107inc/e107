// Import theme specific language pack 
tinyMCE.importPluginLanguagePack('ibrowser', 'uk,de');

// Returns the HTML contents of the ibrowser control.

function TinyMCE_ibrowser_getControlHTML(control_name) {
	switch (control_name) {
		case "ibrowser":
			return '<img id="{$editor_id}_ibrowser" src="{$pluginurl}/images/ibrowser.gif" title="{$lang_ibrowser_desc}" width="20" height="20" class="mceButtonNormal" onmouseover="tinyMCE.switchClass(this,\'mceButtonOver\');" onmouseout="tinyMCE.restoreClass(this);" onmousedown="tinyMCE.restoreAndSwitchClass(this,\'mceButtonDown\');" onclick="tinyMCE.execInstanceCommand(\'{$editor_id}\',\'mceBrowseImage\');">';
	}

	return "";
}

// Executes the mceBrowseImage command.

function TinyMCE_ibrowser_execCommand(editor_id, element, command, user_interface, value) {
	// Handle commands
	switch (command) {
		case "mceBrowseImage":
			var template = new Array();

			template['file'] = '../../plugins/ibrowser/ibrowser.php'; // Relative to theme location
			template['width'] = 480;
			template['height'] = 670;

			var src = "", alt = "", border = "", hspace = "", vspace = "", width = "", height = "", align = "";

			if (tinyMCE.selectedElement != null && tinyMCE.selectedElement.nodeName.toLowerCase() == "img")
				tinyMCE.imgElement = tinyMCE.selectedElement;

			if (tinyMCE.imgElement) {
				src = tinyMCE.imgElement.getAttribute('src') ? tinyMCE.imgElement.getAttribute('src') : "";
				alt = tinyMCE.imgElement.getAttribute('alt') ? tinyMCE.imgElement.getAttribute('alt') : "";
				border = tinyMCE.imgElement.getAttribute('border') ? tinyMCE.imgElement.getAttribute('border') : "";
				hspace = tinyMCE.imgElement.getAttribute('hspace') ? tinyMCE.imgElement.getAttribute('hspace') : "";
				vspace = tinyMCE.imgElement.getAttribute('vspace') ? tinyMCE.imgElement.getAttribute('vspace') : "";
				width = tinyMCE.imgElement.getAttribute('width') ? tinyMCE.imgElement.getAttribute('width') : "";
				height = tinyMCE.imgElement.getAttribute('height') ? tinyMCE.imgElement.getAttribute('height') : "";
				align = tinyMCE.imgElement.getAttribute('align') ? tinyMCE.imgElement.getAttribute('align') : "";

				// Fix for drag-drop/copy paste bug in Mozilla
				mceRealSrc = tinyMCE.imgElement.getAttribute('mce_real_src') ? tinyMCE.imgElement.getAttribute('mce_real_src') : "";
				if (mceRealSrc != "")
					src = mceRealSrc;

				src = eval(tinyMCE.settings['urlconvertor_callback'] + "(src, tinyMCE.imgElement, true);");
			}
				tinyMCE.openWindow(template, {editor_id : editor_id, src : src, alt : alt, border : border, hspace : hspace, vspace : vspace, width : width, height : height, align : align});
				return true;
	}

	// Pass to next handler in chain
	return false;
}