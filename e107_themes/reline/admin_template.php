<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_themes/reline/admin_template.php,v $
|     $Revision: 1.2 $
|     $Date: 2009-08-03 19:40:44 $
|     $Author: marj_nl_fr $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

// [prerenders]

$style = "bottom_left_menu";
$prehelp = $tp -> parseTemplate('{ADMIN_HELP}');

$style = "right_menu";
$pre_admin_menu = $tp -> parseTemplate('{ADMIN_MENU=pre}');
$preright = $tp -> parseTemplate('{ADMIN_STATUS=request}');
$preright .= $tp -> parseTemplate('{ADMIN_LATEST=request}');
$preright .= $tp -> parseTemplate('{ADMIN_PRESET}');
$preright .= $tp -> parseTemplate('{ADMIN_LOG=request}');
$style = "default";

// [layout]

$ADMIN_HEADER = "<table class='container'>
<tr>
<td>
<img class='advanced_image' src='".e_IMAGE."advanced.png' alt='' />
</td>

<td class='top_section'>

<table class='admin_header'>
<tr>
<td class='admin_header_left'>
{ADMIN_LOGO}
</td>

<td class='admin_header_right'>
{ADMIN_ICON}
<br />
{ADMIN_SEL_LAN}
</td>
</tr>
</table>

</td>
</tr>

<tr>
<td class='left_section'>

<table class='top_left_menu_container'><tr><td>
{SETSTYLE=top_left_menu}
{PLUGIN=login_menu}
</td></tr>

<tr><td class='bottom_left_menu_area'>
{SETSTYLE=bottom_left_menu}
{ADMIN_LANG}
{ADMIN_PWORD}
{ADMIN_MSG}
{ADMIN_PLUGINS}";

if ($prehelp!='') {
	$ADMIN_HEADER .= $prehelp;
} else {
	$ADMIN_HEADER .= "{ADMIN_SITEINFO}";
}

$ADMIN_HEADER .= "</td></tr></table>

</td>

<td class='main_container' colspan='2'>

<table class='top_inner_container'>
<tr>
<td class='top_bar'>
{SEARCH}
</td>
</tr>

<tr>
<td class='main_nav'>

<div class='cube_container'>
<img class='cube_image' src='".THEME_ABS."images/cube.png' alt='' />
</div>";

if (ADMIN) {
	$ADMIN_HEADER .= "{ADMIN_ALT_NAV=exit=off}";
} else {
	$ADMIN_HEADER .= "<br />";
}

$ADMIN_HEADER .= "</td>
</tr>
</table>

{SETSTYLE=default}

<table class='bottom_inner_container'>
<tr>
<td class='main_section'>";




$ADMIN_FOOTER = "</td>";

if ($pre_admin_menu || $preright) {
	$ADMIN_FOOTER .= "<td class='right_section'>
	{SETSTYLE=right_menu}
	{ADMIN_MENU}
	".$preright."
	</td>";
}

$ADMIN_FOOTER .= "</tr>
</table>

</td>
</tr>
</table>

<div class='disclaimer'>
{SITELINKS=flat}
<br />
{SITEDISCLAIMER}
<br /><br />
{ADMIN_CREDITS}
</div>";


// [admin button style]

$BUTTONS_START = "<table class='fborder' style='width: 100%'>";

$BUTTON = "<tr><td><div>
<div class='menuButton_menu' onmouseover=\"eover(this, 'menuButton_menu_over')\" onmouseout=\"eover(this, 'menuButton_menu')\" {ONCLICK} 
style='width: 98% !important; width: 100%; padding: 0px 0px 0px 2px; border-right: 0px'>
<img src='".E_16_NAV_ARROW."' style='width: 16px; height: 16px; vertical-align: middle' alt='' />&nbsp;{LINK_TEXT}</div></div></td></tr>";

$BUTTON_OVER = "<tr><td><div>
<div class='menuButton_menu' onmouseover=\"eover(this, 'menuButton_menu_over')\" onmouseout=\"eover(this, 'menuButton_menu')\" {ONCLICK} 
style='width: 98% !important; width: 100%; padding: 0px 0px 0px 2px; border-right: 0px'>
<img src='".E_16_NAV_ARROW_OVER."' style='width: 16px; height: 16px; vertical-align: middle' alt='' />&nbsp;{LINK_TEXT}</div></div></td></tr>";

$BUTTONS_END = "</table>";

$SUB_BUTTONS_START = "<table class='fborder' style='width:100%;'>
<tr><td><div>
<div class='menuButton_menu' onmouseover=\"eover(this, 'menuButton_over')\" onmouseout=\"eover(this, 'menuButton')\" onclick=\"expandit('{SUB_HEAD_ID}');\" 
style='width: 98% !important; width: 100%; padding: 0px 0px 0px 2px; border-right: 0px'>
<img src='".E_16_NAV_ARROW."' style='width: 16px; height: 16px; vertical-align: middle' alt='' />&nbsp;{SUB_HEAD}</div></div></td></tr>
<tr id='{SUB_HEAD_ID}' style='display: none' ><td class='forumheader3' style='text-align:left;'>";

$SUB_BUTTON = "<a style='text-decoration:none;' href='{LINK_URL}'>{LINK_TEXT}</a><br />";

$SUB_BUTTON_OVER = "<b> &laquo; <a style='text-decoration:none;' href='{LINK_URL}'>{LINK_TEXT}</a> &raquo; </b><br />";

$SUB_BUTTONS_END = "</td></tr></table>";

?>