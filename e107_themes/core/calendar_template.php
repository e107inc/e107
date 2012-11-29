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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/calendar_menu/calendar_template.php,v $
|     $Revision: 11346 $
|     $Date: 2010-02-17 13:56:14 -0500 (Wed, 17 Feb 2010) $
|     $Author: secretr $
|
| 10.11.06 steved - mods for next CVS release
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

// TIME SWITCH BUTTONS ------------------------------------------------------------
$sc_style['PREV_MONTH']['pre'] = "<span class='defaulttext'>";
$sc_style['PREV_MONTH']['post'] = "</span>";

$sc_style['CURRENT_MONTH']['pre'] = "<b>";
$sc_style['CURRENT_MONTH']['post'] = "</b>";

$sc_style['NEXT_MONTH']['pre'] = "<span class='defaulttext'>";
$sc_style['NEXT_MONTH']['post'] = "</span>";

$sc_style['PREV_YEAR']['pre'] = "";
$sc_style['PREV_YEAR']['post'] = "";

$sc_style['MONTH_LIST']['pre'] = "";
$sc_style['MONTH_LIST']['post'] = "";

$sc_style['NEXT_YEAR']['pre'] = "";
$sc_style['NEXT_YEAR']['post'] = "";

//<table style='width:98%;' class='fborder'>

$CALENDAR_TIME_TABLE = "
<table cellpadding='0' cellspacing='1' class='fborder' style='width:100%'>
<tr>
	<td style='width:18%; text-align:left'>{PREV_MONTH}</td>
	<td class='fcaption' style='width:64%; text-align:center'>{CURRENT_MONTH}</td>
	<td style='width:18%; text-align:right'>{NEXT_MONTH}</td>
</tr>\n
<tr>
	<td style='text-align:left'>{PREV_YEAR}</td>
	<td class='fcaption' style='text-align:center; vertical-align:middle'>{MONTH_LIST}</td>
	<td style='text-align:right'>{NEXT_YEAR}</td>
</tr>\n
</table>";



// NAVIGATION BUTTONS ------------------------------------------------------------
//$sc_style['NAV_LINKCURRENTMONTH']['pre'] = "<span class='button' style='width:120px; '>";
//$sc_style['NAV_LINKCURRENTMONTH']['post'] = "</span>";
$sc_style['NAV_LINKCURRENTMONTH']['pre'] = "";
$sc_style['NAV_LINKCURRENTMONTH']['post'] = "";

$CALENDAR_NAVIGATION_TABLE = "
<div style='text-align:center; margin-bottom:20px;'>
<form method='post' action='" . e_SELF . "?" . e_QUERY . "' id='calform'>
<table border='0' cellpadding='0' cellspacing='0' style='width:100%;'>
<tr>
	<td style='text-align:center;'>{NAV_CATEGORIES} {NAV_BUT_ALLEVENTS} {NAV_BUT_VIEWCAT} {NAV_BUT_ENTEREVENT} {NAV_BUT_SUBSCRIPTION} {NAV_LINKCURRENTMONTH}</td>
</tr>\n
</table>
</form>
</div>";



// EVENT LIST ------------------------------------------------------------
$sc_style['EVENTLIST_CAPTION']['pre'] = "<tr><td class='fcaption' colspan='2'>";
$sc_style['EVENTLIST_CAPTION']['post'] = ":<br /><br /></td></tr>\n";

$EVENT_EVENTLIST_TABLE_START = "<table style='width:100%' class='fborder'>{EVENTLIST_CAPTION}";
$EVENT_EVENTLIST_TABLE_END = "</table>";



// EVENT ARCHIVE ------------------------------------------------------------
$sc_style['EVENTARCHIVE_CAPTION']['pre'] = "<tr><td colspan='2' class='fcaption'>";
$sc_style['EVENTARCHIVE_CAPTION']['post'] = "</td></tr>\n";

