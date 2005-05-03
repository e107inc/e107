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
|     $Revision: 1.8 $
|     $Date: 2005-05-03 15:04:20 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

// ##### CONTENT RECENT LIST --------------------------------------------------
$CONTENT_RECENT_TABLE_START = "";
$CONTENT_RECENT_TABLE_END = "";
if(!$CONTENT_RECENT_TABLE_START){
				$CONTENT_RECENT_TABLE_START = "<table class='content_table'>";
}
if(!$CONTENT_RECENT_TABLE){
				$CONTENT_RECENT_TABLE = "
				<tr>
					<td class='content_icon'>{CONTENT_RECENT_TABLE_ICON}</td>
					<td>
						<table>
							<tr><td class='content_heading'>{CONTENT_RECENT_TABLE_HEADING}</td></tr>
							<tr><td class='content_subheading'>{CONTENT_RECENT_TABLE_SUBHEADING}</td></tr>
							<tr><td class='content_summary'>{CONTENT_RECENT_TABLE_SUMMARY}</td></tr>
							<tr><td class='content_info'>{CONTENT_RECENT_TABLE_DATE} {CONTENT_RECENT_TABLE_EPICONS} {CONTENT_RECENT_TABLE_AUTHORDETAILS} ".CONTENT_LAN_44." {CONTENT_RECENT_TABLE_REFER}</td></tr>
							<tr><td class='content_rating'>{CONTENT_RECENT_TABLE_RATING}</td></tr>
							<tr><td class='content_parent'>{CONTENT_RECENT_TABLE_PARENT}</td></tr>
						</table>
					</td>
				</tr>
				<tr><td class='content_spacer' colspan='2'></td></tr>\n";
}
if(!$CONTENT_RECENT_TABLE_END){
				$CONTENT_RECENT_TABLE_END = "</table>";

}
// ##### ----------------------------------------------------------------------

?>