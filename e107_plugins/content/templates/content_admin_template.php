<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/content/templates/content_admin_template.php,v $
 * $Revision: 1.6 $
 * $Date: 2009-11-17 13:23:58 $
 * $Author: marj_nl_fr $
 */

global $sc_style, $content_shortcodes;

// ##### CONTENT OPTIONS --------------------------------------------------
if(!isset($CONTENT_ADMIN_OPTIONS_START)){
	$CONTENT_ADMIN_OPTIONS_START = "
	<div style='text-align:center'>
	".$rs -> form_open("post", e_SELF."?option", "optionsform","","", "")."
	<table style='".ADMIN_WIDTH."' class='fborder'>
	<tr>
	<td class='fcaption' style='width:1%;'></td>
	<td class='fcaption' style='width:5%; text-align:center;'>".CONTENT_ADMIN_CAT_LAN_25."</td>
	<td class='fcaption' style='width:74%; text-align:left;'>".CONTENT_ADMIN_CAT_LAN_19."</td>
	<td class='fcaption' style='width:10%; text-align:center;'>".CONTENT_ADMIN_CAT_LAN_20."</td>
	<td class='fcaption' style='width:10%; text-align:center;'>".CONTENT_ADMIN_OPT_LAN_167."</td>
	</tr>
	<tr><td colspan='5' style='border:0; height:20px;'></td></tr>
	<tr>
		<td class='forumheader3' style='width:1%;'></td>
		<td class='forumheader3' style='width:5%;'></td>
		<td class='forumheader3' style='width:74%; white-space:nowrap;'>".CONTENT_ADMIN_OPT_LAN_1."</td>
		<td class='forumheader3' style='width:10%; text-align:center; white-space:nowrap;'>
			<a href='".e_SELF."?option.default'>".CONTENT_ICON_OPTIONS."</a>
		</td>
		<td class='forumheader3' style='width:10%;'></td>
	</tr>
	<tr><td colspan='5' style='border:0; height:20px;'></td></tr>";
}
if(!isset($CONTENT_ADMIN_OPTIONS_TABLE)){
	$CONTENT_ADMIN_OPTIONS_TABLE = "
	<tr>
		<td class='forumheader3' style='width:1%; text-align:left; vertical-align:top;'><a href='javascript:void(0);' onclick=\"expandit('ci_{CONTENT_ID}')\">".CONTENT_ICON_DETAILS."</a></td>
		<td class='forumheader3' style='width:5%; text-align:center; vertical-align:top;'>{CONTENT_CAT_ICON}</td>
		<td class='forumheader3' style='width:74%;'>
			{CONTENT_HEADING}
			<div id='ci_{CONTENT_ID}' style='display:none; vertical-align:top; margin-top:10px;'>
			<table class='fborder' style='width:98%;'>
			<tr>
				<td style='width:15%; white-space:nowrap; line-height:150%;'>".CONTENT_TEMPLATE_LAN_1."</td>
				<td>{CONTENT_ID}</td>
			</tr>
			<tr>
				<td style='width:15%; white-space:nowrap; line-height:150%;'>".CONTENT_TEMPLATE_LAN_2."</td>
				<td>{CONTENT_AUTHOR}</td>
			</tr>
			<tr>
				<td style='line-height:150%;'>".CONTENT_TEMPLATE_LAN_3."</td>
				<td>{CONTENT_LINK_CATEGORY}</td>
			</tr>
			<tr>
				<td style='line-height:150%;'>".CONTENT_TEMPLATE_LAN_4."</td>
				<td>{CONTENT_SUBHEADING}</td>
			</tr>
			</table>
			</div>
		</td>
		<td class='forumheader3' style='width:10%; text-align:center; vertical-align:top; white-space:nowrap;'>
			{CONTENT_LINK_OPTION}
		</td>
		<td class='forumheader3' style='width:10%; text-align:center; vertical-align:top; white-space:nowrap;'>
			{CONTENT_INHERIT}
		</td>
	</tr>";
}
if(!isset($CONTENT_ADMIN_OPTIONS_END)){
	$CONTENT_ADMIN_OPTIONS_END = "
	<tr>
		<td class='forumheader3' colspan='4'></td>
		<td class='forumheader3' style='text-align:center;'>
			<input class='button' type='submit' name='updateinherit' value='".CONTENT_ADMIN_CAT_LAN_7."' />
		</td>
	</tr>
	</table>
	</form>
	</div>";
}


// ##### CONTENT ERROR --------------------------------------------------
if(!isset($CONTENT_ADMIN_ERROR)){
	$CONTENT_ADMIN_ERROR = "<div style='text-align:center;'>".CONTENT_ADMIN_CAT_LAN_9."</div>";
}


