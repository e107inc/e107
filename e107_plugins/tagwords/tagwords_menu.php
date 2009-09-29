<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2008 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Tagwords Menu
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/tagwords/tagwords_menu.php,v $
 * $Revision: 1.3 $
 * $Date: 2009-09-29 17:43:44 $
 * $Author: secretr $
 *
*/

global $tag;

$e107 = e107::getInstance();

require_once(e_PLUGIN."tagwords/tagwords_class.php");
$tag = new tagwords();

if(varsettrue($tag->pref['tagwords_class']) && !check_class($tag->pref['tagwords_class']) )
{
	return;
}

$text = $e107->tp->parseTemplate($tag->template['menu_cloud'], true, $tag->shortcodes);
$caption = $tag->pref['tagwords_menu_caption'] ? defset($tag->pref['tagwords_menu_caption'], $tag->pref['tagwords_menu_caption']) : LAN_TAG_16;
$e107->ns->tablerender($caption, $text);

?>