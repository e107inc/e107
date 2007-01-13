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
|     $Source: /cvs_backup/e107_0.8/e107_plugins/content/templates/content_admin_template.php,v $
|     $Revision: 1.1 $
|     $Date: 2007-01-13 22:33:03 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

global $sc_style, $content_shortcodes;

$stylespacer = "style='border:0; height:20px;'";

// ##### CONTENT OPTIONS --------------------------------------------------
if(!isset($CONTENT_ADMIN_OPTIONS_START)){
	$CONTENT_ADMIN_OPTIONS_START = "
	<div style='text-align:center'>
	".$rs -> form_open("post", e_SELF."?option", "optionsform","","", "")."
	<table style='".ADMIN_WIDTH."' class='fborder'>
	<tr>
	<td class='fcaption' style='width:5%'>".CONTENT_ADMIN_CAT_LAN_24."</td>
	<td class='fcaption' style='width:5%'>".CONTENT_ADMIN_CAT_LAN_25."</td>
	<td class='fcaption' style='width:15%'>".CONTENT_ADMIN_CAT_LAN_18."</td>
	<td class='fcaption' style='width:65%'>".CONTENT_ADMIN_CAT_LAN_19."</td>
	<td class='fcaption' style='width:10%; text-align:center'>".CONTENT_ADMIN_CAT_LAN_20."</td>
	<td class='fcaption' style='width:10%'>".CONTENT_ADMIN_OPT_LAN_167."</td>
	</tr>
	<tr><td colspan='5' $stylespacer></td></tr>
	<tr>
		<td class='forumheader3' style='width:5%; text-align:left'></td>
		<td class='forumheader3' style='width:5%; text-align:center'></td>
		<td class='forumheader3' style='width:15%'></td>
		<td class='forumheader3' style='width:65%; white-space:nowrap;'>".CONTENT_ADMIN_OPT_LAN_1."</td>
		<td class='forumheader3' style='width:10%; text-align:center; white-space:nowrap;'>
			<a href='".e_SELF."?option.default'>".CONTENT_ICON_OPTIONS."</a>
		</td>
		<td class='forumheader3' style='width:10%'></td>
	</tr>
	<tr><td colspan='6' $stylespacer></td></tr>";
}
if(!isset($CONTENT_ADMIN_OPTIONS_TABLE)){
	$CONTENT_ADMIN_OPTIONS_TABLE = "
	<tr>
		<td class='forumheader3' style='width:5%; text-align:left'>{CONTENT_ID}</td>
		<td class='forumheader3' style='width:5%; text-align:center'>{CONTENT_CAT_ICON}</td>
		<td class='forumheader3' style='width:15%'>{CONTENT_AUTHOR}</td>
		<td class='forumheader3' style='width:65%;'>
			{CONTENT_LINK_CATEGORY} {CONTENT_HEADING} {CONTENT_SUBHEADING}
		</td>
		<td class='forumheader3' style='width:10%; text-align:center; white-space:nowrap;'>
			{CONTENT_LINK_OPTION}
		</td>
		<td class='forumheader3' style='width:10%; text-align:center; white-space:nowrap;'>
			{CONTENT_INHERIT}
		</td>
	</tr>";
}
if(!isset($CONTENT_ADMIN_OPTIONS_END)){
	$CONTENT_ADMIN_OPTIONS_END = "
	<tr>
		<td class='forumheader3' colspan='5'></td>
		<td class='forumheader3'>
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
		<td class='fcaption' style='width:5%'>".CONTENT_ADMIN_CAT_LAN_24."</td>
		<td class='fcaption' style='width:65%'>".CONTENT_ADMIN_CAT_LAN_19."</td>
		<td class='fcaption' style='width:10%; text-align:center'>".CONTENT_ADMIN_CAT_LAN_20."</td>
	</tr>";
}
if(!isset($CONTENT_ADMIN_MANAGER_TABLE)){
	$CONTENT_ADMIN_MANAGER_TABLE = "
	<tr>
		<td class='forumheader3' style='width:5%; text-align:left'>{CONTENT_ID}</td>
		<td class='forumheader3' style='width:65%;'>
			{CONTENT_LINK_CATEGORY} {CONTENT_MANAGER_PRE}{CONTENT_HEADING} {CONTENT_SUBHEADING}
		</td>
		<td class='forumheader3' style='width:10%; text-align:center; white-space:nowrap;'>
			{CONTENT_LINK_MANAGER}
		</td>
	</tr>";
}
if(!isset($CONTENT_ADMIN_MANAGER_END)){
	$CONTENT_ADMIN_MANAGER_END = "
	</table>
	".$rs -> form_close()."
	</div>";
}


