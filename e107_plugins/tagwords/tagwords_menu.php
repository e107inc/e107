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
|     $Source: /cvs_backup/e107_0.8/e107_plugins/tagwords/tagwords_menu.php,v $
|     $Revision: 1.1 $
|     $Date: 2008-12-29 20:51:07 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

global $tag;

require_once(e_PLUGIN."tagwords/tagwords_class.php");
$tag = new tagwords();

if(varsettrue($tag->pref['tagwords_class']) && !check_class($tag->pref['tagwords_class']) )
{
	return;
}

$text = $tp->parseTemplate($tag->template['menu_cloud'], FALSE, $tag->shortcodes);
$caption = ($tag->pref['tagwords_menu_caption'] ? $tag->pref['tagwords_menu_caption'] : LAN_TAG_16);
$ns->tablerender($caption, $text);

?>