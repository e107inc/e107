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
|     $Revision: 1.1 $
|     $Date: 2005-05-03 15:04:23 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

// ##### CONTENT SUBMIT TYPE LIST --------------------------------------------------
if(!$CONTENT_SUBMIT_TYPE_TABLE_START){
				$CONTENT_SUBMIT_TYPE_TABLE_START = "
				<table class='content_table'>\n";
}
if(!$CONTENT_SUBMIT_TYPE_TABLE){
				$CONTENT_SUBMIT_TYPE_TABLE = "
				<tr>
					<td class='content_icon'>{CONTENT_SUBMIT_TYPE_TABLE_ICON}</td>
					<td>
						<table style='width:100%;'>
							<tr><td class='content_heading'>{CONTENT_SUBMIT_TYPE_TABLE_HEADING}</td></tr>
							<tr><td class='content_subheading'>{CONTENT_SUBMIT_TYPE_TABLE_SUBHEADING}</td></tr>
						</table>
					</td>
				</tr>
				<tr><td class='content_spacer'></td></tr>\n";
}
if(!$CONTENT_SUBMIT_TYPE_TABLE_END){
				$CONTENT_SUBMIT_TYPE_TABLE_END = "
				</table>";
}
// ##### ----------------------------------------------------------------------

?>