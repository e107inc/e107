<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Forum statistics template - Bootstrap 5
 *
 */

if (!defined('e107_INIT')) { exit(); }

$FORUM_STATS_TEMPLATE['summary']['caption'] = LAN_FORUM_6000;
$FORUM_STATS_TEMPLATE['summary']['item']    = '
	<div class="table-responsive">
		<table class="table align-middle">
			<tbody>
				<tr><th scope="row" class="w-50 text-end">'.LAN_FORUM_6001.':</th><td class="w-50">{OPEN_DATE}</td></tr>
				<tr><th scope="row" class="w-50 text-end">'.LAN_FORUM_6002.':</th><td class="w-50">{OPEN_SINCE}</td></tr>
				<tr><th scope="row" class="w-50 text-end">'.LAN_FORUM_6003.':</th><td class="w-50">{TOTAL_POSTS}</td></tr>
				<tr><th scope="row" class="w-50 text-end">'.LAN_FORUM_1007.':</th><td class="w-50">{TOTAL_TOPICS}</td></tr>
				<tr><th scope="row" class="w-50 text-end">'.LAN_FORUM_6004.':</th><td class="w-50">{TOTAL_REPLIES}</td></tr>
				<tr><th scope="row" class="w-50 text-end">'.LAN_FORUM_6005.':</th><td class="w-50">{TOTAL_VIEWS}</td></tr>
				<tr><th scope="row" class="w-50 text-end">'.LAN_FORUM_6014.':</th><td class="w-50">{POSTSPERDAY}</td></tr>
				<tr><th scope="row" class="w-50 text-end">'.LAN_FORUM_6006.':</th><td class="w-50">{DB_SIZE}</td></tr>
				<tr><th scope="row" class="w-50 text-end">'.LAN_FORUM_6007.':</th><td class="w-50">{AVG_ROW_LEN}</td></tr>
			</tbody>
		</table>
	</div>';


$FORUM_STATS_TEMPLATE['most_active']['caption'] = LAN_FORUM_0011;
$FORUM_STATS_TEMPLATE['most_active']['start']   = '
	<div class="table-responsive">
		<table class="table table-striped align-middle">
			<thead>
				<tr>
					<th scope="col" class="text-center">'.LAN_FORUM_6008.'</th>
					<th scope="col">'.LAN_FORUM_1003.'</th>
					<th scope="col" class="text-center">'.LAN_FORUM_0003.'</th>
					<th scope="col" class="text-center">'.LAN_FORUM_6009.'</th>
					<th scope="col" class="text-center">'.LAN_DATE.'</th>
				</tr>
			</thead>
			<tbody>';
$FORUM_STATS_TEMPLATE['most_active']['item']    = '
				<tr>
					<td class="text-center">{COUNT}</td>
					<td><a href="{URL}">{THREAD_NAME}</a></td>
					<td class="text-center">{THREAD_TOTAL_REPLIES}</td>
					<td class="text-center">{UINFO}</td>
					<td class="text-center">{THREAD_DATESTAMP}</td>
				</tr>';
$FORUM_STATS_TEMPLATE['most_active']['end']     = '
			</tbody>
		</table>
	</div>';


$FORUM_STATS_TEMPLATE['most_viewed']['caption'] = LAN_FORUM_6010;
$FORUM_STATS_TEMPLATE['most_viewed']['start']   = '
	<div class="table-responsive">
		<table class="table table-striped align-middle">
			<thead>
				<tr>
					<th scope="col" class="text-center">'.LAN_FORUM_6008.'</th>
					<th scope="col">'.LAN_FORUM_1003.'</th>
					<th scope="col" class="text-center">'.LAN_FORUM_1005.'</th>
					<th scope="col" class="text-center">'.LAN_FORUM_6009.'</th>
					<th scope="col" class="text-center">'.LAN_DATE.'</th>
				</tr>
			</thead>
			<tbody>';
