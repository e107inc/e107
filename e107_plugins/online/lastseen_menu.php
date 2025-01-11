<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

if(!defined('e107_INIT'))
{
	exit;
}

$online_shortcodes = e107::getScBatch('online', true);

if(THEME_LEGACY !== true)
{
	$LASTSEEN_TEMPLATE = e107::getTemplate('online', 'online_menu', 'lastseen'); // $ONLINE_MENU_TEMPLATE['lastseen'];
}
else
{
	if(is_readable(THEME.'online_menu_template.php'))
	{
		require(THEME.'online_menu_template.php');
	}
	else
	{
		require(e_PLUGIN.'online/templates/online_menu_template.php');
		$LASTSEEN_TEMPLATE = $ONLINE_MENU_TEMPLATE['lastseen'];
	}
}

$menu_pref 	= e107::getConfig('menu')->getPref();
$tp 		= e107::getParser();
$num 		= intval(vartrue($menu_pref['online_ls_amount'], 10));

$sql->select("user", "user_id, user_name, user_currentvisit", "user_currentvisit != '0' ORDER BY user_currentvisit DESC LIMIT 0,".$num);
$lslist = $sql->db_getList();

$text = $tp->parseTemplate($LASTSEEN_TEMPLATE['start'], true);

foreach($lslist as $row)
{
	//	$online_shortcodes->setScVar('currentUser', $row);
	$online_shortcodes->currentUser = $row;
	$text .= $tp->parseTemplate($LASTSEEN_TEMPLATE['item'], true, $online_shortcodes);
}

$text .= $tp->parseTemplate($LASTSEEN_TEMPLATE['end'], true, $online_shortcodes);

$caption = vartrue($menu_pref['online_ls_caption'], LAN_LASTSEEN_1);

e107::getRender()->tablerender($caption, $text, 'lastseen');