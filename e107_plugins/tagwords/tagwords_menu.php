<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Tagwords Menu
 *
*/
if (!defined('e107_INIT')){ exit; } 

global $tag;

$e107 = e107::getInstance();

require_once(e_PLUGIN."tagwords/tagwords_class.php");
$tag = new tagwords();

if(vartrue($tag->pref['tagwords_class']) && !check_class($tag->pref['tagwords_class']) )
{
	return;
}

$text = e107::getParser()->parseTemplate($tag->template['menu_cloud'], true, $tag->shortcodes);
$caption = $tag->pref['tagwords_menu_caption'] ? defset($tag->pref['tagwords_menu_caption'], $tag->pref['tagwords_menu_caption']) : LAN_TAG_16;
e107::getRender()->tablerender($caption, $text);

?>