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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/content/templates/default/content_type_template.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-02-03 23:31:40 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

// ##### CONTENT TYPE LIST --------------------------------------------------
if(!$CONTENT_TYPE_TABLE_START){
				$CONTENT_TYPE_TABLE_START = "
				<div style='text-align:center'>
				<table style='width:95%' border='0'>\n";
}
if(!$CONTENT_TYPE_TABLE){
				$CONTENT_TYPE_TABLE = "
				<tr>
					<td>
						<table style='width:100%' border='0'>
							<tr>
								<td style='vertical-align:top; white-space:nowrap; width:50px;'>{CONTENT_TYPE_TABLE_ICON}</td>
								<td style='vertical-align:top; padding-left:10px;'>
									{CONTENT_TYPE_TABLE_HEADING}<br />
									{CONTENT_TYPE_TABLE_SUBHEADING}
								</td>
								<td style='vertical-align:top; white-space:nowrap; text-align:right; width:50px;'>
									{CONTENT_TYPE_TABLE_TOTAL} {CONTENT_TYPE_TABLE_TOTAL_LAN}
								</td>
							</tr>
						</table>
					</td>
				</tr>\n";
}
if(!$CONTENT_TYPE_TABLE_SUBMIT){
				$CONTENT_TYPE_TABLE_SUBMIT = "
				<tr><td><div class='spacer'><br /></div></td></tr>
				<tr>
					<td>
						<table style='width:100%' border='0'>
							<tr>
								<td style='vertical-align:top; white-space:nowrap; width:50px;'>{CONTENT_TYPE_TABLE_SUBMIT_ICON}</td>
								<td style='vertical-align:top; padding-left:10px;'>
									{CONTENT_TYPE_TABLE_SUBMIT_HEADING}<br />
									{CONTENT_TYPE_TABLE_SUBMIT_SUBHEADING}
								</td>
							</tr>
						</table>
					</td>
				</tr>";
}
if(!$CONTENT_TYPE_TABLE_MANAGER){
				$CONTENT_TYPE_TABLE_MANAGER = "
				<tr><td><div class='spacer'><br /></div></td></tr>
				<tr>
					<td>
						<table style='width:100%' border='0'>
							<tr>
								<td style='vertical-align:top; white-space:nowrap; width:50px;'>{CONTENT_TYPE_TABLE_MANAGER_ICON}</td>
								<td style='vertical-align:top; padding-left:10px;'>
									{CONTENT_TYPE_TABLE_MANAGER_HEADING}<br />
									{CONTENT_TYPE_TABLE_MANAGER_SUBHEADING}
								</td>
							</tr>
						</table>
					</td>
				</tr>";
}
if(!$CONTENT_TYPE_TABLE_LINE){
				$CONTENT_TYPE_TABLE_LINE = "
				<tr><td><br /><div class='spacer' style='border-top:1px solid #000;'></div></td></tr>";
}
if(!$CONTENT_TYPE_TABLE_END){
				$CONTENT_TYPE_TABLE_END = "
				</table>
				</div>";
}
// ##### ----------------------------------------------------------------------

?>