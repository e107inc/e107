<?php
if (!defined("e107_INIT")) { exit; }
$sc_style["ADMIN_SEL_LAN"]["pre"] = '<br />';
$sc_style["ADMIN_SEL_LAN"]["post"] = '';
define("LINKSRENDERONLYMAIN", "1");
if(ADMIN) {
if(e_SELF == SITEURLBASE.e_ADMIN_ABS."admin.php")
{
	$ADMIN_HEADER = '
	<div class="admin_header clearfix">
		<div class="admin_header_left">
			{ADMIN_LOGO} {ADMIN_UPDATE=adminpanel|text}
		</div>
		<div class="admin_header_right">
			<div>{ADMIN_SEL_LAN}</div><div>{ADMIN_LOGGED}</div><div>{ADMIN_SITEINFO=version}</div>
		</div>
	</div>
	<div class="clear"></div>
	<div class="admin_main_nav_bg">
		<div class="admin_main_nav">
			{ADMIN_ALT_NAV}
		</div>
	</div>
	<table class="admin_main_col">
		<tr>
			<td class="admin_left_col">
				{ADMIN_MENU}
				{ADMIN_PRESET}
				{ADMIN_SITEINFO}
				{ADMIN_DOCS}
			</td>
			<td class="admin_mid_col">
	';
	$ADMIN_FOOTER = '
			</td>
			<td class="admin_right_col">
				'.(e_PAGE != "admin.php" ? "{ADMIN_UPDATE} " : "").'
				{ADMIN_LANG}
				{ADMIN_PWORD}
				{ADMIN_STATUS=request}
				{ADMIN_LATEST=request}
				{ADMIN_LOG=request}
				{ADMIN_HELP}
				{ADMIN_MSG}
				{ADMIN_PLUGINS}
			</td>
		</tr>
	</table>
	{ADMIN_CREDITS}
	';
	}else{
	$ADMIN_HEADER = '
	<div class="admin_header clearfix">
		<div class="admin_header_left">
			{ADMIN_LOGO=link=index}
		</div>
		<div class="admin_header_right">
			<div>{ADMIN_SEL_LAN}</div><div>{ADMIN_LOGGED}</div><div>{ADMIN_SITEINFO=version}</div>
		</div>
	</div>
	<div class="clear"></div>
	<div class="admin_main_nav_bg">
		<div class="admin_main_nav">
			{ADMIN_ALT_NAV}
		</div>
	</div>
	<table class="admin_main_col">
		<tr>
			<td class="admin_left_col">
					{ADMIN_MENU}
					{ADMIN_MSG}
					{ADMIN_LANG}
					{ADMIN_PWORD}
					{ADMIN_HELP}
					{ADMIN_DOCS}
					{ADMIN_PLUGINS}
			</td>
			<td class="admin_mid_col">
	';
	$ADMIN_FOOTER = '
			</td>
		</tr>
	</table>
	{ADMIN_CREDITS}
	';
	}
}else{
	$ADMIN_HEADER = '
	<div class="admin_header clearfix">
		<div class="admin_header_left">
			{ADMIN_LOGO=link=index}
		</div>
		<div class="admin_header_right">
			<div style="padding: 0px 30px 0px 0px;">{ADMIN_SEL_LAN}</div><div>{ADMIN_LOGGED}</div><div>{ADMIN_SITEINFO=version}</div>
		</div>
	</div>
	<div class="clear"></div>
	<div class="admin_main_nav_bg">
		<div class="admin_main_nav">
			{ADMIN_ALT_NAV}
		</div>
	</div>
	<table class="admin_main_col">
		<tr>
			<td class="admin_mid_col">
	';
	$ADMIN_FOOTER = '
			</td>
		</tr>
	</table>
	{ADMIN_CREDITS}
	';
}

$BUTTONS_START = "
<table style='width: 100%'>
";

$BUTTON = "
	<tr>
		<td>
			<div class='link_button'>
				<div style='width:100%; text-align:left'>
					<a style='cursor:hand; cursor:pointer; text-decoration:none;' {ONCLICK} >
						{LINK_TEXT}
					</a>
				</div>
			</div>
		</td>
	</tr>";

$BUTTON_OVER = "
	<tr>
		<td>
			<div class='link_button_selected'>
				<div style='width:100%; text-align:left'>
					<a style='cursor:hand; cursor:pointer; text-decoration:none;' {ONCLICK} >
						{LINK_TEXT}
					</a>
				</div>
			</div>
		</td>
	</tr>
";

$BUTTONS_END = "
</table>
";

$SUB_BUTTONS_START = "
<table class='fborder' style='width:100%;'>
	<tr>
		<td style='text-align:center; font-weight: bold;'>
			<div class='emenadsuBar'>
    		<a style='text-align:center; cursor:hand; cursor:pointer; text-decoration:none;' onclick=\"expandit('{SUB_HEAD_ID}');\" >
				{SUB_HEAD}
				</a>
			</div>
		</td>
	</tr>
	<tr id='{SUB_HEAD_ID}' style='display: none' >
		<td style='text-align:left;'>
";

$SUB_BUTTON = "
	<a style='text-decoration:none;' href='{LINK_URL}'>{LINK_TEXT}</a><br />
";

$SUB_BUTTON_OVER = "
	<a style='text-decoration:none;' href='{LINK_URL}'>{LINK_TEXT}</a><br />
";

$SUB_BUTTONS_END = "
		</td>
	</tr>
</table>
";