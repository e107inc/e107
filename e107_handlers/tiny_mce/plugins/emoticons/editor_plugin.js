
tinyMCE.importPluginLanguagePack('emoticons', 'en');

// Plucin static class
var TinyMCE_emoticonsPlugin = {
	getInfo : function() {
		return {
			longname : 'emoticons',
			author : 'CaMer0n',
			authorurl : 'http://e107coders.org',
			infourl : 'http://www.e107.org',
			version : tinyMCE.majorVersion + "." + tinyMCE.minorVersion
		};
	},

	/**
	 * Returns the HTML contents of the emoticons control.
	 */
	getControlHTML : function(cn) {
		switch (cn) {
			case "emoticons":
				return tinyMCE.getButtonHTML(cn, 'lang_emoticons_desc', '{$pluginurl}/images/emoticons.png', 'mceEmotion');
		}

		return "";
	},

	/**
	 * Executes the mceEmotion command.
	 */
	execCommand : function(editor_id, element, command, user_interface, value) {
		// Handle commands
		switch (command) {
			case "mceEmotion":
				var template = new Array();

				template['file'] = '../../plugins/emoticons/emoticons.php'; // Relative to theme
				template['width'] = 200;
				template['height'] = 200;

				// Language specific width and height addons
				template['width'] += tinyMCE.getLang('lang_emoticons_delta_width', 0);
				template['height'] += tinyMCE.getLang('lang_emoticons_delta_height', 0);

				tinyMCE.openWindow(template, {editor_id : editor_id, inline : "yes"});

				return true;
		}

		// Pass to next handler in chain
		return false;
	}
};

// Register plugin
tinyMCE.addPlugin('emoticons', TinyMCE_emoticonsPlugin);