// ##### CONTENT MANAGER --------------------------------------------------
if(!isset($CONTENT_ADMIN_MANAGER_START)){
	$CONTENT_ADMIN_MANAGER_START = "
	<div style='text-align:center'>
	".$rs -> form_open("post", e_SELF."?".$qs[0], "catform","","", "")."
	<table style='".ADMIN_WIDTH."' class='fborder'>
	<tr>
		<td class='fcaption' style='width:1%;'></td>
		<td class='fcaption' style='width:5%; text-align:center;'>".CONTENT_ADMIN_CAT_LAN_25."</td>
		<td class='fcaption' style='width:74%;'>".CONTENT_ADMIN_CAT_LAN_19."</td>
		<td class='fcaption' style='width:10%; text-align:center;'>".CONTENT_ADMIN_CAT_LAN_20."</td>
		<td class='fcaption' style='width:10%; text-align:center;'>".CONTENT_ADMIN_OPT_LAN_167."</td>
	</tr>
	{CONTENT_ADMIN_SPACER=true}
	<tr>
		<td class='forumheader3' style='width:1%;'></td>
		<td class='forumheader3' style='width:5%;'></td>
		<td class='forumheader3' style='width:74%; white-space:nowrap;'>".CONTENT_ADMIN_OPT_LAN_1."</td>
		<td class='forumheader3' style='width:10%; text-align:center; white-space:nowrap;'>
			<a href='".e_SELF."?manager.default'>".CONTENT_ICON_CONTENTMANAGER_SMALL."</a>
		</td>
		<td class='forumheader3' style='width:10%;'></td>
	</tr>";
}
if(!isset($CONTENT_ADMIN_MANAGER_TABLE)){
	$CONTENT_ADMIN_MANAGER_TABLE = "
	{CONTENT_ADMIN_SPACER}
	<tr>
		<td class='{CONTENT_ADMIN_HTML_CLASS}' style='width:1%; text-align:left; vertical-align:top;'><a href='javascript:void(0);' onclick=\"expandit('ci_{CONTENT_ID}')\">".CONTENT_ICON_DETAILS."</a></td>
		<td class='{CONTENT_ADMIN_HTML_CLASS}' style='width:5%; text-align:center; vertical-align:top;'>{CONTENT_CAT_ICON}</td>
		<td class='{CONTENT_ADMIN_HTML_CLASS}' style='width:74%;'>
			{CONTENT_MANAGER_PRE}{CONTENT_HEADING}
			<div id='ci_{CONTENT_ID}' style='display:none; vertical-align:top; margin-top:10px;'>
			<table class='fborder' style='width:98%;'>
			<tr>
				<td style='width:15%; white-space:nowrap; line-height:150%;'>".CONTENT_TEMPLATE_LAN_1."</td>
				<td>{CONTENT_ID}</td>
			</tr>
			<tr>
				<td style='width:15%; white-space:nowrap; line-height:150%;'>".CONTENT_TEMPLATE_LAN_2."</td>
				<td>{CONTENT_AUTHOR}</td>
			</tr>
			<tr>
				<td style='line-height:150%;'>".CONTENT_TEMPLATE_LAN_3."</td>
				<td>{CONTENT_LINK_CATEGORY}</td>
			</tr>
			<tr>
				<td style='line-height:150%;'>".CONTENT_TEMPLATE_LAN_4."</td>
				<td>{CONTENT_SUBHEADING}</td>
			</tr>
			</table>
			</div>
		</td>
		<td class='{CONTENT_ADMIN_HTML_CLASS}' style='width:10%; text-align:center; vertical-align:top; white-space:nowrap;'>
			{CONTENT_LINK_MANAGER}
		</td>
		<td class='{CONTENT_ADMIN_HTML_CLASS}' style='width:10%; text-align:center; vertical-align:top; white-space:nowrap;'>
			{CONTENT_MANAGER_INHERIT}
		</td>
	</tr>";
}
if(!isset($CONTENT_ADMIN_MANAGER_END)){
	$CONTENT_ADMIN_MANAGER_END = "
	<tr>
		<td class='forumheader3' colspan='4'></td>
		<td class='forumheader3' style='text-align:center;'>
			<input class='button' type='submit' name='updatemanagerinherit' value='".CONTENT_ADMIN_CAT_LAN_7."' />
		</td>
	</tr>
	</table>
	</form>
	</div>";
}


// ##### CONTENT MANAGER CATEGORY --------------------------------------------------
$CONTENT_ADMIN_MANAGER_ROW_TITLE	= "<tr><td colspan='2' class='fcaption'>{TOPIC_CAPTION}</td></tr>";
$CONTENT_ADMIN_MANAGER_ROW_NOEXPAND = "
<tr>
	<td class='forumheader3' style='width:35%; vertical-align:top;'>{TOPIC_TOPIC}</td>
	<td class='forumheader3'>{TOPIC_FIELD}</td>
</tr>";

if(!isset($CONTENT_ADMIN_MANAGER_CATEGORY)){
	$CONTENT_ADMIN_MANAGER_CATEGORY = "
	<div style='text-align:center'>
	".$rs -> form_open("post", e_SELF."?".e_QUERY, "managerform", "", "enctype='multipart/form-data'")."
	<table class='fborder' style='".ADMIN_WIDTH."'>
	<tr>
		<td colspan='2' class='fcaption'>".CONTENT_ADMIN_MANAGER_LAN_9."</td>
	</tr>
	<tr>
		<td class='forumheader3' style='text-align:left'>
			".CONTENT_ADMIN_MANAGER_LAN_6."<br />".CONTENT_ADMIN_MANAGER_LAN_7."<br />
		</td>
		<td class='forumheader3' style='text-align:left'>
			{CONTENT_ADMIN_MANAGER_SUBMIT}
		</td>
	</tr>
	<tr>
		<td class='forumheader3' style='text-align:left'>
			".CONTENT_ADMIN_MANAGER_LAN_0."<br />".CONTENT_ADMIN_MANAGER_LAN_1."<br />
		</td>
		<td class='forumheader3' style='text-align:left'>
			{CONTENT_ADMIN_MANAGER_APPROVE}
		</td>
	</tr>
	<tr>
		<td class='forumheader3' style='text-align:left'>
			".CONTENT_ADMIN_MANAGER_LAN_2."<br />".CONTENT_ADMIN_MANAGER_LAN_3."<br />
		</td>
		<td class='forumheader3' style='text-align:left'>
			{CONTENT_ADMIN_MANAGER_PERSONAL}
		</td>
	</tr>
	<tr>
		<td class='forumheader3' style='text-align:left'>
			".CONTENT_ADMIN_MANAGER_LAN_4."<br />".CONTENT_ADMIN_MANAGER_LAN_5."<br />
		</td>
		<td class='forumheader3' style='text-align:left'>
			{CONTENT_ADMIN_MANAGER_CATEGORY}
		</td>
	</tr>
	</table>
	<br /><br />
	{CONTENT_ADMIN_MANAGER_OPTIONS}
	<br /><br />
	<table class='fborder' style='".ADMIN_WIDTH."'>
	<tr>
		<td colspan='2' class='fcaption' style='text-align:center'>
			{CONTENT_ADMIN_BUTTON}
		</td>
	</tr>
	</table>
	</form>
	</div>";
}


