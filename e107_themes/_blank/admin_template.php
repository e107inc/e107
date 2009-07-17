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
 * $Revision: 1.13 $
 * $Date: 2009-07-17 03:53:14 $
 * $Author: e107coders $
 *
*/

if (!defined('e107_INIT')) { exit; }

define("ADLINK_COLS",5);


if (!defined('ADMIN_TRUE_ICON'))
{
	define("ADMIN_TRUE_ICON", "<img class='icon action S32' src='".e_IMAGE_ABS."admin_images/true_32.png' alt='' />");
	define("ADMIN_TRUE_ICON_PATH", e_IMAGE."admin_images/true_32.png");
}

if (!defined('ADMIN_FALSE_ICON'))
{
	define("ADMIN_FALSE_ICON", "<img class='icon action S32' src='".e_IMAGE_ABS."admin_images/false_32.png' alt='' />");
	define("ADMIN_FALSE_ICON_PATH", e_IMAGE."admin_images/false_32.png");
}

if (!defined('ADMIN_EDIT_ICON'))
{
	define("ADMIN_EDIT_ICON", "<img class='icon action S32' src='".e_IMAGE_ABS."admin_images/edit_32.png' alt='' title='".LAN_EDIT."' />");
	define("ADMIN_EDIT_ICON_PATH", e_IMAGE."admin_images/edit_32.png");
}

if (!defined('ADMIN_DELETE_ICON'))
{
	define("ADMIN_DELETE_ICON", "<img class='icon action S32' src='".e_IMAGE_ABS."admin_images/delete_32.png' alt='' title='".LAN_DELETE."' />");
	define("ADMIN_DELETE_ICON_PATH", e_IMAGE."admin_images/delete_32.png");
}

if (!defined('ADMIN_WARNING_ICON'))
{
	define("ADMIN_WARNING_ICON", "<img class='icon action S32' src='".e_IMAGE_ABS."admin_images/warning_32.png' alt='' />");
	define("ADMIN_WARNING_ICON_PATH", e_IMAGE."admin_images/warning_32.png");
}

if (!defined('ADMIN_INFO_ICON'))
{
	define("ADMIN_INFO_ICON", "<img class='icon action S32' src='".e_IMAGE_ABS."admin_images/info_32.png' alt='' />");
	define("ADMIN_INFO_ICON_PATH", e_IMAGE."admin_images/info_32.png");
}

if (!defined('ADMIN_CONFIGURE_ICON'))
{
	define("ADMIN_CONFIGURE_ICON", "<img class='icon action S32' src='".e_IMAGE_ABS."admin_images/cat_tools_32.png' alt='' />");
	define("ADMIN_CONFIGURE_ICON_PATH", e_IMAGE."admin_images/cat_tools_32.png");
}

if (!defined('ADMIN_VIEW_ICON'))
{
	define("ADMIN_VIEW_ICON", "<img class='icon action S32' src='".e_IMAGE_ABS."admin_images/search_32.png' alt='' />");
	define("ADMIN_VIEW_ICON_PATH", e_IMAGE."admin_images/admin_images/search_32.png");
}

if (!defined('ADMIN_URL_ICON'))
{
	define("ADMIN_URL_ICON", "<img class='icon action S32' src='".e_IMAGE_ABS."admin_images/forums_32.png' alt='' />");
	define("ADMIN_URL_ICON_PATH", e_IMAGE."admin_images/forums_32.png");
}

if (!defined('ADMIN_INSTALLPLUGIN_ICON'))
{
	define("ADMIN_INSTALLPLUGIN_ICON", "<img class='icon action S32' src='".e_IMAGE_ABS."admin_images/plugin_install_32.png' alt='' />");
	define("ADMIN_INSTALLPLUGIN_ICON_PATH", e_IMAGE."admin_images/plugin_install_32.png");
}

if (!defined('ADMIN_UNINSTALLPLUGIN_ICON'))
{
	define("ADMIN_UNINSTALLPLUGIN_ICON", "<img class='icon action S32' src='".e_IMAGE_ABS."admin_images/plugin_uninstall_32.png' alt='' />");
	define("ADMIN_UNINSTALLPLUGIN_ICON_PATH", e_IMAGE."admin_images/plugin_unstall_32.png");
}

if (!defined('ADMIN_UPGRADEPLUGIN_ICON'))
{
	define("ADMIN_UPGRADEPLUGIN_ICON", "<img class='icon action S32' src='".e_IMAGE_ABS."admin_images/up_32.png' alt='' />");
	define("ADMIN_UPGRADEPLUGIN_ICON_PATH", e_IMAGE."admin_images/up_32.png");
}

if (!defined('ADMIN_UP_ICON'))
{
	define("ADMIN_UP_ICON", "<img class='icon action S32' src='".e_IMAGE_ABS."admin_images/up_32.png' alt='' title='".LAN_DELETE."' />");
	define("ADMIN_UP_ICON_PATH", e_IMAGE."admin_images/up_32.png");
}

if (!defined('ADMIN_DOWN_ICON'))
{
	define("ADMIN_DOWN_ICON", "<img class='icon action S32' src='".e_IMAGE_ABS."admin_images/down_32.png' alt='' title='".LAN_DELETE."' />");
	define("ADMIN_DOWN_ICON_PATH", e_IMAGE."admin_images/down_32.png");
}




include_lan(THEME."languages/".e_LANGUAGE.".php");

//{FS_ADMIN_ALT_NAV}
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