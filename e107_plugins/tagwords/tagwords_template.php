<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Tagwords Template
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/tagwords/tagwords_template.php,v $
 * $Revision$
 * $Date$
 * $Author$
 *
*/

if (!defined('e107_INIT')) { exit; }

global $sc_style, $tagwords_shortcodes;
if(!defined("USER_WIDTH")){ define("USER_WIDTH","width:97%"); }

//##### form element ------------------------------------------------

$TEMPLATE_TAGWORDS['caption'] = LAN_TAG_1."<br /><span class='smalltext'>(".LAN_TAG_21.")</span>";
$TEMPLATE_TAGWORDS['form'] = "<textarea class='tbox' cols='".TAG_TEXTAREA_COLS."' rows='".TAG_TEXTAREA_ROWS."' id='tagwords' name='tagwords'>{TAG_WORD|form}</textarea><br />";

//##### intro in the 'related content' list -------------------------

$sc_style['TAG_SEARCH|search']['pre'] = "<div style='float:right; margin-bottom:5px; margin-left:5px;'>";
$sc_style['TAG_SEARCH|search']['post'] = "</div>";

$sc_style['TAG_LINK|home']['pre'] = "";
$sc_style['TAG_LINK|home']['post'] = "<br /><br />";

$TEMPLATE_TAGWORDS['intro'] = "
{TAG_LINK|home}
{TAG_SEARCH|search}
<div>
	{TAG_WORD|result}<br />
	<br />
</div>";

//##### area heading in the 'related content' list ------------------

$TEMPLATE_TAGWORDS['area'] = "<h2>{TAG_AREA_HEADING}</h2>";

//##### link to an item present in the 'related content' list -------

$TEMPLATE_TAGWORDS['link_start'] = "<div><ul>";
$TEMPLATE_TAGWORDS['link_item'] = "<li>{TAG_LINK}</li>";
$TEMPLATE_TAGWORDS['link_end'] = "</ul></div>";

//##### options --------------------------------------------------

$TEMPLATE_TAGWORDS['options'] = "
<form id='plugin-tagwords-form' method='get' action='{TAG_URL}'>
	{TAG_SEARCH}
</form>
<form id='dataform' name='dataform' method='get' action='{TAG_URL}'>
	<div style='line-height:150%; padding-bottom:10px;'>
		{TAG_TYPE} {TAG_SORT} {TAG_AREA} {TAG_BUTTON}
	</div>
</form>";

//##### cloud -------------------------------------------------------

$sc_style['TAG_NUMBER']['pre'] = "&nbsp;(";
$sc_style['TAG_NUMBER']['post'] = ")";

$TEMPLATE_TAGWORDS['cloud_start'] = "";
$TEMPLATE_TAGWORDS['cloud_item'] = " {TAG_WORD}{TAG_NUMBER}";
$TEMPLATE_TAGWORDS['cloud_end'] = "";

$TEMPLATE_TAGWORDS['cloud'] = "
{TAG_OPTIONS}
<div class='tagwords' style='text-align:center;'>
	<div style='text-align:justify; width:85%; line-height:250%; margin:0 auto;'>
		{TAG_CLOUD}
	</div>
</div>";

//##### cloud list --------------------------------------------------

$sc_style['TAG_NUMBER|list']['pre'] = " (";
$sc_style['TAG_NUMBER|list']['post'] = ")";

$TEMPLATE_TAGWORDS['cloudlist_start'] = "<ul>";
$TEMPLATE_TAGWORDS['cloudlist_item'] = "<li>{TAG_WORD} {TAG_NUMBER|list}</li>";
$TEMPLATE_TAGWORDS['cloudlist_end'] = "</ul>";

$TEMPLATE_TAGWORDS['cloudlist'] = "
{TAG_OPTIONS}
<div class='tagwords'>
	{TAG_CLOUD|list}
</div>";

//##### menu cloud --------------------------------------------------