$sc_style['CONTENT_ADMIN_SPACER']['pre'] = "<tr><td class='forumheader3' colspan='7' style='height:15px; border:0;'>";
$sc_style['CONTENT_ADMIN_SPACER']['post'] = "</td></tr>";

// ##### CONTENT CATEGORY --------------------------------------------------
if(!isset($CONTENT_ADMIN_CATEGORY_START)){
	$CONTENT_ADMIN_CATEGORY_START = "
	<div style='text-align:center'>
	".$rs -> form_open("post", e_SELF."?".$qs[0], "catform","","", "")."
	<table style='".ADMIN_WIDTH."' class='fborder'>
	<tr>
	<td class='fcaption' style='width:1%'></td>
	<td class='fcaption' style='width:5%; text-align:center;'>".CONTENT_ADMIN_CAT_LAN_25."</td>
	<td class='fcaption' style='width:84%'>".CONTENT_ADMIN_CAT_LAN_19."</td>
	<td class='fcaption' style='width:10%; text-align:center'>".CONTENT_ADMIN_CAT_LAN_20."</td>
	</tr>";
}
if(!isset($CONTENT_ADMIN_CATEGORY_TABLE)){
	$CONTENT_ADMIN_CATEGORY_TABLE = "
	{CONTENT_ADMIN_SPACER}
	<tr>
	<td class='{CONTENT_ADMIN_HTML_CLASS}' style='width:1%; text-align:left; vertical-align:top;'><a href='javascript:void(0);' onclick=\"expandit('ci_{CONTENT_ID}')\">".CONTENT_ICON_DETAILS."</a></td>
	<td class='{CONTENT_ADMIN_HTML_CLASS}' style='width:5%; text-align:center; vertical-align:top;'>{CONTENT_CAT_ICON}</td>
	<td class='{CONTENT_ADMIN_HTML_CLASS}' style='width:84%;'>
		{CONTENT_MANAGER_PRE}{CONTENT_HEADING}
		<div id='ci_{CONTENT_ID}' style='display:none; vertical-align:top; margin-top:10px;'>
		<table class='fborder' style='width:98%;'>
		<tr>
			<td style='width:15%; white-space:nowrap; line-height:150%;'>".CONTENT_TEMPLATE_LAN_1."</td>
			<td>{CONTENT_ID}</td>
		</tr>
		<tr>
			<td style='width:15%; white-space:nowrap; line-height:150%;'>".CONTENT_TEMPLATE_LAN_2."</td>
			<td>{CONTENT_AUTHOR}</td>
		</tr>
		<tr>
			<td style='line-height:150%;'>".CONTENT_TEMPLATE_LAN_3."</td>
			<td>{CONTENT_LINK_CATEGORY}</td>
		</tr>
		<tr>
			<td style='line-height:150%;'>".CONTENT_TEMPLATE_LAN_4."</td>
			<td>{CONTENT_SUBHEADING}</td>
		</tr>
		</table>
		</div>
	</td>
	<td class='{CONTENT_ADMIN_HTML_CLASS}' style='width:10%; text-align:center; white-space:nowrap; vertical-align:top;'>
		{CONTENT_ADMIN_OPTIONS}
	</td>
	</tr>";
}
if(!isset($CONTENT_ADMIN_CATEGORY_END)){
	$CONTENT_ADMIN_CATEGORY_END = "
	</table>
	</form>
	</div>";
}


// ##### CONTENT SUBMITTED --------------------------------------------------

if(!isset($CONTENT_ADMIN_SUBMITTED_START)){
	$CONTENT_ADMIN_SUBMITTED_START = "
	<div style='text-align:center'>
	".$rs -> form_open("post", e_SELF, "submittedform","","", "")."
	<table style='".ADMIN_WIDTH."' class='fborder'>
	<tr>
	<td class='fcaption' style='width:1%'>".CONTENT_ADMIN_ITEM_LAN_8."</td>
	<td style='width:5%; text-align:center;' class='fcaption'>".CONTENT_ADMIN_ITEM_LAN_9."</td>
	<td style='width:50%; text-align:left;' class='fcaption'>".CONTENT_ADMIN_ITEM_LAN_11."</td>
	<td style='width:10%; text-align:center;' class='fcaption'>".CONTENT_ADMIN_ITEM_LAN_12."</td>
	</tr>";
}
if(!isset($CONTENT_ADMIN_SUBMITTED_TABLE)){
	$CONTENT_ADMIN_SUBMITTED_TABLE = "
	<tr>
		<td class='{CONTENT_ADMIN_HTML_CLASS}' style='width:1%; text-align:left; vertical-align:top;'><a href='javascript:void(0);' onclick=\"expandit('ci_{CONTENT_ID}')\">".CONTENT_ICON_DETAILS."</a></td>
		<td class='{CONTENT_ADMIN_HTML_CLASS}' style='width:5%; text-align:center; vertical-align:top;'>{CONTENT_ICON}</td>
		<td class='{CONTENT_ADMIN_HTML_CLASS}' style='width:64%;'>
			{CONTENT_HEADING}
			<div id='ci_{CONTENT_ID}' style='display:none; vertical-align:top; margin-top:10px;'>
			<table class='fborder' style='width:98%;'>
			<tr>
				<td style='width:15%; white-space:nowrap; line-height:150%;'>".CONTENT_TEMPLATE_LAN_1."</td>
				<td>{CONTENT_ID}</td>
			</tr>
			<tr>
				<td style='width:15%; white-space:nowrap; line-height:150%;'>".CONTENT_TEMPLATE_LAN_2."</td>
				<td>{CONTENT_AUTHOR}</td>
			</tr>
			<tr>
				<td style='line-height:150%;'>".CONTENT_TEMPLATE_LAN_5."</td>
				<td>{CONTENT_ADMIN_CATEGORY}</td>
			</tr>
			<tr>
				<td style='line-height:150%;'>".CONTENT_TEMPLATE_LAN_4."</td>
				<td>{CONTENT_SUBHEADING}</td>
			</tr>
			</table>
			</div>
		</td>
		<td class='{CONTENT_ADMIN_HTML_CLASS}' style='width:10%; text-align:center; vertical-align:top; white-space:nowrap;'>
			{CONTENT_ADMIN_OPTIONS}
		</td>
	</tr>";
}

