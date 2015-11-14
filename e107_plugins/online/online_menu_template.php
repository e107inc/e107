<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * e107 Main
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/online/online_menu_template.php,v $
 * $Revision$
 * $Date$
 * $Author$
*/

global $sc_style;
//##### LASTSEEN MENU ---------------------------------------------------------

$LASTSEEN_TEMPLATE['start']	= "<ul class='lastseen-menu '>";
$LASTSEEN_TEMPLATE['item']	= "<li>{LASTSEEN_USERLINK} <small class='muted'>{LASTSEEN_DATE}</small></li>";
$LASTSEEN_TEMPLATE['end']	= "</ul>";

//##### ONLINE MENU -----------------------------------------------------------

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
{ONLINE_MEMBERS_LIST_EXTENDED}
{ONLINE_ONPAGE}
{ONLINE_MEMBER_TOTAL}
{ONLINE_MEMBER_NEWEST}
<li>
{ONLINE_MOST}
<small class='muted'>
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
$ONLINE_TEMPLATE['online_members_list_extended'] = "{SETIMAGE: w=40}<li class='media'><span class='media-object pull-left'>{ONLINE_MEMBER_IMAGE=avatar}</span><span class='media-body'>{ONLINE_MEMBER_USER} ".LAN_ONLINE_7." {ONLINE_MEMBER_PAGE}</span></li>";

?>