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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/content/templates/default/content_top_template.php,v $
|     $Revision: 1.3 $
|     $Date: 2005-05-03 15:04:22 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

$CONTENT_TOP_TABLE_START = "";
$CONTENT_TOP_TABLE_END = "";
// ##### CONTENT TOP --------------------------------------------------
if(!$CONTENT_TOP_TABLE_START){
				$CONTENT_TOP_TABLE_START = "
				<table class='content_table'>";
}
if(!$CONTENT_TOP_TABLE){
				$CONTENT_TOP_TABLE = "
					<tr>
						<td class='content_icon'>{CONTENT_TOP_TABLE_ICON}</td>
						<td>
							<table style='width:100%;'>
								<tr><td class='content_heading'>{CONTENT_TOP_TABLE_HEADING}</td></tr>
								<tr><td class='content_info'>{CONTENT_TOP_TABLE_AUTHOR}</td></tr>
								<tr><td class='content_rating' style='width:100%; text-align:right;'>{CONTENT_TOP_TABLE_RATING}</td></tr>
							</table>
						</td>
					</tr>
					<tr><td class='content_spacer' colspan='2'></td></tr>
				\n";

}
if(!$CONTENT_TOP_TABLE_END){
				$CONTENT_TOP_TABLE_END = "
				</table>";
}
// ##### ----------------------------------------------------------------------

?>