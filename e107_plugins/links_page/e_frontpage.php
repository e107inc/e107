<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/links_page/e_frontpage.php,v $
 * $Revision: 1.3 $
 * $Date: 2009-11-18 01:05:46 $
 * $Author: e107coders $
 */

if (!defined('e107_INIT')) { exit; }

if (file_exists(e_PLUGIN."links_page/languages/".e_LANGUAGE.".php")) {
	include_once(e_PLUGIN."links_page/languages/".e_LANGUAGE.".php");
	} else {
	include_once(e_PLUGIN."links_page/languages/English.php");
}
$front_page['links_page'] = array('page' => $PLUGINS_DIRECTORY.'links_page/links.php', 'title' => LCLAN_PLUGIN_LAN_1);

?>