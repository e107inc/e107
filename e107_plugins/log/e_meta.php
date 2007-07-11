<?php
/*
+ ----------------------------------------------------------------------------------------------+
|     e107 website system  : http://e107.org
|     Steve Dunstan 2001-2002 : jalist@e107.org
|     Released under the terms and conditions of the GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_plugins/log/e_meta.php,v $
|     $Revision: 1.2 $
|     $Date: 2007-07-11 13:51:14 $
|     $Author: sweetas $
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
		echo "<script type='text/javascript'>\n";
		echo "<!--\n";
		echo "document.write( '<link rel=\"stylesheet\" type=\"text/css\" href=\"".e_PLUGIN_ABS."log/log.php?referer=' + ref + '&color=' + colord + '&eself=' + eself + '&res=' + res + '\">' );\n";
		echo "// -->\n";
		echo "</script>\n\n";
	}
}



?>