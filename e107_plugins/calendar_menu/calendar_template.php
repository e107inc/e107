<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Templates for event calendar displays
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/calendar_menu/calendar_template.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

/**
 *	e107 Event calendar plugin
 *
 * Templates for event calendar displays
 *
 *	@package	e107_plugins
 *	@subpackage	event_calendar
 *	@version 	$Id$;
 *
 */


if (!defined('e107_INIT')) { exit; }
if (!defined('USER_WIDTH')){ define('USER_WIDTH','width:auto'); }

//global $sc_style;

  $ec_images_path = e_IMAGE;
  $ec_images_path_abs = e_IMAGE_ABS;
  if (!defined('EC_RECENT_ICON')) 
  {
	define('EC_RECENT_ICON',e_IMAGE.'generic/new.png');
	define('EC_RECENT_ICON_ABS',e_IMAGE_ABS.'generic/new.png');
  }		// Filename of icon used to flag recent events




// TIME SWITCH BUTTONS ------------------------------------------------------------
$sc_style['EC_PREV_MONTH']['pre'] = "<span class='defaulttext'>";
$sc_style['EC_PREV_MONTH']['post'] = "</span>";

$sc_style['EC_CURRENT_MONTH']['pre'] = "<b>";
$sc_style['EC_CURRENT_MONTH']['post'] = "</b>";

$sc_style['EC_NEXT_MONTH']['pre'] = "<span class='defaulttext'>";
$sc_style['EC_NEXT_MONTH']['post'] = "</span>";

$sc_style['EC_PREV_YEAR']['pre'] = '';
$sc_style['EC_PREV_YEAR']['post'] = '';

$sc_style['EC_MONTH_LIST']['pre'] = '';
$sc_style['EC_MONTH_LIST']['post'] = '';

$sc_style['EC_NEXT_YEAR']['pre'] = '';
$sc_style['EC_NEXT_YEAR']['post'] = '';


$CALENDAR_TIME_TABLE = "
<table cellpadding='0' cellspacing='1' class='table fborder' style='width:100%'>
<tr>
	<td class='forumheader' style='width:18%; text-align:left'>{EC_PREV_MONTH}</td>
	<td class='fcaption' style='width:64%; text-align:center'>{EC_CURRENT_MONTH}</td>
	<td class='forumheader' style='width:18%; text-align:right'>{EC_NEXT_MONTH}</td>
</tr>\n
<tr>
	<td class='forumheader3' style='text-align:left'>{EC_PREV_YEAR}</td>
	<td class='fcaption' style='text-align:center; vertical-align:middle'>{EC_MONTH_LIST}</td>
	<td class='forumheader3' style='text-align:right'>{EC_NEXT_YEAR}</td>
</tr>\n
</table>";



// NAVIGATION BUTTONS
//$sc_style['NAV_LINKCURRENTMONTH']['pre'] = "<span class='button' style='width:120px; '>";
//$sc_style['NAV_LINKCURRENTMONTH']['post'] = "</span>";
$sc_style['EC_NAV_LINKCURRENTMONTH']['pre'] = "";
$sc_style['EC_NAV_LINKCURRENTMONTH']['post'] = "";

$CALENDAR_NAVIGATION_TABLE = "
<div style='text-align:center; margin-bottom:20px;'>
<form method='post' action='" . e_SELF . "?" . e_QUERY . "' id='calform'>
<table border='0' cellpadding='0' cellspacing='0' style='width:100%;'>
<tr>
<td style='text-align:center;'>{EC_NAV_CATEGORIES} {EC_NAV_BUT_ALLEVENTS} {EC_NAV_BUT_VIEWCAT} {EC_NAV_BUT_ENTEREVENT} {EC_NAV_BUT_SUBSCRIPTION} {EC_NAV_BUT_PRINTLISTS} {EC_NAV_LINKCURRENTMONTH}</td>
</tr>\n
</table>
</form>
</div>";



// EVENT LIST ------------------------------------------------------------
$sc_style['EC_EVENTLIST_CAPTION']['pre'] = "<tr><td class='fcaption' colspan='2'>";
$sc_style['EC_EVENTLIST_CAPTION']['post'] = ":<br /><br /></td></tr>\n";

$EVENT_EVENTLIST_TABLE_START = "<table style='width:100%' class='table fborder'>{EC_EVENTLIST_CAPTION}";
$EVENT_EVENTLIST_TABLE_END = "</table>";



// EVENT ARCHIVE ------------------------------------------------------------
$sc_style['EC_EVENTARCHIVE_CAPTION']['pre'] = "<tr><td colspan='2' class='fcaption'>";
$sc_style['EC_EVENTARCHIVE_CAPTION']['post'] = "</td></tr>\n";

