<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_themes/jayya/admin_template.php,v $
 * $Revision: 1.9 $
 * $Date: 2009-11-18 01:06:02 $
 * $Author: e107coders $
 */

if (!defined('e107_INIT')) { exit; }

define("LINKSRENDERONLYMAIN", true);

// [prerenders]

$style = "leftmenu";
$prehelp = $tp -> parseTemplate('{ADMIN_HELP}');

$style = "rightmenu";
$pre_admin_menu = $tp -> parseTemplate('{ADMIN_MENU=pre}');
$preright = $tp -> parseTemplate('{ADMIN_STATUS=request}');
$preright .= $tp -> parseTemplate('{ADMIN_LATEST=request}');
$preright .= $tp -> parseTemplate('{ADMIN_PRESET}');
$preright .= $tp -> parseTemplate('{ADMIN_LOG=request}');
$style = "default";

// [layout]

$ADMIN_HEADER = "<table class='page_container'>
<tr>
<td>

<table class='top_section'>
<tr>
<td class='top_section_left' style='padding-left: 5px; padding-right: 5px'>
{ADMIN_LOGO}
</td>
<td class='top_section_mid' style='width: 100%'>
<div style='margin-bottom: 3px;'>
{ADMIN_LOGGED}
{ADMIN_SEL_LAN}
</div>
{SITELINKS=flat}
</td>

<td class='top_section_right' style='padding: 0px 18px 0px 18px; width: 68px'>
<div style='height: 32px;'>
{ADMIN_ICON}
</div>
</td>
</tr>
</table>";

if (ADMIN) {
	$ADMIN_HEADER .= "{ADMIN_ALT_NAV}";
} else {
	if (file_exists(THEME.'admin_nav.js')) {
		$ADMIN_HEADER .= "<script type='text/javascript' src='".THEME."admin_nav.js'></script>";
	} else {
		$ADMIN_HEADER .= "<script type='text/javascript' src='".e_FILE."admin_nav.js'></script>";
	}

	$ADMIN_HEADER .= "<div style='width: 100%'><table style='width:100%; border-collapse: collapse; border-spacing: 0px;'>
	<tr><td>
	<div class='menuBar' style='width:100%;'>
	&nbsp;
	</div>
	</td>
	</tr>
	</table></div>";
}

$ADMIN_HEADER .= "<table class='main_section'>
<tr>";
if(ADMIN)
{
    $ADMIN_HEADER .= "
<td class='left_menu'>
<table style='width:100%; border-collapse: collapse; border-spacing: 0px;'>
<tr>
<td>
{SETSTYLE=leftmenu}
{ADMIN_LANG}
{ADMIN_PWORD}
{ADMIN_MSG}
{ADMIN_PLUGINS}";

 /* if (!ADMIN)
    {
	$style='leftmenu';
	$ADMIN_HEADER .= $ns -> tablerender('Welcome', '', '', TRUE);
	$style='default';
    }*/

    if ($prehelp!='')
    {
	$ADMIN_HEADER .= $prehelp;
    }
    else
    {
	$ADMIN_HEADER .= "{ADMIN_UPDATE} {ADMIN_SITEINFO}";
}


$ADMIN_HEADER .= "<br />
</td></tr></table>
    </td>";
}
$ADMIN_HEADER .= "
<td class='default_menu'>
{SETSTYLE=default}
";

$ADMIN_FOOTER = "<br />
</td>";

if ($pre_admin_menu || $preright) {
	$ADMIN_FOOTER .= "<td class='right_menu'>
	<table style='width:100%; border-collapse: collapse; border-spacing: 0px;'>
	<tr>
	<td>
	{SETSTYLE=rightmenu}
	{ADMIN_MENU}
	".$preright."
	<br />
	</td></tr></table>
	</td>";
}

$ADMIN_FOOTER .= "</tr>
</table>
<div style='text-align:center'>
<br />
{SITEDISCLAIMER}
<br /><br />
{ADMIN_CREDITS}
<br />
</div>
</td>
</tr>
</table>
";


/* NEW ADMIN MENU TEMPLATE
 * see function e_admin_menu() in e107_admin/header.php
 */
$E_ADMIN_MENU['start'] = '
<ul class="plugin-navigation">
';

$E_ADMIN_MENU['button'] = '
	<li>
		<a class="link{LINK_CLASS}" href="{LINK_URL}"{ID}{ONCLICK}>{LINK_TEXT}</a>
		{SUB_MENU}
	</li>
';
$E_ADMIN_MENU['button_active'] = '
	<li class="active">
		<a class="link-active{LINK_CLASS}" href="{LINK_URL}"{ID}{ONCLICK}>{LINK_TEXT}</a>
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

$BUTTONS_START = "<table class='fborder' style='width: 100%'>";

$BUTTON = "<tr><td class='link_button'><div class='emenuBar link_button'>
<div class='menuButton link_button' onmouseover=\"eover(this, 'menuButton_over link_button')\" onmouseout=\"eover(this, 'menuButton link_button')\" {ONCLICK}
style='width: 98% !important; width: 100%; padding: 0px 0px 0px 2px; border-right: 0px'>
<img src='".E_16_NAV_ARROW."' style='width: 16px; height: 16px; vertical-align: middle' alt='' />&nbsp;{LINK_TEXT}</div></div></td></tr>";

$BUTTON_OVER = "<tr><td class='link_button'><div class='emenuBar link_button'>
<div class='menuButton link_button' onmouseover=\"eover(this, 'menuButton_over link_button')\" onmouseout=\"eover(this, 'menuButton link_button')\" {ONCLICK}
style='width: 98% !important; width: 100%; padding: 0px 0px 0px 2px; border-right: 0px'>
<img src='".E_16_NAV_ARROW_OVER."' style='width: 16px; height: 16px; vertical-align: middle' alt='' />&nbsp;{LINK_TEXT}</div></div></td></tr>";

$BUTTONS_END = "</table>";

$SUB_BUTTONS_START = "<table class='fborder' style='width:100%;'>
<tr><td style='border-bottom: 1px solid #000'><div class='emenuBar'>
<div class='menuButton' onmouseover=\"eover(this, 'menuButton_over')\" onmouseout=\"eover(this, 'menuButton')\" onclick=\"expandit('{SUB_HEAD_ID}');\"
style='width: 98% !important; width: 100%; padding: 0px 0px 0px 2px; border-right: 0px'>
<img src='".E_16_NAV_ARROW."' style='width: 16px; height: 16px; vertical-align: middle' alt='' />&nbsp;{SUB_HEAD}</div></div></td></tr>
<tr id='{SUB_HEAD_ID}' style='display: none' ><td class='forumheader3' style='text-align:left;'>";

$SUB_BUTTON = "<a style='text-decoration:none;' href='{LINK_URL}'>{LINK_TEXT}</a><br />";

$SUB_BUTTON_OVER = "<b> &laquo; <a style='text-decoration:none;' href='{LINK_URL}'>{LINK_TEXT}</a> &raquo; </b><br />";

$SUB_BUTTONS_END = "</td></tr></table>";

?>
