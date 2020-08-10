<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2015 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Plugin Trackback
 *
 *
*/
if (!defined('e107_INIT')) { exit; }

if(e107::isInstalled('trackback') && !empty($pref['trackbackEnabled']) && USER_AREA)
{
	echo "<link rel='pingback' href='".SITEURLBASE.e_PLUGIN_ABS."trackback/trackback.php' />";
}

