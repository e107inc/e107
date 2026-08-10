<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Forum statistics template - default
 *
 */

if (!defined('e107_INIT')) { exit(); }

/*
 * Each block below is one panel of forum_stats.php. On a Bootstrap theme the
 * six panels become tabs; otherwise they are stacked under their captions.
 *
 * 'caption' names the panel. 'start' / 'item' / 'end' wrap a table whose
 * 'item' is repeated per row; 'summary' has no rows and so has no item.
 *
 * Shortcodes come from shortcodes/batch/stats_shortcodes.php.
 */

$FORUM_STATS_TEMPLATE['summary']['caption'] = LAN_FORUM_6000;
$FORUM_STATS_TEMPLATE['summary']['item']    = "
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


$FORUM_STATS_TEMPLATE['most_active']['caption'] = LAN_FORUM_0011;
$FORUM_STATS_TEMPLATE['most_active']['start']   = "
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
$FORUM_STATS_TEMPLATE['most_active']['item']    = "
			<tr>
			<td style='width: 10%; text-align: center;' class='forumheader3'>{COUNT}</td>
			<td style='width: 40%;' class='forumheader3'><a href='{URL}'>{THREAD_NAME}</a></td>
			<td style='width: 10%; text-align: center;' class='forumheader3'>{THREAD_TOTAL_REPLIES}</td>
			<td style='width: 20%; text-align: center;' class='forumheader3'>{UINFO}</td>
			<td style='width: 20%; text-align: center;' class='forumheader3'>{THREAD_DATESTAMP}</td>
			</tr>
			";
$FORUM_STATS_TEMPLATE['most_active']['end']     = "</table>";


$FORUM_STATS_TEMPLATE['most_viewed']['caption'] = LAN_FORUM_6010;
$FORUM_STATS_TEMPLATE['most_viewed']['start']   = "
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
$FORUM_STATS_TEMPLATE['most_viewed']['item']    = "
			<tr>
			<td style='width: 10%; text-align: center;' class='forumheader3'>{COUNT}</td>
			<td style='width: 40%;' class='forumheader3'><a href='{URL}'>{THREAD_NAME}</a></td>
			<td style='width: 10%; text-align: center;' class='forumheader3'>{THREAD_VIEWS}</td>
			<td style='width: 20%; text-align: center;' class='forumheader3'>{UINFO}</td>
			<td style='width: 20%; text-align: center;' class='forumheader3'>{THREAD_DATESTAMP}</td>
			</tr>
			";
$FORUM_STATS_TEMPLATE['most_viewed']['end']     = "</table>";


$FORUM_STATS_TEMPLATE['top_posters']['caption'] = LAN_FORUM_0010;
$FORUM_STATS_TEMPLATE['top_posters']['start']   = "
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
$FORUM_STATS_TEMPLATE['top_posters']['item']    = "<tr>
			<td style='width: 10%; text-align: center;' class='forumheader3'>{COUNT}</td>
			<td style='width: 20%;' class='forumheader3'><a href='{USER_URL}'>{USER_NAME}</a></td>
			<td style='width: 10%; text-align: center;' class='forumheader3'>{USER_FORUMS}</td>
			<td style='width: 10%; text-align: center;' class='forumheader3'>{USER_PERCENTAGE}%</td>
			<td style='width: 50%;' class='forumheader3'>{PERCENTAGE_BAR}
			</td>
			</tr>
			";
$FORUM_STATS_TEMPLATE['top_posters']['end']     = "</tbody>
		</table>
		";


$FORUM_STATS_TEMPLATE['top_starters']['caption'] = LAN_FORUM_6011;
$FORUM_STATS_TEMPLATE['top_starters']['start']   = "
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
$FORUM_STATS_TEMPLATE['top_starters']['item']    = "<tr>
			<td style='width: 10%; text-align: center;' class='forumheader3'>{COUNT}</td>
			<td style='width: 20%;' class='forumheader3'><a href='{USER_URL}'>{USER_NAME}</a></td>
			<td style='width: 10%; text-align: center;' class='forumheader3'>{USER_FORUMS}</td>
			<td style='width: 10%; text-align: center;' class='forumheader3'>{USER_PERCENTAGE}%</td>
			<td style='width: 50%; text-align: center;' class='forumheader3'>{PERCENTAGE_BAR}</td>
			</tr>
			";
$FORUM_STATS_TEMPLATE['top_starters']['end']     = "</table>";


$FORUM_STATS_TEMPLATE['top_repliers']['caption'] = LAN_FORUM_6012;
$FORUM_STATS_TEMPLATE['top_repliers']['start']   = "
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
$FORUM_STATS_TEMPLATE['top_repliers']['item']    = "
			<tr>
			<td style='width: 10%; text-align: center;' class='forumheader3'>{COUNT}</td>
			<td style='width: 20%;' class='forumheader3'><a href='{USER_URL}'>{USER_NAME}</a></td>
			<td style='width: 10%; text-align: center;' class='forumheader3'>{USER_FORUMS}</td>
			<td style='width: 10%; text-align: center;' class='forumheader3'>{USER_PERCENTAGE}%</td>
			<td style='width: 50%; text-align: center;' class='forumheader3'>{PERCENTAGE_BAR}</td>
			</tr>
			";
$FORUM_STATS_TEMPLATE['top_repliers']['end']     = "</table>";
