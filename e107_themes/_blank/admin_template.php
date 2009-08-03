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
 * $Revision: 1.17 $
 * $Date: 2009-08-03 19:41:17 $
 * $Author: marj_nl_fr $
 *
*/

if (!defined('e107_INIT')) { exit(); }

define("ADLINK_COLS",5);





include_lan(e_THEME."_blank/languages/".e_LANGUAGE.".php");

//{FS_ADMIN_ALT_NAV}
$ADMIN_HEADER = "
<div class='admin-wrapper'>
	<div class='admin-header'>
		<div class='admin-header-content'>
			<div class='f-right'><!-- -->{ADMIN_LANG=nobutton&nomenu}</div>
			{ADMIN_LOGO}
			{ADMIN_LOGGED}
			{ADMIN_SEL_LAN}

		</div>
		<div style='height: 20px;'><!-- --></div>
		<div class='admin-navigation'>
			<div id='nav'>{ADMIN_NAVIGATION}</div>
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
						{ADMIN_MENUMANAGER} 
						{ADMIN_PRESET}

						{SETSTYLE=none}
						{ADMIN_PWORD}
						{ADMIN_STATUS=request}
						{ADMIN_LATEST=request}
						{ADMIN_LOG=request}
						{ADMIN_MSG}
						{ADMIN_PLUGINS}
						{ADMIN_UPDATE}

						{SETSTYLE=site_info}

						{ADMIN_HELP}

					</div>
				</td>
			</tr>
		</table>
	</div>
	<div class='admin-footer'>
		<!-- -->
	</div>
</div>
";

/* NEW ADMIN MENU TEMPLATE
 * see function e_admin_menu() in e107_admin/header.php
 */
$E_ADMIN_MENU['start'] = '
<ul class="plugin-navigation">
';

$E_ADMIN_MENU['button'] = '
	<li>
		<a class="link{LINK_CLASS}" href="{LINK_URL}"{ID}{ONCLICK}>&raquo;&nbsp;{LINK_TEXT}</a>
		{SUB_MENU}
	</li>
';
$E_ADMIN_MENU['button_active'] = '
	<li>
		<a class="link-active{LINK_CLASS}" href="{LINK_URL}"{ID}{ONCLICK}>&raquo;&nbsp;{LINK_TEXT}</a>
		{SUB_MENU}
	</li>
';

$E_ADMIN_MENU['start_sub'] = '
		<ul class="plugin-navigation-sub{SUB_CLASS}"{SUB_ID}>
';

$E_ADMIN_MENU['button_sub'] = '
			<li>
				<a class="link" href="{LINK_URL}">&raquo;&nbsp;{LINK_TEXT}</a>
				{SUB_MENU}
			</li>
';
$E_ADMIN_MENU['button_active_sub'] = '
			<li>
				<a class="link-active" href="{LINK_URL}">&raquo;&nbsp;{LINK_TEXT}</a>
				{SUB_MENU}
			</li>
';

$E_ADMIN_MENU['end_sub'] = '
		</ul>
';

$E_ADMIN_MENU['end'] = '
</ul>
';

/* NEW ADMIN SLIDE DOWN MENU TEMPLATE
 * see function admin_navigation() in e107_files/shortcodes/admin_navigation.php
 * TODO move it together with menu.css/menu.js to the theme templates/e107_files folder (default menu render)
 */
$E_ADMIN_NAVIGATION['start'] = '
<ul id="nav-links">
';

$E_ADMIN_NAVIGATION['button'] = '
	<li>
		<a class="menuButton" href="{LINK_URL}"{ONCLICK}>{LINK_IMAGE}{LINK_TEXT}</a>
		{SUB_MENU}
	</li>
';
$E_ADMIN_NAVIGATION['button_active'] = '
	<li>
		<a class="menuButton active" href="{LINK_URL}"{ONCLICK}>{LINK_IMAGE}{LINK_TEXT}</a>
		{SUB_MENU}
	</li>
';

$E_ADMIN_NAVIGATION['start_sub'] = '
		<ul class="menu"{SUB_ID}>
';

$E_ADMIN_NAVIGATION['button_sub'] = '
			<li>
				<a class="menuItem{SUB_CLASS}" href="{LINK_URL}"{ONCLICK}>{LINK_IMAGE}{LINK_TEXT}</a>
				{SUB_MENU}
			</li>
';
$E_ADMIN_NAVIGATION['button_active_sub'] = '
			<li>
				<a class="menuItem{SUB_CLASS}" href="{LINK_URL}"{ONCLICK}>{LINK_IMAGE}{LINK_TEXT}</a>
				{SUB_MENU}
			</li>
';

$E_ADMIN_NAVIGATION['end_sub'] = '
		</ul>
';

$E_ADMIN_NAVIGATION['end'] = '
</ul>
';
?>