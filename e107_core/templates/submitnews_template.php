<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Submit news template - default
 */

if(!defined('e107_INIT')) { exit; }

/*
 * Three fields are not always there: name and email are a guest's, and the
 * attachment row appears only where uploads are on. Their rows are wrappers, so
 * that a shortcode returning nothing takes its row with it. Every other row is
 * in the body below, where a theme overriding only $SUBMITNEWS_TEMPLATE can
 * still see it.
 *
 * Shortcodes come from e107_core/shortcodes/batch/submitnews_shortcodes.php.
 */

$SUBMITNEWS_WRAPPER['form']['SUBMITNEWS_NAME'] = "
				<tr>
					<td style='width:20%' class='forumheader3'>{LAN=NAME}</td>
					<td style='width:80%' class='forumheader3'>{---}</td>
				</tr>";

$SUBMITNEWS_WRAPPER['form']['SUBMITNEWS_EMAIL'] = "
				<tr>
					<td style='width:20%' class='forumheader3'>{LAN=EMAIL}</td>
					<td style='width:80%' class='forumheader3'>{---}</td>
				</tr>";

$SUBMITNEWS_WRAPPER['form']['SUBMITNEWS_ATTACH'] = "
				<tr>
					<td style='width:20%' class='forumheader3'>{LAN=SUBNEWSLAN_5}<br /><span class='smalltext'>{LAN=SUBNEWSLAN_6}</span></td>
					<td style='width:80%' class='forumheader3'>{---}</td>
				</tr>";

$SUBMITNEWS_TEMPLATE['form'] = "{SUBMITNEWS_SUBHEADER}
		<div>
			<form id='dataform' method='post' action='".e_SELF."' enctype='multipart/form-data' onsubmit='return frmVerify()'>
				<table class='table fborder'>
				{SUBMITNEWS_NAME}
				{SUBMITNEWS_EMAIL}
				<tr>
					<td style='width:20%' class='forumheader3'>{LAN=CATEGORY}</td>
					<td style='width:80%' class='forumheader3'>{SUBMITNEWS_CATEGORY}</td>
				</tr>
				<tr>
					<td style='width:20%' class='forumheader3'>{LAN=TITLE}</td>
					<td style='width:80%' class='forumheader3'>{SUBMITNEWS_TITLE}</td>
				</tr>
				<tr>
					<td style='width:20%' class='forumheader3'>{LAN=135}</td>
					<td style='width:80%' class='forumheader3'>{SUBMITNEWS_BODY}</td>
				</tr>
				<tr>
					<td style='width:20%' class='forumheader3'>{LAN=SUBNEWSLAN_9}</td>
					<td style='width:80%' class='forumheader3'>{SUBMITNEWS_KEYWORDS}</td>
				</tr>
				<tr>
					<td style='width:20%' class='forumheader3'>{LAN=SUMMARY}</td>
					<td style='width:80%' class='forumheader3'>{SUBMITNEWS_SUMMARY}</td>
				</tr>
				<tr>
					<td style='width:20%' class='forumheader3'>{LAN=META_DESCRIPTION}</td>
					<td style='width:80%' class='forumheader3'>{SUBMITNEWS_DESCRIPTION}</td>
				</tr>
				<tr>
					<td style='width:20%' class='forumheader3'>{LAN=SUBNEWSLAN_13}</td>
					<td style='width:80%' class='forumheader3'>{SUBMITNEWS_MEDIA}</td>
				</tr>
				{SUBMITNEWS_ATTACH}
				<tr>
					<td colspan='2' style='text-align:center' class='forumheader'>
						{SUBMITNEWS_SUBMIT}
					</td>
				</tr>
				</table>
			</form>
		</div>";
