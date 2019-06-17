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
global $menu_pref;

$tp = e107::getParser();

e107::includeLan(e_PLUGIN.'online/languages/'.e_LANGUAGE.'.php');

require_once(e_PLUGIN.'online/online_shortcodes.php');
$mode = empty($menu_pref['online_show_memberlist_extended']) ? 'default' : 'extended';
$online_shortcodes = new online_shortcodes;
$online_shortcodes->wrapper('online_menu/'.$mode);

if(deftrue('BOOTSTRAP'))
{
	$ONLINE_TEMPLATE = e107::getTemplate('online', 'online_menu', $mode);
}
else
{

		// legacy default ------------------------

		global $sc_style;

		$sc_style['ONLINE_GUESTS']['pre'] = "<li>".LAN_ONLINE_1;
		$sc_style['ONLINE_GUESTS']['post'] = "</li>";

		$sc_style['ONLINE_MEMBERS']['pre'] = "<li>".LAN_ONLINE_2;
		$sc_style['ONLINE_MEMBERS']['post'] = "</li>";

		$sc_style['ONLINE_MEMBERS_LIST']['pre'] = "<ul>";
		$sc_style['ONLINE_MEMBERS_LIST']['post'] = "</ul>";

		$sc_style['ONLINE_MEMBERS_LIST_EXTENDED']['pre'] = "<ul class='unstyled list-unstyled'>";
		$sc_style['ONLINE_MEMBERS_LIST_EXTENDED']['post'] = "</ul>";

		$sc_style['ONLINE_ONPAGE']['pre'] = "<li>".LAN_ONLINE_3;
		$sc_style['ONLINE_ONPAGE']['post'] = "</li>";

		$sc_style['ONLINE_MEMBER_TOTAL']['pre'] = "<li>".LAN_ONLINE_2;
		$sc_style['ONLINE_MEMBER_TOTAL']['post'] = "</li>";

		$sc_style['ONLINE_MEMBER_NEWEST']['pre'] = "<li>".LAN_ONLINE_6;
		$sc_style['ONLINE_MEMBER_NEWEST']['post'] = "</li>";

		$sc_style['ONLINE_MOST']['pre'] = LAN_ONLINE_8;
		$sc_style['ONLINE_MOST']['post'] = "<br />";

		$sc_style['ONLINE_MOST_MEMBERS']['pre'] = LAN_ONLINE_2;
		$sc_style['ONLINE_MOST_MEMBERS']['post'] = "";

		$sc_style['ONLINE_MOST_GUESTS']['pre'] = "".LAN_ONLINE_1;
		$sc_style['ONLINE_MOST_GUESTS']['post'] = ", ";

		$sc_style['ONLINE_MOST_DATESTAMP']['pre'] = "".LAN_ONLINE_9;
		$sc_style['ONLINE_MOST_DATESTAMP']['post'] = "";

		$ONLINE_TEMPLATE['enabled'] = "

		<ul class='online-menu'>
		{ONLINE_GUESTS}
		{ONLINE_MEMBERS}
		{ONLINE_MEMBERS_LIST}
		{ONLINE_MEMBERS_LIST_EXTENDED}
		{ONLINE_ONPAGE}
		{ONLINE_MEMBER_TOTAL}
		{ONLINE_MEMBER_NEWEST}
		<li>
		{ONLINE_MOST}
		<small class='text-muted muted'>
		{ONLINE_MOST_GUESTS}
		{ONLINE_MOST_MEMBERS}
		{ONLINE_MOST_DATESTAMP}
		</small>
		</li>
		</ul>
		";

		//##### ONLINE TRACKING DISABLED ----------------------------------------------
		$ONLINE_TEMPLATE['disabled'] = "{ONLINE_TRACKING_DISABLED}";

		//##### ONLINE MEMBER LIST EXTENDED -------------------------------------------
		$ONLINE_TEMPLATE['online_members_list_extended'] = "{SETIMAGE: w=40}<li class='media'><span class='media-object pull-left float-left'>{ONLINE_MEMBER_IMAGE=avatar}</span><span class='media-body'>{ONLINE_MEMBER_USER} ".LAN_ONLINE_7." {ONLINE_MEMBER_PAGE}</span></li>";





	if (is_readable(THEME.'templates/online/online_menu_template.php'))
	{
		require(THEME.'templates/online/online_menu_template.php');
	}
	elseif (is_readable(THEME.'online_menu_template.php'))
	{
		require(THEME.'online_menu_template.php');
	}
	else
	{
		require(e_PLUGIN.'online/templates/online_menu_template.php');
	}


}

$online_shortcodes->memberTemplate = $ONLINE_TEMPLATE['online_members_list_extended'];
$online_shortcodes->newestTemplate = $ONLINE_TEMPLATE['online_member_newest'];

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
	$caption .= "<a class='e-modal pull-right float-right' data-modal-caption='".LAN_SETTINGS."' href='".$path."' title='".LAN_SETTINGS."'><i class='glyphicon glyphicon-cog'></i></a>";
}


e107::getRender()->tablerender($caption, $text, 'online_extended');

