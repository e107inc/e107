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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/content/templates/default/content_cat_template.php,v $
|     $Revision: 1.5 $
|     $Date: 2005-05-03 15:04:20 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

$CONTENT_CAT_TABLE_START = "";
$CONTENT_CAT_TABLE = "";
$CONTENT_CAT_TABLE_END = "";
$CONTENT_CAT_LIST_TABLE = "";
$CONTENT_CAT_LISTSUB_TABLE_START = "";
$CONTENT_CAT_LISTSUB_TABLE = "";
$CONTENT_CAT_LISTSUB_TABLE_END = "";

// ##### CONTENT CAT ----------------------------------------------------------
if(!$CONTENT_CAT_TABLE_START){
				$CONTENT_CAT_TABLE_START = "
				<table class='content_table'>\n";
}
if(!$CONTENT_CAT_TABLE){
				$CONTENT_CAT_TABLE = "
				<tr>
					<td class='content_icon'>{CONTENT_CAT_TABLE_ICON}</td>
					<td>
						<table style='width:100%;'>
							<tr>
								<td class='content_heading'>{CONTENT_CAT_TABLE_HEADING}</td>
								<td class='content_info' style='text-align:right;'>{CONTENT_CAT_TABLE_AMOUNT}</td>
							</tr>
							<tr>
								<td class='content_subheading' colspan='2'>{CONTENT_CAT_TABLE_SUBHEADING}</td>								
							</tr>
							<tr>
								<td class='content_info'>{CONTENT_CAT_TABLE_DATE} / {CONTENT_CAT_TABLE_AUTHORDETAILS} {CONTENT_CAT_TABLE_EPICONS}</td>
								<td class='content_info' style='text-align:right;'>{CONTENT_CAT_TABLE_COMMENT}</td>
							</tr>
							<tr>
								<td class='content_text' colspan='2'>{CONTENT_CAT_TABLE_TEXT}</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr><td class='content_spacer' colspan='2'></td></tr>\n";
}
if(!$CONTENT_CAT_TABLE_END){
				$CONTENT_CAT_TABLE_END = "
				</table>\n";
}
// ##### ----------------------------------------------------------------------

// ##### CONTENT CAT_LIST -----------------------------------------------------
if(!$CONTENT_CAT_LIST_TABLE){
				$CONTENT_CAT_LIST_TABLE = "
				<table class='content_table'>
				<tr>
					<td class='content_icon'>{CONTENT_CAT_LIST_TABLE_ICON}</td>
					<td>
						<table style='width:100%;'>
							<tr>
								<td class='content_heading'>{CONTENT_CAT_LIST_TABLE_HEADING}</td>
								<td class='content_info' style='text-align:right;'>{CONTENT_CAT_LIST_TABLE_AMOUNT}</td>
							</tr>
							<tr>
								<td class='content_subheading' colspan='2'>{CONTENT_CAT_LIST_TABLE_SUBHEADING}</td>								
							</tr>
							<tr>
								<td class='content_info'>{CONTENT_CAT_LIST_TABLE_DATE} / {CONTENT_CAT_LIST_TABLE_AUTHORDETAILS} {CONTENT_CAT_LIST_TABLE_EPICONS}</td>
								<td class='content_info' style='text-align:right;'>{CONTENT_CAT_LIST_TABLE_COMMENT}</td>
							</tr>
							<tr>
								<td class='content_rate' colspan='2'>{CONTENT_CAT_LIST_TABLE_RATING}</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr><td class='content_spacer' colspan='2'></td></tr>
				<tr><td class='content_text' colspan='2'>{CONTENT_CAT_LIST_TABLE_TEXT}</td></tr>
				<tr><td class='content_spacer' colspan='2'></td></tr>
				</table>\n";
}
// ##### ----------------------------------------------------------------------

// ##### CONTENT CAT_LIST SUB -------------------------------------------------
if(!$CONTENT_CAT_LISTSUB_TABLE_START){
				$CONTENT_CAT_LISTSUB_TABLE_START = "
				<table class='content_table'>";
}
if(!$CONTENT_CAT_LISTSUB_TABLE){
				$CONTENT_CAT_LISTSUB_TABLE = "
				<tr>
					<td class='content_icon'>{CONTENT_CAT_LISTSUB_TABLE_ICON}</td>
					<td>
						<table style='width:100%;'>
							<tr>
								<td class='content_heading'>{CONTENT_CAT_LISTSUB_TABLE_HEADING}</td>
								<td class='content_info' style='text-align:right;'>{CONTENT_CAT_LISTSUB_TABLE_AMOUNT}</td>
							</tr>
							<tr><td class='content_subheading' colspan='2'>{CONTENT_CAT_LISTSUB_TABLE_SUBHEADING}</td></tr>
						</table>
					</td>
				</tr>\n";
}
if(!$CONTENT_CAT_LISTSUB_TABLE_END){
				$CONTENT_CAT_LISTSUB_TABLE_END = "
				</table>";
}
// ##### ----------------------------------------------------------------------

?>