if(!isset($CONTENT_ADMIN_SUBMITTED_END)){
	$CONTENT_ADMIN_SUBMITTED_END = "
	</table>
	</form>
	</div>";
}


// ##### CONTENT ORDER --------------------------------------------------
if(!isset($CONTENT_ADMIN_ORDER_START)){
	$CONTENT_ADMIN_ORDER_START = "
	<div style='text-align:center'>
	".$rs -> form_open("post", e_SELF."?order", "orderform")."
	<table class='fborder' style='".ADMIN_WIDTH."'>							
	<tr>
		<td class='fcaption' style='width:1%'></td>
		<td class='fcaption' style='width:5%; text-align:center;'>".CONTENT_ADMIN_CAT_LAN_25."</td>
		<td class='fcaption' style='width:64%'>".CONTENT_ADMIN_CAT_LAN_19."</td>
		<td class='fcaption' style='width:10%; text-align:center; white-space:nowrap;'>".CONTENT_ADMIN_ITEM_LAN_58."</td>
		<td class='fcaption' style='width:10%; text-align:center; white-space:nowrap;'>".CONTENT_ADMIN_ITEM_LAN_59."</td>
		<td class='fcaption' style='width:10%; text-align:center; white-space:nowrap;'>".CONTENT_ADMIN_ITEM_LAN_60."</td>
	</tr>";
}
if(!isset($CONTENT_ADMIN_ORDER_TABLE)){
	$CONTENT_ADMIN_ORDER_TABLE = "
	{CONTENT_ADMIN_SPACER}
	<tr>
		<td class='{CONTENT_ADMIN_HTML_CLASS}' style='width:1%; text-align:left; vertical-align:top;'><a href='javascript:void(0);' onclick=\"expandit('ci_{CONTENT_ID}')\">".CONTENT_ICON_DETAILS."</a></td>
		<td class='{CONTENT_ADMIN_HTML_CLASS}' style='width:5%; text-align:center; vertical-align:top;'>{CONTENT_CAT_ICON}</td>
		<td class='{CONTENT_ADMIN_HTML_CLASS}' style='width:64%;'>
			{CONTENT_MANAGER_PRE}{CONTENT_HEADING} {CONTENT_ADMIN_ORDER_AMOUNT}
			<div id='ci_{CONTENT_ID}' style='display:none; vertical-align:top; margin-top:10px;'>
			<table class='fborder' style='width:98%;'>
			<tr>
				<td style='width:15%; white-space:nowrap; line-height:150%;'>".CONTENT_TEMPLATE_LAN_1."</td>
				<td>{CONTENT_ID}</td>
			</tr>
			<tr>
				<td style='width:15%; white-space:nowrap; line-height:150%;'>".CONTENT_TEMPLATE_LAN_2."</td>
				<td>{CONTENT_AUTHOR}</td>
			</tr>
			<tr>
				<td style='line-height:150%;'>".CONTENT_TEMPLATE_LAN_3."</td>
				<td>{CONTENT_LINK_CATEGORY}</td>
			</tr>
			<tr>
				<td style='line-height:150%;'>".CONTENT_TEMPLATE_LAN_4."</td>
				<td>{CONTENT_SUBHEADING}</td>
			</tr>
			</table>
			</div>
		</td>
		<td class='{CONTENT_ADMIN_HTML_CLASS}' style='width:10%; text-align:center; vertical-align:top; white-space:nowrap;'>
			{CONTENT_ADMIN_ORDER_CAT}
			{CONTENT_ADMIN_ORDER_CATALL}
		</td>
		<td class='{CONTENT_ADMIN_HTML_CLASS}' style='width:10%; text-align:center; vertical-align:top; white-space:nowrap;'>
			{CONTENT_ADMIN_ORDER_UPDOWN}
		</td>
		<td class='{CONTENT_ADMIN_HTML_CLASS}' style='width:10%; text-align:center; vertical-align:top; white-space:nowrap;'>
			{CONTENT_ADMIN_ORDER_SELECT}
		</td>
	</tr>";
}
if(!isset($CONTENT_ADMIN_ORDER_END)){
	$CONTENT_ADMIN_ORDER_END = "
	<tr>
		<td class='fcaption' colspan='4'>&nbsp;</td>
		<td class='fcaption' colspan='2' style='text-align:center'>
			{CONTENT_ADMIN_BUTTON}
		</td>
	</tr>
	</table>
	</form>
	</div>";
}