$EVENT_ARCHIVE_TABLE_START = "<br /><table style='width:100%' class='table fborder'>{EC_EVENTARCHIVE_CAPTION}";
$EVENT_ARCHIVE_TABLE = "
<tr>
	<td style='width:35%; vertical-align:top' class='forumheader3'>{EC_EVENT_RECENT_ICON}{EC_EVENTARCHIVE_DATE}</td>
	<td style='width:65%' class='forumheader3'>{EC_EVENTARCHIVE_HEADING}</td>
</tr>\n";
//<br />{EVENTARCHIVE_DETAILS}
$EVENT_ARCHIVE_TABLE_EMPTY = "<tr><td colspan='2' class='forumheader3'>{EC_EVENTARCHIVE_EMPTY}</td></tr>\n";
$EVENT_ARCHIVE_TABLE_END = "</table>";



// EVENT SHOW EVENT ------------------------------------------------------------
$EVENT_EVENT_TABLE_START = "<table style='width:100%' class='table fborder' cellspacing='0' cellpadding='0'>";
$EVENT_EVENT_TABLE_END = "</table>";

$sc_style['EC_EVENT_HEADING_DATE']['pre'] = "";
$sc_style['EC_EVENT_HEADING_DATE']['post'] = "";

$sc_style['EC_EVENT_DETAILS']['pre'] = "<tr><td colspan='2' class='forumheader3'>";
$sc_style['EC_EVENT_DETAILS']['post'] = "</td></tr>\n";

$sc_style['EC_EVENT_LOCATION']['pre'] = "<tr><td colspan='2' class='forumheader3'><b>".EC_LAN_32."</b> ";
$sc_style['EC_EVENT_LOCATION']['post'] = "</td></tr>";

$sc_style['EC_EVENT_AUTHOR']['pre'] = "<b>".EC_LAN_31."</b> ";
$sc_style['EC_EVENT_AUTHOR']['post'] = "&nbsp;";

$sc_style['EC_EVENT_CONTACT']['pre'] = "<b>".EC_LAN_33."</b> ";
$sc_style['EC_EVENT_CONTACT']['post'] = "&nbsp;";

$sc_style['EC_EVENT_THREAD']['pre'] = "<tr><td colspan='2' class='forumheader3'><span class='smalltext'>";
$sc_style['EC_EVENT_THREAD']['post'] = "</span></td></tr>\n";

$sc_style['EC_EVENT_CATEGORY']['pre'] = "<b>".EC_LAN_30."</b> ";
$sc_style['EC_EVENT_CATEGORY']['post'] = "&nbsp;";

$sc_style['EC_EVENT_DATE_START']['pre'] = '';
$sc_style['EC_EVENT_DATE_START']['post'] = '';

$sc_style['EC_EVENT_TIME_START']['pre'] = '';
$sc_style['EC_EVENT_TIME_START']['post'] = '';

$sc_style['EC_EVENT_DATE_END']['pre'] = '';
$sc_style['EC_EVENT_DATE_END']['post'] = '';

$sc_style['EC_EVENT_TIME_END']['pre'] = '';
$sc_style['EC_EVENT_TIME_END']['post'] = '';

$sc_style['EC_EVENT_EVENT_DATE_TIME']['pre'] =  "<b>".EC_LAN_29."</b> ";
$sc_style['EC_EVENT_EVENT_DATE_TIME']['post'] = '';

$sc_style['EC_IFNOT_ALLDAY']['pre'] = EC_LAN_144;
$sc_style['EC_IFNOT_ALLDAY']['post'] = "";

// The $EVENT_EVENT_DATETIME strings are used with the EC_EVENT_EVENT_DATE_TIME shortcode.
// There are four cases, each with a corresponding index into $EVENT_EVENT_DATETIME:
// 	0 - Normal event, starting and finishing on different dates (the 'original' default)
//	1 - Normal event, starting and finishing on the same day
//	2 - All-day event, starting and finishing on different days
//	3 - All-day event, starting and finishing on the same day
$EVENT_EVENT_DATETIME[0]  = "{EC_EVENT_DATE_START}".EC_LAN_144."{EC_EVENT_TIME_START}<b>  ".EC_LAN_69."</b> {EC_EVENT_DATE_END}{EC_IFNOT_ALLDAY=EC_EVENT_TIME_END}";
$EVENT_EVENT_DATETIME[1]  = "{EC_EVENT_DATE_START} ".EC_LAN_84." {EC_EVENT_TIME_START}".EC_LAN_85."{EC_EVENT_TIME_END}";
$EVENT_EVENT_DATETIME[2]  = "{EC_EVENT_DATE_START} <b>".EC_LAN_69."</b> {EC_EVENT_DATE_END}";
$EVENT_EVENT_DATETIME[3]  = "{EC_EVENT_DATE_START}";


