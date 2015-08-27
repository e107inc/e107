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
 * $Source: /cvs_backup/e107_0.8/e107_plugins/online/lastseen_menu.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT')) { exit; }

include_lan(e_PLUGIN.'online/languages/'.e_LANGUAGE.'.php');
require_once(e_PLUGIN.'online/online_shortcodes.php');
if (is_readable(THEME.'online_menu_template.php')) 
{
	require(THEME.'online_menu_template.php');
} 
else 
{
	require(e_PLUGIN.'online/online_menu_template.php');
}

$menu_pref = e107::getConfig('menu')->getPref('');
$tp = e107::getParser();

$num = intval(vartrue($menu_pref['online_ls_amount'],10));

$sql -> db_Select('user', 'user_id, user_name, user_currentvisit', 'ORDER BY user_currentvisit DESC LIMIT 0,'.$num, 'nowhere');
$lslist = $sql -> db_getList();

$text = $tp -> parseTemplate($LASTSEEN_TEMPLATE['start'], TRUE);
foreach($lslist as $row)
{
	setScVar('online_shortcodes', 'currentUser', $row);
	$text .= $tp -> parseTemplate($LASTSEEN_TEMPLATE['item'],TRUE);
}
$text .= $tp -> parseTemplate($LASTSEEN_TEMPLATE['end'], TRUE);

$caption = vartrue($menu_pref['online_ls_caption'],LAN_LASTSEEN_1);
$ns->tablerender($caption, $text, 'lastseen');

?>