// ##### CONTENT ORDER CONTENT --------------------------------------------------
if(!isset($CONTENT_ADMIN_ORDER_CONTENT_START)){
	$CONTENT_ADMIN_ORDER_CONTENT_START = "
	<div style='text-align:center'>
	".$rs -> form_open("post", "{CONTENT_ADMIN_FORM_TARGET}", "orderform")."
	<table class='fborder' style='".ADMIN_WIDTH."'>
	<tr>
		<td class='fcaption' style='width:1%'></td>
		<td class='fcaption' style='width:79%; text-align:left; white-space:nowrap;'>".CONTENT_ADMIN_ITEM_LAN_11."</td>
		<td class='fcaption' style='width:10%; text-align:center; white-space:nowrap;'>".CONTENT_ADMIN_ITEM_LAN_59."</td>
		<td class='fcaption' style='width:10%; text-align:center; white-space:nowrap;'>".CONTENT_ADMIN_ITEM_LAN_60."</td>
	</tr>";
}
if(!isset($CONTENT_ADMIN_ORDER_CONTENT_TABLE)){
	$CONTENT_ADMIN_ORDER_CONTENT_TABLE = "
	<tr>
		<td class='forumheader3' style='width:1%; text-align:left; vertical-align:top;'><a href='javascript:void(0);' onclick=\"expandit('ci_{CONTENT_ID}')\">".CONTENT_ICON_DETAILS."</a></td>
		<td class='forumheader3' style='width:79%; text-align:left;'>
			{CONTENT_HEADING} ({CONTENT_ORDER})
			<div id='ci_{CONTENT_ID}' style='display:none; vertical-align:top; margin-top:10px;'>
			<table class='fborder' style='width:98%;'>
			<tr>
				<td style='width:15%; white-space:nowrap; line-height:150%;'>".CONTENT_TEMPLATE_LAN_1."</td>
				<td>{CONTENT_ID}</td>
			</tr>
			<tr>
				<td style='width:15%; white-space:nowrap; line-height:150%;'>".CONTENT_TEMPLATE_LAN_2."</td>
				<td>{CONTENT_AUTHOR}</td>
			</tr>
			<tr>
				<td style='line-height:150%;'>".CONTENT_TEMPLATE_LAN_3."</td>
				<td>{CONTENT_LINK_ITEM}</td>
			</tr>
			<tr>
				<td style='line-height:150%;'>".CONTENT_TEMPLATE_LAN_4."</td>
				<td>{CONTENT_SUBHEADING}</td>
			</tr>
			</table>
			</div>
		</td>
		<td class='forumheader3' style='width:10%; text-align:center; vertical-align:top; white-space:nowrap;'>
			{CONTENT_ADMIN_ORDER_UPDOWN}
		</td>
		<td class='forumheader3' style='width:10%; text-align:center; vertical-align:top; white-space:nowrap;'>
			{CONTENT_ADMIN_ORDER_SELECT}
		</td>
	</tr>";
}
if(!isset($CONTENT_ADMIN_ORDER_CONTENT_END)){
	$CONTENT_ADMIN_ORDER_CONTENT_END = "
	<tr>
		<td class='fcaption' colspan='2'>&nbsp;</td>
		<td class='fcaption' colspan='2' style='text-align:center'>
			{CONTENT_ADMIN_BUTTON}
		</td>
	</tr>
	</table>
	</form>
	</div>";
}


// ##### CONTENT ITEM LIST : LETTERINDEX --------------------------------------------------
if(!isset($CONTENT_ADMIN_CONTENT_LIST_LETTER)){
	$CONTENT_ADMIN_CONTENT_LIST_LETTER = "
	<div style='text-align:center'>
	<form method='post' action='{CONTENT_ADMIN_FORM_TARGET}'>
	<table class='fborder' style='".ADMIN_WIDTH."'>
	<tr><td colspan='2' class='fcaption'>".CONTENT_ADMIN_ITEM_LAN_6."</td></tr>
	<tr><td colspan='2' class='forumheader3'>{CONTENT_ADMIN_LETTERINDEX}</td></tr>
	</table>
	</form>
	</div>";
}


// ##### CONTENT ITEM LIST --------------------------------------------------
if(!isset($CONTENT_ADMIN_CONTENT_LIST_START)){
	$CONTENT_ADMIN_CONTENT_LIST_START = "
	<div style='text-align:center'>
	".$rs -> form_open("post", e_SELF."?".e_QUERY, "deletecontentform","","", "")."
	<table style='".ADMIN_WIDTH."' class='fborder'>
	<tr>
	<td class='fcaption' style='width:1%'></td>
	<td class='fcaption' style='width:5%; text-align:center;'>".CONTENT_ADMIN_ITEM_LAN_9."</td>
	<td class='fcaption' style='width:84%; text-align:left;'>".CONTENT_ADMIN_ITEM_LAN_11."</td>
	<td class='fcaption' style='width:10%; text-align:center;'>".CONTENT_ADMIN_ITEM_LAN_12."</td>
	</tr>";
}
if(!isset($CONTENT_ADMIN_CONTENT_LIST_TABLE)){
	$CONTENT_ADMIN_CONTENT_LIST_TABLE = "
	<tr>
	<td class='forumheader3' style='width:1%; text-align:left; vertical-align:top;'><a href='javascript:void(0);' onclick=\"expandit('ci_{CONTENT_ID}')\">".CONTENT_ICON_DETAILS."</a></td>
	<td class='forumheader3' style='width:5%; text-align:center; vertical-align:top;'>{CONTENT_ICON}</td>
	<td class='forumheader3' style='width:84%; text-align:left;'>
		{CONTENT_HEADING}
		<div id='ci_{CONTENT_ID}' style='display:none; vertical-align:top; margin-top:10px;'>
		<table class='fborder' style='width:98%;'>
		<tr>
			<td style='width:15%; white-space:nowrap; line-height:150%;'>".CONTENT_TEMPLATE_LAN_1."</td>
			<td>{CONTENT_ID}</td>
		</tr>
		<tr>
			<td style='width:15%; white-space:nowrap; line-height:150%;'>".CONTENT_TEMPLATE_LAN_2."</td>
			<td>{CONTENT_AUTHOR}</td>
		</tr>
		<tr>
			<td style='line-height:150%;'>".CONTENT_TEMPLATE_LAN_3."</td>
			<td>{CONTENT_LINK_ITEM}</td>
		</tr>
		<tr>
			<td style='line-height:150%;'>".CONTENT_TEMPLATE_LAN_4."</td>
			<td>{CONTENT_SUBHEADING}</td>
		</tr>
		</table>
		</div>
	</td>
	<td class='forumheader3' style='width:10%; text-align:center; white-space:nowrap; vertical-align:top;'>
		{CONTENT_ADMIN_OPTIONS}
	</td>
	</tr>";
}
if(!isset($CONTENT_ADMIN_CONTENT_LIST_END)){
	$CONTENT_ADMIN_CONTENT_LIST_END = "
	</table>
	</form>
	</div>";
}


