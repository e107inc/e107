<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */
if (!defined('e107_INIT')) { exit; }
//if(!defined("USER_WIDTH")){ define("USER_WIDTH","width:95%"); }

// LEGACY, NOT DONNE, USING OLD LEGACY TRACK TEMPLATE. IF NEEDED, IT HAS TO BE REWORKED IN FULL
/*
if (!isset($FORUM_TRACK_START))
{
*/
// How it should be??? (LAN Shortcodes replaced by their outputed LANS...)
/*
	$FORUM_TRACK_START = "<div style='text-align:center'>\n<div class='spacer'>\n<table style='".USER_WIDTH."' class='fborder table'>\n<tr>\n<td colspan='3' style='width:60%' class='fcaption'>{LAN=FORUM_0073}</td>\n</tr>\n";
*/
// LEGACY definition with LAN Shortcodes ({TRACKTITLE}).....
/*
$FORUM_TRACK_START = "<div style='text-align:center'>\n<div class='spacer'>\n<table style='".USER_WIDTH."' class='fborder table'>\n<tr>\n<td colspan='3' style='width:60%' class='fcaption'>{TRACKTITLE}</td>\n</tr>\n";

	if (!isset($FORUM_TRACK_MAIN))
	{
		$FORUM_TRACK_MAIN = "<tr>
			<td style='text-align:center; vertical-align:middle; width:6%'  class='forumheader3'>{NEWIMAGE}</td>
			<td style='vertical-align:middle; text-align:left; width:70%'  class='forumheader3'><span class='mediumtext'>{TRACKPOSTNAME}</span></td>
			<td style='vertical-align:middle; text-align:center; width:24%'  class='forumheader3'><span class='mediumtext'>{UNTRACK}</span></td>
			</tr>";
	}
}

if (!isset($FORUM_TRACK_END))
{
	$FORUM_TRACK_END = "</table>\n</div>\n</div>";
}
*/


// New in v2.x - requires a bootstrap theme be loaded.  
$FORUM_STATS_TEMPLATE['start']       = "<div id='forum-stats'>";
$FORUM_STATS_TEMPLATE['item']        = "";
$FORUM_STATS_TEMPLATE['end']         = "</div>";

$FORUM_STATS_TEMPLATE['text_0']       = "
		<table style='width: 100%;' class='fborder table'>
			<tr><td style='width: 50%; text-align: right;'><b>".LAN_FORUM_6001.":</b>&nbsp;&nbsp;</td><td style='width: 50%;'>{OPEN_DATE}</td></tr>
			<tr><td style='width: 50%; text-align: right;'><b>".LAN_FORUM_6002.":</b>&nbsp;&nbsp;</td><td style='width: 50%;'>{OPEN_SINCE}</td></tr>
			<tr><td style='width: 50%; text-align: right;'><b>".LAN_FORUM_6003.":</b>&nbsp;&nbsp;</td><td style='width: 50%;'>{TOTAL_POSTS}</td></tr>
			<tr><td style='width: 50%; text-align: right;'><b>".LAN_FORUM_1007.":</b>&nbsp;&nbsp;</td><td style='width: 50%;'>{TOTAL_TOPICS}</td></tr>
			<tr><td style='width: 50%; text-align: right;'><b>".LAN_FORUM_6004.":</b>&nbsp;&nbsp;</td><td style='width: 50%;'>{TOTAL_REPLIES}</td></tr>
			<tr><td style='width: 50%; text-align: right;'><b>".LAN_FORUM_6005.":</b>&nbsp;&nbsp;</td><td style='width: 50%;'>{TOTAL_VIEWS}</td></tr>
			<tr><td style='width: 50%; text-align: right;'><b>".LAN_FORUM_6014.":</b>&nbsp;&nbsp;</td><td style='width: 50%;'>{POSTSPERDAY}</td></tr>
			<tr><td style='width: 50%; text-align: right;'><b>".LAN_FORUM_6006.":</b>&nbsp;&nbsp;</td><td style='width: 50%;'>{DB_SIZE}</td></tr>
			<tr><td style='width: 50%; text-align: right;'><b>".LAN_FORUM_6007.":</b>&nbsp;&nbsp;</td><td style='width: 50%;'>{AVG_ROW_LEN}</td></tr>
		</table>";

$FORUM_STATS_TEMPLATE['text_1']['start']       = "
		<table style='width: 100%;' class='fborder table'>
		<thead>
		<tr>
		<th style='width: 10%; text-align: center;' class='fcaption'>".LAN_FORUM_6008."</th>
		<th style='width: 40%;' class='fcaption'>".LAN_FORUM_1003."</th>
		<th style='width: 10%; text-align: center;' class='fcaption'>".LAN_FORUM_0003."</th>
		<th style='width: 20%; text-align: center;' class='fcaption'>".LAN_FORUM_6009."</th>
		<th style='width: 20%; text-align: center;' class='fcaption'>".LAN_DATE."</th>
		</tr>
		</thead>
		";

$FORUM_STATS_TEMPLATE['text_1']['item']       = "
			<tr>
			<td style='width: 10%; text-align: center;' class='forumheader3'>{COUNT}</td>
			<td style='width: 40%;' class='forumheader3'><a href='{URL}'>{THREAD_NAME}</a></td>
			<td style='width: 10%; text-align: center;' class='forumheader3'>{THREAD_TOTAL_REPLIES}</td>
			<td style='width: 20%; text-align: center;' class='forumheader3'>{UINFO}</td>
			<td style='width: 20%; text-align: center;' class='forumheader3'>{THREAD_DATESTAMP}</td>
			</tr>
";

