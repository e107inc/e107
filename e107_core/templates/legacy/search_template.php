<?php

if (!defined('e107_INIT')) { exit; }

if (!defined("USER_WIDTH")){ define("USER_WIDTH","width:95%"); }

$SEARCH_TEMPLATE['shortcode'] = "<input class='tbox form-control search' type='text' name='q' size='20' value='' maxlength='50' />
	<input class='btn btn-default btn-secondary button search' type='submit' name='s' value=\"".LAN_SEARCH."\" />";

$SEARCH_TEMPLATE['form']['start'] = "<div>
	<form id='searchform' method='get' action='{SEARCH_FORM_URL}'>
	<table style='".USER_WIDTH."' class='table fborder'><tr>
	<td class='forumheader3' style='width: 40%'>".LAN_199."</td>
	<td class='forumheader3' style='width: 60%; white-space: nowrap'>
	{SEARCH_MAIN_SEARCHFIELD}&nbsp;{SEARCH_MAIN_SUBMIT}&nbsp;{ENHANCED_ICON}
	</td>
	</tr>{SEARCH_ENHANCED}";

$SEARCH_TEMPLATE['form']['enhanced'] = "<tr id='{ENHANCED_DISPLAY_ID}' {ENHANCED_DISPLAY}>
	<td class='forumheader3' style='width: 40%; white-space: nowrap'>{ENHANCED_TEXT}</td>
	<td class='forumheader3' style='width: 60%; white-space: nowrap'>
	{ENHANCED_FIELD}
	</td>
	</tr>";

$SEARCH_TEMPLATE['form']['category'] = "<tr>
	<td style='width:30%' class='forumheader3'>".LAN_SEARCH_19."<br />
	{SEARCH_MAIN_CHECKALL} {SEARCH_MAIN_UNCHECKALL}
	</td>
	<td style='width:70%' class='forumheader3'>
	{SEARCH_MAIN_CHECKBOXES}{SEARCH_DROPDOWN}&nbsp;<table>{SEARCH_ADVANCED}</table>
	<br />
	</td>
	</tr>";

$SEARCH_TEMPLATE['form']['type'] = "<tr id='advanced_type' {SEARCH_TYPE_DISPLAY}>
	<td style='width:30%' class='forumheader3'>".LAN_SEARCH_75.":</td>
	<td style='width:70%' class='forumheader3'>
	{SEARCH_TYPE_SEL}
	<br />
	</td>
	</tr>";

$SEARCH_TEMPLATE['form']['advanced'] = "<tr>
	<td class='forumheader3'>
	{SEARCH_ADV_A}
	</td>
	<td class='forumheader3'>
	{SEARCH_ADV_B}
	</td>
	</tr>";

$SEARCH_TEMPLATE['form']['advanced-combo'] = "<tr>
	<td class='forumheader3' colspan='2'>
	{SEARCH_ADV_TEXT}
	</td>
	</tr>";

$SEARCH_TEMPLATE['form']['message'] = "<tr>
	<td class='forumheader3' style='text-align: center' colspan='2'>
	{SEARCH_MESSAGE}
	</td>
	</tr>";

$SEARCH_TEMPLATE['form']['end'] = "<tr style='display: none !important; display: visible'>
	<td style='display: none' colspan='2'></td>
	</tr></table>
	</form>
	</div>";
