<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * e107 Core Plugin - Online Menu
 *
*/

if (!defined('e107_INIT')) { exit; }

//global $pref;
$menu_pref = e107::getConfig('menu')->getPref();

$tp = e107::getParser();

e107::includeLan(e_PLUGIN.'online/languages/'.e_LANGUAGE.'.php');

// require_once(e_PLUGIN.'online/online_shortcodes.php');
$mode = empty($menu_pref['online_show_memberlist_extended']) ? 'default' : 'extended';

$online_shortcodes = e107::getScBatch('online', true);
$online_shortcodes->wrapper('online_menu/'.$mode);

if(is_readable(THEME.'online_menu_template.php'))
{
	require(THEME.'online_menu_template.php');
}
else
{
	$onlineTpl       = e107::getTemplate('online', 'online_menu');
	$ONLINE_TEMPLATE = vartrue($onlineTpl['ONLINE_TEMPLATE'], varset($onlineTpl[$mode], array()));
}

$online_shortcodes->memberTemplate = varset($ONLINE_TEMPLATE['online_members_list_extended'], '');
$online_shortcodes->newestTemplate = varset($ONLINE_TEMPLATE['online_member_newest'], '');

//if(!defined('e_TRACKING_DISABLED') && varsettrue($pref['track_online']))
if(!defined('e_TRACKING_DISABLED'))
{
	$text = $tp->parseTemplate($ONLINE_TEMPLATE['enabled'], TRUE, $online_shortcodes);
}
else
{
	if (ADMIN)
	{
		$text = $tp->parseTemplate($ONLINE_TEMPLATE['disabled'], TRUE, $online_shortcodes);
	}
	else
	{
		return;
	}
}

$img = (is_readable(THEME.'images/online_menu.png') ? "<img src='".THEME_ABS."images/online_menu.png' alt='' />" : '');

$caption = $img.' '.vartrue($menu_pref['online_caption'], LAN_ONLINE_4);

if (getperms('1')) 
{
	$path = e_PLUGIN_ABS."online/config.php?iframe=1";
	$caption .= "<a class='e-modal pull-right float-right float-end' data-bs-toggle='modal' data-bs-target='#uiModal' data-modal-caption='".LAN_SETTINGS."' href='".$path."' title='".LAN_SETTINGS."'><i class='fa fa-cog'></i></a>";
}


e107::getRender()->tablerender($caption, $text, 'online_extended');