// ##### CONTENT CATEGORY SELECTOR --------------------------------------------------
if(!isset($CONTENT_ADMIN_CONTENT_CATSELECT)){
	$CONTENT_ADMIN_CONTENT_CATSELECT = "
	<div style='text-align:center'>
	<table style='".ADMIN_WIDTH."' class='fborder'>
	<tr><td class='fcaption' colspan='2'>".CONTENT_ADMIN_MAIN_LAN_2."</td></tr>
	<tr>
		<td class='forumheader3' style='width:20%; vertical-align:top;'>".CONTENT_ADMIN_CAT_LAN_27."</td>
		<td class='forumheader3'>{CONTENTFORM_CATEGORYSELECT}</td>
	</tr>
	</table>
	</div>";
}


// ##### CONTENT CATEGORY CREATE --------------------------------------------------

$sc_style['CATFORM_CATEGORY']['pre'] = "<tr><td class='forumheader3' style='width:20%; vertical-align:top;'>".CONTENT_ADMIN_CAT_LAN_27."</td><td class='forumheader3'>";
$sc_style['CATFORM_CATEGORY']['post'] = "</td></tr>";

$sc_style['CATFORM_HEADING']['pre'] = "<tr><td class='forumheader3' style='width:20%; vertical-align:top;'>".CONTENT_ADMIN_CAT_LAN_2."</td><td class='forumheader3'>";
$sc_style['CATFORM_HEADING']['post'] = "</td></tr>";

$sc_style['CATFORM_SUBHEADING']['pre'] = "<tr><td class='forumheader3' style='width:20%; vertical-align:top;'>".CONTENT_ADMIN_CAT_LAN_3."</td><td class='forumheader3'>";
$sc_style['CATFORM_SUBHEADING']['post'] = "</td></tr>";

$sc_style['CATFORM_TEXT']['pre'] = "<tr><td class='forumheader3' style='width:20%; vertical-align:top;'>".CONTENT_ADMIN_CAT_LAN_4."</td><td class='forumheader3'>";
$sc_style['CATFORM_TEXT']['post'] = "</td></tr>";

$sc_style['CATFORM_DATESTART']['pre'] = "<tr><td class='forumheader3' style='width:20%; vertical-align:top;'>".CONTENT_ADMIN_DATE_LAN_15."</td><td class='forumheader3'>".CONTENT_ADMIN_DATE_LAN_17."<br /><br />";
$sc_style['CATFORM_DATESTART']['post'] = "</td></tr>";

$sc_style['CATFORM_DATEEND']['pre'] = "<tr><td class='forumheader3' style='width:20%; vertical-align:top;'>".CONTENT_ADMIN_DATE_LAN_16."</td><td class='forumheader3'>".CONTENT_ADMIN_DATE_LAN_18."<br /><br />";
$sc_style['CATFORM_DATEEND']['post'] = "</td></tr>";

$sc_style['CATFORM_UPLOAD']['pre'] = "<tr><td class='forumheader3' style='width:20%; vertical-align:top;'>".CONTENT_ADMIN_CAT_LAN_63."</td><td class='forumheader3'>";
$sc_style['CATFORM_UPLOAD']['post'] = "</td></tr>";

$sc_style['CATFORM_ICON']['pre'] = "<tr><td class='forumheader3' style='width:20%; vertical-align:top;'>".CONTENT_ADMIN_CAT_LAN_5."</td><td class='forumheader3'>";
$sc_style['CATFORM_ICON']['post'] = "</td></tr>";

$sc_style['CATFORM_COMMENT']['pre'] = "<tr><td class='forumheader3' style='width:20%; vertical-align:top;'>".CONTENT_ADMIN_CAT_LAN_14."</td><td class='forumheader3'>";
$sc_style['CATFORM_COMMENT']['post'] = "</td></tr>";

$sc_style['CATFORM_RATING']['pre'] = "<tr><td class='forumheader3' style='width:20%; vertical-align:top;'>".CONTENT_ADMIN_CAT_LAN_15."</td><td class='forumheader3'>";
$sc_style['CATFORM_RATING']['post'] = "</td></tr>";

$sc_style['CATFORM_PEICON']['pre'] = "<tr><td class='forumheader3' style='width:20%; vertical-align:top;'>".CONTENT_ADMIN_CAT_LAN_16."</td><td class='forumheader3'>";
$sc_style['CATFORM_PEICON']['post'] = "</td></tr>";

$sc_style['CATFORM_VISIBILITY']['pre'] = "<tr><td class='forumheader3' style='width:20%; vertical-align:top;'>".CONTENT_ADMIN_CAT_LAN_17."</td><td class='forumheader3'>";
$sc_style['CATFORM_VISIBILITY']['post'] = "</td></tr>";

