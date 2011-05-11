/**
 * $Id$
 *
 * @author Moxiecode
 * @copyright Copyright © 2004-2008, Moxiecode Systems AB, All rights reserved.
 */

(function() {
	tinymce.create('tinymce.plugins.e107BBCodePlugin', {
		init : function(ed, url) {
			var t = this, dialect = ed.getParam('bbcode_dialect', 'punbb').toLowerCase();

			ed.onBeforeSetContent.add(function(ed, o) {
				o.content = t['_' + dialect + '_bbcode2html'](o.content);
			});

			ed.onPostProcess.add(function(ed, o) {
				if (o.set)
					o.content = t['_' + dialect + '_bbcode2html'](o.content);

				if (o.get)
					o.content = t['_' + dialect + '_html2bbcode'](o.content);
			});
		},

		getInfo : function() {
			return {
				longname : 'e107 BBCode Plugin',
				author : 'Moxiecode Systems AB - Modified by e107 Inc',
				authorurl : 'http://tinymce.moxiecode.com',
				infourl : 'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/bbcode',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		},

		// Private methods

		// HTML -> BBCode in PunBB dialect
		_punbb_html2bbcode : function(s) {
			s = tinymce.trim(s);

			function rep(re, str) {
				s = s.replace(re, str);
			};
			
		//	rep(/<td>([\s\S]*?)<\/td>/gim,"[td]$1[/td]\n"); // verified
		//	rep(/<tr>([\s\S]*?)<\/tr>/gim,"[tr]$1[/tr]\n"); // verified
		//	rep(/<table>([\s\S]*?)<\/table>/gim,"[table]$1[/table]\n"); // verified
			
			
			
			return s;
			
			rep(/<div style="text-align: center;">([\s\S]*)<\/div>/gi,"[center]$1[/center]"); // verified
			
			
			rep(/<li>/gi,"[*]"); // verified
			rep(/<\/li>/gi,""); // verified
			rep(/<ul>([\s\S]*?)<\/ul>/gim,"[list]$1[/list]\n"); // verified
			rep(/<ol .* style=\'list-style-type:\s*([\w]*).*\'>([\s\S]*)<\/ol>/gim,"[list=$1]$2[/list]\n"); // verified
			rep(/<ol>([\s\S]*?)<\/ol>/gim,"[list=decimal]$1[/list]\n"); // verified
			rep(/<span style="color: (#?.*?);">([\s\S]*)<\/span>/gi,"[color=$1]$2[/color]"); // verified
		
		

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
		
			rep(/<img.*?style=\"(.*?)\".*?src=\"(.*?)\".*?\/>/gi,"[img style=$1]$2[/img]");
			rep(/<img.*?src=\"(.*?)\".*?\/>/gi,"[img]$1[/img]");
	//		rep(/<img.*?style=\"(.*?)\".*?src=\"(.*?)\" alt=\"(.*?)\" .*?width=\"(.*?)\".*? .*?height=\"(.*?)\" .*?\/>/gi,"[img style=$1;width:$4px; height:$5;]$2[/img]");
			
			rep(/<span class=\"codeStyle\">(.*?)<\/span>/gi,"[code]$1[/code]");
			rep(/<span class=\"quoteStyle\">(.*?)<\/span>/gi,"[quote]$1[/quote]");
			rep(/<strong class=\"codeStyle\">(.*?)<\/strong>/gi,"[code][b]$1[/b][/code]");
			rep(/<strong class=\"quoteStyle\">(.*?)<\/strong>/gi,"[quote][b]$1[/b][/quote]");
			rep(/<em class=\"codeStyle\">(.*?)<\/em>/gi,"[code][i]$1[/i][/code]");
			rep(/<em class=\"quoteStyle\">(.*?)<\/em>/gi,"[quote][i]$1[/i][/quote]");
			rep(/<u class=\"codeStyle\">(.*?)<\/u>/gi,"[code][u]$1[/u][/code]");
			rep(/<u class=\"quoteStyle\">(.*?)<\/u>/gi,"[quote][u]$1[/u][/quote]");
			rep(/<\/(strong|b)>/gi,"[/b]");
			rep(/<(strong|b)>/gi,"[b]");
			rep(/<\/(em|i)>/gi,"[/i]");
			rep(/<(em|i)>/gi,"[i]");
			rep(/<\/u>/gi,"[/u]");
			rep(/<span style=\"text-decoration: ?underline;\">(.*?)<\/span>/gi,"[u]$1[/u]");
			rep(/<u>/gi,"[u]");
			rep(/<blockquote[^>]*>/gi,"[quote]");
			rep(/<\/blockquote>/gi,"[/quote]");
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
		_punbb_bbcode2html : function(s) {
			s = tinymce.trim(s);

			function rep(re, str) {
				s = s.replace(re, str);
			};

			// example: [b] to <strong>
			
			
			rep(/(\[list=.*\])\\*([\s\S]*)(\[\/list])/gim,"<ol>$2</ol>"); // verified
			rep(/(\[list\])\\*([\s\S]*)(\[\/list])/gim,"<ul>$2</ul>");// verified
			rep(/^ *?\[\*\](.*)/gim,"<li>$1</li>"); 
			rep(/\[center\]([\s\S]*)\[\/center\]/gi,"<div style=\"text-align:center\">$1</div>"); // verified
			rep(/\[color=(.*?)\]([\s\S]*)\[\/color\]/gi,"<span style=\"color: $1;\">$2<\/span>"); // verified
			
		//	rep(/\n/gi,"<br \/>"); // breaks lists.. need a regex to exclude everything between [list]...[/list]
		
			rep(/\[b\]/gi,"<strong>");
			rep(/\[\/b\]/gi,"</strong>");
			rep(/\[i\]/gi,"<em>");
			rep(/\[\/i\]/gi,"</em>");
			rep(/\[u\]/gi,"<u>");
			rep(/\[\/u\]/gi,"</u>");
			rep(/\[link=([^\]]+)\](.*?)\[\/link\]/gi,"<a href=\"$1\">$2</a>");
			rep(/\[url\](.*?)\[\/url\]/gi,"<a href=\"$1\">$1</a>");
			rep(/\[img.*?style=(.*?).*?\](.*?)\[\/img\]/gi,"<img style=\"$1\" src=\"$2\" />");
			rep(/\[img.*?\](.*?)\[\/img\]/gi,"<img src=\"$1\" />");
		//	rep(/\[color=(.*?)\](.*?)\[\/color\]/gi,"<font color=\"$1\">$2</font>");
			rep(/\[code\](.*?)\[\/code\]/gi,"<span class=\"codeStyle\">$1</span>&nbsp;");
			rep(/\[quote.*?\](.*?)\[\/quote\]/gi,"<span class=\"quoteStyle\">$1</span>&nbsp;");

			// e107 FIXME!
		
			
			//	rep("/\[list\](.+?)\[\/list\]/is", '<ul class="listbullet">$1</ul>'); 
		

		
		
//

			return s; 
		}
	});

	// Register plugin
	tinymce.PluginManager.add('e107bbcode', tinymce.plugins.e107BBCodePlugin);
})();