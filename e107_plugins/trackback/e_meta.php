<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2008 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Plugin administration - newsfeeds
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/trackback/e_meta.php,v $
 * $Revision: 1.4 $
 * $Date: 2008-12-20 22:32:36 $
 * $Author: e107steved $
 *
*/
if (!defined('e107_INIT')) { exit; }

if(plugInstalled('trackback') && isset($pref['trackbackEnabled']))
{
	echo "<link rel='pingback' href='".SITEURLBASE.e_PLUGIN_ABS."trackback/trackback.php' />";
}

?>