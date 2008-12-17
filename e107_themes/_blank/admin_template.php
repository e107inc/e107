<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2008 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Admin template - _blank theme
 *
 * $Source: /cvs_backup/e107_0.8/e107_themes/_blank/admin_template.php,v $
 * $Revision: 1.7 $
 * $Date: 2008-12-17 17:27:07 $
 * $Author: secretr $
 *
*/

if (!defined('e107_INIT')) { exit; }

define("ADLINK_COLS",5);
include_lan(THEME."languages/".e_LANGUAGE.".php");


$ADMIN_HEADER = "
<div class='admin-wrapper'>
	<div class='admin-header'>
		<div class='admin-header-content'>
			<div class='f-right'><!-- -->{ADMIN_LANG=nobutton&nomenu}</div>
			{ADMIN_LOGO}
			{ADMIN_LOGGED}
			{ADMIN_SEL_LAN}
			{ADMIN_USERLAN}
		</div>
		<div style='height: 20px;'><!-- --></div>
		<div class='admin-navigation'>
			{FS_ADMIN_ALT_NAV}
			<div class='clear'><!-- --></div>
		</div>
	</div>
	<div class='admin-page-body'>
		<table class='main-table' cellpadding='0' cellspacing='0'>
			<tr>
				<!--
				<td class='col-left'></td>
				-->
				<td>
					<div class='col-main'>
						<div class='inner-wrapper'>
						{SETSTYLE=admin_content}
";
/*
	{SETSTYLE=admin_menu}
	<!--
	{ADMIN_NAV}
	-->
		{ADMIN_LANG}

		{ADMIN_SITEINFO}

		{ADMIN_DOCS}
 */
$ADMIN_FOOTER = "
						</div>
					</div>
				</td>
				<td class='col-right'>
					<div class='col-right'>

						{SETSTYLE=admin_menu}
						{ADMIN_MENU}

						{SETSTYLE=none}

						{ADMIN_PWORD}
						{ADMIN_STATUS=request}
						{ADMIN_LATEST=request}
						{ADMIN_LOG=request}
						{ADMIN_MSG}
						{ADMIN_PLUGINS}

						{ADMIN_PRESET}
						{ADMIN_UPDATE}
						{SETSTYLE=site_info}
						{ADMIN_HELP}
					</div>
				</td>
			</tr>
		</table>
	</div>
	<div class='admin-footer'>
		{ADMIN_CREDITS}
	</div>
</div>
";

/* REEDIT

 * function show_admin_menu() in e107_admin/header.php

*/

$BUTTONS_START = '
<ul class="plugin-navigation">
';
$BUTTON = '
	<li>
		<a class="link" href="{LINK_URL}"{ONCLICK}>&raquo;&nbsp;{LINK_TEXT}</a>
	</li>
';
$BUTTON_OVER = '
	<li>
		<a class="link-active" href="{LINK_URL}"{ONCLICK}>&raquo;&nbsp;{LINK_TEXT}</a>
	</li>
';
$SUB_BUTTONS_START = '
<ul class="plugin-navigation">
	<li>
		<a class="link" href="{LINK_URL}"onclick="expandit(\'{SUB_HEAD_ID}\');" >&raquo;&nbsp;{SUB_HEAD}</a>
		<ul class="sub-nav" id="{SUB_HEAD_ID}" style="display: none">
';
$SUB_BUTTON = '
			<li>
				<a class="link" href="{LINK_URL}">&raquo;&nbsp;{LINK_TEXT}</a>
			</li>
';
$SUB_BUTTON_OVER = '
			<li>
				<a class="link-active" href="{LINK_URL}">&raquo;&nbsp;{LINK_TEXT}</a>
			</li>
';
$SUB_BUTTONS_END = '
		</ul>
	</li>
</ul>
';
$BUTTONS_END = '
</ul>
';


?>