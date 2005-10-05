<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_plugins/links_page/e_frontpage.php,v $
|     $Revision: 1.5 $
|     $Date: 2005-10-05 10:32:00 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

if (file_exists(e_PLUGIN."links_page/languages/".e_LANGUAGE.".php")) {
	include_once(e_PLUGIN."links_page/languages/".e_LANGUAGE.".php");
	} else {
	include_once(e_PLUGIN."links_page/languages/English.php");
}
$front_page['links_page'] = array('page' => $PLUGINS_DIRECTORY.'links_page/links.php', 'title' => LCLAN_PLUGIN_LAN_1);

?>