$EVENT_EVENT_TABLE = "
<tr>
  <td >
	<a href='#{EC_EVENT_ID}' class='e-show-if-js e-expandit fcaption' style='display:inline-block; cursor:pointer; text-align:left; border:0px solid #000; width:100%' title='".EC_LAN_132."'>{EC_EVENT_RECENT_ICON}{EC_EVENT_CAT_ICON}{EC_EVENT_HEADING_DATE}{EC_IFNOT_ALLDAY=EC_EVENT_TIME_START}&nbsp;-&nbsp;{EC_EVENT_TITLE}</a>
	<div id='{EC_EVENT_ID}' {EC_EVENT_DISPLAYCLASS} style='padding-top:10px; padding-bottom:10px; text-align:left;'>
	  <table style='width:100%;'  cellspacing='0' cellpadding='0'>
		<tr><td colspan='2' class='forumheader3'>{EC_EVENT_AUTHOR} {EC_EVENT_CAT_ICON} {EC_EVENT_CATEGORY} {EC_EVENT_CONTACT} {EC_EVENT_OPTIONS}</td></tr>
		<tr><td colspan='2' class='forumheader3'>{EC_EVENT_EVENT_DATE_TIME}</td></tr>\n
		{EC_EVENT_LOCATION}
		{EC_EVENT_DETAILS}
		{EC_EVENT_THREAD}
	  </table>
	</div>
  </td>
</tr>\n
";

//------------------------------------------
// CALENDAR CALENDAR - 'Big' calendar
//------------------------------------------
$CALENDAR_CALENDAR_START = "
<div style='text-align:center'>
<table cellpadding='0' cellspacing='1' class='table fborder' style='background-color:#DDDDDD; width:100%'>
<colgroup>
<col style='width:14%; padding-bottom:0px;padding-right:0px; margin-right:0px; padding:2px;' />
<col style='width:14%; padding-bottom:0px;padding-right:0px; margin-right:0px; padding:2px;' />
<col style='width:14%; padding-bottom:0px;padding-right:0px; margin-right:0px; padding:2px;' />
<col style='width:14%; padding-bottom:0px;padding-right:0px; margin-right:0px; padding:2px;' />
<col style='width:14%; padding-bottom:0px;padding-right:0px; margin-right:0px; padding:2px;' />
<col style='width:14%; padding-bottom:0px;padding-right:0px; margin-right:0px; padding:2px;' />
<col style='width:14%; padding-bottom:0px;padding-right:0px; margin-right:0px; padding:2px;' />
</colgroup>";

$CALENDAR_CALENDAR_END = "
</tr>\n</table></div>";

// 'Empty' cells where there's not a day at all
$CALENDAR_CALENDAR_DAY_NON = "<td style='width:14%;height:90px;'></td>";

//header row
$CALENDAR_CALENDAR_HEADER_START = "<tr>";
$CALENDAR_CALENDAR_HEADER = "<td class='fcaption' style='z-index: -1; background-color:#000; color:#FFF; width:90px; height:20px; text-align:center; vertical-align:middle;'>{EC_CALENDAR_CALENDAR_HEADER_DAY}</td>";
$CALENDAR_CALENDAR_HEADER_END = "</tr>\n<tr>";


$CALENDAR_CALENDAR_WEEKSWITCH = "</tr>\n<tr>";

//today
$CALENDAR_CALENDAR_DAY_TODAY = "
<td class='forumheader3' style='vertical-align:top;height:90px;'>
<span style='z-index: 2; position:relative; top:1px; height:10px;padding-right:0px'>{EC_CALENDAR_CALENDAR_DAY_TODAY_HEADING}</span>";

//day has events
$CALENDAR_CALENDAR_DAY_EVENT = "
<td class='forumheader3' style='z-index: 1;vertical-align:top;height:90px;'>
<span style='z-index: 2; position:relative; top:1px; height:10px;padding-right:0px'><b>{EC_CALENDAR_CALENDAR_DAY_EVENT_HEADING}</b></span>";

// no events and not today
$CALENDAR_CALENDAR_DAY_EMPTY = "
<td class='forumheader2' style='z-index: 1;vertical-align:top;height:90px;'>
<span style='z-index: 2; position:relative; top:1px; height:10px;padding-right:0px'><b>{EC_CALENDAR_CALENDAR_DAY_EMPTY_HEADING}</b></span>";