$EVENT_ARCHIVE_TABLE_START = "<br /><table style='width:100%' class='fborder'>{EVENTARCHIVE_CAPTION}";
$EVENT_ARCHIVE_TABLE = "
<tr>
	<td style='width:35%; vertical-align:top' class='forumheader3'>{EVENT_RECENT_ICON}{EVENTARCHIVE_DATE}</td>
	<td style='width:65%' class='forumheader3'>{EVENTARCHIVE_HEADING}</td>
</tr>\n";
//<br />{EVENTARCHIVE_DETAILS}
$EVENT_ARCHIVE_TABLE_EMPTY = "<tr><td colspan='2' class='forumheader3'>{EVENTARCHIVE_EMPTY}</td></tr>\n";
$EVENT_ARCHIVE_TABLE_END = "</table>";



// EVENT SHOW EVENT ------------------------------------------------------------
$EVENT_EVENT_TABLE_START = "<table style='width:100%' class='fborder' cellspacing='0' cellpadding='0'>";
$EVENT_EVENT_TABLE_END = "</table>";

$sc_style['EVENT_HEADING_DATE']['pre'] = "";
$sc_style['EVENT_HEADING_DATE']['post'] = "";

$sc_style['EVENT_DETAILS']['pre'] = "<tr><td colspan='2' class='forumheader3'>";
$sc_style['EVENT_DETAILS']['post'] = "</td></tr>\n";

$sc_style['EVENT_LOCATION']['pre'] = "<b>".EC_LAN_32."</b> ";
$sc_style['EVENT_LOCATION']['post'] = "";

$sc_style['EVENT_AUTHOR']['pre'] = "<b>".EC_LAN_31."</b> ";
$sc_style['EVENT_AUTHOR']['post'] = "&nbsp;";

$sc_style['EVENT_CONTACT']['pre'] = "<b>".EC_LAN_33."</b> ";
$sc_style['EVENT_CONTACT']['post'] = "&nbsp;";

$sc_style['EVENT_THREAD']['pre'] = "<tr><td colspan='2' class='forumheader3'><span class='smalltext'>";
$sc_style['EVENT_THREAD']['post'] = "</span></td></tr>\n";

$sc_style['EVENT_CATEGORY']['pre'] = "<b>".EC_LAN_30."</b> ";
$sc_style['EVENT_CATEGORY']['post'] = "&nbsp;";

$sc_style['EVENT_DATE_START']['pre'] = (isset($thisevent['event_allday']) && $thisevent['event_allday']) ? "<b>".EC_LAN_68."</b> " : "<b>".EC_LAN_29."</b> ";
$sc_style['EVENT_DATE_START']['post'] = "";

$sc_style['EVENT_TIME_START']['pre'] = EC_LAN_144;
$sc_style['EVENT_TIME_START']['post'] = "";

$sc_style['EVENT_DATE_END']['pre'] = "<b>".EC_LAN_69."</b> ";
$sc_style['EVENT_DATE_END']['post'] = "";

$sc_style['EVENT_TIME_END']['pre'] = EC_LAN_144;
$sc_style['EVENT_TIME_END']['post'] = "";

$EVENT_EVENT_TABLE = "
<tr>
	<td >
		<div title='".EC_LAN_132."' class='fcaption' style='cursor:pointer; text-align:left; border:0px solid #000;' onclick=\"expandit('{EVENT_ID}')\">{EVENT_RECENT_ICON}{EVENT_CAT_ICON}{EVENT_HEADING_DATE}{EVENT_TIME_START}&nbsp;-&nbsp;{EVENT_TITLE}</div>
		<div id='{EVENT_ID}' style='display:{EVENT_DISPLAYSTYLE}; padding-top:10px; padding-bottom:10px; text-align:left;'>
			<table style='width:100%;'  cellspacing='0' cellpadding='0'>
				<tr><td colspan='2' class='forumheader3'>{EVENT_AUTHOR} {EVENT_CAT_ICON} {EVENT_CATEGORY} {EVENT_CONTACT} {EVENT_OPTIONS}</td></tr>
				<tr><td colspan='2' class='forumheader3'>{EVENT_DATE_START}{EVENT_TIME_START} {EVENT_DATE_END}{EVENT_TIME_END}</td></tr>\n
				<tr><td colspan='2' class='forumheader3'>{EVENT_LOCATION}</td></tr>
				{EVENT_DETAILS}
				{EVENT_THREAD}
			</table>
		</div>
	</td>
