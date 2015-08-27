/**
 * plugin.js
 *
 * Copyright, Moxiecode Systems AB
 * Released under LGPL License.
 *
 * License: http://www.tinymce.com/license
 * Contributing: http://www.tinymce.com/contributing
 */

/*global tinymce:true */

(function() {
	tinymce.create('tinymce.plugins.e107Plugin', {
		
		init : function(ed,url) {

			var t = this, dialect = ed.getParam('bbcode_dialect', 'e107').toLowerCase();


			ed.on('beforeSetContent', function(e) {
				e.content = t['_' + dialect + '_bbcode2html'](e.content, url);
			});



			ed.on('postProcess', function(e) {

          //      console.log(e);
          //      alert(e.content); // remove comment to test Firefox issue: http://www.tinymce.com/develop/bugtracker_view.php?id=7655

				if (e.set) {
					e.content = t['_' + dialect + '_bbcode2html'](e.content, url);
				}

				if (e.get) {
					e.content = t['_' + dialect + '_html2bbcode'](e.content, url);
				}


			});
			
		/*
		// Emoticons 
			ed.addButton('e107-bbcode', {
				text: 'bbcode',
				icon: 'emoticons',
				onclick: function() {
					// Open window
										
					ed.windowManager.open({
						title: 'Example plugin',
						body: [
							{type: 'textbox', name: 'code', label: 'BbCode'},
                            {type: 'textbox', name: 'parm', label: 'Parameters'}
						],
						onsubmit: function(e) {
							// Insert content when the window form is submitted
							ed.insertContent('Title: ' + e.data.title);
						}
					});
				}
			});
			
			*/
			// Media Manager Button 
			ed.addButton('e107-image', {
				text: '',
				title: 'Insert Media-Manager Image',
				icon: 'image',
				onclick: function() {
					
					ed.windowManager.open({
						title: 'Media Manager',
						url: url + '/mediamanager.php?image',
						width: 1050,
						height: 650
					
					});
				}
			});
			
					// Media Manager Button 
			ed.addButton('e107-video', {
				text: '',
				title: 'Insert Media-Manager Video',
				icon: 'media',
				resizable : 'no',
                inline : 'yes',
                close_previous : 'no',
                
				onclick: function() {
					
					ed.windowManager.open({
						title: 'Media Manager',
						url: url + '/mediamanager.php?video',
						width: 1050,
						height: 650
					
					});
				}
			});
			
			ed.addButton('e107-glyph', {
				text: '',
				title: 'Insert Media-Manager Glyph',
				icon: 'charmap',
				onclick: function() {
					
					ed.windowManager.open({
						title: 'Media Manager',
						url: url + '/mediamanager.php?glyph',
						width: 1050,
						height: 650
					
					});
				}
			});
			
			
		},

		getInfo: function() {
			return {
				longname: 'e107 Parser Plugin',
				author: 'Moxiecode Systems AB',
				authorurl: 'http://www.tinymce.com',
				infourl: 'http://www.tinymce.com/wiki.php/Plugin:bbcode'
			};
		},

		// Private methods

		// HTML -> BBCode in PunBB dialect
		_e107_html2bbcode : function(s, url) {
			s = tinymce.trim(s);

		//	return s;
		
			var p = $.ajax({
					type: "POST",
					url: url + "/parser.php",
					data: { content: s, mode: 'tobbcode' },
					async       : false,

					dataType: "html",
					success: function(html) {
				      return html;
				    }
				}).responseText;

			return p;

			
		},

		// BBCode -> HTML from PunBB dialect
		_e107_bbcode2html : function(s, url) {
			s = tinymce.trim(s);

		//	return s;

			var p = $.ajax({
					type: "POST",
					url: url + "/parser.php",
					data: { content: s, mode: 'tohtml' },
					async       : false,

					dataType: "html",
					success: function(html) {
				      return html;
				    }
				}).responseText;

				return p;

			
		}
	});

	// Register plugin
	tinymce.PluginManager.add('e107', tinymce.plugins.e107Plugin);
})();