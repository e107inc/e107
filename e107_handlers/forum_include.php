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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/forum_include.php,v $
|     $Revision: 1.1 $
|     $Date: 2004-12-21 06:57:52 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

function img_path($filename) {
	$image = (file_exists(THEME.'forum/'.$filename)) ? THEME.'forum/'.$filename : e_IMAGE.'forum/'.$filename;
	return $image;
}

require_once(e_HANDLER.'multilang/pictures.php');

if (file_exists(THEME.'forum_icons_template.php')) {
	require_once(THEME.'forum_icons_template.php');
} else {
	require_once(e_BASE.$THEMES_DIRECTORY.'templates/forum_icons_template.php');
}

?>