// ##### CONTENT MANAGER CATEGORY --------------------------------------------------
if(!isset($CONTENT_ADMIN_MANAGER_CATEGORY)){
	$CONTENT_ADMIN_MANAGER_CATEGORY = "
	<div style='text-align:center'>
	".$rs -> form_open("post", e_SELF."?".e_QUERY, "managerform", "", "enctype='multipart/form-data'")."
	<table class='fborder' style='".ADMIN_WIDTH."'>
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
	<tr>
		<td colspan='2' class='fcaption' style='text-align:center'>
			{CONTENT_ADMIN_BUTTON}
		</td>
	</tr>
	</table>
	</form>
	</div>";
}


// ##### CONTENT CATEGORY --------------------------------------------------
if(!isset($CONTENT_ADMIN_CATEGORY_START)){
	$CONTENT_ADMIN_CATEGORY_START = "
	<div style='text-align:center'>
	".$rs -> form_open("post", e_SELF."?".$qs[0], "catform","","", "")."
	<table style='".ADMIN_WIDTH."' class='fborder'>
	<tr>
	<td class='fcaption' style='width:5%'>".CONTENT_ADMIN_CAT_LAN_24."</td>
	<td class='fcaption' style='width:5%'>".CONTENT_ADMIN_CAT_LAN_25."</td>
	<td class='fcaption' style='width:15%'>".CONTENT_ADMIN_CAT_LAN_18."</td>
	<td class='fcaption' style='width:65%'>".CONTENT_ADMIN_CAT_LAN_19."</td>
	<td class='fcaption' style='width:10%; text-align:center'>".CONTENT_ADMIN_CAT_LAN_20."</td>
	</tr>";
}
if(!isset($CONTENT_ADMIN_CATEGORY_TABLE)){
	$CONTENT_ADMIN_CATEGORY_TABLE = "
	<tr>
	<td class='{CONTENT_ADMIN_HTML_CLASS}' style='width:5%; text-align:left'>{CONTENT_ID}</td>
	<td class='{CONTENT_ADMIN_HTML_CLASS}' style='width:5%; text-align:center'>{CONTENT_CAT_ICON}</td>
	<td class='{CONTENT_ADMIN_HTML_CLASS}' style='width:15%'>{CONTENT_AUTHOR}</td>
	<td class='{CONTENT_ADMIN_HTML_CLASS}' style='width:65%;'>
		 {CONTENT_LINK_CATEGORY} {CONTENT_MANAGER_PRE}{CONTENT_HEADING} {CONTENT_SUBHEADING}
	</td>
	<td class='{CONTENT_ADMIN_HTML_CLASS}' style='width:10%; text-align:left; white-space:nowrap;'>
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
	<td style='width:5%; text-align:center;' class='fcaption'>".CONTENT_ADMIN_ITEM_LAN_8."</td>
	<td style='width:5%; text-align:center;' class='fcaption'>".CONTENT_ADMIN_ITEM_LAN_9."</td>
	<td style='width:15%; text-align:left;' class='fcaption'>".CONTENT_ADMIN_ITEM_LAN_48."</td>
	<td style='width:15%; text-align:left;' class='fcaption'>".CONTENT_ADMIN_ITEM_LAN_10."</td>
	<td style='width:50%; text-align:left;' class='fcaption'>".CONTENT_ADMIN_ITEM_LAN_11."</td>
	<td style='width:10%; text-align:center;' class='fcaption'>".CONTENT_ADMIN_ITEM_LAN_12."</td>
	</tr>";
}
if(!isset($CONTENT_ADMIN_SUBMITTED_TABLE)){
	$CONTENT_ADMIN_SUBMITTED_TABLE = "
	<tr>
	<td class='forumheader3' style='width:5%; text-align:center;'>{CONTENT_ID}</td>
	<td class='forumheader3' style='width:5%; text-align:center;'>{CONTENT_ICON}</td>
	<td class='forumheader3' style='width:15%; text-align:left;'>{CONTENT_ADMIN_CATEGORY}</td>
	<td class='forumheader3' style='width:15%; text-align:left;'>{CONTENT_AUTHOR}</td>
	<td class='forumheader3' style='width:75%; text-align:left;'>
		{CONTENT_HEADING} {CONTENT_SUBHEADING}
	</td>
	<td class='forumheader3' style='width:5%; text-align:center; white-space:nowrap;'>
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
		<td class='fcaption' style='width:5%'>".CONTENT_ADMIN_CAT_LAN_24."</td>
		<td class='fcaption' style='width:5%'>".CONTENT_ADMIN_CAT_LAN_25."</td>
		<td class='fcaption' style='width:15%'>".CONTENT_ADMIN_CAT_LAN_18."</td>
		<td class='fcaption' style='width:50%'>".CONTENT_ADMIN_CAT_LAN_19."</td>
		<td class='fcaption' style='width:5%; text-align:center; white-space:nowrap;'>".CONTENT_ADMIN_ITEM_LAN_58."</td>
		<td class='fcaption' style='width:5%; text-align:center; white-space:nowrap;'>".CONTENT_ADMIN_ITEM_LAN_59."</td>
		<td class='fcaption' style='width:5%; text-align:center; white-space:nowrap;'>".CONTENT_ADMIN_ITEM_LAN_60."</td>
	</tr>";
}
if(!isset($CONTENT_ADMIN_ORDER_TABLE)){
	$CONTENT_ADMIN_ORDER_TABLE = "
	<tr>
		<td class='{CONTENT_ADMIN_HTML_CLASS}' style='width:5%; text-align:left'>{CONTENT_ID}</td>
		<td class='{CONTENT_ADMIN_HTML_CLASS}' style='width:5%; text-align:center'>{CONTENT_CAT_ICON}</td>
		<td class='{CONTENT_ADMIN_HTML_CLASS}' style='width:15%'>{CONTENT_AUTHOR}</td>
		<td class='{CONTENT_ADMIN_HTML_CLASS}' style='width:50%;'>
			{CONTENT_LINK_CATEGORY}
			{CONTENT_MANAGER_PRE}{CONTENT_HEADING} {CONTENT_SUBHEADING} {CONTENT_ADMIN_ORDER_AMOUNT}
		</td>
		<td class='{CONTENT_ADMIN_HTML_CLASS}' style='width:5%; text-align:left; white-space:nowrap;'>
			{CONTENT_ADMIN_ORDER_CAT}
			{CONTENT_ADMIN_ORDER_CATALL}
		</td>
		<td class='{CONTENT_ADMIN_HTML_CLASS}' style='width:5%; text-align:center; white-space:nowrap;'>
			{CONTENT_ADMIN_ORDER_UPDOWN}
		</td>
		<td class='{CONTENT_ADMIN_HTML_CLASS}' style='width:5%; text-align:center; white-space:nowrap;'>
			{CONTENT_ADMIN_ORDER_SELECT}
		</td>
	</tr>";
}
if(!isset($CONTENT_ADMIN_ORDER_END)){
	$CONTENT_ADMIN_ORDER_END = "
	<tr>
		<td class='fcaption' colspan='5'>&nbsp;</td>
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
	<tr><td class='fcaption' colspan='5'>".CONTENT_ADMIN_MAIN_LAN_2."</td></tr>
	<tr>
		<td class='forumheader' style='width:5%; text-align:center; white-space:nowrap;'>".CONTENT_ADMIN_ITEM_LAN_8."</td>
		<td class='forumheader' style='width:15%; text-align:left;'>".CONTENT_ADMIN_ITEM_LAN_10."</td>
		<td class='forumheader' style='width:70%; text-align:center; white-space:nowrap;'>".CONTENT_ADMIN_ITEM_LAN_11."</td>
		<td class='forumheader' style='width:5%; text-align:center; white-space:nowrap;'>".CONTENT_ADMIN_ITEM_LAN_59."</td>
		<td class='forumheader' style='width:5%; text-align:center; white-space:nowrap;'>".CONTENT_ADMIN_ITEM_LAN_60."</td>
	</tr>";
}
if(!isset($CONTENT_ADMIN_ORDER_CONTENT_TABLE)){
	$CONTENT_ADMIN_ORDER_CONTENT_TABLE = "
	<tr>
		<td class='forumheader3' style='width:5%; text-align:center; white-space:nowrap;'>{CONTENT_ID}</td>
		<td class='forumheader3' style='width:15%; text-align:left; white-space:nowrap;'>
			{CONTENT_AUTHOR}
		</td>
		<td class='forumheader3' style='width:70%; text-align:left;'>
			{CONTENT_LINK} {CONTENT_HEADING} ({CONTENT_ORDER})
		</td>
		<td class='forumheader3' style='width:5%; text-align:center; white-space:nowrap;'>
			{CONTENT_ADMIN_ORDER_UPDOWN}
		</td>
		<td class='forumheader3' style='width:5%; text-align:center; white-space:nowrap;'>
			{CONTENT_ADMIN_ORDER_SELECT}
		</td>
	</tr>";
}
if(!isset($CONTENT_ADMIN_ORDER_CONTENT_END)){
	$CONTENT_ADMIN_ORDER_CONTENT_END = "
	<tr>
		<td class='fcaption' colspan='3'>&nbsp;</td>
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
	<td class='fcaption' style='width:5%; text-align:center;'>".CONTENT_ADMIN_ITEM_LAN_8."</td>
	<td class='fcaption' style='width:5%; text-align:center;'>".CONTENT_ADMIN_ITEM_LAN_9."</td>
	<td class='fcaption' style='width:10%; text-align:left;'>".CONTENT_ADMIN_ITEM_LAN_10."</td>
	<td class='fcaption' style='width:70%; text-align:left;'>".CONTENT_ADMIN_ITEM_LAN_11."</td>
	<td class='fcaption' style='width:10%; text-align:center;'>".CONTENT_ADMIN_ITEM_LAN_12."</td>
	</tr>";
}
if(!isset($CONTENT_ADMIN_CONTENT_LIST_TABLE)){
	$CONTENT_ADMIN_CONTENT_LIST_TABLE = "
	<tr>
	<td class='forumheader3' style='width:5%; text-align:center'>{CONTENT_ID}</td>
	<td class='forumheader3' style='width:5%; text-align:center'>{CONTENT_ICON}</td>
	<td class='forumheader3' style='width:10%; text-align:left'>{CONTENT_AUTHOR}</td>
	<td class='forumheader3' style='width:70%; text-align:left;'>
		{CONTENT_LINK_ITEM} {CONTENT_HEADING} {CONTENT_SUBHEADING}</td>
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
	<tr><td class='forumheader' style='text-align:center' colspan='2'>{CONTENT_ADMIN_BUTTON}</td></tr>
	</table>
	</form>
	</div>";
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