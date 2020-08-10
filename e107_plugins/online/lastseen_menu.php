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

e107::includeLan(e_PLUGIN.'online/languages/'.e_LANGUAGE.'.php');

if(class_exists('online_shortcodes'))
{
	$online_shortcodes = new online_shortcodes;
}
else
{
	require_once(e_PLUGIN.'online/online_shortcodes.php');
}

if(THEME_LEGACY !== true)
{
	$LASTSEEN_TEMPLATE = e107::getTemplate('online','online_menu', 'lastseen'); // $ONLINE_MENU_TEMPLATE['lastseen'];
}
else
{
	if (is_readable(THEME.'online_menu_template.php'))
	{
		require(THEME.'online_menu_template.php');
	}
	else
	{
		require(e_PLUGIN.'online/templates/online_menu_template.php');
		$LASTSEEN_TEMPLATE = $ONLINE_MENU_TEMPLATE['lastseen'];
	}

}

$menu_pref = e107::getConfig('menu')->getPref();
$tp = e107::getParser();

$num = intval(vartrue($menu_pref['online_ls_amount'],10));

$sql->select('user', 'user_id, user_name, user_currentvisit', 'ORDER BY user_currentvisit DESC LIMIT 0,'.$num, 'nowhere');
$lslist = $sql -> db_getList();

$text = $tp -> parseTemplate($LASTSEEN_TEMPLATE['start'], true);
foreach($lslist as $row)
{
// 	setScVar('online_shortcodes', 'currentUser', $row);
	$online_shortcodes->currentUser = $row;
	$text .= $tp -> parseTemplate($LASTSEEN_TEMPLATE['item'],true, $online_shortcodes);
}
$text .= $tp -> parseTemplate($LASTSEEN_TEMPLATE['end'], true, $online_shortcodes);

$caption = vartrue($menu_pref['online_ls_caption'],LAN_LASTSEEN_1);

e107::getRender()->tablerender($caption, $text, 'lastseen');

