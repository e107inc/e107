<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2008 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * e107 Main
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/online/online_menu_template.php,v $
 * $Revision: 1.2 $
 * $Date: 2009-09-21 21:43:44 $
 * $Author: e107steved $
*/

//##### LASTSEEN MENU ---------------------------------------------------------

$sc_style['LASTSEEN_DATE']['pre'] = "<br /> [ ";
$sc_style['LASTSEEN_DATE']['post'] = " ]";

$TEMPLATE_LASTSEEN['START']	= "<ul style='margin-left:15px; margin-top:0px; padding-left:0px;'>";
$TEMPLATE_LASTSEEN['ITEM']	= "<li style='list-style-type: square;'>{LASTSEEN_USERLINK} {LASTSEEN_DATE}</li>";
$TEMPLATE_LASTSEEN['END']	= "</ul>";

//##### ONLINE MENU -----------------------------------------------------------

$sc_style['ONLINE_GUESTS']['pre'] = LAN_ONLINE_1;
$sc_style['ONLINE_GUESTS']['post'] = "<br />";

$sc_style['ONLINE_MEMBERS']['pre'] = LAN_ONLINE_2;
$sc_style['ONLINE_MEMBERS']['post'] = "";

$sc_style['ONLINE_MEMBERS_LIST']['pre'] = ", ";
$sc_style['ONLINE_MEMBERS_LIST']['post'] = "";

$sc_style['ONLINE_MEMBERS_LIST_EXTENDED']['pre'] = "<br />";
$sc_style['ONLINE_MEMBERS_LIST_EXTENDED']['post'] = "<br />";

$sc_style['ONLINE_ONPAGE']['pre'] = LAN_ONLINE_3;
$sc_style['ONLINE_ONPAGE']['post'] = "<br />";

$sc_style['ONLINE_MEMBER_TOTAL']['pre'] = LAN_ONLINE_2;
$sc_style['ONLINE_MEMBER_TOTAL']['post'] = "<br />";

$sc_style['ONLINE_MEMBER_NEWEST']['pre'] = LAN_ONLINE_6;
$sc_style['ONLINE_MEMBER_NEWEST']['post'] = "<br />";

$sc_style['ONLINE_MOST']['pre'] = LAN_ONLINE_8;
$sc_style['ONLINE_MOST']['post'] = "<br />";

$sc_style['ONLINE_MOST_MEMBERS']['pre'] = LAN_ONLINE_2;
$sc_style['ONLINE_MOST_MEMBERS']['post'] = "";

$sc_style['ONLINE_MOST_GUESTS']['pre'] = LAN_ONLINE_1;
$sc_style['ONLINE_MOST_GUESTS']['post'] = "";

$sc_style['ONLINE_MOST_DATESTAMP']['pre'] = LAN_ONLINE_9;
$sc_style['ONLINE_MOST_DATESTAMP']['post'] = "";

$TEMPLATE_ONLINE['ENABLED'] = "
{ONLINE_GUESTS}
{ONLINE_MEMBERS}{ONLINE_MEMBERS_LIST}<br />
{ONLINE_MEMBERS_LIST_EXTENDED}
{ONLINE_ONPAGE}
<br />
{ONLINE_MEMBER_TOTAL}
{ONLINE_MEMBER_NEWEST}
<br />
{ONLINE_MOST}
({ONLINE_MOST_MEMBERS}, {ONLINE_MOST_GUESTS}) {ONLINE_MOST_DATESTAMP}
";

//##### ONLINE TRACKING DISABLED ----------------------------------------------
$TEMPLATE_ONLINE['DISABLED'] = "{ONLINE_TRACKING_DISABLED}";

//##### ONLINE MEMBER LIST EXTENDED -------------------------------------------
$TEMPLATE_ONLINE['ONLINE_MEMBERS_LIST_EXTENDED'] = "{ONLINE_MEMBER_IMAGE} {ONLINE_MEMBER_USER} ".LAN_ONLINE_7." {ONLINE_MEMBER_PAGE}<br />";

?>