if(!isset($CONTENT_ADMIN_CAT_CREATE)){
	$CONTENT_ADMIN_CAT_CREATE = "
	<div style='text-align:center'>
	".$rs -> form_open("post", e_SELF."?".e_QUERY, "dataform", "", "enctype='multipart/form-data'")."
	<table class='fborder' style='".ADMIN_WIDTH."'>
	{CATFORM_CATEGORY}
	{CATFORM_HEADING}
	{CATFORM_SUBHEADING}
	{CATFORM_TEXT}
	{CATFORM_DATESTART}
	{CATFORM_DATEEND}
	{CATFORM_UPLOAD}
	{CATFORM_ICON}
	{CATFORM_COMMENT}
	{CATFORM_RATING}
	{CATFORM_PEICON}
	{CATFORM_VISIBILITY}
	<tr><td class='forumheader' style='text-align:center' colspan='2'>{CONTENT_ADMIN_BUTTON}</td></tr>
	</table>
	</form>
	</div>";
}


// ##### CONTENT CONTENT CREATE --------------------------------------------------

$sc_style['CONTENTFORM_CATEGORY']['pre'] = "<tr><td class='forumheader3' style='width:20%; vertical-align:top;'>".CONTENT_ADMIN_CAT_LAN_27."</td><td class='forumheader3'>";
$sc_style['CONTENTFORM_CATEGORY']['post'] = "</td></tr>";

$sc_style['CONTENTFORM_HEADING']['pre'] = "<tr><td class='forumheader3' style='width:20%; vertical-align:top;'>".CONTENT_ADMIN_ITEM_LAN_11."</td><td class='forumheader3'>";
$sc_style['CONTENTFORM_HEADING']['post'] = "</td></tr>";

$sc_style['CONTENTFORM_SUBHEADING']['pre'] = "<tr><td class='forumheader3' style='width:20%; vertical-align:top;'>".CONTENT_ADMIN_ITEM_LAN_16."</td><td class='forumheader3'>";
$sc_style['CONTENTFORM_SUBHEADING']['post'] = "</td></tr>";

$sc_style['CONTENTFORM_SUMMARY']['pre'] = "<tr><td class='forumheader3' style='width:20%; vertical-align:top;'>".CONTENT_ADMIN_ITEM_LAN_17."</td><td class='forumheader3'>";
$sc_style['CONTENTFORM_SUMMARY']['post'] = "</td></tr>";

$sc_style['CONTENTFORM_TEXT']['pre'] = "<tr><td class='forumheader3' style='width:20%; vertical-align:top;'>".CONTENT_ADMIN_ITEM_LAN_18."</td><td class='forumheader3'>";
$sc_style['CONTENTFORM_TEXT']['post'] = "</td></tr>";

$sc_style['CONTENTFORM_AUTHOR']['pre'] = "<tr><td class='forumheader3' style='width:20%; vertical-align:top;'>".CONTENT_ADMIN_ITEM_LAN_51."</td><td class='forumheader3'>(".CONTENT_ADMIN_ITEM_LAN_71.")<br />";
$sc_style['CONTENTFORM_AUTHOR']['post'] = "</td></tr>";

$sc_style['CONTENTFORM_DATESTART']['pre'] = "<tr><td class='forumheader3' style='width:20%; vertical-align:top;'>".CONTENT_ADMIN_DATE_LAN_15."</td><td class='forumheader3'>".CONTENT_ADMIN_DATE_LAN_17."<br /><br />";
$sc_style['CONTENTFORM_DATESTART']['post'] = "</td></tr>";

$sc_style['CONTENTFORM_DATEEND']['pre'] = "<tr><td class='forumheader3' style='width:20%; vertical-align:top;'>".CONTENT_ADMIN_DATE_LAN_16."</td><td class='forumheader3'>".CONTENT_ADMIN_DATE_LAN_18."<br /><br />";
$sc_style['CONTENTFORM_DATEEND']['post'] = "</td></tr>";

$sc_style['CONTENTFORM_UPLOAD']['pre'] = "<tr><td class='forumheader3' style='width:20%; vertical-align:top;'>".CONTENT_ADMIN_ITEM_LAN_104."</td><td class='forumheader3'>".CONTENT_ADMIN_ITEM_LAN_112."<br />".CONTENT_ADMIN_ITEM_LAN_113."<br />";
$sc_style['CONTENTFORM_UPLOAD']['post'] = "</td></tr>";

$sc_style['CONTENTFORM_ICON']['pre'] = "<tr><td class='forumheader3' style='width:20%; vertical-align:top;'>".CONTENT_ADMIN_ITEM_LAN_20."</td><td class='forumheader3'>".CONTENT_ADMIN_ITEM_LAN_75."<br />";
$sc_style['CONTENTFORM_ICON']['post'] = "</td></tr>";

$sc_style['CONTENTFORM_ATTACH']['pre'] = "<tr><td class='forumheader3' style='width:20%; vertical-align:top;'>".CONTENT_ADMIN_ITEM_LAN_24."</td><td class='forumheader3'>".CONTENT_ADMIN_ITEM_LAN_76."<br />";
$sc_style['CONTENTFORM_ATTACH']['post'] = "</td></tr>";

$sc_style['CONTENTFORM_IMAGES']['pre'] = "<tr><td class='forumheader3' style='width:20%; vertical-align:top;'>".CONTENT_ADMIN_ITEM_LAN_31."</td><td class='forumheader3'>".CONTENT_ADMIN_ITEM_LAN_77."<br />";
$sc_style['CONTENTFORM_IMAGES']['post'] = "</td></tr>";

$sc_style['CONTENTFORM_COMMENT']['pre'] = "<tr><td class='forumheader3' style='width:20%; vertical-align:top;'>".CONTENT_ADMIN_ITEM_LAN_36."</td><td class='forumheader3'>";
$sc_style['CONTENTFORM_COMMENT']['post'] = "</td></tr>";

$sc_style['CONTENTFORM_RATING']['pre'] = "<tr><td class='forumheader3' style='width:20%; vertical-align:top;'>".CONTENT_ADMIN_ITEM_LAN_37."</td><td class='forumheader3'>";
$sc_style['CONTENTFORM_RATING']['post'] = "</td></tr>";

