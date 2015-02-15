/**
 * $Id$
 *
 * @author Moxiecode
 * @copyright Copyright © 2004-2008, Moxiecode Systems AB, All rights reserved.
 */

(function() {
	tinymce.create('tinymce.plugins.e107BBCodePlugin', {
		init : function(ed, url) {
			
			// Bootstrap 
			ed.addCommand('mceBoot', function() {
				ed.windowManager.open({
					file : url + '/dialog.php',
					width : 900 , // + parseInt(ed.getLang('e107bbcode.delta_width', 0)),
					height : 450, //  + parseInt(ed.getLang('e107bbcode.delta_height', 0)),
					inline : 1
				}, {
					plugin_url : url, // Plugin absolute URL
					some_custom_arg : 'custom arg' // Custom argument
				});
			});

			// Register  button
			ed.addButton('bootstrap', {
				title : 'Insert Bootstrap Elements',
				cmd : 'mceBoot',
				image : url + '/img/bootstrap.png'
			});
			
			// e107 Bbcode 
			ed.addCommand('mcee107', function() {
				ed.windowManager.open({
					file : url + '/dialog.php?bbcode',
					width : 900 , // + parseInt(ed.getLang('e107bbcode.delta_width', 0)),
					height : 450, //  + parseInt(ed.getLang('e107bbcode.delta_height', 0)),
					inline : 1
				}, {
					plugin_url : url, // Plugin absolute URL
					some_custom_arg : 'custom arg' // Custom argument
				});
			});

			// Register  button
			ed.addButton('e107bbcode', {
				title : 'Insert e107 Bbcode',
				cmd : 'mcee107',
				image : url + '/img/bbcode.png'
			});
			
			

			// Add a node change handler, selects the button in the UI when a image is selected
	//		ed.onNodeChange.add(function(ed, cm, n) {
	//			cm.setActive('example', n.nodeName == 'IMG');
	//		});
			
			
			// ------------
			
			
			var t = this, dialect = ed.getParam('bbcode_dialect', 'e107').toLowerCase();

			ed.onBeforeSetContent.add(function(ed, o) {
		
				o.content = t['_' + dialect + '_bbcode2html'](o.content,url);
			});

			ed.onPostProcess.add(function(ed, o) {
				if (o.set)
					o.content = t['_' + dialect + '_bbcode2html'](o.content,url);

				if (o.get)
					o.content = t['_' + dialect + '_html2bbcode'](o.content,url);
			});
		},

		getInfo : function() {
			return {
				longname : 'e107 BBCode Plugin',
				author : 'Moxiecode Systems AB - Modified by e107 Inc',
				authorurl : 'http://e107.org',
				infourl : 'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/bbcode',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		},

		// Private methods

		// HTML -> BBCode in PunBB dialect
		_e107_html2bbcode : function(s,url) {
			s = tinymce.trim(s);
			
			
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
			
			
			

			function rep(re, str) {
				s = s.replace(re, str);
			}
			
		//	return s;
				
			rep(/<table(.*)>/gim, "[table]");
			rep(/<\/table>/gim, "[/table]");
			rep(/<td>/gim, "[td]");
			rep(/<\/td>/gim, "[/td]");
			rep(/<tr>/gim, "[tr]");
			rep(/<\/tr>/gim, "[/tr]");
			rep(/<tbody>/gim, "[tbody]");
			rep(/<\/tbody>/gim, "[/tbody]");
			
			
			rep(/<div style="text-align: center;">([\s\S]*)<\/div>/gi,"[center]$1[/center]"); // verified
					
			rep(/<li>/gi,		"[*]"); // verified
			rep(/<\/li>/gi,		""); // verified
			rep(/<ul>([\s\S]*?)<\/ul>\n/gim,	"[list]$1[/list]"); // verified
			
			rep(/<ol .* style=\'list-style-type:\s*([\w]*).*\'>([\s\S]*)<\/ol>/gim,"[list=$1]$2[/list]\n"); // verified
			rep(/<ol>([\s\S]*?)<\/ol>/gim,"[list=decimal]$1[/list]\n"); // verified
			rep(/<span style="color: (#?.*?);">([\s\S]*)<\/span>/gi,"[color=$1]$2[/color]"); // verified
			rep(/<h2>/gim,		"[h]"); // verified
			rep(/<\/h2>/gim, 	"[/h]"); // verified
			

			// example: <strong> to [b]
			rep(/<a.*?href=\"(.*?)\".*?>(.*?)<\/a>/gi,"[link=$1]$2[/link]");
			rep(/<font.*?color=\"(.*?)\".*?class=\"codeStyle\".*?>(.*?)<\/font>/gi,"[code][color=$1]$2[/color][/code]");
			rep(/<font.*?color=\"(.*?)\".*?class=\"quoteStyle\".*?>(.*?)<\/font>/gi,"[quote][color=$1]$2[/color][/quote]");
			rep(/<font.*?class=\"codeStyle\".*?color=\"(.*?)\".*?>(.*?)<\/font>/gi,"[code][color=$1]$2[/color][/code]");
			rep(/<font.*?class=\"quoteStyle\".*?color=\"(.*?)\".*?>(.*?)<\/font>/gi,"[quote][color=$1]$2[/color][/quote]");
			rep(/<span style=\"color: ?(.*?);\">(.*?)<\/span>/gi,"[color=$1]$2[/color]");
			rep(/<font.*?color=\"(.*?)\".*?>(.*?)<\/font>/gi,"[color=$1]$2[/color]");
			rep(/<span style=\"font-size:(.*?);\">(.*?)<\/span>/gi,"[size=$1]$2[/size]");
			rep(/<font>(.*?)<\/font>/gi,"$1");
		
		//	rep(/<img.*?style=\"(.*?)\".*?src=\"(.*?)\".*?\/>/gi,"[img style=$1]$2[/img]");
		
		
			// New Image Handler // verified
		//	rep(/<img(?:\s*)?(?:style="(.*)")?\s?(?:src="([^;"]*)")(?:\s*)?(?:width="([\d]*)")?\s*(?:height="([\d]*)")?(?:\s*)?(?:alt="(\S*)")? (?:\s*)?\/>/gi,"[img style=$1;width:$4px;height:$5px]$2[/img]" );
		
		//rep(/<img(?:\s*)?(?:style="(.*)")?\s?(?:src="([\S ]*)")(?:\s*)?(?:alt="(\S*)")?(?:\s*)?(?:width="([\d]*)")?\s*(?:height="([\d]*)")?(?:\s*)?\/>/gi,"[img style=$1;width:$4px;height:$5px]$2[/img]" )
		rep(/<img(?:\s*)?(?:style="([^"]*)")?\s?(?:src="([^"]*)")(?:\s*)?(?:alt="(\S*)")?(?:\s*)?(?:width="([\d]*)")?\s*(?:height="([\d]*)")?(?:\s*)?\/>/gm,"[img style=width:$4px;height:$5px;$1]$2[/img]" );
		rep(/;width:px;height:px/gi, ""); // Img cleanup. 
		//	rep(/<img\s*?src=\"(.*?)\".*?\/>/gi,"[img]$1[/img]");
			
			rep(/<blockquote[^>]*>/gi,"[blockquote]");
			rep(/<\/blockquote>/gi,"[/blockquote]");
			
			rep(/<code[^>]*>/gi,"[code]");
			rep(/<\/code>/gi,"[/code]");
					
		//	rep(/<span class=\"codeStyle\">(.*?)<\/span>/gi,"[code]$1[/code]");
		//	rep(/<span class=\"quoteStyle\">(.*?)<\/span>/gi,"[quote]$1[/quote]");
		//	rep(/<strong class=\"codeStyle\">(.*?)<\/strong>/gi,"[code][b]$1[/b][/code]");
		//	rep(/<strong class=\"quoteStyle\">(.*?)<\/strong>/gi,"[quote][b]$1[/b][/quote]");
		//	rep(/<em class=\"codeStyle\">(.*?)<\/em>/gi,"[code][i]$1[/i][/code]");
		//	rep(/<em class=\"quoteStyle\">(.*?)<\/em>/gi,"[quote][i]$1[/i][/quote]");
		//	rep(/<u class=\"codeStyle\">(.*?)<\/u>/gi,"[code][u]$1[/u][/code]");
		//	rep(/<u class=\"quoteStyle\">(.*?)<\/u>/gi,"[quote][u]$1[/u][/quote]");
		
			rep(/<\/(strong|b)>/gi,"[/b]");
			rep(/<(strong|b)>/gi,"[b]");
			rep(/<\/(em|i)>/gi,"[/i]");
			rep(/<(em|i)>/gi,"[i]");
			rep(/<\/u>/gi,"[/u]");
			rep(/<span style=\"text-decoration: ?underline;\">(.*?)<\/span>/gi,"[u]$1[/u]");
			rep(/<u>/gi,"[u]");
		
			
			// Compromise - but BC issues for sure. 
		//	rep(/<br \/>/gi,"[br]");
		//	rep(/<br\/>/gi,"[br]");
		//	rep(/<br>/gi,"[br]");
		
			 rep(/<br \/>/gi,"\n");
			 rep(/<br\/>/gi,"\n");
			 rep(/<br>/gi,"\n");
			
			
			rep(/<p>/gi,"");
			rep(/<\/p>/gi,"\n");
			rep(/&nbsp;/gi," ");
			rep(/&quot;/gi,"\"");
			rep(/&lt;/gi,"<");
			rep(/&gt;/gi,">");
			rep(/&amp;/gi,"&");
			
			// e107
		
			
			return s; 
		},
		
		// BBCode -> HTML from PunBB dialect
		_e107_bbcode2html : function(s,url) {
			s = tinymce.trim(s);
	
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
			
			
			return s;
			

			function rep(re, str) {
				s = s.replace(re, str);
			}
			
	
			// example: [b] to <strong>
			
		//	rep(/<ul>(\r|\n)?/gim, "<ul>"); // remove line-breaks
		//	rep(/<\/li>(\r|\n)?/gim, "</li>"); // remove line-breaks
		//	rep(/<\/ul>(\r|\n)?/gim, "</ul>"); // remove line-breaks
		
			rep(/\[table]/gim, "<table>");
			rep(/\[\/table]/gim, "</table>");
			rep(/\[td]/gim, "<td>");
			rep(/\[\/td]/gim, "</td>");
			rep(/\[tr]/gim, "<tr>");
			rep(/\[\/tr]/gim, "</tr>");
			rep(/\[tbody]/gim, "<tbody>");
			rep(/\[\/tbody]/gim, "</tbody>");
			
			rep(/\[h]/gim,		"<h2>"); // verified
			rep(/\[\/h]/gim, 	"</h2>"); // verified
			
			rep(/\[list](?:\n)/gim,		"<ul>\n"); // verified
		//	rep(/\[list]/gim,		"<ul>"); // verified

			rep(/\[\/list](?:\n)?/gim, 	"</ul>\n"); // verified
			rep(/^ *?(?:\*|\[\*\])([^\*[]*)/gm,"<li>$1</li>\n"); 
		//	return s;
		//	rep(/(\[list=.*\])\\*([\s\S]*)(\[\/list])(\n|\r)/gim,"<ol>$2</ol>"); // verified
		//	rep(/(\[list\])\\*([\s\S]*)(\[\/list])(\n|\r)?/gim,"<ul>$2</ul>");// verified
		
						
			rep(/\[center\]([\s\S]*)\[\/center\]/gi,"<div style=\"text-align:center\">$1</div>"); // verified
			rep(/\[color=(.*?)\]([\s\S]*)\[\/color\]/gi,"<span style=\"color: $1;\">$2<\/span>"); // verified
			
		//	rep(/\[br]/gi,"<br />"); // compromise
				
			rep(/\[blockquote\]/gi,"<blockquote>");
			rep(/\[\/blockquote\]/gi,"</blockquote>");
			
			rep(/\[code\]/gi,"<code>");
			rep(/\[\/code\]/gi,"</code>");
	
		//rep( /(?<!(\[list]))\r|\n/gim,"<br />" )
		
		
			rep(/\[b\]/gi,"<strong>");
			rep(/\[\/b\]/gi,"</strong>");
			rep(/\[i\]/gi,"<em>");
			rep(/\[\/i\]/gi,"</em>");
			rep(/\[u\]/gi,"<u>");
			rep(/\[\/u\]/gi,"</u>");
			rep(/\[link=([^\]]+)\](.*?)\[\/link\]/gi,"<a href=\"$1\">$2</a>");
			rep(/\[url\](.*?)\[\/url\]/gi,"<a href=\"$1\">$1</a>");
		//	rep(/\[img.*?style=(.*?).*?\](.*?)\[\/img\]/gi,"<img style=\"$1\" src=\"$2\" />");
		
			// When Width and Height are present: 
			rep(/\[img\s*?style=(?:width:(\d*)px;height:(\d*)px;)([^\]]*)]([\s\S]*?)\[\/img]/gm, "<img style=\"$3\" src=\"$4\" alt=\"\" width=\"$1\" height=\"$2\" />");  
		
			// No width/height but style is present
			rep(/\[img\s*?style=([^\]]*)]([\s\S]*?)\[\/img]/gi,"<img style=\"$1\" src=\"$2\" />");	
			
			rep(/\[img\](.*?)\[\/img\]/gi,"<img src=\"$1\" />");
		//	rep(/\[color=(.*?)\](.*?)\[\/color\]/gi,"<font color=\"$1\">$2</font>");
		//	rep(/\[code\](.*?)\[\/code\]/gi,"<span class=\"codeStyle\">$1</span>&nbsp;");
		//	rep(/\[quote.*?\](.*?)\[\/quote\]/gi,"<span class=\"quoteStyle\">$1</span>&nbsp;");
			
		//	rep(/<br \/>/gm, "<br />\n");
			rep(/(\r|\n)$/gim,"<br />"); 
		//	rep(/(\r|\n)/gim,"<br />\n"); // this will break bullets. 
		

			// e107 FIXME!
		
			
			//	rep("/\[list\](.+?)\[\/list\]/is", '<ul class="listbullet">$1</ul>'); 
		

		
		
//

			return s; 
		}
	});

	// Register plugin
	tinymce.PluginManager.add('e107bbcode', tinymce.plugins.e107BBCodePlugin);
})();