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
|     $Revision: 1.4 $
|     $Date: 2005-02-13 19:17:24 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

// ##### CONTENT RECENT LIST --------------------------------------------------
$CONTENT_RECENT_TABLE_START = "";
$CONTENT_RECENT_TABLE_END = "";
if(!$CONTENT_RECENT_TABLE_START){
				$CONTENT_RECENT_TABLE_START = "
				<div style='text-align:center'>
				<table style='width:95%' border='0'>\n";
}
if(!$CONTENT_RECENT_TABLE){
				$CONTENT_RECENT_TABLE = "
				<tr>
					<td style='width:1%; white-space:nowrap; vertical-align:top; padding:0; '>
						{CONTENT_RECENT_TABLE_ICON}
					</td>
					<td style='width:99%; vertical-align:top; padding-top:2px; padding:0;'>
						<table style='width:100%; vertical-align:top;' border='0'>
						<tr>
							<td style='text-align:left; padding:2px; vertical-align:top; border:0;'>
								{CONTENT_RECENT_TABLE_HEADING}
							</td>
							<td style='text-align:right; padding:2px; vertical-align:top; border:0;'>
								
								<span class='smalltext'>{CONTENT_RECENT_TABLE_EPICONS} {CONTENT_RECENT_TABLE_AUTHORDETAILS} ".CONTENT_LAN_44." {CONTENT_RECENT_TABLE_REFER}</span>
							</td>
						</tr>
						<tr>
							<td style='text-align:left; padding:2px; vertical-align:top; border:0;'>
								<i>{CONTENT_RECENT_TABLE_SUBHEADING}</i>
							</td>
							<td style='text-align:right; padding:2px; vertical-align:top; border:0;'>
								<span class='smalltext'>{CONTENT_RECENT_TABLE_DATE}</span>
							</td>
						</tr>
						<tr>
							<td colspan='2' style='padding:2px; vertical-align:top; border:0;'>
								<span class='smalltext'>{CONTENT_RECENT_TABLE_RATING}</span>
							</td>
						</tr>
						<tr>
							<td colspan='2' style='padding:2px; vertical-align:middle; border:0;'>
								<span class='smalltext' style='vertical-align:middle;'>{CONTENT_RECENT_TABLE_PARENT}</span>
							</td>
						</tr>
						<tr>
							<td colspan='2' style='padding:2px; vertical-align:middle; border:0;'>
								<span class='smalltext' style='vertical-align:middle;'>{CONTENT_RECENT_TABLE_SUMMARY}</span>
							</td>
						</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td colspan='2'>
						<div class='spacer'><br /><br /></div>
					</td>
				</tr>";
}
if(!$CONTENT_RECENT_TABLE_END){
				$CONTENT_RECENT_TABLE_END = "
				</table>
				</div>\n";
}
// ##### ----------------------------------------------------------------------

?>