$FORUM_STATS_TEMPLATE['text_1']['end']       =		"</table>";

$FORUM_STATS_TEMPLATE['text_2']['start']       = "
		<table style='width: 100%;' class='fborder table'>
		<thead>
		<tr>
		<th style='width: 10%; text-align: center;' class='fcaption'>".LAN_FORUM_6008."</th>
		<th style='width: 40%;' class='fcaption'>".LAN_FORUM_1003."</th>
		<th style='width: 10%; text-align: center;' class='fcaption'>".LAN_FORUM_1005."</th>
		<th style='width: 20%; text-align: center;' class='fcaption'>".LAN_FORUM_6009."</th>
		<th style='width: 20%; text-align: center;' class='fcaption'>".LAN_DATE."</th>
		</tr>
		</thead>
		";

$FORUM_STATS_TEMPLATE['text_2']['item']       = "
			<tr>
			<td style='width: 10%; text-align: center;' class='forumheader3'>{COUNT}</td>
			<td style='width: 40%;' class='forumheader3'><a href='{URL}'>{THREAD_NAME}</a></td>
			<td style='width: 10%; text-align: center;' class='forumheader3'>{THREAD_VIEWS}</td>
			<td style='width: 20%; text-align: center;' class='forumheader3'>{UINFO}</td>
			<td style='width: 20%; text-align: center;' class='forumheader3'>{THREAD_DATESTAMP}</td>
			</tr>
";

$FORUM_STATS_TEMPLATE['text_2']['end']       =		"</table>";

$FORUM_STATS_TEMPLATE['text_3']['start']       = "
		<table style='width: 100%;' class='fborder table'>
		<thead>
		<tr>
		<th style='width: 10%; text-align: center;' class='fcaption'>".LAN_FORUM_6008."</th>
		<th style='width: 20%;' class='fcaption'>".LAN_NAME."</th>
		<th style='width: 10%; text-align: center;' class='fcaption'>".LAN_FORUM_2032."</th>
		<th style='width: 10%; text-align: center;' class='fcaption'>%</th>
		<th style='width: 50%; text-align: center;' class='fcaption'>&nbsp;</th>
		</tr>
		</thead>
		<tbody>
		";

$FORUM_STATS_TEMPLATE['text_3']['item']       = "
<tr>
			<td style='width: 10%; text-align: center;' class='forumheader3'>{COUNT}</td>
			<td style='width: 20%;' class='forumheader3'><a href='{USER_URL}'>{USER_NAME}</a></td>
			<td style='width: 10%; text-align: center;' class='forumheader3'>{USER_FORUMS}</td>
			<td style='width: 10%; text-align: center;' class='forumheader3'>{USER_PERCENTAGE}%</td>
			<td style='width: 50%;' class='forumheader3'>{PERCENTAGE_BAR}</td>
			</tr>
";

$FORUM_STATS_TEMPLATE['text_3']['end']       =		"</tbody>
		</table>";

		
$FORUM_STATS_TEMPLATE['text_4']['start']       = "
<table style='width: 100%;' class='fborder table'>
		<thead>
		<tr>
		<th style='width: 10%; text-align: center;' class='fcaption'>".LAN_FORUM_6008."</th>
		<th style='width: 20%;' class='fcaption'>".LAN_NAME."</th>
		<th style='width: 10%; text-align: center;' class='fcaption'>".LAN_FORUM_2032."</th>
		<th style='width: 10%; text-align: center;' class='fcaption'>%</th>
		<th style='width: 50%; text-align: center;' class='fcaption'>&nbsp;</th>
		</tr>
		</thead>
";

$FORUM_STATS_TEMPLATE['text_4']['item']       = "
<tr>
			<td style='width: 10%; text-align: center;' class='forumheader3'>{COUNT}</td>
			<td style='width: 20%;' class='forumheader3'><a href='{USER_URL}'>{USER_NAME}</a></td>
			<td style='width: 10%; text-align: center;' class='forumheader3'>{USER_FORUMS}</td>
			<td style='width: 10%; text-align: center;' class='forumheader3'>{USER_PERCENTAGE}%</td>
			<td style='width: 50%;' class='forumheader3'>{PERCENTAGE_BAR}</td>
			</tr>
";

$FORUM_STATS_TEMPLATE['text_4']['end']       =		"</table>";

		
$FORUM_STATS_TEMPLATE['text_5']['start']       = "
		<table style='width: 100%;' class='fborder table'>
		<thead>
		<tr>
		<th style='width: 10%; text-align: center;' class='fcaption'>".LAN_FORUM_6008."</th>
		<th style='width: 20%;' class='fcaption'>".LAN_NAME."</th>
		<th style='width: 10%; text-align: center;' class='fcaption'>".LAN_FORUM_2032."</th>
		<th style='width: 10%; text-align: center;' class='fcaption'>%</th>
		<th style='width: 50%; text-align: center;' class='fcaption'>&nbsp;</th>
		</tr>
		</thead>
";

$FORUM_STATS_TEMPLATE['text_5']['item']       = "
<tr>
			<td style='width: 10%; text-align: center;' class='forumheader3'>{COUNT}</td>
			<td style='width: 20%;' class='forumheader3'><a href='{USER_URL}'>{USER_NAME}</a></td>
			<td style='width: 10%; text-align: center;' class='forumheader3'>{USER_FORUMS}</td>
			<td style='width: 10%; text-align: center;' class='forumheader3'>{USER_PERCENTAGE}%</td>
			<td style='width: 50%;' class='forumheader3'>{PERCENTAGE_BAR}</td>
			</tr>
";

$FORUM_STATS_TEMPLATE['text_5']['end']       =		"</table>";