$sc_style['CONTENTFORM_PEICON']['pre'] = "<tr><td class='forumheader3' style='width:20%; vertical-align:top;'>".CONTENT_ADMIN_ITEM_LAN_38."</td><td class='forumheader3'>";
$sc_style['CONTENTFORM_PEICON']['post'] = "</td></tr>";

$sc_style['CONTENTFORM_VISIBILITY']['pre'] = "<tr><td class='forumheader3' style='width:20%; vertical-align:top;'>".CONTENT_ADMIN_ITEM_LAN_39."</td><td class='forumheader3'>";
$sc_style['CONTENTFORM_VISIBILITY']['post'] = "</td></tr>";

$sc_style['CONTENTFORM_SCORE']['pre'] = "<tr><td class='forumheader3' style='width:20%; vertical-align:top;'>".CONTENT_ADMIN_ITEM_LAN_40."</td><td class='forumheader3'>";
$sc_style['CONTENTFORM_SCORE']['post'] = "</td></tr>";

$sc_style['CONTENTFORM_META']['pre'] = "<tr><td class='forumheader3' style='width:20%; vertical-align:top;'>".CONTENT_ADMIN_ITEM_LAN_53."</td><td class='forumheader3'>".CONTENT_ADMIN_ITEM_LAN_70."<br />";
$sc_style['CONTENTFORM_META']['post'] = "</td></tr>";

$sc_style['CONTENTFORM_LAYOUT']['pre'] = "<tr><td class='forumheader3' style='width:20%; vertical-align:top;'>".CONTENT_ADMIN_ITEM_LAN_92."</td><td class='forumheader3'>";
$sc_style['CONTENTFORM_LAYOUT']['post'] = "</td></tr>";

$sc_style['CONTENTFORM_CUSTOM']['pre'] = "<tr><td class='forumheader3' style='width:20%; vertical-align:top;'>".CONTENT_ADMIN_ITEM_LAN_54."</td><td class='forumheader3'>".CONTENT_ADMIN_ITEM_LAN_84."<br />".CONTENT_ADMIN_ITEM_LAN_68."<br />";
$sc_style['CONTENTFORM_CUSTOM']['post'] = "</td></tr>";

if(!isset($CONTENT_ADMIN_CONTENT_CREATE)){
	$CONTENT_ADMIN_CONTENT_CREATE = "
	<div style='text-align:center;'>
	".$rs -> form_open("post", e_SELF."?".e_QUERY, "dataform", "", "enctype='multipart/form-data'")."
	<table style='".ADMIN_WIDTH."' class='fborder'>
	{CONTENTFORM_CATEGORY}
	{CONTENTFORM_HEADING}
	{CONTENTFORM_SUBHEADING}
	{CONTENTFORM_SUMMARY}
	{CONTENTFORM_TEXT}
	{CONTENTFORM_AUTHOR}
	{CONTENTFORM_DATESTART}
	{CONTENTFORM_DATEEND}
	{CONTENTFORM_UPLOAD}
	{CONTENTFORM_ICON}
	{CONTENTFORM_ATTACH}
	{CONTENTFORM_IMAGES}
	{CONTENTFORM_COMMENT}
	{CONTENTFORM_RATING}
	{CONTENTFORM_PEICON}
	{CONTENTFORM_VISIBILITY}
	{CONTENTFORM_SCORE}
	{CONTENTFORM_META}
	{CONTENTFORM_LAYOUT}
	{CONTENTFORM_CUSTOM}
	{CONTENTFORM_PRESET}
	{CONTENTFORM_HOOK}
	<tr><td class='forumheader' style='text-align:center' colspan='2'>{CONTENT_ADMIN_BUTTON}</td></tr>
	</table>
	</form>
	</div>";
}

//hooks
if(!isset($CONTENT_ADMIN_CONTENT_CREATE_HOOKSTART))
{
	$CONTENT_ADMIN_CONTENT_CREATE_HOOKSTART = "<tr><td class='fcaption' colspan='2' >".LAN_HOOKS." </td></tr>";
}
if(!isset($CONTENT_ADMIN_CONTENT_CREATE_HOOKITEM))
{
	$CONTENT_ADMIN_CONTENT_CREATE_HOOKITEM = "
	<tr>
	<td style='width:30%; vertical-align:top;' class='forumheader3'>{HOOKCAPTION}</td>
	<td style='width:70%' class='forumheader3'>{HOOKTEXT}</td>
	</tr>";
}

//custom tags
if(!isset($CONTENT_ADMIN_CONTENT_CREATE_CUSTOMSTART)){
	$CONTENT_ADMIN_CONTENT_CREATE_CUSTOMSTART = "<table style='width:100%; border:0;'>";
}
if(!isset($CONTENT_ADMIN_CONTENT_CREATE_CUSTOMTABLE)){
	$CONTENT_ADMIN_CONTENT_CREATE_CUSTOMTABLE = "
	<tr>
		<td class='forumheader3' style='border:0;'>{CONTENTFORM_CUSTOM_KEY}</td>
		<td class='forumheader3' style='border:0;'>{CONTENTFORM_CUSTOM_VALUE}</td>
	</tr>";
}
if(!isset($CONTENT_ADMIN_CONTENT_CREATE_CUSTOMEND)){
	$CONTENT_ADMIN_CONTENT_CREATE_CUSTOMEND = "</table>";
}


//preset tags
if(!isset($CONTENT_ADMIN_CONTENT_CREATE_PRESET)){
	$CONTENT_ADMIN_CONTENT_CREATE_PRESET = "
	<tr>
		<td class='forumheader3' style='width:20%; vertical-align:top;'>{CONTENTFORM_PRESET_KEY}</td>
		<td class='forumheader3'>{CONTENTFORM_PRESET_VALUE}</td>
	</tr>";
}

?>