</tr>\n
";


// CALENDAR SHOW EVENT ------------------------------------------------------------
$sc_style['CALENDAR_CALENDAR_RECENT_ICON']['pre'] = "<td style='vertical-align:top; color: #000;  width:10px;'>";
$sc_style['CALENDAR_CALENDAR_RECENT_ICON']['post'] = "</td>";
$CALENDAR_SHOWEVENT = "<table cellspacing='0' cellpadding='0' style='width:100%;'><tr>{CALENDAR_CALENDAR_RECENT_ICON}<td style='vertical-align:top; width:10px;'>{SHOWEVENT_IMAGE}</td><td style='vertical-align:top; width:2%;'>{SHOWEVENT_INDICAT}</td><td style='vertical-align:top;'>{SHOWEVENT_HEADING}</td></tr>\n</table>";



// CALENDAR CALENDAR ------------------------------------------------------------
$CALENDAR_CALENDAR_START = "
<div style='text-align:center'>
<table cellpadding='0' cellspacing='1' style='border: 1px #CCCCCC solid; width:100%'>";

$CALENDAR_CALENDAR_END = "
</tr>\n</table></div>";

$CALENDAR_CALENDAR_DAY_NON = "<td style='width:12%;height:60px;'></td>";

//header row
$CALENDAR_CALENDAR_HEADER_START = "<tr>";
$CALENDAR_CALENDAR_HEADER = "<td class='fcaption' style='z-index: -1; background-color:#F9F9F9; border-bottom: 1px #CCCCCC solid; width:90px; height:20px; text-align:center; vertical-align:middle;'>{CALENDAR_CALENDAR_HEADER_DAY}</td>";
$CALENDAR_CALENDAR_HEADER_END = "</tr>\n<tr>";


$CALENDAR_CALENDAR_WEEKSWITCH = "</tr>\n<tr>";

//today
$CALENDAR_CALENDAR_DAY_TODAY = "
<td class='forumheader3' style='vertical-align:top; width:14%; height:90px; padding-bottom:0px;padding-right:0px; margin-right:0px; padding:2px;'>
<span style='z-index: 2; position:relative; top:1px; height:10px;padding-right:0px'>{CALENDAR_CALENDAR_DAY_TODAY_HEADING}</span>";

//day has events
$CALENDAR_CALENDAR_DAY_EVENT = "
<td class='forumheader3' style='z-index: 1;vertical-align:top; width:14%; height:90px; padding-bottom:0px;padding-right:0px; margin-right:0px; padding:2px;'>
<span style='z-index: 2; position:relative; top:1px; height:10px;padding-right:0px'><b>{CALENDAR_CALENDAR_DAY_EVENT_HEADING}</b></span>";

// no events and not today
$CALENDAR_CALENDAR_DAY_EMPTY = "
<td class='forumheader2' style='z-index: 1;vertical-align:top; width:14%; height:90px;padding-bottom:0px;padding-right:0px; margin-right:0px; padding:2px;'>
<span style='z-index: 2; position:relative; top:1px; height:10px;padding-right:0px'><b>{CALENDAR_CALENDAR_DAY_EMPTY_HEADING}</b></span>";

$CALENDAR_CALENDAR_DAY_END = "</td>";

//====================================================================
// Calendar menu templates
$CALENDAR_MENU_START = "<div style='text-align:center'>";
$CALENDAR_MENU_TABLE_START =   "<table cellpadding='0' cellspacing='1' style='width:100%' class='fborder'>";

