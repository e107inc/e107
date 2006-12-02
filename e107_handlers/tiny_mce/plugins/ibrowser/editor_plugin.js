// Import theme specific language pack
// $Source: /cvs_backup/e107_0.8/e107_handlers/tiny_mce/plugins/ibrowser/editor_plugin.js,v $
// $Revision: 1.1.1.1 $
// $Date: 2006-12-02 04:34:14 $
// $Author: mcfly_e107 $

tinyMCE.importPluginLanguagePack('ibrowser', 'en,es,da,de,fr,nl,pl,sv,ru');

// Returns the HTML contents of the ibrowser control.

var TinyMCE_ibrowserPlugin = {
	getInfo : function() {
		return {
			longname : 'ibrowser',
			author : 'Your name',
			authorurl : '',
			infourl : '',
			version : "1.1"
		};
	},

	getControlHTML : function(cn) {
		switch (cn) {
			case "ibrowser":
				return tinyMCE.getButtonHTML(cn, 'lang_ibrowser_desc', '{$pluginurl}/images/ibrowser.gif', 'mceBrowseImage', true);
		}

		return "";
	},


	execCommand : function(editor_id, element, command, user_interface, value) {
		// Handle commands
		switch (command) {
		case "mceBrowseImage":
			var template = new Array();

			template['file'] = '../../plugins/ibrowser/ibrowser.php'; // Relative to theme location
			template['width'] = 480;
			template['height'] = 670;

			var src = "", alt = "", border = "", hspace = "", vspace = "", width = "", height = "", align = "";
            var margin_left = "";
			var margin_right = "";
			var margin_top = "";
			var margin_bottom = "";

			if (tinyMCE.selectedElement != null && tinyMCE.selectedElement.nodeName.toLowerCase() == "img")
				tinyMCE.imgElement = tinyMCE.selectedElement;

            if (tinyMCE.imgElement) {
                src = tinyMCE.imgElement.getAttribute('src') ? tinyMCE.imgElement.getAttribute('src') : "";
                alt = tinyMCE.imgElement.getAttribute('alt') ? tinyMCE.imgElement.getAttribute('alt') : "";
			}
            /*

                border = tinyMCE.imgElement.style.border ? tinyMCE.imgElement.style.border : "";
                hspace = tinyMCE.imgElement.getAttribute('hspace') ? tinyMCE.imgElement.getAttribute('hspace') : "";
                vspace = tinyMCE.imgElement.getAttribute('vspace') ? tinyMCE.imgElement.getAttribute('vspace') : "";
                width = tinyMCE.imgElement.style.width ? tinyMCE.imgElement.style.width.replace('px','') : "";
                height = tinyMCE.imgElement.style.height ? tinyMCE.imgElement.style.height.replace('px','') : "";
                align = tinyMCE.imgElement.getAttribute('align') ? tinyMCE.imgElement.getAttribute('align') : "";

                margin_left = tinyMCE.imgElement.style.marginLeft ? tinyMCE.imgElement.style.marginLeft.replace('px','') : "";
                margin_right = tinyMCE.imgElement.style.marginRight ? tinyMCE.imgElement.style.marginRight.replace('px','') : "";
                margin_top = tinyMCE.imgElement.style.marginTop ? tinyMCE.imgElement.style.marginTop.replace('px','') : "";
                margin_bottom = tinyMCE.imgElement.style.marginBottom ? tinyMCE.imgElement.style.marginBottom.replace('px','') : "";

                // Fix for drag-drop/copy paste bug in Mozilla
                mceRealSrc = tinyMCE.imgElement.getAttribute('mce_real_src') ? tinyMCE.imgElement.getAttribute('mce_real_src') : "";
                if (mceRealSrc != "")
                    src = mceRealSrc;

           //       src = eval(tinyMCE.settings['urlconvertor_callback'] + "(src, tinyMCE.imgElement, true);");
            }
*/
				tinyMCE.openWindow(template, {editor_id : editor_id, src : src, alt : alt, border : border, hspace : hspace, vspace : vspace, width : width, height : height, align : align});
				return true;
	}

   		// Pass to next handler in chain
		return false;
	}

};


tinyMCE.addPlugin("ibrowser", TinyMCE_ibrowserPlugin);