$sc_style['TAG_NUMBER|menu']['pre'] = "&nbsp;(";
$sc_style['TAG_NUMBER|menu']['post'] = ") ";

$sc_style['TAG_SEARCH|menu']['pre'] = "<div style='margin-bottom:5px;'>";
$sc_style['TAG_SEARCH|menu']['post'] = "</div>";

$TEMPLATE_TAGWORDS['menu_cloud_start'] = "";
$TEMPLATE_TAGWORDS['menu_cloud_item'] = " {TAG_WORD|menu}{TAG_NUMBER|menu}";
$TEMPLATE_TAGWORDS['menu_cloud_end'] = "";

$TEMPLATE_TAGWORDS['menu_cloud'] = "
<div class='tagwords' style='text-align:center;'>
	{TAG_SEARCH|menu}
	<div style=' width:90%; line-height:250%; margin:0 auto;'>
		{TAG_CLOUD|menu}
	</div>
	{TAG_LINK|menu}
</div>";

//##### admin -------------------------------------------------------

$TEMPLATE_TAGWORDS['admin_options'] = "
<form method='post' action='".e_SELF.(e_QUERY ? "?".e_QUERY : "")."'>
<table class='table adminform'>
	<colgroup span='2'>
		<col class='col-label' />
		<col class='col-control' />
	</colgroup>
<tr>
	<td colspan='2'>".LAN_TAG_OPT_25."</td>

</tr>
<tr>
	<td>".LAN_TAG_OPT_2."</td>
	<td>{TAG_OPT_MIN}</td>
</tr>
<tr>
	<td>".LAN_TAG_OPT_3."</td>
	<td>{TAG_OPT_CLASS}</td>
</tr>
<tr>
	<td colspan='2'>".LAN_TAG_OPT_16."</td>
</tr>
<tr>
	<td>".LAN_TAG_OPT_4."</td>
	<td>{TAG_OPT_DEFAULT_SORT}</td>
</tr>
<tr>
	<td>".LAN_TAG_OPT_7."</td>
	<td>{TAG_OPT_DEFAULT_STYLE}</td>
</tr>
<tr>
	<td>".LAN_TAG_OPT_26."</td>
	<td>
		{TAG_OPT_VIEW_SORT}<br />
		{TAG_OPT_VIEW_STYLE}<br />
		{TAG_OPT_VIEW_AREA}<br />
		{TAG_OPT_VIEW_SEARCH}<br />
		{TAG_OPT_VIEW_FREQ}<br />
	</td>
</tr>
<tr>
	<td colspan='2'>".LAN_TAG_OPT_17."</td>
</tr>
<tr>
	<td>".LAN_TAG_OPT_18."</td>
	<td>{TAG_OPT_CAPTION|menu}</td>
</tr>
<tr>
	<td>".LAN_TAG_OPT_15."</td>
	<td>{TAG_OPT_MIN|menu}</td>
</tr>
<tr>
	<td>".LAN_TAG_OPT_4."</td>
	<td>{TAG_OPT_DEFAULT_SORT|menu}</td>
</tr>
<tr>
	<td>".LAN_TAG_OPT_26."</td>
	<td>
		{TAG_OPT_VIEW_SEARCH|menu}<br />
		{TAG_OPT_VIEW_FREQ|menu}<br />
	</td>
</tr>
<tr>
	<td colspan='2'>".LAN_TAG_OPT_21."</td>
</tr>
<tr>
	<td>".LAN_TAG_OPT_22."</td>
	<td>{TAG_OPT_SEPERATOR}</td>
</tr>
<tr>
	<td colspan='2'>".LAN_TAG_OPT_23."</td>
</tr>
<tr>
	<td>".LAN_TAG_OPT_24."</td>
	<td>{TAG_OPT_ACTIVEAREAS}</td>
</tr>
</table>
<div class='buttons-bar center'>
	{TAG_OPT_BUTTON}
</div>
</table>
</form>";

?>