$CALENDAR_CALENDAR_DAY_END = "</td>";

// CALENDAR SHOW EVENT
$sc_style['EC_CALENDAR_CALENDAR_RECENT_ICON']['pre'] = "<td style='vertical-align:top; color: #0; background-color: #ff00; width:10px;'>";
$sc_style['EC_CALENDAR_CALENDAR_RECENT_ICON']['post'] = "</td>";
$CALENDAR_SHOWEVENT = "<table cellspacing='0' cellpadding='0' style='width:100%;'><tr>{EC_CALENDAR_CALENDAR_RECENT_ICON}<td style='vertical-align:top; width:10px;'>{EC_SHOWEVENT_IMAGE}</td><td style='vertical-align:top; width:2%;'>{EC_SHOWEVENT_INDICAT}</td><td style='vertical-align:top;'>{EC_SHOWEVENT_HEADING}</td></tr>\n</table>";


//------------------------------------------
// Calendar menu - 'Small' calendar
//------------------------------------------
$CALENDAR_MENU_HDG_LINK_CLASS = "class='forumlink'";			// Class, and optional style, for menu heading if its a clickable link
$CALENDAR_MENU_START = "<div style='text-align:center'>";
$CALENDAR_MENU_TABLE_START =  "<table cellpadding='0' cellspacing='1' style='width:100%;' class='table fborder'>";	// colgroup doesn't work!

$CALENDAR_MENU_END = "</tr></table></div>";

// Blank cells at beginning and end
$CALENDAR_MENU_DAY_NON = "<td class='forumheader3' style='width:14.28%; padding:1px; text-align:center; '><br /></td>";

//header row
$CALENDAR_MENU_HEADER_START = "<tr>\n";
$CALENDAR_MENU_HEADER_FRONT = "<td class='forumheader' style='text-align:center; vertical-align:middle;'><span class='smalltext'>";
$CALENDAR_MENU_HEADER_BACK = "</span></td>";
$CALENDAR_MENU_HEADER_END = "</tr>\n<tr>";


$CALENDAR_MENU_WEEKSWITCH = "</tr>\n<tr>";

// Start and end CSS for date cells - six cases to decode, determined by array index:
//     	1 - Today, no events
//		2 - Some other day, no events
//		3 - Today with events
//		4 - Some other day with events
//		5 - today with events, one or more of which has recently been added/updated
//		6 - Some other day with events, one or more of which has recently been added/updated

 
//today, no events
$CALENDAR_MENU_DAY_START['1'] = "<td class='indent' style='width:14.28%; padding:1px; text-align:center; '>";

// no events and not today
$CALENDAR_MENU_DAY_START['2'] = "<td class='forumheader3' style='width:14.28%; padding:1px; text-align:center; '>";

//day has events - same whether its today or not
$CALENDAR_MENU_DAY_START['3'] = "<td class='indent' style='width:14.28%; padding:1px; text-align:center; '>";
$CALENDAR_MENU_DAY_START['4'] = "<td class='indent' style='width:14.28%; padding:1px; text-align:center; '>";
// day has events, one which is recently added/updated
$CALENDAR_MENU_DAY_START['5'] = "<td class='indent' style='width:14.28%; padding:1px; text-align:center; '>";
$CALENDAR_MENU_DAY_START['6'] = "<td class='indent' style='width:14.28%; padding:1px; text-align:center; '>";
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
$sc_style['EC_NEXT_EVENT_TIME']['pre'] = EC_LAN_144;
$sc_style['EC_NEXT_EVENT_TIME']['post'] = "";
// Following are original styles
//$sc_style['NEXT_EVENT_ICON']['pre'] = "<img style='border:0px' src='";
//$sc_style['NEXT_EVENT_ICON']['post'] = "' alt='' />&nbsp;";
// Following to 'float right' on a larger icon
$sc_style['EC_NEXT_EVENT_ICON']['pre'] = "<img style='clear: right; float: left; margin: 0px 3px 0px 0px; padding:1px; border: 0px;' src='";
$sc_style['EC_NEXT_EVENT_ICON']['post'] = "' alt='' />";


if (!isset($EVENT_CAL_FE_LINE))
{  
  $EVENT_CAL_FE_LINE = "{EC_NEXT_EVENT_RECENT_ICON}{EC_NEXT_EVENT_ICON}{EC_NEXT_EVENT_DATE}{EC_NEXT_EVENT_TIME}<br /><strong>{EC_NEXT_EVENT_TITLE}</strong>{EC_NEXT_EVENT_GAP}";
}


?>