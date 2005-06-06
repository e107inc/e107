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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/content/templates/default/content_recent_template.php,v $
|     $Revision: 1.13 $
|     $Date: 2005-06-06 13:28:15 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

// ##### CONTENT RECENT LIST --------------------------------------------------
$CONTENT_RECENT_TABLE_START = "";
$CONTENT_RECENT_TABLE_END = "";

global $sc_style, $content_shortcodes, $qs, $content_pref, $aa, $prefetchbreadcrumb, $row, $gen, $rater, $plugintable, $crumb;

$sc_style['CONTENT_RECENT_TABLE_ICON']['pre'] = "<tr><td class='content_icon' rowspan='3' style='padding-right:10px;'>";
$sc_style['CONTENT_RECENT_TABLE_ICON']['post'] = "</td></tr>";

$sc_style['CONTENT_RECENT_TABLE_DATE']['pre'] = CONTENT_LAN_10." ";
$sc_style['CONTENT_RECENT_TABLE_DATE']['post'] = "";

$sc_style['CONTENT_RECENT_TABLE_PARENT']['pre'] = CONTENT_LAN_9." ";
$sc_style['CONTENT_RECENT_TABLE_PARENT']['post'] = "";

$sc_style['CONTENT_RECENT_TABLE_REFER']['pre'] = CONTENT_LAN_44." ";
$sc_style['CONTENT_RECENT_TABLE_REFER']['post'] = " ";

$sc_style['CONTENT_RECENT_TABLE_AUTHORDETAILS']['pre'] = CONTENT_LAN_11." ";
$sc_style['CONTENT_RECENT_TABLE_AUTHORDETAILS']['post'] = "";

$sc_style['CONTENT_RECENT_TABLE_SUBHEADING']['pre'] = "<tr><td class='content_subheading' colspan='2' style='padding-bottom:5px;'>";
$sc_style['CONTENT_RECENT_TABLE_SUBHEADING']['post'] = "</td></tr>";

$sc_style['CONTENT_RECENT_TABLE_SUMMARY']['pre'] = "<tr><td class='content_summary' style='height:1%;'>";
$sc_style['CONTENT_RECENT_TABLE_SUMMARY']['post'] = "</td></tr>";

$sc_style['CONTENT_RECENT_TABLE_TEXT']['pre'] = "<tr><td class='content_text'>";
$sc_style['CONTENT_RECENT_TABLE_TEXT']['post'] = "</td></tr>";

$sc_style['CONTENT_RECENT_TABLE_RATING']['pre'] = "<tr><td colspan='2' class='content_rating' style='padding-top:5px; height:1%;'>";
$sc_style['CONTENT_RECENT_TABLE_RATING']['post'] = "</td></tr>";

if(!$CONTENT_RECENT_TABLE_START){
				$CONTENT_RECENT_TABLE_START = "";
}
if(!$CONTENT_RECENT_TABLE){
				$CONTENT_RECENT_TABLE = "
				<table class='content_table'>
					<tr>
					<td class='content_heading'>{CONTENT_RECENT_TABLE_HEADING}</td>
					<td class='content_info' style='padding-bottom:5px; text-align:right;'>{CONTENT_RECENT_TABLE_REFER}</td>
				</tr>
				{CONTENT_RECENT_TABLE_SUBHEADING}
				<tr>
					<td class='content_info' colspan='2' style='padding-bottom:5px;'>{CONTENT_RECENT_TABLE_DATE} {CONTENT_RECENT_TABLE_PARENT} {CONTENT_RECENT_TABLE_AUTHORDETAILS} {CONTENT_RECENT_TABLE_EPICONS} {CONTENT_RECENT_TABLE_EDITICON} </td>
				</tr>				
				
				<tr>
					<td colspan='2' >
						<table style='width:100%;' cellpadding='0' cellspacing='0' border='0'>
							{CONTENT_RECENT_TABLE_ICON}								
							{CONTENT_RECENT_TABLE_SUMMARY}
							{CONTENT_RECENT_TABLE_TEXT}
							{CONTENT_RECENT_TABLE_RATING}
						</table>
					</td>
				</tr>
				<tr><td class='content_spacer' colspan='2' ></td></tr>
				</table>\n";
}
if(!$CONTENT_RECENT_TABLE_END){
				$CONTENT_RECENT_TABLE_END = "";

}
// ##### ----------------------------------------------------------------------

?>