$CALENDAR_MENU_END = "</tr></table></div>";

// Blank cells at beginning and end
$CALENDAR_MENU_DAY_NON = "<td class='forumheader3' style='padding:1px; text-align:center'><br /></td>";

//header row
$CALENDAR_MENU_HEADER_START = "<tr>\n";
$CALENDAR_MENU_HEADER_FRONT = "<td class='forumheader' style='text-align:center; vertical-align:middle;'><span class='smalltext'>";
$CALENDAR_MENU_HEADER_BACK = "</span></td>";
$CALENDAR_MENU_HEADER_END = "</tr>\n<tr>";


$CALENDAR_MENU_WEEKSWITCH = "</tr>\n<tr>";

// Start and end CSS for date cells - six cases to decode, determined by array index:
//     	1 - Today, no events
//		2 - Some other day, no events (or no icon defined)
//		3 - Today with events (and icon defined)
//		4 - Some other day with events (and icon defined)
//		5 - today with events, one or more of which has recently been added/updated (and icon defined)
//		6 - Some other day with events, one or more of which has recently been added/updated (and icon defined)
 
//today, no events
$CALENDAR_MENU_DAY_START['1'] = "<td class='indent' style='width:14.28%; padding:1px; text-align:center; '>";

// no events and not today
$CALENDAR_MENU_DAY_START['2'] = "<td class='forumheader3' style='width:14.28%; padding:1px; text-align:center; '>";

//day has events - same whether its today or not
$CALENDAR_MENU_DAY_START['3'] = "<td class='indent' style='width:14.28%; padding:1px; text-align:center; '>";
$CALENDAR_MENU_DAY_START['4'] = "<td class='indent' style='width:14.28%; padding:1px; text-align:center;'>";
// day has events, one which is recently added/updated
$CALENDAR_MENU_DAY_START['5'] = "<td class='indent' style='width:14.28%; padding:1px; text-align:center; '>";
$CALENDAR_MENU_DAY_START['6'] = "<td class='indent' style='width:14.28%; padding:1px; text-align:center;'>";
// Example highlight using background colour:
//$CALENDAR_MENU_DAY_START['5'] = "<td class='indent' style='width:14.28%; padding:1px; text-align:center; background-color: #FF8000;'>";
//$CALENDAR_MENU_DAY_START['6'] = "<td class='indent' style='width:14.28%; padding:1px; text-align:center; background-color: #FF0000; '>";

$CALENDAR_MENU_DAY_END['1'] = "</td>";
$CALENDAR_MENU_DAY_END['2'] = "</td>";
$CALENDAR_MENU_DAY_END['3'] = "</td>";
$CALENDAR_MENU_DAY_END['4'] = "</td>";
$CALENDAR_MENU_DAY_END['5'] = "</td>";
$CALENDAR_MENU_DAY_END['6'] = "</td>";

//============================================================================
// Next event menu template
$sc_style['NEXT_EVENT_TIME']['pre'] = EC_LAN_144;
$sc_style['NEXT_EVENT_TIME']['post'] = "";
// Following are original styles
//$sc_style['NEXT_EVENT_ICON']['pre'] = "<img style='border:0px' src='";
//$sc_style['NEXT_EVENT_ICON']['post'] = "' alt='' />&nbsp;";
// Following to 'float right' on a larger icon
$sc_style['NEXT_EVENT_ICON']['pre'] = "<img style='clear: right; float: left; margin: 0px 3px 0px 0px; padding:1px; border: 0px;' src='";
$sc_style['NEXT_EVENT_ICON']['post'] = "' alt='' />";


if (!isset($EVENT_CAL_FE_LINE))
{  
  $EVENT_CAL_FE_LINE = "{NEXT_EVENT_ICON}{NEXT_EVENT_DATE}{NEXT_EVENT_TIME}<br /><strong>{NEXT_EVENT_TITLE}</strong>{NEXT_EVENT_GAP}";
}


?>