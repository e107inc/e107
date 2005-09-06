<?php

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
	<td class='forumheader' style='width:18%; text-align:left'>{PREV_MONTH}</td>
	<td class='fcaption' style='width:64%; text-align:center'>{CURRENT_MONTH}</td>
	<td class='forumheader' style='width:18%; text-align:right'>{NEXT_MONTH}</td>
</tr>
<tr>
	<td class='forumheader3' style='text-align:left'>{PREV_YEAR}</td>
	<td class='fcaption' style='text-align:center; vertical-align:middle'>{MONTH_LIST}</td>
	<td class='forumheader3' style='text-align:right'>{NEXT_YEAR}</td>
</tr>
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
</tr>
</table>
</form>
</div>";
/*
	<table border='0' cellpadding='2' cellspacing='3' class='forumheader3' style='width:100%;'>
	<tr>
		<td style='text-align:right;'>{NAV_CATEGORIES}</td>
		<td style='text-align:center;'>{NAV_BUT_ALLEVENTS}</td>
	</tr>
	<tr>
		<td style='text-align:right;'>{NAV_BUT_VIEWCAT}</td>
		<td style='text-align:center;'>{NAV_BUT_ENTEREVENT}</td>
	</tr>
	<tr>
		<td style='text-align:center;' colspan='2'>{NAV_BUT_SUBSCRIPTION}</td>
	</tr>
	</table>
	*/


// EVENT LIST ------------------------------------------------------------
$sc_style['EVENTLIST_CAPTION']['pre'] = "<tr><td class='fcaption' colspan='2'>";
$sc_style['EVENTLIST_CAPTION']['post'] = ":<br /><br /></td></tr>";

$EVENT_EVENTLIST_TABLE_START = "<table style='width:100%' class='fborder'>{EVENTLIST_CAPTION}";
$EVENT_EVENTLIST_TABLE_END = "</table>";



// EVENT ARCHIVE ------------------------------------------------------------
$sc_style['EVENTARCHIVE_CAPTION']['pre'] = "<tr><td colspan='2' class='fcaption'>";
$sc_style['EVENTARCHIVE_CAPTION']['post'] = "</td></tr>";

$EVENT_ARCHIVE_TABLE_START = "<br /><table style='width:100%' class='fborder'>{EVENTARCHIVE_CAPTION}";
$EVENT_ARCHIVE_TABLE = "
<tr>
	<td style='width:35%; vertical-align:top' class='forumheader3'>{EVENTARCHIVE_DATE}</td>
	<td style='width:65%' class='forumheader3'>{EVENTARCHIVE_HEADING}</td>
</tr>";
//<br />{EVENTARCHIVE_DETAILS}
$EVENT_ARCHIVE_TABLE_EMPTY = "<tr><td colspan='2' class='forumheader3'>{EVENTARCHIVE_EMPTY}</td></tr>";
$EVENT_ARCHIVE_TABLE_END = "</table>";



// EVENT SHOW EVENT ------------------------------------------------------------
$EVENT_EVENT_TABLE_START = "<table style='width:100%' class='fborder' cellspacing='0' cellpadding='0'>";
$EVENT_EVENT_TABLE_END = "</table>";

$sc_style['EVENT_DETAILS']['pre'] = "<tr><td colspan='2' class='forumheader3'>";
$sc_style['EVENT_DETAILS']['post'] = "</td></tr>";

$sc_style['EVENT_LOCATION']['pre'] = "<b>".EC_LAN_32."</b> ";
$sc_style['EVENT_LOCATION']['post'] = "";

$sc_style['EVENT_AUTHOR']['pre'] = "<b>".EC_LAN_31."</b> ";
$sc_style['EVENT_AUTHOR']['post'] = "&nbsp;";

$sc_style['EVENT_CONTACT']['pre'] = "<b>".EC_LAN_33."</b> ";
$sc_style['EVENT_CONTACT']['post'] = "&nbsp;";

$sc_style['EVENT_THREAD']['pre'] = "<tr><td colspan='2' class='forumheader3'><span class='smalltext'>";
$sc_style['EVENT_THREAD']['post'] = "</span></td></tr>";

$sc_style['EVENT_CATEGORY']['pre'] = "<b>".EC_LAN_30."</b> ";
$sc_style['EVENT_CATEGORY']['post'] = "&nbsp;";

$sc_style['EVENT_DATE_START']['pre'] = "";
$sc_style['EVENT_DATE_START']['post'] = "";

$sc_style['EVENT_DATE_END']['pre'] = "";
$sc_style['EVENT_DATE_END']['post'] = "";

$EVENT_EVENT_TABLE = "
<tr>
	<td >
		<div title='".EC_LAN_132."' class='fcaption' style='cursor:pointer; text-align:left; border:0px solid #000;' onclick=\"expandit('{EVENT_ID}')\">{EVENT_HEADING}</div>
		<div id='{EVENT_ID}' style='display:{EVENT_DISPLAYSTYLE}; padding-top:10px; padding-bottom:10px; text-align:left;'>
			<table style='width:100%;'  cellspacing='0' cellpadding='0'>
				<tr><td colspan='2' class='forumheader3'>{EVENT_AUTHOR} {EVENT_CATEGORY} {EVENT_CONTACT} {EVENT_OPTIONS}</td></tr>
				<tr><td colspan='2' class='forumheader3'>{EVENT_DATE_START} {EVENT_DATE_END}</td></tr>
				<tr><td colspan='2' class='forumheader3'>{EVENT_LOCATION}</td></tr>
				{EVENT_DETAILS}
				{EVENT_THREAD}
			</table>
		</div>
	</td>
</tr>
";


// CALENDAR SHOW EVENT ------------------------------------------------------------
$CALENDAR_SHOWEVENT = "<table cellspacing='0' cellpadding='0' style='width:100%;'><tr><td style='vertical-align:top; width:10px;'>{SHOWEVENT_IMAGE}</td><td style='vertical-align:top; width:2%;'>{SHOWEVENT_INDICAT}</td><td style='vertical-align:top;'>{SHOWEVENT_HEADING}</td></tr></table>";



// CALENDAR CALENDAR ------------------------------------------------------------
$CALENDAR_CALENDAR_START = "
<div style='text-align:center'>
<table cellpadding='0' cellspacing='1' class='fborder' style='background-color:#DDDDDD; width:100%'>";

$CALENDAR_CALENDAR_END = "
</tr></table></div>";

$CALENDAR_CALENDAR_DAY_NON = "<td style='width:12%;height:60px;'></td>";

//header row
$CALENDAR_CALENDAR_HEADER_START = "<tr>";
$CALENDAR_CALENDAR_HEADER = "<td class='fcaption' style='z-index: -1; background-color:#000; color:#FFF; width:90px; height:20px; text-align:center; vertical-align:middle;'>{CALENDAR_CALENDAR_HEADER_DAY}</td>";
$CALENDAR_CALENDAR_HEADER_END = "</tr><tr>";


$CALENDAR_CALENDAR_WEEKSWITCH = "</tr><tr>";

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


?>