$FORUM_STATS_TEMPLATE['most_viewed']['item']    = '
				<tr>
					<td class="text-center">{COUNT}</td>
					<td><a href="{URL}">{THREAD_NAME}</a></td>
					<td class="text-center">{THREAD_VIEWS}</td>
					<td class="text-center">{UINFO}</td>
					<td class="text-center">{THREAD_DATESTAMP}</td>
				</tr>';
$FORUM_STATS_TEMPLATE['most_viewed']['end']     = '
			</tbody>
		</table>
	</div>';


$FORUM_STATS_TEMPLATE['top_posters']['caption'] = LAN_FORUM_0010;
$FORUM_STATS_TEMPLATE['top_posters']['start']   = '
	<div class="table-responsive">
		<table class="table table-striped align-middle">
			<thead>
				<tr>
					<th scope="col" class="text-center">'.LAN_FORUM_6008.'</th>
					<th scope="col">'.LAN_NAME.'</th>
					<th scope="col" class="text-center">'.LAN_FORUM_2032.'</th>
					<th scope="col" class="text-center">%</th>
					<th scope="col" class="w-50"></th>
				</tr>
			</thead>
			<tbody>';
$FORUM_STATS_TEMPLATE['top_posters']['item']    = '
				<tr>
					<td class="text-center">{COUNT}</td>
					<td><a href="{USER_URL}">{USER_NAME}</a></td>
					<td class="text-center">{USER_FORUMS}</td>
					<td class="text-center">{USER_PERCENTAGE}%</td>
					<td class="w-50">{PERCENTAGE_BAR}</td>
				</tr>';
$FORUM_STATS_TEMPLATE['top_posters']['end']     = '
			</tbody>
		</table>
	</div>';


$FORUM_STATS_TEMPLATE['top_starters']['caption'] = LAN_FORUM_6011;
$FORUM_STATS_TEMPLATE['top_starters']['start']   = '
	<div class="table-responsive">
		<table class="table table-striped align-middle">
			<thead>
				<tr>
					<th scope="col" class="text-center">'.LAN_FORUM_6008.'</th>
					<th scope="col">'.LAN_NAME.'</th>
					<th scope="col" class="text-center">'.LAN_FORUM_2032.'</th>
					<th scope="col" class="text-center">%</th>
					<th scope="col" class="w-50"></th>
				</tr>
			</thead>
			<tbody>';
$FORUM_STATS_TEMPLATE['top_starters']['item']    = '
				<tr>
					<td class="text-center">{COUNT}</td>
					<td><a href="{USER_URL}">{USER_NAME}</a></td>
					<td class="text-center">{USER_FORUMS}</td>
					<td class="text-center">{USER_PERCENTAGE}%</td>
					<td class="w-50">{PERCENTAGE_BAR}</td>
				</tr>';
$FORUM_STATS_TEMPLATE['top_starters']['end']     = '
			</tbody>
		</table>
	</div>';


$FORUM_STATS_TEMPLATE['top_repliers']['caption'] = LAN_FORUM_6012;
$FORUM_STATS_TEMPLATE['top_repliers']['start']   = '
	<div class="table-responsive">
		<table class="table table-striped align-middle">
			<thead>
				<tr>
					<th scope="col" class="text-center">'.LAN_FORUM_6008.'</th>
					<th scope="col">'.LAN_NAME.'</th>
					<th scope="col" class="text-center">'.LAN_FORUM_2032.'</th>
					<th scope="col" class="text-center">%</th>
					<th scope="col" class="w-50"></th>
				</tr>
			</thead>
			<tbody>';
$FORUM_STATS_TEMPLATE['top_repliers']['item']    = '
				<tr>
					<td class="text-center">{COUNT}</td>
					<td><a href="{USER_URL}">{USER_NAME}</a></td>
					<td class="text-center">{USER_FORUMS}</td>
					<td class="text-center">{USER_PERCENTAGE}%</td>
					<td class="w-50">{PERCENTAGE_BAR}</td>
				</tr>';
$FORUM_STATS_TEMPLATE['top_repliers']['end']     = '
			</tbody>
		</table>
	</div>';
