<?php
/*
* e107 website system
*
* Copyright 2001-2015 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* Site access logging
*

*
*/
if (!defined('e107_INIT')) { exit; }


if (vartrue($pref['statActivate']))
{
	
	if(!$pref['statCountAdmin'] && ADMIN) // don't count admin visits
	{
		return;
	}
	
	// Page to log here
	
	if(USER_AREA)
	{
		require_once(e_PLUGIN.'log/consolidate.php');
		$lgc = new logConsolidate();
		$lgc->run();

		$err_flag = '';
		if (defined('ERR_PAGE_ACTIVE'))
		{	// We've got an error - set a flag to log it
			$err_flag = "&err_direct=".ERR_PAGE_ACTIVE;
			if (is_numeric(e_QUERY)) $err_flag .= '/'.substr(e_QUERY,0,10);		// This should pick up the error code - and limit numeric length to upset the malicious
			$err_flag .= "&err_referer=".$_SERVER['HTTP_REFERER'];
		}
				
	}

	if(USER_AREA)
	{
		$logJS = "

		$(function() {

			function rstr2b64(input)
			{
				var b64pad  = \"=\"; /* base-64 pad character. \"=\" for strict RFC compliance   */
				var tab = \"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/\";
				var output = \"\";
				var len = input.length;
				for(var i = 0; i < len; i += 3)
				{
					var triplet = (input.charCodeAt(i) << 16)
			                | (i + 1 < len ? input.charCodeAt(i+1) << 8 : 0)
			                | (i + 2 < len ? input.charCodeAt(i+2)      : 0);
					for(var j = 0; j < 4; j++)
					{
						if(i * 8 + j * 6 > input.length * 8) output += b64pad;
						else output += tab.charAt((triplet >>> 6*(3-j)) & 0x3F);
					}
				}
				return output;
			}





			var ref			=\"\"+escape(top.document.referrer);
			var eeself       = escape(window.location.href);

			var colord 		= window.screen.colorDepth;
			var res 		= window.screen.width + \"x\" + window.screen.height;
			var logString 	= 'referer=' + ref + '&colour=' + colord + '&eself=' + eeself + '&res=' + res + '".$err_flag."';
			logString 		= rstr2b64(logString);

			var url = '".SITEURLBASE.e_PLUGIN_ABS."log/log.php';
			var dataText = 'lv='+logString;

			$.ajax({
				type: 'get',
				url: url,
				data: {'lv' :logString},
				success: function(e) {
					if(e)
					{
				//		alert(e);
					}
				}
			});
		});
		";




		e107::js('footer-inline', $logJS, 'jquery');
	}
}


