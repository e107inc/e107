<?php
/*
+ ----------------------------------------------------------------------------------------------+
|     e107 website system  : http://e107.org
|     Steve Dunstan 2001-2002 : jalist@e107.org
|     Released under the terms and conditions of the GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_plugins/log/e_meta.php,v $
|     $Revision: 1.3 $
|     $Date: 2007-08-14 19:27:22 $
|     $Author: e107steved $
+-----------------------------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

if (isset($pref['statActivate']) && $pref['statActivate'])
{
	if(!$pref['statCountAdmin'] && ADMIN)
	{
		/* don't count admin visits */
	}
	else
	{
		require_once(e_PLUGIN."log/consolidate.php");
		$err_flag = '';
		if (defined("ERR_PAGE_ACTIVE"))
		{	// We've got an error - set a flag to log it
		  $err_flag = "&err_direct=".ERR_PAGE_ACTIVE;
		  if (is_numeric(e_QUERY)) $err_flag .= '/'.substr(e_QUERY,0,10);		// This should pick up the error code - and limit numeric length to upset the malicious
		  $err_flag .= "&err_referer=".$_SERVER['HTTP_REFERER'];
		}
		echo "<script type='text/javascript'>\n";
		echo "<!--\n";
		echo "document.write( '<link rel=\"stylesheet\" type=\"text/css\" href=\"".e_PLUGIN_ABS."log/log.php?referer=' + ref + '&color=' + colord + '&eself=' + eself + '&res=' + res + '{$err_flag}\">' );\n";
		echo "// -->\n";
		echo "</script>\n\n";
	}
}



?>