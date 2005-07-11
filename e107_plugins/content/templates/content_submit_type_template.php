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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/content/templates/content_submit_type_template.php,v $
|     $Revision: 1.5 $
|     $Date: 2005-07-11 07:47:22 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/
global $sc_style, $content_shortcodes;

$sc_style['CONTENT_SUBMIT_TYPE_TABLE_ICON']['pre'] = "";
$sc_style['CONTENT_SUBMIT_TYPE_TABLE_ICON']['post'] = "";

$sc_style['CONTENT_SUBMIT_TYPE_TABLE_SUBHEADING']['pre'] = "";
$sc_style['CONTENT_SUBMIT_TYPE_TABLE_SUBHEADING']['post'] = "";

// ##### CONTENT SUBMIT TYPE LIST --------------------------------------------------
if(!isset($CONTENT_SUBMIT_TYPE_TABLE_START)){
				$CONTENT_SUBMIT_TYPE_TABLE_START = "
				<table class='fborder' style='width:98%; text-align:left;'>\n";
}
if(!isset($CONTENT_SUBMIT_TYPE_TABLE)){
				$CONTENT_SUBMIT_TYPE_TABLE = "
				<tr>
					<td class='forumheader3' style='width:10%; white-space:nowrap;' rowspan='2'>{CONTENT_SUBMIT_TYPE_TABLE_ICON}</td>
					<td class='forumheader3'>{CONTENT_SUBMIT_TYPE_TABLE_HEADING}</td>
				</tr>
				<tr><td class='forumheader3'>{CONTENT_SUBMIT_TYPE_TABLE_SUBHEADING}<br /></td></tr>\n";
}
if(!isset($CONTENT_SUBMIT_TYPE_TABLE_END)){
				$CONTENT_SUBMIT_TYPE_TABLE_END = "
				</table>";
}
// ##### ----------------------------------------------------------------------

?>