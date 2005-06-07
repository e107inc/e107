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
|     $Revision: 1.14 $
|     $Date: 2005-06-07 19:37:24 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

// ##### CONTENT RECENT LIST --------------------------------------------------
$CONTENT_RECENT_TABLE_START = "";
$CONTENT_RECENT_TABLE_END = "";

global $sc_style, $content_shortcodes, $qs, $content_pref, $aa, $prefetchbreadcrumb, $row, $gen, $rater, $plugintable, $crumb;

$sc_style['CONTENT_RECENT_TABLE_ICON']['pre'] = "<td class='forumheader3' rowspan='7' style='vertical-align:top; width:10%; white-space:nowrap; padding-right:10px;'>";
$sc_style['CONTENT_RECENT_TABLE_ICON']['post'] = "</td>";

$sc_style['CONTENT_RECENT_TABLE_DATE']['pre'] = CONTENT_LAN_10." ";
$sc_style['CONTENT_RECENT_TABLE_DATE']['post'] = "";

$sc_style['CONTENT_RECENT_TABLE_PARENT']['pre'] = CONTENT_LAN_9." ";
$sc_style['CONTENT_RECENT_TABLE_PARENT']['post'] = "";

$sc_style['CONTENT_RECENT_TABLE_REFER']['pre'] = " ".CONTENT_LAN_44;
$sc_style['CONTENT_RECENT_TABLE_REFER']['post'] = "";

$sc_style['CONTENT_RECENT_TABLE_AUTHORDETAILS']['pre'] = CONTENT_LAN_11." ";
$sc_style['CONTENT_RECENT_TABLE_AUTHORDETAILS']['post'] = "";

$sc_style['CONTENT_RECENT_TABLE_SUBHEADING']['pre'] = "<tr><td class='forumheader3'>";
$sc_style['CONTENT_RECENT_TABLE_SUBHEADING']['post'] = "</td></tr>";

$sc_style['CONTENT_RECENT_TABLE_SUMMARY']['pre'] = "<tr><td class='forumheader3'>";
$sc_style['CONTENT_RECENT_TABLE_SUMMARY']['post'] = "</td></tr>";

$sc_style['CONTENT_RECENT_TABLE_TEXT']['pre'] = "<tr><td class='forumheader3'>";
$sc_style['CONTENT_RECENT_TABLE_TEXT']['post'] = "</td></tr>";

$sc_style['CONTENT_RECENT_TABLE_RATING']['pre'] = "<tr><td class='forumheader3'>";
$sc_style['CONTENT_RECENT_TABLE_RATING']['post'] = "</td></tr>";

if(!$CONTENT_RECENT_TABLE_START){
				$CONTENT_RECENT_TABLE_START = "";
}
if(!$CONTENT_RECENT_TABLE){
				$CONTENT_RECENT_TABLE = "
				<table class='fborder' style='width:98%; text-align:left;'>
					<tr>
						{CONTENT_RECENT_TABLE_ICON}
						<td class='fcaption'>{CONTENT_RECENT_TABLE_HEADING} {CONTENT_RECENT_TABLE_REFER}</td>
					</tr>
					{CONTENT_RECENT_TABLE_SUBHEADING}
					<tr><td class='forumheader3'>{CONTENT_RECENT_TABLE_DATE} {CONTENT_RECENT_TABLE_PARENT} {CONTENT_RECENT_TABLE_AUTHORDETAILS} {CONTENT_RECENT_TABLE_EPICONS} {CONTENT_RECENT_TABLE_EDITICON}</td></tr>			
					{CONTENT_RECENT_TABLE_SUMMARY}
					{CONTENT_RECENT_TABLE_TEXT}
					{CONTENT_RECENT_TABLE_RATING}
				</table><br />\n";
}
if(!$CONTENT_RECENT_TABLE_END){
				$CONTENT_RECENT_TABLE_END = "";

}
// ##### ----------------------------------------------------------------------

?>